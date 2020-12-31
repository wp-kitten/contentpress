@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Marketplace')}}</title>
@endsection

@section('main')
    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('a.Marketplace')}}</h1>
            </div>
            @if(vp_current_user_can('install_plugins'))
                <ul class="list-unstyled list-inline mb-0">
                    <li class="">
                        <a href="{{route('admin.plugins.marketplace.refresh')}}" class="btn btn-primary">{{__('a.Refresh')}}</a>
                    </li>
                </ul>
            @endif
        </div>
    </div>

    @include('admin.partials.notices')

    @if(vp_current_user_can('install_plugins'))
        <div class="row marketplace">
            @if($plugins)
                @foreach($plugins as $pluginDirName => $pluginInfo)
                    <div class="col-sm-12 col-md-6 mb-4">
                        <div class="tile h-100">
                            <div class="row">
                                <div class="col-sm-12 col-md-4">
                                    <img src="{{url(path_combine(VALPRESS_MARKETPLACE_URL, 'plugins', $pluginDirName, $pluginInfo['thumbnail']))}}"
                                         class="image-responsive"
                                         alt="{{$pluginInfo['display_name']}}"/>
                                </div>
                                <div class="col-sm-12 col-md-8 p-3">
                                    <h6>{{$pluginInfo['display_name']}}</h6>
                                    <p class="description">{{$pluginInfo['description']}}</p>
                                    <div class="meta">
                                        <p class="author cp-no-mb">
                                            <span>{{__('a.By:')}}</span>
                                            @foreach($pluginInfo['authors'] as $author)
                                                <a href="{{$author['url']}}" target="_blank" title="{{__('a.Opens in a new tab/window')}}" class="">
                                                    {{$author['name']}}
                                                </a>
                                            @endforeach
                                        </p>
                                        <p class="version cp-no-mb">{{__('a.Version: :version', ['version' => $pluginInfo['version']])}}</p>
                                    </div>
                                    <div class="">
                                        @if($pluginsManager->exists($pluginDirName))
                                            @if($pluginsManager->isActivePlugin($pluginDirName) && vp_current_user_can('deactivate_plugins'))
                                                <a href="{{route('admin.plugins.deactivate__get', [$pluginDirName])}}" class="btn btn-danger btn-sm">{{__('a.Deactivate')}}</a>
                                            @elseif(vp_current_user_can('activate_plugins'))
                                                <a href="{{route('admin.plugins.activate__get', [$pluginDirName])}}" class="btn btn-success btn-sm">{{__('a.Activate')}}</a>
                                            @endif
                                        @elseif(vp_current_user_can('install_plugins'))
                                            <a href="#"
                                               class="btn btn-primary btn-sm"
                                               onclick="event.preventDefault(); document.getElementById('form-plugin-install-{{$pluginDirName}}').submit();">
                                                {{__('a.Install')}}
                                            </a>
                                            <form id="form-plugin-install-{{$pluginDirName}}"
                                                  action="{{route('admin.plugins.marketplace.install', [
                                                                    'plugin_dir_name' => $pluginDirName,
                                                                    'version' => $pluginInfo['version']
                                                            ])}}" method="post" class="hidden">
                                                @csrf
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="col-sm-12">
                    <div class="alert alert-info">
                        {{__('a.No plugins found on marketplace.')}}
                    </div>
                </div>
            @endif
        </div>
    @endif
@endsection
