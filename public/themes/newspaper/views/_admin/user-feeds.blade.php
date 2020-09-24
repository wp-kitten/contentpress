@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('np::m.Your feeds')}}</title>
@endsection

@section('main')
    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('np::m.Your feeds')}}</h1>
                <p class="text-description">{{__('np::m.Your feeds will be automatically updated by a cron job scheduled to run every hour.')}}</p>
            </div>
        </div>
    </div>

    @include('admin.partials.notices')

    <div class="row">
        <div class="col-md-4">
            <div class="tile">
                <div class="card-body">
                    <h4 class="tile-title">{{__('np::m.Add new feed')}}</h4>
                    <form method="post" action="{{ route('admin.users.feeds.submit') }}">
                        @csrf

                        <div class="form-group">
                            <label for="website_url">{{__('np::m.Website or feed url')}}</label>
                            <input type="text" class="form-control" name="website_url" id="website_url" value="" placeholder="{{__('np::m.Url')}}" maxlength="255" required/>
                        </div>

                        <div class="form-group">
                            <div class="animated-checkbox">
                                <label for="is_feed">
                                    <input type="checkbox"
                                           id="is_feed"
                                           name="is_feed"
                                           value="1"
                                           class=""/>
                                    <span class="label-text">{{__('np::m.Is feed url?')}}</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="feed-category">{{__('np::m.Category')}}</label>
                            <input type="text" class="form-control" name="feed_category" id="feed-category" value="" placeholder="{{__('np::m.Category name')}}" maxlength="50" required/>
                        </div>

                        <div class="form-group">
                            <div class="animated-checkbox">
                                <label for="feed-visibility">
                                    <input type="checkbox"
                                           id="feed-visibility"
                                           name="feed_private"
                                           value="1"
                                           class=""/>
                                    <span class="label-text">{{__('np::m.Private?')}}</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <p class="text-description">{{__('np::m.Please keep in mind that you can only delete your private feeds.')}}</p>
                        </div>
                        <button type="submit" class="btn btn-primary mr-2">{{__('np::m.Add')}}</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="tile">
                <div class="card-body">
                    <h4 class="tile-title">
                        {{__('np::m.Your feeds') }}
                    </h4>

                    @if($feeds && $feeds->count())
                        <div class="list-wrapper">
                            <ul class="d-flex flex-column list-unstyled list">
                                @forelse($feeds as $feed)
                                    <li class="cp-flex cp-flex--center cp-flex--space-between mb-3 border-bottom">
                                        <p>
                                            @php
                                                $categories = $feed->category->parentCategories();
                                                $catsTree = [];
                                                if( ! empty($categories)){
                                                    foreach($categories as $cat){
                                                        $catsTree[] = '<a href="'.esc_attr(cp_get_category_link($cat)).'">'.$cat->name.'</a>';
                                                    }
                                                }
                                                $catsTree[] = '<a href="'.esc_attr(cp_get_category_link($feed->category)).'">'.$feed->category->name.'</a>';
                                            @endphp
                                            <span class="d-block text-description">{!! implode('/', $catsTree) !!}</span>
                                            <span class="d-block" title="{{$feed->url}}">{{cp_ellipsis($feed->url)}}</span>
                                        </p>
                                        <div>
                                            <a href="{{route('admin.users.feeds.edit', $feed->id)}}" class="mr-2">{{__('np::m.Edit')}}</a>
                                            {{--// Only private feeds can be deleted --}}
                                            @if(np_isUserFeed($feed->id, NPFR_CATEGORY_PRIVATE))
                                                <a href="#"
                                                   class="text-danger"
                                                   data-confirm="{{__('np::m.Are you sure you want to delete this feed?')}}"
                                                   data-form-id="form-feed-delete-{{$feed->id}}">
                                                    {{__('np::m.Delete')}}
                                                </a>
                                                <form id="form-feed-delete-{{$feed->id}}" action="{{route('admin.users.feeds.delete', $feed->id)}}" method="post" class="hidden">
                                                    @csrf
                                                </form>
                                            @endif
                                        </div>
                                    </li>
                                @empty
                                    <li class="borderless">
                                        <div class="bs-component">
                                            <div class="alert alert-info">
                                                {{__('np::m.No feeds found. Why not add one?')}}
                                            </div>
                                        </div>
                                    </li>
                                @endforelse
                            </ul>
                        </div>

                        {!! $feeds->render() !!}
                    @else
                        <div class="bs-component">
                            <div class="alert alert-info">
                                {{__('np::m.No feeds found. Why not add one?')}}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>{{-- End .row --}}

@endsection
