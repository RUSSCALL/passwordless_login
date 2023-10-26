<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\View\View;
use App\Notifications\login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use App\Providers\RouteServiceProvider;
use App\Http\Requests\Auth\LoginRequest;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store()
    {
       $validated = request()->validate(['email' => 'required|exists:users,email']);

        $user = User::where(['email' => $validated['email'] ])->first();
        
       $link =  URL::temporarySignedRoute('login.token' , now()->addHour() , ['user' => $user->id ]);

       $user->notify(new login($link));

       return back()->with(['status' => 'please check your email for a login link']);
       
    }


    public function loginviatoken(User $user) 
    {
        Auth::login($user);

        request()->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::HOME);
        
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
