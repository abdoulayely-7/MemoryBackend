<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class NewPasswordController extends Controller
{
    /**
     * Handle an incoming new password request.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */



    public function store(Request $request): JsonResponse
    {
        Log::info('Password reset request received', $request->all());

        // Validation
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'motDePasse' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Password reset attempt
        $status = Password::reset(
            $request->only('email', 'motDePasse', 'motDePasse_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'motDePasse' => Hash::make($request->input('motDePasse')),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            Log::error('Password reset failed', ['status' => $status]);
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json(['status' => __($status)]);
    }


}
