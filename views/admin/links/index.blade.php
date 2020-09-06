@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Links')}}</title>
@endsection

@section('main')

    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('a.Links')}}</h1>
            </div>
        </div>
    </div>

    @include('admin.partials.notices')

    @if(cp_current_user_can('manage_options'))
        <div class="row">
            <div class="col-md-4">
                <div class="tile">
                    <h3 class="tile-title">{{__('a.Add new')}}</h3>

                    <form method="post" action="{{route('admin.links.create', ['id' => request('id')])}}">
                        <div class="form-group">
                            <label for="link-title-field">{{__('a.Title')}}</label>
                            <input type="text" class="form-control" value="" name="title" id="link-title-field"/>
                        </div>

                        <div class="form-group">
                            <label for="link-url-field">{{__('a.Url')}}</label>
                            <input type="url" class="form-control" value="" name="url" id="link-url-field"/>
                        </div>

                        <button type="submit" class="btn btn-primary">{{__('a.Add')}}</button>
                        @csrf
                    </form>
                </div>
            </div>



            <div class="col-md-6">
                <div class="tile">
                    <h3 class="tile-title">{{__('a.All')}}</h3>

                    <div class="list-wrapper">
                        <ul class="d-flex flex-column list-unstyled list">
                            @forelse($links as $link)
                                <li class="cp-flex cp-flex--center cp-flex--space-between mb-3 border-bottom">
                                    <p>
                                        <span class="d-block">{{$link->title}}</span>
                                        <span class="d-block text-description">{{$link->url}}</span>
                                    </p>
                                    <div>
                                        <a href="{{route('admin.links.edit', ['id' => $link->id])}}" class="mr-2">{{__('a.Edit')}}</a>
                                        <a href="#"
                                           class="text-danger"
                                           data-confirm="{{__('a.Are you sure you want to delete this link?')}}"
                                           data-form-id="form-link-delete-{{$link->id}}">
                                            {{__('a.Delete')}}
                                        </a>
                                        <form id="form-link-delete-{{$link->id}}" action="{{route('admin.links.delete', $link->id)}}" method="post" class="hidden">
                                            @csrf
                                        </form>
                                    </div>
                                </li>
                            @empty
                                <li class="borderless">
                                    <div class="bs-component">
                                        <div class="alert alert-info">
                                            {{__('a.No links found. Why not create one?')}}
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
