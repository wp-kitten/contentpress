<div class="col-sm-12">
    <div class="blog-sidebar">
        <svg wire:loading class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>

        @if($categories)
            <div class="widget widget-categories">
                <div class="widget-title">
                    <h3 class="text-danger mt-0">{{__('cpdt::m.Categories')}}</h3>
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
</div>
