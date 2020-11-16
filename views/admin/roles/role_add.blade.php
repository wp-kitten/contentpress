@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Add new role')}}</title>
@endsection

@section('main')
    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('a.Add new role')}}</h1>
            </div>
            <ul class="list-unstyled list-inline mb-0">
                <li class="">
                    <a class="btn btn-primary d-none d-md-block" href="{{route('admin.roles.all')}}">{{__('a.Back')}}</a>
                </li>
            </ul>
        </div>
    </div>

    @include('admin.partials.notices')

    <div class="tile">

        <section>
            <form method="post" action="{{route('admin.roles.create')}}">
                @csrf

                <div class="form-group">
                    <label for="name">{{__('a.Name')}}</label>
                    <input id="name" name="name" type="text"
                           class="form-control"
                           maxlength="190"
                           value="{{old('name')}}"/>
                </div>
                <div class="form-group">
                    <label for="display_name">{{__('a.Display name')}}</label>
                    <input id="display_name" name="display_name" type="text" class="form-control" maxlength="190" value="{{old('display_name')}}"/>
                </div>
                <div class="form-group">
                    <label for="description">{{__('a.Description')}}</label>
                    <textarea id="description" name="description" rows="5" class="form-control" maxlength="500">{!! old('description') !!}</textarea>
                </div>
                <div class="form-group">
                    <button class="btn btn-primary">{{__('a.Insert')}}</button>
                </div>
            </form>
        </section>
    </div>

@endsection
