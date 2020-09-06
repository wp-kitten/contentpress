@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Edit link')}}</title>
@endsection

@section('main')

    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('a.Edit link')}}</h1>
            </div>
            @if(cp_current_user_can('manage_options'))
                <ul class="list-unstyled list-inline mb-0">
                    <li class="">
                        <a href="{{route('admin.links.all')}}" class="btn btn-primary">{{__('a.Back')}}</a>
                    </li>
                </ul>
            @endif
        </div>
    </div>

    @include('admin.partials.notices')

    @if(cp_current_user_can('manage_options'))
        <div class="row">
            <div class="col-md-4">
                <div class="tile">
                    <h3 class="tile-title">{{__('a.Edit')}}</h3>

                    <form method="post" action="{{route('admin.links.update', $link->id)}}">
                        <div class="form-group">
                            <label for="link-title-field">{{__('a.Title')}}</label>
                            <input type="text" class="form-control" value="{{$link->title}}" name="title" id="link-title-field"/>
                        </div>

                        <div class="form-group">
                            <label for="link-url-field">{{__('a.Url')}}</label>
                            <input type="url" class="form-control" value="{{$link->url}}" name="url" id="link-url-field"/>
                        </div>

                        <button type="submit" class="btn btn-primary">{{__('a.Update')}}</button>
                        @csrf
                    </form>
                </div>
            </div>

        </div>
    @endif
@endsection
