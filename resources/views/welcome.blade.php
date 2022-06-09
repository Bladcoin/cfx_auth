<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>
    </head>
    <body class="antialiased">
        <p>{{ Auth::user() }}</p>
        <a href="{{ route("google.auth") }}"><button>asdasd</button></a>     
    </body>
</html>
