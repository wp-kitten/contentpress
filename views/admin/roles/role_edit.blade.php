@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Edit role')}}</title>
@endsection

@section('main')
    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('a.Edit role')}}</h1>
            </div>
            <ul class="list-unstyled list-inline mb-0">
                <li class="">
                    <a class="btn btn-primary d-none d-md-block" href="{{route('admin.roles.all')}}">{{__('a.Back')}}</a>
                </li>
            </ul>
        </div>
    </div>

    @include('admin.partials.notices')

    @if($role)
        <div class="tile">
            <h3 class="tile-title">{{$role->display_name}}</h3>

            <section>
                <form method="post" action="{{route('admin.roles.update', $role->id)}}">
                    @csrf

                    @if(vp_is_role_protected($role->name))
                        <div class="alert alert-warning">
                            <p class="mb-0">
                                {{__("a.This role is protected which means the name cannot be changed due to the fact that it's used throughout the application and if changed it will cause a fatal error.")}}
                            </p>
                        </div>
                    @endif

                    <div class="form-group">
                        <label for="name">{{__('a.Name')}}</label>
                        <input id="name" name="name" type="text"
                               class="form-control"
                               maxlength="190"
                               @if(vp_is_role_protected($role->name)) disabled @endif
                               value="{{old('name') ? old('name') : $role->name}}"/>
                    </div>
                    <div class="form-group">
                        <label for="display_name">{{__('a.Display name')}}</label>
                        <input id="display_name" name="display_name" type="text" class="form-control" maxlength="190" value="{{old('display_name') ? old('display_name') : $role->display_name}}"/>
                    </div>
                    <div class="form-group">
                        <label for="description">{{__('a.Description')}}</label>
                        <textarea id="description" name="description" rows="5" class="form-control" maxlength="500">{!! old('description') ? old('description') : $role->description !!}</textarea>
                    </div>
                    <div class="form-group">
                        <button class="btn btn-primary">{{__('a.Update')}}</button>
                    </div>
                </form>
            </section>

        </div>
    @endif

@endsection
