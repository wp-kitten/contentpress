@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Menus')}}</title>
@endsection

@section('main')

    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('a.New Menu')}}</h1>
            </div>
        </div>
    </div>

    @include('admin.partials.notices')

    @if(cp_current_user_can('manage_menus'))
        <div class="row">
            <div class="col-md-3">
                <div class="tile">
                    <h4 class="tile-title">{{__('a.New menu')}}</h4>
                    <form method="post" action="{{route('admin.menus.create')}}">
                        <div class="form-group">
                            <label for="menu_name-field">{{__('a.Name')}}</label>
                            <input type="text" class="form-control" value="{{old('menu_name')}}" name="menu_name" id="menu_name-field"/>
                        </div>

                        <button type="submit" class="btn btn-primary">{{__('a.Create')}}</button>
                        @csrf
                    </form>
                </div>
            </div>

            <div class="col-md-4">
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
                                           class="text-danger"
                                           data-confirm="{{__('a.Are you sure you want to delete this menu?')}}"
                                           data-form-id="form-menu-delete-{{$menu->id}}">
                                            {{__('a.Delete')}}
                                        </a>
                                        <form id="form-menu-delete-{{$menu->id}}" action="{{route('admin.menus.delete', $menu->id)}}" method="post" class="hidden">
                                            @csrf
                                        </form>
                                    </div>
                                </li>
                            @empty
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
