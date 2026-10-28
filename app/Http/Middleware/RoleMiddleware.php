<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
     
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (! in_array($user->role, $roles)) {
            $dashboard = match ($user->role) {
                'pengelola_operator' => route('pengelola-operator.dashboard'),
                'operator' => route('operator.dashboard'),
                'pengajar' => route('pengajar.dashboard'),
                'pelajar' => route('pelajar.dashboard'),
                default => route('home'),
            };

            return redirect($dashboard);
        }

        if (in_array($user->role, ['pengajar', 'pelajar']) && $user->status === 'pending') {
            return redirect()->route('pending');
        }

        return $next($request);
    }
}

