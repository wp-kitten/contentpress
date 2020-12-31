@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Upload theme')}}</title>
@endsection

@section('main')

    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('a.Upload theme')}}</h1>
            </div>

            <ul class="list-unstyled list-inline mb-0">
                <li>
                    <a href="{{route('admin.themes.all')}}" class="btn btn-primary">{{__('a.Back')}}</a>
                </li>
            </ul>
        </div>
    </div>

    @include('admin.partials.notices')

    @if(vp_current_user_can('install_themes'))
        <div class="row">
            <div class="col-md-12">
                <div class="theme_upload_field-wrapper">
                    <input type="file" id="theme_upload_field" accept=".zip" class="dropify"/>
                </div>
            </div>
        </div>
    @endif
@endsection
