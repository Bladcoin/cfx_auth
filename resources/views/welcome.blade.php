<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>POS-CFX</title>
        <link href="/css/bootstrap.min.css" rel="stylesheet">
        <script src='/js/bootstrap.bundle.min.js'></script>
        <script src='/js/big.min.js'></script>
        <script src="/js/chart.js"></script>
    </head>
    <body>
        <div id="app">
            <root
                :reset-token="{{ isset($token) ? '\'' . $token . '\'' : null }}"
            	:user='@json(Auth::user())'
            	:google-auth='`{{ route("google.auth") }}`'
            	:facebook-auth='`{{ route("facebook.auth") }}`'
            	:referrer='`{{ URL::previous() }}`'
            />
        </div>
    </body>
    <script src="{{ asset('/js/main.js') }}">
    </script>
</html>
