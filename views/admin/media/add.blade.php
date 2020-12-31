@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Add media')}}</title>
@endsection

@section('main')
    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('a.Add media')}}</h1>
            </div>

            @if(vp_current_user_can('add_media'))
                <ul class="list-unstyled list-inline mb-0">
                    <li>
                        <a href="{{route('admin.media.all')}}" class="btn btn-primary">{{__('a.Back')}}</a>
                    </li>
                </ul>
            @endif
        </div>
    </div>

    @include('admin.partials.notices')

    @if(vp_current_user_can('add_media'))
        <div class="tile mb-4">
            <div class="row">
                <div class="col-md-12 grid-margin">
                    <form id="media-upload-form">
                        <div class="form-group">
                            <div class="post_image_field-wrapper">
                                <input type="file" id="media_image_upload_field" accept="image/*" class="dropify"/>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="row image-preview-uploads js-image-preview-uploads"></div>
            <div class="backdrop-preview"></div>
        </div>
    @endif

@endsection
