<li class="dropdown">
    <a class="app-nav__item" href="#" data-toggle="dropdown" aria-label="{{__('a.Open Profile Menu')}}">
        <i class="fa fa-user fa-lg"></i>
    </a>
    <ul class="dropdown-menu settings-menu dropdown-menu-right">
        @if(vp_current_user_can('manage_options'))
            <li>
                <a class="dropdown-item" href="{{route('admin.settings.all')}}">
                    <i class="fa fa-cog fa-lg"></i>
                    {{__('a.Settings')}}
                </a>
            </li>
        @endif

        <li>
            <a class="dropdown-item" href="{{route('admin.users.edit', ['id' => vp_get_current_user()->getAuthIdentifier()])}}">
                <i class="fa fa-user fa-lg"></i>
                {{__('a.Profile')}}
            </a>
        </li>

        <li>
            <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('frm-logout').submit();">
                <i class="fa fa-sign-out fa-lg"></i>
                {{__('a.Logout')}}
            </a>
            <form id="frm-logout" action="{{ route('logout') }}" method="post" class="hidden">
                {{ csrf_field() }}
            </form>
        </li>
    </ul>
</li>
