<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
        <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
        <script src="{{ asset('assets/js/popper.min.js') }}"></script>
        <script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('assets/js/peerjs.min.js') }}"></script>
        <script src="{{ asset('assets/js/script.js') }}"></script>
    </head>
    <body>
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mt-5">
                <img src="{{ asset('assets/image/print.png') }}" alt="" class="img-fluid bg-transparent">
            </div>
        </div>
    </div>
    </body>
</html>
