<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function forgotPassword()
    {
        return view('welcome');
    }

    public function updatePassword(Request $request)
    {


        $request->validate(['email' => 'required|email']);
        try {
            $status = Password::sendResetLink(
                $request->only('email')
            );

            if ($status === Password::RESET_LINK_SENT) {
                $status = 200;
            } else {
                throw new \Throwable();
            }
            return response()->json([
                "status" => $status,
                "message" => "OK"
            ]);
        } catch (\Throwable $error) {
            return response()->json([
                "status" => 400
            ], 400);
        }
    }

    public function resetPasswordView($token)
    {
        return view("welcome", ["token" => $token]);
    }

    public function resetPassword(Request $request)
    {



        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        try {
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password)
                    ])->setRememberToken(Str::random(60));

                    $user->save();

                    event(new PasswordReset($user));
                }
            );

            return response()->json([
                "status" => $status,
                "message" => "OK"
            ]);
        } catch (\Throwable $error) {

            return response()->json([
                "status" => 400,
                "message" => $error
            ], 400);
        }
    }
}
