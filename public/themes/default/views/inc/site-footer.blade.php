<footer class="cpdt-site-footer">
    <div class="container">
        <div class="footer-content-wrap">
            <h4 class="logo mt-0">
                <a href="{{route('app.home')}}">
                    {{env('APP_NAME', 'ContentPress')}}
                </a>
            </h4>

            @if(cp_has_menu('footer-menu'))
                <nav class="footer-menu-nav">
                    <ul class="list-unstyled footer-menu-wrap">
                        {!! cp_menu('footer-menu') !!}
                    </ul>
                </nav>
            @endif
        </div>

    </div>
</footer>
