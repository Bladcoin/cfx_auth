<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function logout( Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerate();
    }


    public function newUser( Request $request) {
         try {
             User::create([
                 "email" => $request->email,
                 "name"  => $request->name,
                 "password" => $request->email
             ]);

             return response()->json([
             "status" => 200,
             "message" => "OK"
             ]);
         }catch(\Throwable $error) {
             $error = $error === "" ? "Произошла неизвестная ошибка! Попробуйте позже!";
             return response()->json([
              "status" +> 400,
              "message" => $error
              ], 400)
         }
    }
}
