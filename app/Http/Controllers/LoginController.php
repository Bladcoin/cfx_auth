<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Auth\Events\Registered;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerate();
    }


    public function newUser(RegisterRequest $request)
    {
        try {
            $user = User::create([
                "email" => $request->email,
                "password" => bcrypt($request->password),
                "name" => $request->name
            ]);



            event(new Registered($user));
            return response()->json([
                "status" => 200,
                "message" => "OK"
            ]);
        } catch (\Throwable $error) {
            $error = $error === "" ? "Произошла неизвестная ошибка! Попробуйте позже!" : $error;
            return response()->json([
                "status" => 400,
                "message" => $error
            ], 400);
        }
    }


    public function login(LoginRequest $request)
    {
        try {
            if (Auth::attempt(["email" => $request->email, "password" => $request->password])) {
                $request->session()->regenerate();
                return response()->json([
                    "status" => 200,
                    "message" => "OK",
                    "user" => Auth::user()
                ]);
            }
        } catch (\Throwable $error) {
            $error = $error === "" ? "Произошла неизвестная ошибка! Попробуйте позже!" : $error;
            return response()->json([
                "status" => 400,
                "message" => $error
            ], 400);
        }
    }
}
