<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function authRedirect()
    {
        return Socialite::driver('google')->redirect();
    }


    public function authCallback()
    {
        $user = Socialite::driver('google')->user();
        $user = User::updateOrCreate([
            'google_id' => $user->id,
        ], [
            'name' => $user->name,
            'email' => $user->email,
            "password" => Hash::make($user->email . "@" . $user->id)
        ]);

        Auth::login($user);

        return redirect('/');
    }
}
