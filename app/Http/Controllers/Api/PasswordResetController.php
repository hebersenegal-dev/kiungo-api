<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cache;
use App\Notifications\PasswordResetOtp;
use App\Models\User;

class PasswordResetController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Un lien de réinitialisation a été envoyé à votre adresse e-mail.'])
            : response()->json(['message' => 'Impossible d\'envoyer le lien de réinitialisation.'], 500);
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        // Find user
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        }

        // Generate 6-digit OTP
        $otp = rand(100000, 999999);

        // Store OTP in cache for 10 minutes
        Cache::put('password_reset_otp_' . $request->email, $otp, now()->addMinutes(10));

        // Send OTP via notification
        try {
            $user->notify(new PasswordResetOtp($otp));

            return response()->json([
                'message' => 'OTP sent successfully to your email.',
                'otp_expires_in' => 10 // minutes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send OTP. Please try again.'
            ], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|numeric|digits:6'
        ]);

        $cachedOtp = Cache::get('password_reset_otp_' . $request->email);

        if (!$cachedOtp) {
            return response()->json([
                'message' => 'OTP has expired. Please request a new one.'
            ], 400);
        }

        if ($cachedOtp != $request->otp) {
            return response()->json([
                'message' => 'Invalid OTP. Please try again.'
            ], 400);
        }

        // OTP is valid, generate a reset token
        $token = Str::random(60);
        Cache::put('password_reset_token_' . $request->email, $token, now()->addMinutes(30));

        // Clear the OTP after successful verification
        Cache::forget('password_reset_otp_' . $request->email);

        return response()->json([
            'message' => 'OTP verified successfully.',
            'reset_token' => $token
        ]);
    }

    public function resetWithOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'reset_token' => 'required',
            'password' => 'required|confirmed|min:8',
        ]);

        // Verify reset token
        $cachedToken = Cache::get('password_reset_token_' . $request->email);

        if (!$cachedToken || $cachedToken !== $request->reset_token) {
            return response()->json([
                'message' => 'Invalid or expired reset token.'
            ], 400);
        }

        // Find user and update password
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        // Clear the reset token
        Cache::forget('password_reset_token_' . $request->email);

        return response()->json([
            'message' => 'Password reset successfully.'
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Mot de passe réinitialisé avec succès.'])
            : response()->json(['message' => 'Échec de la réinitialisation du mot de passe.'], 500);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|confirmed|min:8',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Le mot de passe actuel est incorrect.'],
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
        ])->save();

        return response()->json(['message' => 'Mot de passe modifié avec succès.']);
    }
}
