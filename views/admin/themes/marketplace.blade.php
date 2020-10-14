@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Marketplace')}}</title>
@endsection

@section('main')

    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('a.Marketplace')}}</h1>
            </div>
            @if(cp_current_user_can('install_themes'))
                <ul class="list-unstyled list-inline mb-0">
                    <li class="">
                        <a href="{{route('admin.themes.marketplace.refresh')}}" class="btn btn-primary">{{__('a.Refresh')}}</a>
                    </li>
                </ul>
            @endif
        </div>
    </div>

    @include('admin.partials.notices')

    @if(cp_current_user_can('list_themes'))
        <div class="row themes-list marketplace">
            @foreach($themes as $themeDirName => $themeInfo)
                @php
                    $isActive = ($currentTheme->get('name') == $themeDirName);
                @endphp

                <div class="col-sm-12 col-md-3">
                    <div class="card @if($isActive) active @endif">
                        <div class="card-body">
                            @if($themeInfo['thumbnail'])
                                <a href="{{$themeInfo['page_url']}}"
                                   target="_blank"
                                   title="{{__('a.Visit theme site')}}"
                                   class="theme-title @if($isActive) theme-active text-success @else text-dark @endif">
                                    <img src="{{url(path_combine(CONTENTPRESS_MARKETPLACE_URL, 'themes', $themeDirName, $themeInfo['thumbnail']))}}"
                                         alt="{{$themeInfo['display_name']}}"
                                         class="img-thumbnail"/>
                                </a>
                            @endif
                        </div>
                        <div class="card-footer">
                            <div class="theme-actions d-flex">
                                <h4 class="theme-title title-sm mr-auto">{{$themeInfo['display_name']}}</h4>
                                <div>
                                    @if(cp_current_user_can('switch_themes'))
                                        @if(! $themesManager->exists($themeDirName))
                                            <a href="#"
                                               onclick="event.preventDefault(); document.getElementById('form-theme-install-{{$themeDirName}}').submit();"
                                               class="text-light btn btn-primary btn-sm">
                                                {{__('a.Install')}}
                                            </a>
                                            <form id="form-theme-install-{{$themeDirName}}"
                                                  action="{{route('admin.themes.marketplace.install', [
                                                            'theme_dir_name' => $themeDirName,
                                                            'version' => $themeInfo['version']
                                                        ])}}"
                                                  method="post"
                                                  class="hidden">
                                                @csrf
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
