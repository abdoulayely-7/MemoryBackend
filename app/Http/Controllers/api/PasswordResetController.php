<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class PasswordResetController extends Controller
{
    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Vérifiez si l'utilisateur avec cet email existe
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Aucun utilisateur trouvé avec cet email.'],
            ]);
        }

        // Générer un token de réinitialisation
        $token = Password::broker()->createToken($user);

        // Construire l'URL de réinitialisation du mot de passe avec l'URL du frontend
        $frontendUrl = config('app.frontend_url'); // Assurez-vous que ce paramètre est configuré
        $resetUrl = "{$frontendUrl}/password-reset?token={$token}&email={$request->email}";

        try {
            Mail::to($request->email)->send(new PasswordResetMail($resetUrl));
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Erreur lors de l\'envoi de l\'email.'], 500);
        }

        return response()->json(['status' => 'Link sent']);
    }
}
