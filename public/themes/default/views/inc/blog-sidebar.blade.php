@php

    $postType = App\Models\PostType::where( 'name', 'post' )->first();
    $languageID = cp_get_frontend_user_language_id();
    $tags = App\Models\Tag::where( 'post_type_id', $postType->id )->latest( 'language_id', $languageID )->limit( 20 )->get();
    $_categories = App\Models\Category::where( 'post_type_id', $postType->id )->where( 'language_id', $languageID )->where( 'category_id', null )->latest()->get();
    $categories = [];
    if ( $_categories ) {
        foreach ( $_categories as $category ) {
            $categories[ $category->id ] = [
                'category' => $category,
                'num_posts' => $category->posts()->count(),
            ];
        }
    }

@endphp

<div class="blog-sidebar">

    <div class="widget widget-search mt-0">
        <div class="widget-title">
            <h3 class="text-danger mt-0">{{__('cpdt::m.Search')}}</h3>
        </div>
        <div class="widget-content mt-4 mb-2">
            {{cp_search_form()}}
        </div>
    </div>

    @if($categories)
        <div class="widget widget-categories">
            <div class="widget-title">
                <h3 class="text-danger">{{__('cpdt::m.Categories')}}</h3>
            </div>
            <div class="widget-content">
                <ul class="list-unstyled mt-3 mb-3 categories-list">
                    @foreach($categories as $categoryID => $info)
                        <li>
                            <a class="category-name text-info text-capitalize" href="{{cp_get_category_link($info['category'])}}">
                                {!! $info['category']->name !!}
                            </a>
                            <span class="text-grey">{{$info['num_posts']}}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if($tags && $tags->count())
        <div class="widget widget-tags">
            <div class="widget-title">
                <h3 class="text-danger">{{__('cpdt::m.Tags')}}</h3>
            </div>
            <div class="widget-content">
                <ul class="list-unstyled mt-3 tags-list">
                    <li class="mb-3">
                        @foreach($tags as $tag)
                            <a href="{{cp_get_tag_link($tag)}}" class="text-info ml-2">
                                {!! wp_kses_post($tag->name) !!}
                            </a>
                        @endforeach
                    </li>
                </ul>
            </div>
        </div>
    @endif

</div>
