<header class="cpdt-site-header">
    <div class="container">
        <div class="row">
            <div class="col-sm-12 col-md-4">
                <h1 class="logo">{{env('APP_NAME', 'ContentPress')}}</h1>
            </div>
            <div class="col-sm-12 col-md-8">
                {{--// Maybe show ad space here --}}
            </div>
        </div>

        @if(cp_has_menu('main-menu'))
            <nav class="cpdt-navbar">
                <div class="cpdt-navbar-nav">
                    {!! cp_menu('main-menu') !!}
                </div>
            </nav>
        @endif
    </div>
</header>
