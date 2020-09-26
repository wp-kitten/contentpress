{{--
    Style #5
        6 inline posts with image
--}}
@php
    /**@var App\Newspaper\NewspaperHelper $newspaperHelper*/
    /**@var App\Models\Post $post */
    /**@var App\Models\Category $category */
    $cacheKey = "home-section-5-{$category->id}";
    $posts = $cache->get($cacheKey);
    if( ! $posts ) {
        $posts = $newspaperHelper->clearOutCache()->categoryTreeGetPosts($category, $postStatusID, 6);
        if( ! empty( $posts ) ) {
            $cache->set( $cacheKey, $posts );
        }
    }
@endphp
@if($posts)
    <div class="row s-5">
        <div class="col-sm-12">
            <section class="section-cat-title mt-3">
                <h3>{!! $category->name !!}</h3>
            </section>
        </div>
        @foreach($posts as $postID => $post)
            <div class="col-xs-12 col-md-4">
                <article class="hentry-loop special mb-3">
                    <header class="hentry-header">
                        {!! $newspaperHelper->getPostImageOrPlaceholder($post, '', 'image-responsive', ['alt' => $post->title]) !!}
                        <h4 class="hentry-title">
                            <a href={{cp_get_permalink($post)}} class="text-info">
                                {!! cp_ellipsis($post->title, 50) !!}
                            </a>
                        </h4>
                    </header>
                </article>
            </div>
        @endforeach
    </div>
@endif
