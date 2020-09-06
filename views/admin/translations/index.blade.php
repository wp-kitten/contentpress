@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Translations')}}</title>
@endsection

@section('main')

    <div class="row">
        <div class="col-md-12">
            <div class="card bg-white">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <h4 class="mt-1 mb-1">{{__('a.Translations')}}</h4>
                </div>
            </div>
        </div>
    </div>

    @include('admin.partials.notices')

    <div class="row">

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h4 class="tile-title">{{__('a.Admin Dashboard')}}</h4>
                    <a href="{{route('admin.translations.fn', ['fn' => \App\Helpers\TranslationManager::LANG_FILE_ADMIN])}}" class="text-primary">
                        <span class="mdi mdi-view-dashboard"></span>
                        <span>{{__('a.Admin Dashboard')}}</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h4 class="tile-title">{{__('a.Frontend')}}</h4>
                    <a href="{{route('admin.translations.fn', ['fn' => \App\Helpers\TranslationManager::LANG_FILE_FRONTEND])}}" class="text-primary">
                        <span class="mdi mdi-sitemap"></span>
                        <span>{{__('a.Frontend')}}</span>
                    </a>
                </div>
            </div>
        </div>

    </div>
@endsection
