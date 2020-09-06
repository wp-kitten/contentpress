@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('dcg::m.Dummy Content Generator')}}</title>
@endsection

@section('main')

    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('dcg::m.Content Generator')}}</h1>
            </div>
        </div>
    </div>

    @include('admin.partials.notices')

    @if(cp_current_user_can('manage_taxonomies'))
        <div class="tile">
            <div class="card-body">
                <h4 class="tile-title">{{__('dcg::m.Content')}}</h4>

                <p><?php esc_html_e( __( 'dcg::m.You can use this page to create some dummy content to get you started.' ) );?></p>
                <p><?php esc_html_e( __( 'dcg::m.This content will consist of: categories, tags, posts, pages.' ) );?></p>
                <form method="post"
                      class="form-dummy-content-generator"
                      action="{{route("admin.dummy_content_generator.generate")}}">

                    <button type="submit" class="btn btn-primary mt-3">{{__('dcg::m.Generate')}}</button>
                    @csrf
                </form>
            </div>
        </div>

    @endif
@endsection
