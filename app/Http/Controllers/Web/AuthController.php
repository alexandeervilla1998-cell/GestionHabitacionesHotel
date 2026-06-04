<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('home.home');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'correo'   => 'required|email',
            'password' => 'required',
        ]);

        $credentials = [
            'correo'   => $request->correo,
            'password' => $request->password,
            'activo'   => true,
        ];

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // Verificar que el usuario tenga un rol válido (admin o recepcionista)
            if (!in_array($user->rol, ['admin', 'recepcionista'])) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return back()->withErrors([
                    'correo' => 'Solo administradores y recepcionistas pueden acceder al sistema.',
                ])->withInput();
            }
            
            $request->session()->regenerate();

            return redirect()->intended(route('home.home'));
        }

        return back()->withErrors([
            'correo' => 'Correo o contraseña incorrectos, o usuario inactivo.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
