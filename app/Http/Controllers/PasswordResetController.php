<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordResetController extends Controller
{
    /**
     * Show the password reset form.
     */
    public function showResetForm($token)
    {
        $user = User::where('reset_token', $token)
            ->where('reset_expires_at', '>', now())
            ->first();

        if (!$user) {
            return redirect('/')->with('error', 'Enlace inválido o expirado.');
        }

        return view('auth.reset-password-token', compact('token'));
    }

    /**
     * Reset the user's password.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'password' => 'required|string|min:4|confirmed',
        ]);

        $user = User::where('reset_token', $request->token)
            ->where('reset_expires_at', '>', now())
            ->first();

        if (!$user) {
            return back()->withErrors(['token' => 'Enlace inválido o expirado.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'reset_token' => null,
            'reset_expires_at' => null,
        ]);

        return redirect('/')->with('success', 'Contraseña actualizada exitosamente. Puedes iniciar sesión ahora.');
    }
}
