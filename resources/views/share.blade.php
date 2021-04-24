<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Cinemaa.cc</title>

    <link rel="apple-touch-icon" sizes="180x180" href="{{ env('FRONTEND_URL') }}/img/icons/filmforras/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ env('FRONTEND_URL') }}/img/icons/filmforras/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ env('FRONTEND_URL') }}/img/icons/filmforras/favicon-16x16.png">
    <link rel="manifest" href="{{ env('FRONTEND_URL') }}/img/icons/filmforras/site.webmanifest">
    <link rel="shortcut icon" href="{{ env('FRONTEND_URL') }}/img/icons/filmforras/favicon.ico">
    <link rel="mask-icon" href="{{ env('FRONTEND_URL') }}/img/icons/filmforras/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">


    <meta property="og:title" content="{{ $movieTitle }}">
    <meta property="og:description" content="{{ $movieDescription }}">
    <meta property="og:image" content="{{ $movieImage }}">

</head>
<body style="background-color: #1a191f; color: white;">

</body>
</html>
