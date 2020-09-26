@inject('catModel', App\Models\Category)
@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('np::m.Theme Options')}}</title>
@endsection

@php
    $registrationEnabled = $settings->getSetting('user_registration_open');

    $generalOptions = [];
    $generalOptions['enable_user_custom_home'] = (isset($options['general']['enable_user_custom_home']) ? $options['general']['enable_user_custom_home'] : false);


    $homepageOptions = [];
    $homepageOptions['section-1'] = (isset($options['homepage']['section-1']) ? intval($options['homepage']['section-1']) : 0);
    $homepageOptions['section-2'] = (isset($options['homepage']['section-2']) ? intval($options['homepage']['section-2']) : 0);
    $homepageOptions['section-3'] = (isset($options['homepage']['section-3']) ? intval($options['homepage']['section-3']) : 0);
    $homepageOptions['section-4'] = (isset($options['homepage']['section-4']) ? intval($options['homepage']['section-4']) : 0);
    $homepageOptions['section-5'] = (isset($options['homepage']['section-5']) ? intval($options['homepage']['section-5']) : 0);
    $homepageOptions['section-6'] = (isset($options['homepage']['section-6']) ? intval($options['homepage']['section-6']) : 0);
    $homepageOptions['section-7'] = (isset($options['homepage']['section-7']) ? intval($options['homepage']['section-7']) : 0);
@endphp

@section('main')
    {{--<pre>@php var_export($options) @endphp</pre>--}}
    <form method="post" action="{{route('admin.themes.newspaper-options.save')}}" class="np-theme-options-page-wrap">
        @csrf
        <div class="app-title">
            <div class="cp-flex cp-flex--center cp-flex--space-between">
                <div>
                    <h1>{{__('np::m.Theme Options')}}</h1>
                </div>

                @if(cp_current_user_can('manage_options'))
                    <ul class="list-unstyled list-inline mb-0">
                        <li>
                            <button type="submit" class="btn btn-primary">{{__('np::m.Save')}}</button>
                        </li>
                    </ul>
                @endif
            </div>
        </div>

        @include('admin.partials.notices')

        @if(cp_current_user_can('manage_options'))

            {{-- GENERAL OPTIONS --}}
            <div class="row">
                <div class="col-sm-12">
                    <div class="tile">
                        <h3 class="tile-title">{{__('np::m.General Options')}}</h3>

                        <div class="form-group">

                            <p class="text-description">{{__('np::m.Enabling this option will allow registered users to customize the website homepage to display their selected feeds.')}}</p>
                            @if($registrationEnabled && defined('NPFR_PLUGIN_DIR_NAME'))
                                @php $checked = ($generalOptions['enable_user_custom_home'] ? 'checked' : ''); @endphp
                                <input type="checkbox"
                                       id="chk-enable_user_custom_home"
                                       name="general[enable_user_custom_home]"
                                       {!! $checked !!}
                                       value="1"/>
                                <label for="chk-enable_user_custom_home">{{__('np::m.Allow users to have custom homepages?')}}</label>
                            @else
                                <p class="text-danger">{{__('np::m.In order to have this functionality the user registration must be opened.')}}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- HOMEPAGE OPTIONS --}}
            <div class="row">
                <div class="col-sm-12">
                    <div class="tile">
                        <h3 class="tile-title">{{__('np::m.Homepage Options')}}</h3>
                    </div>
                </div>

                <div class="col-sm-12">
                    <div class="row">
                        @foreach($homepageOptions as $sectionName => $sectionCatID)
                            @php $sectionID = str_replace('section-', '', $sectionName); @endphp
                            <div class="col-sm-12 col-md-4">
                                <div class="tile">
                                    <div class="form-group">
                                        <label for="{{$sectionName}}-category">{{__('np::m.Section :number Category', ['number' => $sectionID])}}</label>
                                        <p class="text-description">{{__('np::m.Select the category to be displayed in section :number.', ['number' => $sectionID])}}</p>
                                        <select id="{{$sectionName}}-category" name="homepage[{{$sectionName}}]" class="selectize-control theme-options-selectize-control-single">
                                            @forelse($categories as $categoryID => $subcategories)
                                                @php
                                                    $cat = $catModel->find($categoryID);
                                                    if( empty( $subcategories ) ) {
                                                        $selected = ($categoryID == $sectionCatID ? 'selected' : '');
                                                        echo '<option value="'.esc_attr($categoryID).'" '.$selected.'>'.$cat->name.'</option>';
                                                    }
                                                    else {
                                                        echo '<optgroup label="'.$cat->name.'">';
                                                        $selected = ($categoryID == $sectionCatID ? 'selected' : '');
                                                        echo '<option value="'.esc_attr($categoryID).'" '.$selected.'>'.$cat->name.'</option>';
                                                        foreach($subcategories as $subcategoryID){
                                                            $selected = ($subcategoryID == $sectionCatID ? 'selected' : '');
                                                            $subcat = $catModel->find($subcategoryID);
                                                            echo '<option value="'.esc_attr($subcategoryID).'" '.$selected.'>'.$subcat->name.'</option>';
                                                        }
                                                        echo '</optgroup>';
                                                    }
                                                @endphp
                                            @empty
                                            @endforelse
                                        </select>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>{{-- END .row --}}
                </div>
            </div>
        @endif
    </form>

@endsection
