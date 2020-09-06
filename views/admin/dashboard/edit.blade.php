@if(cp_current_user_can('edit_dashboard'))
    @php
        $statsHelper = App\Helpers\StatsHelper::getInstance();

        $section1_data = (isset($widgets['section-1']) ? $widgets['section-1'] : [] );
        $section2_data = (isset($widgets['section-2']) ? $widgets['section-2'] : [] );
        $section3_data = (isset($widgets['section-3']) ? $widgets['section-3'] : [] );
        $section4_data = (isset($widgets['section-4']) ? $widgets['section-4'] : [] );

        $section1_widgets =
        $section2_widgets =
        $section3_widgets =
        $section4_widgets = [];

        foreach($section1_data as $className => $id){
            $widget = new $className($id);
            array_push($section1_widgets, $widget);
        }

        foreach($section2_data as $className => $id){
            $widget = new $className($id);
            array_push($section2_widgets, $widget);
        }

        foreach($section3_data as $className => $id){
            $widget = new $className($id);
            array_push($section3_widgets, $widget);
        }

        foreach($section4_data as $className => $id){
            $widget = new $className($id);
            array_push($section4_widgets, $widget);
        }
    @endphp

    @extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Dashboard')}}</title>
@endsection

@section('main')
    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('a.Edit dashboard content')}}</h1>
            </div>
            @if(cp_current_user_can('edit_dashboard'))
                <ul class="list-unstyled list-inline mb-0">
                    <li class="">
                        <a class="btn btn-primary" href="{{route('admin.dashboard')}}">
                            {{__('a.Back')}}
                        </a>
                    </li>
                    <li class="">
                        <a class="btn btn-primary ml-3 js-dash-btn-save" href="#">
                            {{__('a.Save')}}
                            <span class="circle-loader circle-loader--xsmall circle-loader--light d-inline-block hidden" id="js-loader"></span>
                        </a>
                    </li>
                </ul>
                <form id="form-refresh-stats" class="hidden">
                    @csrf
                </form>
            @endif
        </div>
    </div>



    @include('admin.partials.notices')

    <div class="row">
        {{-- EDITABLE CONTENT --}}
        <div class="col-sm-12">
            <div class="row">
                <div class="col-md-3 grid-margin">

                    <div id="section-1" class="js-dragula-section">

                        @foreach($section1_widgets as $widget)
                            @php $widget->render(); @endphp
                        @endforeach

                    </div>

                </div>

                <div class="col-md-3 grid-margin">

                    <div id="section-2" class="js-dragula-section">

                        @foreach($section2_widgets as $widget)
                            @php $widget->render(); @endphp
                        @endforeach

                    </div>

                </div>

                <div class="col-md-3 grid-margin">

                    <div id="section-3" class="js-dragula-section">

                        @foreach($section3_widgets as $widget)
                            @php $widget->render(); @endphp
                        @endforeach

                    </div>

                </div>

                <div class="col-md-3 grid-margin">

                    <div id="section-4" class="js-dragula-section">

                        @foreach($section4_widgets as $widget)
                            @php $widget->render(); @endphp
                        @endforeach

                    </div>

                </div>

            </div>
        </div>

    </div>

@endsection

@endif
