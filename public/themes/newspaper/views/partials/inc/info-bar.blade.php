<div class="row">
    <div class="col-xs-12 col-sm-12 text-right">
        <div class="info-bar-wrap">
            <span class="the-date">
                @if(wp_is_mobile())
                    {{date('D, M j, Y')}}
                @else
                    {{date('l, F j, Y')}}
                @endif
                </span>
            <div class="auth-wrap">
                {!! np_menuRenderAuthLinks() !!}
            </div>
        </div>

    </div>
</div>
