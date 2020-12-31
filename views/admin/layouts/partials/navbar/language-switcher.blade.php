@inject('languageClass', App\Models\Language)
@php
    $crtLanguageCode = App\Helpers\VPML::getBackendUserLanguageCode();
    $enabledLanguages = App\Helpers\VPML::getLanguages();


@endphp
<li class="dropdown">
    <a class="app-nav__item" href="#" data-toggle="dropdown" aria-label="{{__('a.Languages')}}">
        <i class="{{vp_get_flag_class($crtLanguageCode)}}" title="{{$languageClass->getNameFrom($crtLanguageCode)}}"></i>
    </a>
    <ul class="dropdown-menu settings-menu dropdown-menu-right">
        @foreach($enabledLanguages as $languageCode)
            @if($languageCode == $crtLanguageCode)
                @continue
            @endif
            <li>
                <a class="dropdown-item" href="{{route('admin.dashboard.lang_switch', $languageCode)}}">
                    <i class="{{vp_get_flag_class($languageCode)}}"></i>
                    {{$languageClass->getNameFrom($languageCode)}}
                </a>
            </li>
        @endforeach
    </ul>
</li>
