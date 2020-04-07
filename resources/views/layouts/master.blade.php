<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    <meta property="og:title" content="Curb Run" />
    <meta property="og:description" content="Get your grocery store curbside pickup slot" />
    <meta property="og:image" content="{{ asset('android-chrome-512x512.png') }}" />
    <title>Curb Run - Get your grocery store curbside pickup slot</title>
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <script>window.__ASSET_URL__ = '{{ asset('/') }}';</script>
  </head>
<body>
<div id="app">
@yield('content')
</div>
<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
