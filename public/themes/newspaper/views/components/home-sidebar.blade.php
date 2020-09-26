@php
    /**@var App\Newspaper\NewspaperHelper $newspaperHelper*/
        $entries = [];
        $categories = $newspaperHelper->getTopCategories();
        $posts = $newspaperHelper->getRandomPosts(5);
        $tags = App\Models\Tag::latest()->limit(100)->get();
        if($categories){
            $postStatusPublishID = (new App\Models\PostStatus())->where('name', 'publish')->first()->id;
            foreach($categories as $category){
                $numPosts = $newspaperHelper->categoryTreeCountPosts($category);
                if( ! empty($numPosts)){
                    $entries[$category->id] = $numPosts;
                }
            }
        }
@endphp
<div class="widget widget-categories bg-white p-3">
    <div class="widget-title">
        <h3 class="text-danger">{{__('np::m.Categories')}}</h3>
    </div>
    <div class="widget-content">
        <ul class="list-unstyled mt-3 mb-3 categories-list">
            @if(! empty($entries))
                @forelse($categories as $category)
                    @if(isset($entries[$category->id]))
                        <li>
                            <a class="category-name text-info" href="{{cp_get_category_link($category)}}">{!! $category->name !!}</a>
                            <span class="num-posts text-dark">{{$entries[$category->id]}}</span>
                        </li>
                    @endif
                @empty
                @endforelse
            @endif
        </ul>
    </div>
</div>

<div class="widget widget-search bg-white p-3 mt-2">
    <div class="widget-title">
        <h3 class="text-danger">{{__('np::m.Search')}}</h3>
    </div>
    <div class="widget-content mt-4 mb-2">
        {{cp_search_form()}}
    </div>
</div>

<div class="widget widget-posts bg-white p-3 pb-0 mt-4">
    <div class="widget-title">
        <h3 class="text-danger">{{__('np::m.Random posts')}}</h3>
    </div>
    <div class="widget-content">
        <ul class="list-unstyled mt-3 posts-list">
            @if($posts && $posts->count())
                @foreach($posts as $post)
                    <li class="mb-3">
                        {!! $newspaperHelper->getPostImageOrPlaceholder($post, '', 'image-responsive rounded', ['alt' => $post->title]) !!}
                        <a href="{{cp_get_permalink($post)}}" class="text-info ml-2" title="{{$post->title}}">
                            {!! cp_ellipsis(wp_kses_post($post->title), 60) !!}
                        </a>
                    </li>
                @endforeach
            @endif
        </ul>
    </div>
</div>

<div class="widget widget-tags bg-white p-3 pb-0 mt-4">
    <div class="widget-title">
        <h3 class="text-danger">{{__('np::m.Tags')}}</h3>
    </div>
    <div class="widget-content">
        <ul class="list-unstyled mt-3 tags-list">
            @if(count($tags))
                <li class="mb-3">
                    @foreach($tags as $tag)
                        <a href="{{cp_get_tag_link($tag)}}" class="text-info ml-2">
                            {!! wp_kses_post($tag->name) !!}
                        </a>
                    @endforeach
                </li>
            @endif
        </ul>
    </div>
</div>

