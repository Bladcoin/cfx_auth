<?php

namespace App\Http\Controllers;

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
            User::create([
                "email" => $request->email,
                "password" => bcrypt($request->password),
                "name" => $request->name
            ]);

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
}
