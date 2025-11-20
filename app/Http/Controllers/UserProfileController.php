<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UserProfileController extends Controller
{
    public function profilePicture($id)
    {
        $user = User::where('azure_id', $id)->orWhere('id', $id)->first();
        
        if (!$user) {
            abort(404);
        }

        // Only allow users to see their own profile picture, or admins
        if ($user->id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        // Try to fetch from Microsoft Graph API using email (works if user has public profile)
        // Note: This may require authentication in some cases
        $identifier = $user->azure_id ?: $user->email;
        
        try {
            $response = Http::timeout(5)->get("https://graph.microsoft.com/v1.0/users/{$identifier}/photo/\$value");
            
            if ($response->successful() && $response->header('Content-Type') && strpos($response->header('Content-Type'), 'image') !== false) {
                return response($response->body())
                    ->header('Content-Type', $response->header('Content-Type'))
                    ->header('Cache-Control', 'public, max-age=3600');
            }
        } catch (\Throwable $th) {
            // Silently fail and use fallback avatar
        }

        // Fallback to initials avatar
        return $this->generateInitialsAvatar($user->name);
    }

    protected function generateInitialsAvatar($name)
    {
        $initials = strtoupper(substr($name, 0, 1));
        $parts = explode(' ', $name);
        if (count($parts) > 1) {
            $initials = strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts) - 1], 0, 1));
        }

        $svg = '<svg width="40" height="40" xmlns="http://www.w3.org/2000/svg">
            <rect width="40" height="40" fill="#4F46E5" rx="20"/>
            <text x="50%" y="50%" font-family="Arial, sans-serif" font-size="14" font-weight="600" fill="white" text-anchor="middle" dominant-baseline="central">' . htmlspecialchars($initials) . '</text>
        </svg>';

        return response($svg)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
