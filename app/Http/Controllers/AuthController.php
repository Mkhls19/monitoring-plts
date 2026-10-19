<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SystemLog;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('pages.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'name' => ['required'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            SystemLog::create([
                'event_type' => 'LOGIN',
                'description' => "User '" . Auth::user()->name . "' berhasil login."
            ]);

            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'name' => 'Kredensial yang diberikan tidak cocok dengan data kami.',
        ])->onlyInput('name');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            SystemLog::create([
                'event_type' => 'LOGOUT',
                'description' => "User '" . $user->name . "' logout."
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
