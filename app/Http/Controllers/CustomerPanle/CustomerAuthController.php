<?php

namespace App\Http\Controllers\CustomerPanle;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class CustomerAuthController extends Controller
{
    public function loginForm()
    {
        return view('auth.customer_login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'customer_code' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');
        if (Auth::guard('customer')->attempt(['customer_code' => $request->customer_code, 'password' => $request->password], $remember)) {
            $request->session()->regenerate();
            return redirect()->route('customer-panel.dashboard');
        }
        return back()->withErrors(['customer_code' => 'Invalid customer code or password.'])->onlyInput('customer_code');
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('customer-panel.login');
    }
}