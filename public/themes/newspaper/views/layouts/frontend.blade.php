<!doctype html>
@php
    $currentLanguageCode = App\Helpers\CPML::getFrontendLanguageCode();
    app()->setLocale($currentLanguageCode);
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="{{env('APP_CHARSET', 'utf-8')}}"/>
    <meta http-equiv="Content-Type" content="text/html; charset={{env('APP_CHARSET', 'utf-8')}}"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}"/>

    @hasSection('title')
        @yield('title')
    @else
        <title>{{ config('app.name', 'ContentPress') }}</title>
    @endif

<!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com"/>

    {{contentpressHead()}}
</head>
<body class="{{cp_body_classes()}}">
    {{do_action('contentpress/after_body_open')}}

    <header class="app-header">
        <div>
            <h3>{{config('app.name')}}</h3>
        </div>
        @include('components.nav-menu')
    </header>

    @yield('content')


    {{contentpressFooter()}}
</body>
</html>
