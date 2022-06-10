<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class FacebookController extends Controller
{
    public function authRedirect()
    {
        return Socialite::driver('facebook')->redirect();
    }


    public function authCallback()
    {
        $user = Socialite::driver('facebook')->user();
        $user = User::updateOrCreate([
            'facebook' => $user->id,
        ], [
            'name' => $user->name,
            'email' => $user->email,
            "password" => Hash::make($user->email . "@" . $user->id)
        ]);

        Auth::login($user);

        return redirect('/');
    }
}
