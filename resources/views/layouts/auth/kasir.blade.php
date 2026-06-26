<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="kasir-auth antialiased">
        <div class="kasir-auth__waves" aria-hidden="true">
            <div class="kasir-auth__wave kasir-auth__wave--1"></div>
            <div class="kasir-auth__wave kasir-auth__wave--2"></div>
            <div class="kasir-auth__wave kasir-auth__wave--3"></div>
        </div>

        <div class="relative z-10 flex min-h-svh flex-col items-center justify-center px-6 py-10">
            <div class="w-full max-w-md">
                {{ $slot }}
            </div>
        </div>

        @fluxScripts
    </body>
</html>
