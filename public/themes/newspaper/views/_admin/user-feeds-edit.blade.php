@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('np::m.Edit feed')}}</title>
@endsection

@section('main')
    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('np::m.Edit feed')}}</h1>
            </div>
        </div>
    </div>

    @include('admin.partials.notices')

    <div class="row">
        <div class="col-md-6">
            <div class="tile">
                <div class="card-body">
                    <h4 class="tile-title">{{__('np::m.Edit feed')}}</h4>
                    <form method="post" action="{{ route('admin.users.feeds.update', $feed->id) }}">
                        @csrf
                        <input type="hidden" name="feed_category_id" value="{{$category->id}}"/>

                        <div class="form-group">
                            <label for="feed-url">{{__('np::m.Url')}}</label>
                            <input type="text" class="form-control" name="feed_url" id="feed-url"
                                   value="{{old('feed_url') ? old('feed_url') : $feed->url}}"
                                   placeholder="{{__('np::m.Url')}}" maxlength="255" required/>
                        </div>
                        <div class="form-group">
                            <label for="feed-category">{{__('np::m.Category')}}</label>
                            <input type="text" class="form-control" name="feed_category" id="feed-category" value="{!! $category->name !!}" placeholder="{{__('np::m.Category name')}}" maxlength="50" required/>
                        </div>
                        <div class="form-group">
                            <div class="animated-checkbox">
                                <label for="feed-visibility">
                                    <input type="checkbox"
                                           id="feed-visibility"
                                           name="feed_private"
                                           value="1"
                                           @if($private) checked="checked" @endif
                                           class=""/>
                                    <span class="label-text">{{__('np::m.Private?')}}</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <p class="text-description">{{__('np::m.Please keep in mind that you can only delete your private feeds.')}}</p>
                        </div>
                        <button type="submit" class="btn btn-primary mr-2">{{__('np::m.Update')}}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>{{-- End .row --}}

@endsection
