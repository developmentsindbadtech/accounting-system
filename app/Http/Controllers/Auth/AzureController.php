<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AzureController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('azure')
            ->scopes(['openid', 'profile', 'email', 'User.Read', 'https://graph.microsoft.com/User.Read'])
            ->redirect();
    }

    public function callback(Request $request)
    {
        // Log incoming callback for debugging
        Log::info('Azure SSO callback received', [
            'has_code' => $request->has('code'),
            'has_state' => $request->has('state'),
            'has_error' => $request->has('error'),
            'error' => $request->get('error'),
            'error_description' => $request->get('error_description'),
        ]);

        // Check if Microsoft returned an error
        if ($request->has('error')) {
            Log::error('Azure AD returned error', [
                'error' => $request->get('error'),
                'error_description' => $request->get('error_description'),
            ]);
            return redirect()->route('login')->withErrors([
                'email' => 'Microsoft login failed: ' . $request->get('error_description', 'Unknown error'),
            ]);
        }

        try {
            $azureUser = Socialite::driver('azure')->stateless()->user();
            
            Log::info('Azure user retrieved successfully', [
                'email' => $azureUser->getEmail(),
                'name' => $azureUser->getName(),
                'id' => $azureUser->getId(),
            ]);
        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            Log::error('Azure AD invalid state exception', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('login')->withErrors([
                'email' => 'Session expired. Please try signing in again.',
            ]);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            Log::error('Azure AD API error', [
                'exception' => $e->getMessage(),
                'response' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'No response',
            ]);
            return redirect()->route('login')->withErrors([
                'email' => 'Unable to communicate with Microsoft. Please try again.',
            ]);
        } catch (\Throwable $th) {
            Log::error('Azure AD callback error', [
                'exception' => $th->getMessage(),
                'type' => get_class($th),
                'trace' => $th->getTraceAsString(),
            ]);
            return redirect()->route('login')->withErrors([
                'email' => 'Unable to sign in with Microsoft at the moment. Please try again.',
            ]);
        }

        $email = $azureUser->getEmail();

        if (!$email) {
            Log::warning('Azure SSO: No email returned from Microsoft', [
                'azure_user_raw' => $azureUser->getRaw()
            ]);
            return redirect()->route('login')->withErrors([
                'email' => 'Microsoft did not return an email address for your profile.',
            ]);
        }

        $role = $this->determineRoleFromAzure($azureUser, $request);

        $azureUserId = $azureUser->getId() ?? ($azureUser->user['id'] ?? $azureUser->getRaw()['id'] ?? null);
        
        // Store Azure user data for potential use in error handling
        $azureUserName = $azureUser->getName() ?? ($azureUser->user['displayName'] ?? $email);

        try {
            // Ensure tenant exists - create default tenant if none exists
            $tenant = Tenant::query()->first();
            
            if (!$tenant) {
                Log::warning('No tenant found, attempting to create default tenant', [
                    'email' => $email,
                    'azure_id' => $azureUserId
                ]);
                
                try {
                    // Create a default tenant - wrap in try-catch to handle any database errors
                    $tenant = Tenant::create([
                        'name' => env('TENANT_NAME', 'Sindbad Tech'),
                        'domain' => env('APP_URL') ? parse_url(env('APP_URL'), PHP_URL_HOST) : null,
                        'database_name' => env('DB_DATABASE'),
                        'database_host' => env('DB_HOST', '127.0.0.1'),
                        'database_username' => env('DB_USERNAME'),
                        'database_password' => env('DB_PASSWORD'),
                        'is_active' => true,
                    ]);
                    
                    Log::info('Successfully created default tenant for Azure SSO', [
                        'tenant_id' => $tenant->id,
                        'tenant_name' => $tenant->name
                    ]);
                } catch (\Illuminate\Database\QueryException $tenantError) {
                    // If tenant creation fails, try to find it again (race condition - another request might have created it)
                    Log::warning('Failed to create tenant, checking if another request created it', [
                        'error' => $tenantError->getMessage()
                    ]);
                    
                    $tenant = Tenant::query()->first();
                    
                    if (!$tenant) {
                        // If still no tenant, this is a critical error but we'll try to continue with a fallback
                        Log::error('Critical: No tenant exists and cannot create one', [
                            'email' => $email,
                            'error' => $tenantError->getMessage()
                        ]);
                        
                        // Try to use firstOrCreate with minimal fields as last resort
                        try {
                            $tenant = Tenant::firstOrCreate(
                                ['name' => env('TENANT_NAME', 'Sindbad Tech')],
                                [
                                    'domain' => env('APP_URL') ? parse_url(env('APP_URL'), PHP_URL_HOST) : null,
                                    'database_name' => env('DB_DATABASE', 'accounting_central'),
                                    'database_host' => env('DB_HOST', '127.0.0.1'),
                                    'database_username' => env('DB_USERNAME', 'postgres'),
                                    'database_password' => env('DB_PASSWORD', ''),
                                    'is_active' => true,
                                ]
                            );
                            Log::info('Created tenant using firstOrCreate fallback', ['tenant_id' => $tenant->id]);
                        } catch (\Throwable $fallbackError) {
                            Log::error('All tenant creation methods failed', [
                                'email' => $email,
                                'create_error' => $tenantError->getMessage(),
                                'fallback_error' => $fallbackError->getMessage()
                            ]);
                            // Will be caught by outer catch and show generic error
                            throw new \Exception('Unable to initialize system tenant. Please contact support.');
                        }
                    } else {
                        Log::info('Found tenant created by another request', ['tenant_id' => $tenant->id]);
                    }
                }
            }

            $user = User::where('email', $email)->first();

            if (!$user) {
                // Auto-determine role for new users from sindbad.tech domain
                $autoRole = $role;
                
                // If role is viewer (default) but email is from sindbad.tech, check if they should be accountant or admin
                if ($role === 'viewer' && str_ends_with(strtolower($email), '@sindbad.tech')) {
                    // Check if this email matches any pre-configured accounts
                    $knownAccountants = [
                        'revemar.surigao@sindbad.tech',
                        'hazel.bacalso@sindbad.tech',
                        'aziz.alsultan@sindbad.tech',
                        'mohammed.agbawi@sindbad.tech',
                    ];
                    
                    $knownAdmins = [
                        'development@sindbad.tech',
                    ];
                    
                    if (in_array(strtolower($email), $knownAccountants)) {
                        $autoRole = 'accountant';
                    } elseif (in_array(strtolower($email), $knownAdmins)) {
                        $autoRole = 'admin';
                    } else {
                        // Default sindbad.tech users to accountant role
                        $autoRole = 'accountant';
                    }
                }

                Log::info('Creating new user via Azure SSO', [
                    'email' => $email,
                    'role' => $autoRole,
                    'tenant_id' => $tenant->id,
                    'original_role_from_azure' => $role
                ]);

                // Ensure tenant exists before creating user
                if (!$tenant) {
                    throw new \Exception('System tenant not available. Please contact support.');
                }

                $user = User::create([
                    'tenant_id' => $tenant->id,
                    'name' => $azureUser->getName() ?: ($azureUser->user['displayName'] ?? $email),
                    'email' => $email,
                    'password' => Str::random(32),
                    'role' => $autoRole,
                    'is_active' => true,
                    'azure_id' => $azureUserId,
                ]);
            } else {
                $updateData = [];
                
                // Update role if needed, but prefer existing role for existing users
                // Only upgrade roles, never downgrade (preserve accountant/admin if already set)
                if ($role === 'admin' && $user->role !== 'admin') {
                    // Azure says admin, upgrade to admin
                    $updateData['role'] = $role;
                } elseif ($user->role === 'viewer' && $role !== 'viewer') {
                    // Upgrade viewer to accountant/admin if Azure provides it
                    $updateData['role'] = $role;
                } elseif ($user->role === 'accountant' && $role === 'admin') {
                    // Upgrade accountant to admin if Azure says admin
                    $updateData['role'] = $role;
                }
                // If user is already accountant/admin, don't downgrade to viewer
                // This preserves roles set via seeder or manual assignment
                
                if ($user->azure_id !== $azureUserId && $azureUserId) {
                    $updateData['azure_id'] = $azureUserId;
                }
                
                // Ensure user is active
                if (!$user->is_active) {
                    $updateData['is_active'] = true;
                }
                
                if (!empty($updateData)) {
                    $user->update($updateData);
                    Log::info('Updated existing user via Azure SSO', [
                        'email' => $email,
                        'updated_fields' => array_keys($updateData)
                    ]);
                }
            }

            if (!$user->is_active) {
                Log::warning('Inactive user attempted Azure SSO login', ['email' => $email]);
                return redirect()->route('login')->withErrors([
                    'email' => 'Your account is inactive. Please contact an administrator.',
                ]);
            }

            Auth::login($user, true);

            Log::info('User successfully logged in via Azure SSO', ['email' => $email, 'role' => $user->role]);

            return redirect()->intended(route('dashboard'));
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database error during Azure SSO callback', [
                'email' => $email ?? 'unknown',
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Check if it's a duplicate entry error (user might have been created in another request)
            if (str_contains($e->getMessage(), 'duplicate key') || 
                str_contains($e->getMessage(), 'UNIQUE constraint') ||
                str_contains($e->getMessage(), 'duplicate key value')) {
                
                if (isset($email)) {
                    $user = User::where('email', $email)->first();
                    if ($user && $user->is_active) {
                        Auth::login($user, true);
                        Log::info('User logged in after duplicate key error (race condition)', ['email' => $email]);
                        return redirect()->intended(route('dashboard'));
                    }
                }
            }
            
            // For tenant-related errors, try to create tenant via seeder command as fallback
            if (str_contains($e->getMessage(), 'tenant') || str_contains($e->getMessage(), 'foreign key')) {
                Log::warning('Tenant-related database error, attempting automatic fix', ['email' => $email ?? 'unknown']);
                
                // Try to ensure tenant exists one more time
                try {
                    $tenant = Tenant::firstOrCreate(
                        ['name' => env('TENANT_NAME', 'Sindbad Tech')],
                        [
                            'domain' => env('APP_URL') ? parse_url(env('APP_URL'), PHP_URL_HOST) : null,
                            'database_name' => env('DB_DATABASE', 'accounting_central'),
                            'database_host' => env('DB_HOST', '127.0.0.1'),
                            'database_username' => env('DB_USERNAME', 'postgres'),
                            'database_password' => env('DB_PASSWORD', ''),
                            'is_active' => true,
                        ]
                    );
                    
                    // If tenant now exists and we have email, try to login/create user again
                    if (isset($email) && $tenant && isset($azureUser)) {
                        $user = User::where('email', $email)->first();
                        if (!$user) {
                            // Determine role for new user
                            $userRole = 'accountant'; // Default for sindbad.tech users
                            if (str_ends_with(strtolower($email), '@sindbad.tech')) {
                                $knownAccountants = [
                                    'revemar.surigao@sindbad.tech',
                                    'hazel.bacalso@sindbad.tech',
                                    'aziz.alsultan@sindbad.tech',
                                    'mohammed.agbawi@sindbad.tech',
                                ];
                                $knownAdmins = ['development@sindbad.tech'];
                                
                                if (in_array(strtolower($email), $knownAdmins)) {
                                    $userRole = 'admin';
                                } elseif (in_array(strtolower($email), $knownAccountants)) {
                                    $userRole = 'accountant';
                                } else {
                                    $userRole = 'accountant';
                                }
                            }
                            
                            $user = User::create([
                                'tenant_id' => $tenant->id,
                                'name' => isset($azureUserName) ? $azureUserName : $email,
                                'email' => $email,
                                'password' => Str::random(32),
                                'role' => $userRole,
                                'is_active' => true,
                                'azure_id' => $azureUserId ?? null,
                            ]);
                        }
                        
                        if ($user && $user->is_active) {
                            Auth::login($user, true);
                            Log::info('User successfully logged in after automatic tenant fix', ['email' => $email]);
                            return redirect()->intended(route('dashboard'));
                        }
                    }
                } catch (\Throwable $retryError) {
                    Log::error('Automatic tenant fix failed', [
                        'email' => $email ?? 'unknown',
                        'retry_error' => $retryError->getMessage()
                    ]);
                }
            }
            
            return redirect()->route('login')->withErrors([
                'email' => 'Unable to sign in at the moment. Please try again or use email/password login.',
            ]);
        } catch (\Throwable $th) {
            Log::error('Unexpected error during Azure SSO callback', [
                'email' => $email ?? 'unknown',
                'exception' => $th->getMessage(),
                'type' => get_class($th),
                'trace' => $th->getTraceAsString()
            ]);
            
            // Never show "contact admin" - always show friendly message with alternative
            return redirect()->route('login')->withErrors([
                'email' => 'Unable to sign in with Microsoft at the moment. Please try email/password login below or try again later.',
            ]);
        }
    }

    protected function determineRoleFromAzure($azureUser, Request $request = null): string
    {
        // Try multiple ways to access employeeType from OAuth response
        $employeeType = null;
        
        // Method 1: Direct access from user array
        if (isset($azureUser->user['employeeType'])) {
            $employeeType = $azureUser->user['employeeType'];
        }
        // Method 2: From raw response
        elseif (isset($azureUser->getRaw()['employeeType'])) {
            $employeeType = $azureUser->getRaw()['employeeType'];
        }
        // Method 3: Try accessing via attribute method
        elseif (method_exists($azureUser, 'getAttribute') && $azureUser->getAttribute('employeeType')) {
            $employeeType = $azureUser->getAttribute('employeeType');
        }
        // Method 4: Check if it's in the user object directly
        elseif (isset($azureUser->employeeType)) {
            $employeeType = $azureUser->employeeType;
        }

        // If employeeType is not in OAuth response, fetch it from Microsoft Graph API
        if (empty($employeeType)) {
            try {
                // Get access token - try multiple methods
                $accessToken = null;
                
                // Method 1: Direct token property
                if (property_exists($azureUser, 'token') && $azureUser->token) {
                    $accessToken = $azureUser->token;
                }
                // Method 2: Access token from response body
                elseif (property_exists($azureUser, 'accessTokenResponseBody') && isset($azureUser->accessTokenResponseBody['access_token'])) {
                    $accessToken = $azureUser->accessTokenResponseBody['access_token'];
                }
                // Method 3: Try to get token via reflection (Socialite stores it internally)
                else {
                    try {
                        $reflection = new \ReflectionClass($azureUser);
                        if ($reflection->hasProperty('token')) {
                            $tokenProperty = $reflection->getProperty('token');
                            $tokenProperty->setAccessible(true);
                            $accessToken = $tokenProperty->getValue($azureUser);
                        }
                    } catch (\Throwable $e) {
                        // Reflection failed, continue
                    }
                }

                if ($accessToken) {
                    $userId = $azureUser->getId() ?? ($azureUser->user['id'] ?? $azureUser->getRaw()['id'] ?? null);
                    if ($userId) {
                        $employeeType = $this->fetchEmployeeTypeFromGraph($accessToken, $userId);
                    }
                }
            } catch (\Throwable $th) {
                // Silently fail and default to viewer
            }
        }

        // Map Employee Type to role
        if (is_string($employeeType) && !empty($employeeType)) {
            $normalized = strtolower(trim($employeeType));
            
            if ($normalized === 'admin') {
                return 'admin';
            }
            
            if ($normalized === 'accountant') {
                return 'accountant';
            }
        }

        // Default to viewer if no Employee Type or doesn't match
        return 'viewer';
    }

    protected function fetchEmployeeTypeFromGraph(string $accessToken, string $userId): ?string
    {
        try {
            // Explicitly request employeeType field using $select query parameter
            $response = Http::withToken($accessToken)
                ->timeout(5)
                ->get("https://graph.microsoft.com/v1.0/users/{$userId}?\$select=id,employeeType,mail,displayName");

            if ($response->successful()) {
                $userData = $response->json();
                return $userData['employeeType'] ?? null;
            }
            
            return null;
        } catch (\Throwable $th) {
            return null;
        }
    }
}
