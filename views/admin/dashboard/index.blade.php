@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Dashboard')}}</title>
@endsection

@section('main')
    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('a.Dashboard')}}</h1>
            </div>

            <ul class="list-unstyled list-inline mb-0">
                @if(cp_current_user_can('super_admin'))
                    <li class="">
                        <a href="#" class="btn btn-danger mr-3"
                           data-confirm="{{__('a.Are you really sure you want to reinstall the application? This will erase everything.')}}"
                           data-form-id="form-app-reinstall">{{__('a.Reinstall')}}</a>
                        <form id="form-app-reinstall" action="{{route('admin.dashboard.reinstall_app')}}" method="post" class="hidden">
                            @csrf
                        </form>
                    </li>
                @endif
                @if(cp_current_user_can('administrator'))
                    <li class="">
                        <a href="#" class="btn btn-danger mr-5"
                           data-confirm="{{__('a.Are you really sure you want to clear the application cache?')}}"
                           data-form-id="form-app-clear_cache">{{__('a.Clear app cache')}}</a>
                        <form id="form-app-clear_cache" action="{{route('admin.dashboard.clear_cache')}}" method="post" class="hidden">
                            @csrf
                        </form>
                    </li>
                    <li class="">
                        <a class="btn btn-primary"
                           href="{{route('admin.dashboard.refresh_stats')}}"
                           onclick="event.preventDefault(); document.getElementById('form-refresh-stats').submit();">{{__('a.Refresh stats')}}</a>
                        <form id="form-refresh-stats" class="hidden">
                            @csrf
                        </form>
                    </li>
                @endif
                @if(cp_current_user_can('edit_dashboard'))
                    <li class="">
                        <a class="btn btn-primary mr-0 ml-3" href="{{route('admin.dashboard.edit')}}">{{__('a.Edit')}}</a>
                    </li>
                @endif
            </ul>
        </div>
    </div>


    @include('admin.partials.notices')

    @if(cp_current_user_can('read'))
        <div class="row">

            {{-- SECTION #1 --}}
            <div class="col-md-3">
                <div id="section-1" class="js-dash-widgets-col">
                    @if(isset($widgets['section-1']))
                        @foreach($widgets['section-1'] as $className => $id)
                            @php
                                $widget = new $className($id);
                                $widget->render();
                            @endphp
                        @endforeach
                    @endif
                </div>
            </div>

            {{-- SECTION #2 --}}
            <div class="col-md-3">
                <div id="section-2" class="js-dash-widgets-col">
                    @if(isset($widgets['section-2']))
                        @foreach($widgets['section-2'] as $className => $id)
                            @php
                                $widget = new $className($id);
                                $widget->render();
                            @endphp
                        @endforeach
                    @endif
                </div>
            </div>

            {{-- SECTION #3 --}}
            <div class="col-md-3">
                <div id="section-3" class="js-dash-widgets-col">
                    @if(isset($widgets['section-3']))
                        @foreach($widgets['section-3'] as $className => $id)
                            @php
                                $widget = new $className($id);
                                $widget->render();
                            @endphp
                        @endforeach
                    @endif
                </div>
            </div>

            {{-- SECTION #4 --}}
            <div class="col-md-3">
                <div id="section-4" class="js-dash-widgets-col">
                    @if(isset($widgets['section-4']))
                        @foreach($widgets['section-4'] as $className => $id)
                            @php
                                $widget = new $className($id);
                                $widget->render();
                            @endphp
                        @endforeach
                    @endif
                </div>
            </div>

        </div>{{-- End .row --}}

    @endif

@endsection
