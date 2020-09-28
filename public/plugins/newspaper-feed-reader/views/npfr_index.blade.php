@inject('catModel', App\Models\Category)
@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('npfr::m.Feeds')}}</title>
@endsection

@section('main')

    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('npfr::m.Feeds')}} <small>({{$numFeeds}})</small></h1>
                <p class="text-description">{{__('npfr::m.The feed importer is triggered by a cron job scheduled to run every hour.')}}</p>
            </div>
            <ul class="list-unstyled list-inline mb-0">
                <li class="">
                    @if(! empty($numFeeds) && $numFeeds <= 10 )
                        <a class="btn btn-primary d-none d-inline-block" href="#"
                           onclick="event.preventDefault(); document.getElementById('cpfr-import-feeds').submit();">
                            {{__('npfr::m.Import Feeds')}}
                        </a>
                        <form id="cpfr-import-feeds" method="post" action="{{route('admin.feed_reader.feeds.import')}}" class="hidden">
                            @csrf
                        </form>
                    @endif

                    @if(! App\Models\Category::where('name', \Illuminate\Support\Str::title('Romania'))->first())
                        <a class="btn btn-dark d-none d-inline-block" href="#"
                           onclick="event.preventDefault(); document.getElementById('cpfr-import-default-content').submit();"
                           title="{{__('npfr::m.Creates the default pages, categories & feed urls')}}">
                            {{__('npfr::m.Import default content')}}
                        </a>
                        <form id="cpfr-import-default-content" method="post" action="{{route('admin.feeds.import_default_content')}}" class="hidden">
                            @csrf
                        </form>
                    @endif
                </li>
            </ul>
        </div>
    </div>

    @include('admin.partials.notices')

    @if(cp_current_user_can('manage_options'))
        <div class="row cpfr-page-wrap">
            <div class="col-md-4">
                <div class="tile">
                    <h3 class="tile-title">{{__('npfr::m.Add new')}}</h3>

                    <form method="post" action="{{route('admin.feed_reader.feeds.create', ['id' => request('id')])}}">

                        <div class="form-group">
                            <label for="feed-url-field">{{__('npfr::m.Url')}}</label>
                            <input type="url" class="form-control" value="" name="url" id="feed-url-field"/>
                        </div>

                        <div class="form-group">
                            <label for="cat-name-field">{{__('npfr::m.Category')}}</label>
                            <select id="cat-name-field" name="id" class="selectize-control">
                                @forelse($categories as $categoryID => $subcategories)
                                    @php
                                        $cat = App\Models\Category::find($categoryID);
                                        if( empty( $subcategories ) ) {
                                            echo '<option value="'.esc_attr($categoryID).'">'.$cat->name.'</option>';
                                        }
                                        else {
                                            echo '<optgroup label="'.$cat->name.'">';
                                            echo '<option value="'.esc_attr($categoryID).'">'.$cat->name.'</option>';
                                            foreach($subcategories as $subcategoryID){
                                                $subcat = App\Models\Category::find($subcategoryID);
                                                echo '<option value="'.esc_attr($subcategoryID).'">'.$subcat->name.'</option>';
                                            }
                                            echo '</optgroup>';
                                        }
                                    @endphp
                                @empty
                                @endforelse
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">{{__('npfr::m.Add')}}</button>
                        @csrf
                    </form>
                </div>
            </div>

            <div class="col-md-8">
                <div class="tile">
                    <h3 class="tile-title">{{__('npfr::m.All')}}</h3>

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
                                        <div class="d-inline mr-2">
                                            <a class="text-success" href="#"
                                               onclick="event.preventDefault(); document.getElementById('npfr-import-feed-{{$feed->id}}').submit();">
                                                {{__('npfr::m.Import Feed')}}
                                            </a>
                                            <form id="npfr-import-feed-{{$feed->id}}" method="post" action="{{route('admin.feed_reader.feeds.import_feed', $feed->id)}}" class="hidden">
                                                @csrf
                                            </form>
                                        </div>

                                        <a href="{{route('admin.feed_reader.feeds.edit', ['id' => $feed->id])}}" class="mr-2">{{__('npfr::m.Edit')}}</a>
                                        <a href="#"
                                           class="text-danger"
                                           data-confirm="{{__('npfr::m.Are you sure you want to delete this feed?')}}"
                                           data-form-id="form-feed-delete-{{$feed->id}}">
                                            {{__('npfr::m.Trash')}}
                                        </a>
                                        <form id="form-feed-delete-{{$feed->id}}" action="{{route('admin.feed_reader.feeds.delete', $feed->id)}}" method="post" class="hidden">
                                            @csrf
                                        </form>
                                    </div>
                                </li>
                            @empty
                                <li class="borderless">
                                    <div class="bs-component">
                                        <div class="alert alert-info">
                                            {{__('npfr::m.No feeds found. Why not add one?')}}
                                        </div>
                                    </div>
                                </li>
                            @endforelse
                        </ul>

                        {{-- Render pagination --}}
                        @if($feeds)
                            {{ $feeds->render() }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
