@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('cprt::m.ContentPress Regenerate Thumbnails')}}</title>
@endsection

@section('main')

    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('cprt::m.Regenerate Thumbnails')}}</h1>
            </div>
        </div>
    </div>

    @include('admin.partials.notices')

    @if(cp_current_user_can('list_media'))
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <p>{{__('cprt::m.On this page you can regenerate thumbnails for all uploaded images in your application.')}}</p>
                    <p>{{__('cprt::m.It is recommended you do this every time you install/uninstall a new plugin or theme, in order to allow them to register their own image sizes.')}}</p>

                    <p>{{__('cprt::m.Depending on the number of registered image sizes and the number of uploaded images, this might take a while, please do not close or reload the page until the process finishes.')}}</p>
                    <p>{{__('cprt::m.Please keep in mind that in order to save server resources, the images that are correctly resized will be skipped.')}}</p>

                    <p class="mt-5">
                        <strong>{{trans_choice('cprt::m.images_found', $count, ['num_images' => $count])}}</strong>
                    </p>

                    @if( isset($completed) && isset($failed) )
                        <p><strong>{{__('cprt::m.Successful:')}}</strong> <strong>{{$completed}}</strong></p>
                        <p><strong>{{__('cprt::m.Failed:')}}</strong> <strong>{{$failed}}</strong></p>
                    @endif

                    <div class="js-cprt-progress-wrap mb-4 hidden">
                        <div class="info">
                            <p>{{__('cprt::m.Processed')}}
                                <strong id="js-cprt-processed-num"></strong>
                                {!! __('cprt::m.of <strong>:num_files</strong>', [ 'num_files' => $count]) !!}
                            </p>
                        </div>
                        <div class="progress progress-md">
                            {{-- Display an initial value of 2%, because it will take a while to process each batch so an empty progress bar looks weird until the first batch completes --}}
                            <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated js-cprt-progress-bar" role="progressbar" style="width: 2%" aria-valuenow="2" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>

                    <form method="post"
                          class="form-dummy-content-generator"
                          action="#">

                        <button type="button" class="btn btn-primary mr-2 js-cprt-submit-button">{{__('cprt::m.Regenerate')}}</button>
                        @csrf
                    </form>

                </div>
            </div>
        </div>
    @endif
@endsection
