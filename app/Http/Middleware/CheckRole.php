<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        
        // Flatten and split comma-separated roles
        $allowedRoles = [];
        foreach ($roles as $role) {
            // Split by comma in case roles are passed as "admin,accountant"
            $roleParts = explode(',', $role);
            foreach ($roleParts as $rolePart) {
                $allowedRoles[] = trim($rolePart);
            }
        }
        
        // Check if user has one of the required roles
        if (!in_array($user->role, $allowedRoles)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
