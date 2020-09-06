{{--    Navbar     --}}
<header class="app-header">
    <a class="app-header__logo" href="{{route('app.home')}}">
        {{config('app.name', 'ContentPress')}}
    </a>

    {{-- Sidebar toggle button --}}
    <a class="app-sidebar__toggle" href="#" data-toggle="sidebar" aria-label="{{__('a.Hide Sidebar')}}"></a>

    {{-- Navbar Right Menu --}}
    <ul class="app-nav">
        {{-- LANGUAGE SWITCHER --}}
        @includeWhen(cp_is_multilingual(), 'admin.layouts.partials.navbar.language-switcher')

        {{-- ADMIN ALERTS --}}
        @include('admin.layouts.partials.navbar.admin-alerts')

        {{-- AUTH MENU --}}
        @include('admin.layouts.partials.navbar.user-menu')
    </ul>
</header>
