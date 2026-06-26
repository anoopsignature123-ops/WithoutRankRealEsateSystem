<?php

namespace App\Http\Controllers\AssociatePanel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssociateAuthController extends Controller
{
    public function loginForm()
    {
        if (Auth::guard('associate')->check()) {
            return redirect()->route('associate-panel.dashboard');
        }

        return view('auth.associate_login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'associate_id' => ['required', 'string', 'max:80'],
            'password' => ['required', 'string', 'max:255'],
        ]);
        $remember = $request->boolean('remember');
        if (Auth::guard('associate')->attempt($credentials, $remember)) {
            // config(['session.cookie' => 'associate_session']);
            $request->session()->regenerate();

            return redirect()->route('associate-panel.dashboard');
        }

        return back()->withErrors([
            'associate_id' => 'Invalid associate ID or password.',
        ])->onlyInput('associate_id');
    }

    public function logout(Request $request)
    {
        // config(['session.cookie' => 'associate_session']);
        Auth::guard('associate')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route(
            'associate-panel.login'
        );
    }
}
