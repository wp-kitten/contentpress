{{--

    Only a specific set of notifications will be displayed here

--}}
@php

//#! TODO: THESE ALERTS SHOULD FOLLOW THE SAME FUNCTIONALITY AS THOSE FOR USER NOTICES

    $hasNotifications = false;

    $underMaintenance = cp_is_under_maintenance();
    //#! other settings here...
    $coreUpdateAvailable = false;
    $themesUpdatesAvailable = false;
    $pluginsUpdatesAvailable = false;


    if( $underMaintenance || $themesUpdatesAvailable || $pluginsUpdatesAvailable ) {
        $hasNotifications = true;
    }
    //#! Other checks here
@endphp
@if($hasNotifications && vp_current_user_can('read'))
<li class="dropdown">
    <a class="app-nav__item" href="#" data-toggle="dropdown" aria-label="Show notifications"><i class="fa fa-bell-o fa-lg"></i></a>
    <ul class="app-notification dropdown-menu dropdown-menu-right">
        <li class="app-notification__title">You have 4 new notifications.</li>

        @if($underMaintenance)
            <li>
                <div class="app-notification__content">
                    <a class="app-notification__item" href="{{route('admin.settings.all')}}">
                        <span class="app-notification__icon">
                            <span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x text-danger"></i><i class="fa fa-hdd-o fa-stack-1x fa-inverse"></i>
                            </span>
                        </span>
                        <div>
                            <p class="app-notification__message">{{__('a.Under maintenance')}}</p>
                        </div>
                    </a>
                </div>
            </li>
        @endif

        @if(vp_current_user_can('update_themes') && $themesUpdatesAvailable)
            <li>
                <div class="app-notification__content">
                    <a class="app-notification__item" href="{{route('admin.dashboard.updates')}}">
                        <span class="app-notification__icon">
                            <span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x text-danger"></i><i class="fa fa-hdd-o fa-stack-1x fa-inverse"></i>
                            </span>
                        </span>
                        <div>
                            <p class="app-notification__message">{{__('a.Updates available for themes')}}</p>
                        </div>
                    </a>
                </div>
            </li>
        @endif

        @if(vp_current_user_can('update_plugins') && $pluginsUpdatesAvailable)
            <li>
                <div class="app-notification__content">
                    <a class="app-notification__item" href="{{route('admin.dashboard.updates')}}">
                        <span class="app-notification__icon">
                            <span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x text-danger"></i><i class="fa fa-hdd-o fa-stack-1x fa-inverse"></i>
                            </span>
                        </span>
                        <div>
                            <p class="app-notification__message">{{__('a.Updates available for plugins')}}</p>
                        </div>
                    </a>
                </div>
            </li>
        @endif


        <li class="app-notification__footer"><a href="#">See all notifications.</a></li>
    </ul>
</li>
@endif
