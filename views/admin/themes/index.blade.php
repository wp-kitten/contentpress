@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Themes')}}</title>
@endsection

@section('main')

    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('a.Themes')}}</h1>
            </div>
            @if(cp_current_user_can('install_themes'))
                <ul class="list-unstyled list-inline mb-0">
                    <li class="">
                        <a href="{{route('admin.themes.add')}}" class="btn btn-primary">{{__('a.Upload')}}</a>
                    </li>
                </ul>
            @endif
        </div>
    </div>

    @include('admin.partials.notices')

    @if(cp_current_user_can('list_themes'))
        <div class="row themes-list">
            @foreach($themes as $themeDirName)
                @php
                    $theme = new \App\Helpers\Theme($themeDirName);
                    $themeInfo = $theme->getThemeData();
                    $isActive = ($currentTheme->get('name') == $themeDirName);
                    $hasUpdate = false;
                @endphp

                <div class="col-sm-12 col-md-3">
                    <div class="card @if($isActive) active @endif">
                        <div class="card-body">
                            @if($themeInfo['thumbnail'])
                                <a href="#"
                                   data-toggle="modal"
                                   data-target="#infoModal"
                                   data-name="{{$themeInfo['name']}}"
                                   data-display-name="{{$themeInfo['display_name']}}"
                                   class="theme-title js-open-info-modal @if($isActive) theme-active text-success @else text-dark @endif">
                                    <img src="{{cp_theme_url($themeInfo['name'], $currentTheme->get('thumbnail'))}}" alt="{{$themeInfo['name']}}" class="img-thumbnail"/>
                                </a>
                            @endif
                        </div>
                        <div class="card-footer">
                            <div class="theme-actions d-flex">
                                <h4 class="theme-title mr-auto">{{$themeInfo['display_name']}}</h4>

                                <p>
                                    @if(cp_current_user_can('switch_themes') && ! $isActive)
                                        <a href="{{route('admin.themes.activate', $themeInfo['name'])}}"
                                           class="text-primary js-theme-activate">{{__('a.Activate')}}</a>
                                    @endif

                                    @if($hasUpdate)
                                        <a href="#" class="text-primary js-theme-update">{{__('a.Update')}}</a>
                                    @endif

                                    @if(cp_current_user_can('delete_themes') && ! $isActive)
                                        <a href="{{route('admin.themes.delete', $themeInfo['name'])}}"
                                           data-confirm="{{__('a.Are you sure you want to delete this theme?')}}"
                                           class="text-danger">{{__('a.Delete')}}</a>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Modal --}}
        <div class="modal fade" id="infoModal" tabindex="-1" role="dialog" aria-labelledby="infoModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="infoModalLabel"></h5>
                        <button type="button" class="close text-dark" data-dismiss="modal" aria-label="{{__('a.Close')}}">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="content js-content hidden"></div>
                        <div class="circle-loader js-ajax-loader hidden"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-dark" data-dismiss="modal">{{__('a.Close')}}</button>
                    </div>
                </div>
            </div>
        </div>
        {{-- END: Modal --}}
    @endif
@endsection
