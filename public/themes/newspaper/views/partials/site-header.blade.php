<header class="app-header">

    {{-- TOP BAR --}}
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-12">
                <div class="top-bar bg-dark text-light">
                    @include('components/top-bar')
                </div>
            </div>
        </div>
    </div>

    {{-- HEADER CONTENT --}}
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-md-4">
                <div class="">
                    <h1 class="app-logo">
                        <a href="{{route('app.home')}}" class="text-dark app-logo-text">
                            {{config('app.name')}}
                        </a>
                    </h1>
                </div>

            </div>
            <div class="col-xs-12 col-md-8">
                <div style="background: #ddd; color: #bbbbbb; display: inline-block; font-family: roboto, sans-serif; font-size: 1.2rem; font-weight: 900; height:72px; overflow: hidden; padding: 22px 0; text-align: center; white-space: nowrap; width: 100%;">
                    RESPONSIVE AD AREA
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
