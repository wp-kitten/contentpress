@php
    /**@var App\Newspaper\NewspaperHelper $newspaperHelper*/
    $posts = $newspaperHelper->getRandomPosts(8);
@endphp

<div class="widget widget-search bg-white p-3">
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
            @if(count($posts))
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
