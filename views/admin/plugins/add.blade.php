@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Upload plugin')}}</title>
@endsection

@section('main')

    <div class="app-title">
        <div class="cp-wide d-flex align-items-center">
            <div>
                <h1>{{__('a.Upload plugin')}}</h1>
            </div>

            <div class="d-flex ml-auto">
                <a href="{{route('admin.plugins.all')}}" class="btn btn-primary ml-3">{{__('a.Back')}}</a>
            </div>
        </div>
    </div>

    @include('admin.partials.notices')

    @if(cp_current_user_can('install_plugins'))
        <div class="cp-flex cp-flex--center justify-content-center">
            <div class="col-md-6">
                <div class="theme_upload_field-wrapper">
                    <input type="file" id="plugin_upload_field" accept=".zip" class="dropify"/>
                </div>
            </div>
        </div>
    @endif

    @if(! empty($plugins))
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
