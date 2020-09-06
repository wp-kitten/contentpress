@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('cpcs::m.ContentPress Custom CSS')}}</title>
@endsection

@section('main')

    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('cpcs::m.ContentPress Custom CSS')}}</h1>
            </div>
        </div>
    </div>

    @include('admin.partials.notices')

    @if(cp_current_user_can('manage_options'))
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <div class="card-body">
                        <div class="editor-wrap" style="position: relative; height:600px;">

                            <div id="cpcs_custom_css_container">
                                <div id="cpcs_custom_css"></div>
                            </div>

                            <textarea id="cpcs_custom_css_textarea" name="">{!! $css_rules !!}</textarea>
                        </div>

                        <form method="post" id="cpcs_form" class="clearfix" action="{{route('admin.cp_custom_css.save')}}">
                            <input type="hidden" name="cpcs_custom_css" id="cpcs_custom_css_field" value=""/>
                            <button type="submit" class="btn btn-primary mt-4 mr-2">{{__('cpcs::m.Save')}}</button>
                            @csrf
                        </form>

                        <div class="mt-3 mb-3">
                            <p>{!! __('cpcs::m.The custom styles are written to this file: <strong>:file_path</strong> and automatically loaded in frontend.', [
                                'file_path' => cpcsGetStylesheetUrl()
                            ]) !!}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
