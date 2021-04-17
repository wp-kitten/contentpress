@inject('mediaHelper', 'App\Helpers\MediaHelper')
@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Edit media file')}}</title>
@endsection

@section('main')

    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('a.Edit media file')}}</h1>
            </div>
            <ul class="list-unstyled list-inline mb-0">
                <li class="">
                    <a href="{{route('admin.media.all')}}" class="btn btn-primary">{{__('a.Back')}}</a>
                </li>
            </ul>
        </div>
    </div>

    @include('admin.partials.notices')

    <div class="row">
        <div class="col-md-8">
            <div class="tile">
                @if(vp_current_user_can('update_media'))
                    <form method="post" action="{{route('admin.media.update', $file->id)}}">
                        <div class="form-group">
                            <label for="title">{{__('a.Title')}}</label>
                            <input type="text" name="title" id="title" class="form-control" value="{{$file->title}}"/>
                        </div>
                        <div class="form-group">
                            <label for="alt">{{__('a.Alternative title')}}</label>
                            <input type="text" name="alt" id="alt" class="form-control" value="{{$file->alt}}"/>
                        </div>
                        <div class="form-group">
                            <label for="caption">{{__('a.Caption')}}</label>
                            <input type="text" name="caption" id="caption" class="form-control" value="{{$file->caption}}"/>
                        </div>

                        <button type="submit" name="btn-save" class="btn btn-primary mr-2">
                            {{__('a.Save')}}
                        </button>

                        @csrf
                    </form>
                @else
                    <div class="bs-component">
                        <div class="alert alert-warning">
                            {{__('a.You are not allowed to perform this action.')}}
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-md-4">
            <div class="tile">
                <img src="{{$mediaHelper->getUrl($file->path)}}" alt="{{$file->title}}" class="valpress-thumbnail"/>

                <div class="mt-4">
                    <label for="media-url-field">{{__('a.Url')}}</label>
                    <input type="text" id="media-url-field" value="{{$mediaHelper->getUrl($file->path)}}" class="input-wide" readonly/>
                </div>

                @if(vp_current_user_can('update_media'))
                    <form method="post" action="{{route('admin.media.delete', $file->id)}}" class="mt-4">

                        <button type="submit"
                                name="btn-delete"
                                data-confirm="{{__('a.Are you sure you want to delete this media file?')}}"
                                class="btn btn-danger mr-2">
                            {{__('a.Delete')}}
                        </button>

                        @csrf
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection
