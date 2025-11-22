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

        try {
            $user = User::where('email', $email)->first();

            if (!$user) {
                $tenant = Tenant::query()->first();
                
                if (!$tenant) {
                    Log::error('No tenant found in database during Azure SSO signup', [
                        'email' => $email,
                        'azure_id' => $azureUserId
                    ]);
                    return redirect()->route('login')->withErrors([
                        'email' => 'System configuration error. Please contact your administrator.',
                    ]);
                }

                Log::info('Creating new user via Azure SSO', [
                    'email' => $email,
                    'role' => $role,
                    'tenant_id' => $tenant->id
                ]);

                $user = User::create([
                    'tenant_id' => $tenant->id,
                    'name' => $azureUser->getName() ?: ($azureUser->user['displayName'] ?? $email),
                    'email' => $email,
                    'password' => Str::random(32),
                    'role' => $role,
                    'is_active' => true,
                    'azure_id' => $azureUserId,
                ]);
            } else {
                $updateData = [];
                if ($user->role !== $role) {
                    $updateData['role'] = $role;
                }
                if ($user->azure_id !== $azureUserId && $azureUserId) {
                    $updateData['azure_id'] = $azureUserId;
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
                'email' => $email,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            return redirect()->route('login')->withErrors([
                'email' => 'Database error occurred. Please contact your administrator.',
            ]);
        } catch (\Throwable $th) {
            Log::error('Unexpected error during Azure SSO callback', [
                'email' => $email,
                'exception' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);
            return redirect()->route('login')->withErrors([
                'email' => 'An unexpected error occurred. Please try again or contact your administrator.',
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
