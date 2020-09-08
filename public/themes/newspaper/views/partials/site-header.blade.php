<header class="app-header">

    {{-- News Ticker --}}
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-12">
                <div class="news-ticker">
                    @include('partials.inc.news-ticker')
                </div>
            </div>
        </div>
    </div>

    {{-- HEADER CONTENT --}}
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-12">
                <div class="header-content text-center pt-3 pb-3">
                    <h1 class="app-logo">
                        <a href="{{route('app.home')}}" class="text-dark app-logo-text">
                            {{config('app.name')}}
                        </a>
                    </h1>
                </div>
            </div>
        </div>
    </div>

    {{-- Date & Auth links --}}
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-12">
                <div class="info-bar pb-2">
                    @include('partials.inc.info-bar')
                </div>
            </div>
        </div>
    </div>

    {{-- NAV BAR --}}
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-12">
                @include('components.nav-menu')
            </div>
        </div>
    </div>

</header>
