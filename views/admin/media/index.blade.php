@php
    $range = range(18, 180, 18);
@endphp

@inject('mediaHelper', App\Helpers\MediaHelper)
@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Media')}}</title>
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

            @if(cp_current_user_can('add_media'))
                <ul class="list-unstyled list-inline mb-0">
                    <li>
                        <a href="{{route('admin.media.add')}}" class="btn btn-primary">{{__('a.Upload')}}</a>
                    </li>
                </ul>
            @endif
        </div>
    </div>

    @include('admin.partials.notices')

    <div class="tile mb-4">
        <div class="row">
            <div class="col-md-12">
                <div class="valpress-media-list">
                    @forelse($files as $file)
                        <div class="item">
                            <a href="{{$mediaHelper->getUrl($file->path)}}" class="js-valpress-thumbnail thumbnail" data-id="{{$file->id}}">
                                <img src="{{cp_image($file, 'cp_media_thumb')}}" alt="{{$file->title}}" class="valpress-thumbnail" title="{{__('a.Click to preview')}}"/>
                            </a>
                            <div class="thumbnail-actions cp-flex cp-flex--center justify-content-center">
                                <a href="{{route('admin.media.edit', $file->id)}}" class="thumbnail-edit-link">{{__('a.Edit')}}</a>
                                <a href="#"
                                   data-confirm="{{__('a.Are you sure you want to delete this media file?')}}"
                                   data-form-id="form-image-delete-{{$file->id}}">{{__('a.Delete')}}</a>
                                <form method="post" id="form-image-delete-{{$file->id}}" action="{{route('admin.media.delete', $file->id)}}" class="hidden">
                                    @csrf
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="bs-component">
                            <div class="alert alert-info">{{__('a.No files found')}}</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="cp-pagination-center mt-5">
                    {{$files->render()}}
                </div>
            </div>
        </div>
    </div>
@endsection
