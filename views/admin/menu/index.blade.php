@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Menus')}}</title>
@endsection

@section('main')

    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('a.Menus')}}</h1>
            </div>
            @if(cp_current_user_can('publish_posts'))
                <ul class="list-unstyled list-inline mb-0">
                    <li class="">
                        <a href="{{route('admin.menus.add')}}" class="btn btn-primary">{{__('a.New')}}</a>
                    </li>
                </ul>
            @endif
        </div>
    </div>

    @include('admin.partials.notices')

    @if(cp_current_user_can('manage_menus'))
        <div class="row">
            <div class="col-md-4 grid-margin">
                <div class="tile">
                    <h3 class="tile-title">{{__('a.All')}}</h3>

                    <div class="list-wrapper">
                        <ul class="d-flex flex-column list-unstyled list">
                            @forelse($menus as $menu)
                                <li class="cp-flex cp-flex--center cp-flex--space-between mb-3 border-bottom">
                                    <span>{{$menu->name}}</span>
                                    <div>
                                        <a href="{{route('admin.menus.edit', ['id' => $menu->id])}}" class="mr-2">{{__('a.Edit')}}</a>
                                        <a href="#"
                                           data-confirm="{{__('a.Are you sure you want to delete this menu?')}}"
                                           data-form-id="form-menu-delete-{{$menu->id}}"
                                           class="js-menu-link-delete text-danger">
                                            {{__('a.Delete')}}
                                        </a>
                                        <form id="form-menu-delete-{{$menu->id}}" action="{{route('admin.menus.delete', $menu->id)}}" method="post" class="hidden">
                                            @csrf
                                        </form>
                                    </div>
                                </li>
                            @empty
                                <li class="borderless">
                                    <div class="bs-component">
                                        <div class="alert alert-info">
                                            {{__('a.No menus found. Why not create one?')}}
                                        </div>
                                    </div>
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
