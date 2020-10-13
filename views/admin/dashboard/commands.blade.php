@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Commands')}}</title>
@endsection

@section('main')
    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__("a.Commands")}}</h1>
            </div>
        </div>
    </div>

    @include('admin.partials.notices')

    <div class="row">
        {{-- REINSTALL --}}
        @if(cp_current_user_can('super_admin'))
            <div class="col-sm-12 col-md-4">
                <div class="tile mb-4 h-100">
                    <div class="page-header">
                        <h2 class="mb-3 line-head">{{__('a.Reinstall')}}</h2>
                    </div>
                    <div class="content-section">
                        <p class="text-italic">
                            {{__("a.This command will reinstall the current version of ContentPress.")}}
                        </p>
                        <p class="text-right">
                            <a href="#!"
                               class="btn btn-primary js-reinstall-button"
                               data-form-id="form-reinstall-app"
                               data-confirm="{{__('a.Are you sure you want to reinstall the current version of ContentPress?')}}"
                            >{{__('a.Reinstall')}}</a>
                        </p>
                        <form id="form-reinstall-app" method="post" action="{{route('admin.dashboard.reinstall')}}">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        @endif

        {{-- RESET --}}
        @if(cp_current_user_can('administrator'))
            <div class="col-sm-12 col-md-4">
                <div class="tile mb-4 h-100">
                    <div class="page-header">
                        <h2 class="mb-3 line-head">{{__('a.Reset')}}</h2>
                    </div>
                    <div class="content-section">
                        <p class="text-italic">
                            {{__('a.This command will reset ContentPress to its default state. This means the database and the uploads directory will be wiped clean and the default data installed.')}}
                        </p>
                        <p class="text-right">
                            <a href="#!"
                               class="btn btn-danger js-reset-button"
                               data-form-id="form-reset-app"
                               data-confirm="{{__('a.Are you sure you want to reset the application? This action cannot be undone and it will wipe clean the database and the uploads directory!')}}"
                            >{{__('a.Reset')}}</a>
                        </p>
                        <form id="form-reset-app" method="post" action="{{route('admin.dashboard.reset')}}">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        @endif

        {{-- CLEAR APP CACHE --}}
        @if(cp_current_user_can('administrator'))
            <div class="col-sm-12 col-md-4">
                <div class="tile mb-4 h-100">
                    <div class="page-header">
                        <h2 class="mb-3 line-head">{{__('a.Clear app cache')}}</h2>
                    </div>
                    <div class="content-section">
                        <p class="text-italic">
                            {{__('a.This command will clear the application cache. This action will clear: cache, views, routes, config, compiled and the internal cache.')}}
                        </p>
                        <p class="text-right">
                            <a href="#!"
                               class="btn btn-primary js-clear-cache-button"
                               data-form-id="form-clear-cache"
                               data-confirm="{{__('a.Are you sure you want to clear the application cache?')}}"
                            >{{__('a.Clear')}}</a>
                        </p>
                        <form id="form-clear-cache" method="post" action="{{route('admin.dashboard.clear_cache')}}">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>{{--// .row--}}

    @if(cp_current_user_can('administrator'))
        <div class="row mt-3">
            {{-- COMPOSER UPDATE --}}
            <div class="col-sm-12 col-md-4">
                <div class="tile mt-3 mb-4 h-100">
                    <div class="page-header">
                        <h2 class="mb-3 line-head">{{__('a.Composer Update')}}</h2>
                    </div>
                    <div class="content-section">
                        <p class="text-italic">
                            {{__("a.This command will execute the \"composer update\" command which will update all composer dependencies. Tread carefully, it might have unwanted side effects.")}}
                        </p>
                        <p class="text-right">
                            <a href="#!"
                               class="btn btn-primary js-composer-update-button"
                               data-form-id="form-composer-update"
                               data-confirm="{{__('a.Are you absolutely sure?')}}"
                            >{{__('a.Execute')}}</a>
                        </p>
                        <form id="form-composer-update" method="post" action="{{route('admin.dashboard.composer_update')}}">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>

            {{-- COMPOSER DUMPAUTOLOAD --}}
            <div class="col-sm-12 col-md-4">
                <div class="tile mt-3 mb-4 h-100">
                    <div class="page-header">
                        <h2 class="mb-3 line-head">{{__('a.Composer dumpautoload')}}</h2>
                    </div>
                    <div class="content-section">
                        <p class="text-italic">
                            {{__("a.This command will execute the \"composer dumpautoload\" command which will regenerate the optimized autoload files.")}}
                        </p>
                        <p class="text-right">
                            <a href="#!"
                               class="btn btn-primary js-composer-dumpautoload-button"
                               data-form-id="form-composer-dump-autoload"
                               data-confirm="{{__('a.Are you sure?')}}"
                            >{{__('a.Execute')}}</a>
                        </p>
                        <form id="form-composer-dump-autoload" method="post" action="{{route('admin.dashboard.composer_dump')}}">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>

        </div>{{--// .row--}}
    @endif
@endsection
