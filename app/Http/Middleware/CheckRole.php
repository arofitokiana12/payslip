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
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        // Vérifie si l'utilisateur est connecté et si son rôle est dans la liste
        if (!$user || !$user->hasRole($roles)) {
            return response()->json(['message' => 'Accès interdit.'], 403);
        }

        return $next($request);
    }
}
