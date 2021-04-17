@php
    $range = range(18, 180, 18);
@endphp

@inject('mediaHelper', 'App\Helpers\MediaHelper')
@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Search Media')}}</title>
@endsection
@section('main')

    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('a.Media')}}</h1>
            </div>

            <form class="form-inline" action="{{route('admin.media.search')}}">
                <input type="text" value="{{request()->get('s')}}" class="form-control border mr-3 search-input" name="s" placeholder="{{__('a.Search')}}"/>
                <button type="submit" class="btn btn-primary">{{__('a.Search')}}</button>
                @csrf
            </form>

            @if(vp_current_user_can('add_media'))
                <ul class="list-unstyled list-inline mb-0">
                    <li>
                        <a href="{{route('admin.media.add')}}" class="btn btn-primary">{{__('a.Upload')}}</a>
                    </li>
                </ul>
            @endif
        </div>
    </div>


    @include('admin.partials.notices')

    @if(vp_current_user_can('list_media'))
        <div class="row valpress-media-list">
            @forelse($files as $file)
                <div class="entry">
                    <div class="item">
                        <div class="thumbnail">
                            <div class="centered">
                                <a href="{{$mediaHelper->getUrl($file->path)}}" class="js-valpress-thumbnail" data-id="{{$file->id}}">
                                    <img src="{{$mediaHelper->getUrl($file->path)}}" alt="{{$file->title}}" class="valpress-thumbnail" title="{{__('a.Click to preview')}}"/>
                                </a>
                                <div class="flex thumbnail-actions align-content-between justify-content-between">
                                    <a href="{{route('admin.media.edit', $file->id)}}" class="thumbnail-edit-link ml-2 text-primary">{{__('a.Edit')}}</a>
                                    <a href="#"
                                       class="mr-2 text-danger"
                                       data-confirm="{{__('a.Are you sure you want to delete this media file?')}}"
                                       data-form-id="form-image-delete-{{$file->id}}">
                                        {{__('a.Delete')}}
                                    </a>
                                    <form method="post" id="form-image-delete-{{$file->id}}" action="{{route('admin.media.delete', $file->id)}}" class="hidden">
                                        @csrf
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-md-12">
                    <div class="bs-component">
                        <div class="alert alert-info">{{__('a.No files found')}}</div>
                    </div>
                </div>
            @endforelse
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="cp-pagination-center mt-5">
                    {!! ($files ? $files->appends(request()->except('page'))->links() : '') !!}
                </div>
            </div>
        </div>
    @endif
@endsection
