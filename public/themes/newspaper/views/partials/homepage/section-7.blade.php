{{--
    Style #7
        12 carousel posts with image
--}}
@php
    /**@var App\Newspaper\NewspaperHelper $newspaperHelper*/
    /**@var App\Models\Post $post */
    /**@var App\Models\Category $category */
    $cacheKey = "home-section-7-{$category->id}";
    $posts = $cache->get($cacheKey);
    if( ! $posts ) {
        $posts = $newspaperHelper->clearOutCache()->categoryTreeGetPosts($category, $postStatusID, 12);
        if( ! empty( $posts ) ) {
            $cache->set( $cacheKey, $posts );
        }
    }
@endphp
@if($posts)
    <div class="row s-7">
        <div class="col-sm-12">
            <section class="section-cat-title mt-3">
                <h3>{!! $category->name !!}</h3>
            </section>
        </div>

        <div class="col-sm-12">
            <section class="section-7-posts-carousel related-posts">
                <section class="slider-nav text-right">
                    <a class="btn-prev" href="#" title="{{__('np::m.Previous')}}"><i class="fas fa-chevron-left nav-icon"></i></a>
                    <a class="btn-next" href="#" title="{{__('np::m.Next')}}"><i class="fas fa-chevron-right nav-icon"></i></a>
                </section>
                <div class="siema-slider siema slider-wrap mt-3">
                    @foreach($posts as $post)
                        <div class="slide-item">
                            <article class="hentry-loop carousel">
                                <div class="hentry-header">
                                    {!! $newspaperHelper->getPostImageOrPlaceholder($post, '', 'image-responsive', ['alt' => $post->title]) !!}
                                    <h4 class="hentry-title">
                                        <a href="{{cp_get_permalink($post)}}">{!! cp_ellipsis($post->title, 50) !!}</a>
                                    </h4>
                                </div>
                                <div class="np-relative">
                                    <div class="hentry-content">
                                        <div class="hentry-meta">
                                            <span>{{cp_the_date($post)}}</span>
                                            <span class="hentry-category">
                                                <a href={{cp_get_category_link($category)}}>
                                                    {!! $category->name !!}
                                                </a>
                                        </span>
                                        </div>
                                    </div>
                                    <div class="pt-0 pl-3 pb-3 pr-3">{!! cp_ellipsis($post->excerpt, 80) !!}</div>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
@endif
