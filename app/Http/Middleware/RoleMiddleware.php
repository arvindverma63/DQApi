<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        // Get authenticated user
        $user = JWTAuth::parseToken()->authenticate();

        // Check if the user exists and has the required role OR is a super admin
        if (!$user || (!$user->hasRole($role) && !$user->hasRole('super'))) {
            return response()->json(['message' => 'Unauthorized.'], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
