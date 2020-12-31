@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Updates')}}</title>
@endsection

@section('main')
    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('a.Updates')}}</h1>
            </div>
            @if(vp_current_user_can(['update_core', 'update_plugins', 'update_themes']))
                <ul class="list-unstyled list-inline mb-0">
                    <li class="">
                        <a class="btn btn-primary mr-3"
                           href="#!"
                           onclick="event.preventDefault(); document.getElementById('form-updates-force-check').submit();">
                            {{__('a.Force check')}}
                        </a>
                        <form id="form-updates-force-check" action="{{route('admin.dashboard.force_check_for_updates')}}" method="post" class="hidden">
                            @csrf
                        </form>
                    </li>
                    <li class="">
                        <a class="btn btn-primary"
                           href="#!"
                           onclick="event.preventDefault(); document.getElementById('form-updates-check').submit();">
                            {{__('a.Check for updates')}}
                        </a>
                        <form id="form-updates-check" action="{{route('admin.dashboard.check_for_updates')}}" method="post" class="hidden">
                            @csrf
                        </form>
                    </li>
                </ul>
            @endif
        </div>
    </div>

    @include('admin.partials.notices')

    {{-- CORE --}}
    @if(vp_current_user_can('update_core') && ! empty($core))
        <div class="tile mb-4">
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-header">
                        <h2 class="mb-3 line-head">{{__('a.Core')}}</h2>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <p class="pt-2">
                        <strong class="text-capitalize">{{$core['display_name']}}</strong>
                        <span class="text-info">(v{{$core['version']}})</span>
                    </p>
                </div>
                <div class="col-6">
                    <p>
                        <a href="#!"
                           class="btn btn-primary js-update"
                           data-name=""
                           data-action="update_core"
                           onclick="event.preventDefault();document.getElementById('form-update-core').submit()">{{__('a.Update')}}</a>
                    </p>
                    <form id="form-update-core" method="post" action="{{route('admin.dashboard.update.core', $core['version'])}}">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- PLUGINS --}}
    @if(vp_current_user_can('update_plugins') && ! empty($plugins))
        <div class="tile mb-4">
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-header">
                        <h2 class="mb-3 line-head">{{__('a.Plugins')}}</h2>
                    </div>
                </div>
            </div>
            @foreach($plugins as $fileName => $info)
                <div class="row">
                    <div class="col-6">
                        <p class="pt-2">
                            <strong class="text-capitalize">{{$info['display_name']}}</strong>
                            <span class="text-info">(v{{$info['version']}})</span>
                        </p>
                    </div>
                    <div class="col-6">
                        <p>
                            <a href="#!"
                               class="btn btn-primary js-update"
                               data-name="{{$fileName}}"
                               data-action="update_plugin"
                               onclick="event.preventDefault();document.getElementById('form-update-{{$fileName}}').submit()">{{__('a.Update')}}</a>
                        </p>
                        <form id="form-update-{{$fileName}}" method="post" action="{{route('admin.dashboard.update.plugin', $fileName)}}">
                            @csrf
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- THEMES --}}
    @if(vp_current_user_can('update_themes') && ! empty($themes))
        <div class="tile mb-4">
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-header">
                        <h2 class="mb-3 line-head">{{__('a.Themes')}}</h2>
                    </div>
                </div>
            </div>
            @foreach($themes as $fileName => $info)
                <div class="row border-bottom mt-2">
                    <div class="col-6">
                        <p class="pt-2">
                            <strong class="text-capitalize">{{$info['display_name']}}</strong>
                            <span class="text-info">(v{{$info['version']}})</span>
                        </p>
                    </div>
                    <div class="col-6">
                        <p>
                            <a href="#!"
                               class="btn btn-success js-update"
                               data-name="{{$fileName}}"
                               data-action="update_theme"
                               onclick="event.preventDefault();document.getElementById('form-update-{{$fileName}}').submit()">{{__('a.Update')}}</a>
                        </p>
                        <form id="form-update-{{$fileName}}" method="post" action="{{route('admin.dashboard.update.theme', $fileName)}}">
                            @csrf
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Show message if there are no updates available --}}
    @if(empty($plugins) && empty($themes))
        <div class="bs-component">
            <div class="alert alert-info">
                {{__('a.No updates available')}}
            </div>
        </div>
    @endif
@endsection
