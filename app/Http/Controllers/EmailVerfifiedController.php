<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

class EmailVerfifiedController extends Controller
{
    public function verifyNotification()
    {
        return view("welcome.blade.php");
    }


    public function verifyFromEmail(EmailVerificationRequest $request)
    {
        $request->fulfill();
        return redirect('/');
    }
}
