<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
     
    public function showForm(): View
    {
        return view('auth.login');
    }

     
    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
        }

        $request->session()->regenerate();
        $user = Auth::user();

        $route = match ($user->role) {
            'pengelola_operator' => 'pengelola-operator.dashboard',
            'operator' => 'operator.dashboard',
            'pengajar' => 'pengajar.dashboard',
            'pelajar' => 'pelajar.dashboard',
            default => 'home',
        };

        if (in_array($user->role, ['pengajar', 'pelajar']) && $user->status === 'pending') {
            return redirect()->route('pending');
        }

        return redirect()->intended(route($route));
    }

     
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}

