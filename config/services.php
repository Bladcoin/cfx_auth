<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    "google" => [
        'client_id' => "175318121490-ctknqt1r69t65fik0jcar8kptbma9mu2.apps.googleusercontent.com",
        'client_secret' => "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",
        'redirect' => 'http://127.0.0.1:8000/google/auth/callback',
    ],

    "facebook" => [
        'client_id' => "5892985670718043",
        'client_secret' => "725b6f115f29655219b6dd758a825f07",
        'redirect' => 'http://127.0.0.1:8000/facebook/auth/callback',
    ]
];
