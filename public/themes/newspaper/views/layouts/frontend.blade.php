<!doctype html>
@inject('newspaperHelper',App\Newspaper\NewspaperHelper)
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

    @php $newspaperHelper->printSocialMetaTags() @endphp

    @hasSection('title')
        @yield('title')
    @else
        <title>{{ config('app.name', 'ContentPress') }}</title>
@endif

<!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com"/>

    @cp_head()
</head>
<body class="{{cp_body_classes()}}">
    {{do_action('contentpress/after_body_open')}}


    {{-- SIDENAV --}}
    @if(wp_is_mobile())
        <a href="#" class="btn-open-sidenav" title="{{__( 'np::m.Open side nav' )}}">&#9776;</a>
        <div id="mySidenav" class="sidenav">
            <a href="#" class="btn-close-sidenav">&times;</a>
            <div class="sidenav-content custom-scroll">
                @hasSection('sidenav')
                    @yield('sidenav')
                @else
                    <aside class="site-sidebar">
                        @include('components.blog-sidebar', ['newspaperHelper' => $newspaperHelper])
                    </aside>
                @endif
            </div>
        </div>
    @endif

    @include('partials.site-header')

    @yield('content')

    @include('partials.site-footer')

    @cp_footer()
</body>
</html>
