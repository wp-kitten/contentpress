@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Plugins')}}</title>
@endsection

@section('main')

    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('a.Plugins')}}</h1>
            </div>
            @if(cp_current_user_can('install_plugins'))
                <ul class="list-unstyled list-inline mb-0">
                    <li class="">
                        <a href="{{route('admin.plugins.add')}}" class="btn btn-primary">{{__('a.Upload')}}</a>
                    </li>
                </ul>
            @endif
        </div>
    </div>

    @include('admin.partials.notices')

    @if(cp_current_user_can('list_plugins'))
        <div class="tile">
            <div class="row">
                <div class="col-md-12 grid-margin">
                    <div class="table-responsive">
                        @if(empty($all_plugins))
                            <div class="bs-component">
                                <div class="alert alert-warning">
                                    <span>{{__("a.No plugins found.")}}</span>
                                </div>
                            </div>
                        @else
                            <form id="form-plugins" method="post" action="{{route('admin.plugins.activate__post')}}">
                                <table class="table table-condensed plugins">
                                    <thead>
                                        <tr>
                                            <th scope="col">
                                                <input type="checkbox" id="js-plugins-chk-all" value="1" title="{{__('a.Select all')}}"/>
                                            </th>
                                            <th scope="col">{{__('a.Name')}}</th>
                                            <th scope="col">{{__('a.Description')}}</th>
                                            <th scope="col" class="text-center">{{__('a.Version')}}</th>
                                            <th scope="col" class="text-center">{{__('a.Authors')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($active_plugins as $pluginDirName => $autoloadFile)
                                            <tr class="active">
                                                @php
                                                    $pluginInfo = $pluginsManager->getPluginInfo($pluginDirName);
                                                @endphp
                                                @include('admin.plugins.partials.plugins-list-table', [
                                                    'pluginDirName' => $pluginDirName,
                                                    'pluginInfo' => $pluginInfo,
                                                    'pluginsManager' => $pluginsManager,
                                                    'excludePlugins' => [],
                                                ])
                                            </tr>
                                        @empty
                                        @endforelse

                                        @forelse($all_plugins as $pluginDirName => $pluginInfo)
                                            @if($pluginInfo)
                                                <tr>
                                                    @include('admin.plugins.partials.plugins-list-table', [
                                                        'pluginDirName' => $pluginDirName,
                                                        'pluginInfo' => $pluginInfo,
                                                        'pluginsManager' => $pluginsManager,
                                                        'excludePlugins' => $active_plugins,
                                                    ])
                                                </tr>
                                            @endif
                                        @empty
                                        @endforelse
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="5">
                                                <div class="mt-5">
                                                    <button type="submit" name="__activate_plugins" class="btn btn-primary">{{__('a.Activate')}}</button>
                                                    <button type="submit"
                                                            id="js-btn-deactivate"
                                                            name="__deactivate_plugins"
                                                            data-action="{{route('admin.plugins.deactivate__post')}}"
                                                            data-form-id="form-plugins"
                                                            class="btn btn-dark ml-4">{{__('a.Deactivate')}}</button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                                @csrf
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

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
