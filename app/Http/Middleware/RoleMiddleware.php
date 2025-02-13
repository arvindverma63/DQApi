<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        try {
            // Get authenticated user
            $user = JWTAuth::parseToken()->authenticate();

            // Check if the user exists and has at least one of the required roles
            if (!$user || !array_intersect($user->roles, $roles)) {
                return response()->json(['message' => 'Unauthorized.'], Response::HTTP_FORBIDDEN);
            }

            return $next($request);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Unauthorized. Invalid token.'], Response::HTTP_UNAUTHORIZED);
        }
    }
}
