<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetMail;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email'),
            function ($user, $token) {
                $resetUrl = url('http://localhost:4200/modifierMdp?token=' . $token . '&email=' . urlencode($user->email));

                Mail::to($user->email)->send(new PasswordResetMail($resetUrl));
            }
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)],400);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'motDePasse' => 'required|min:6|confirmed',  // 'confirmed' vérifie que 'motDePasse' et 'motDePasse_confirmation' sont les mêmes
            'token' => 'required',
        ]);

        $status = Password::reset(
            $request->only('email', 'motDePasse', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'motDePasse' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Mot de passe réinitialisé avec succès.'])
            : response()->json(['message' => 'Échec de la réinitialisation du mot de passe.'], 500);
    }


}
