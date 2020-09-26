{{--
    Style #2
        2 columns, each 5 vertical posts

    col 1, col 2
        1 x large post
        5 x small posts
--}}
@inject('postStatus', App\Models\PostStatus)
@php
    /**@var App\Newspaper\NewspaperHelper $newspaperHelper*/
    /**@var App\Models\Post $post */

    $cacheKey = "home-section-2-{$category->id}";
    $posts = $cache->get($cacheKey);
    if( ! $posts ) {
        $posts = $newspaperHelper->clearOutCache()->categoryTreeGetPosts($category, $postStatusID, 10);
        if( ! empty( $posts ) ) {
            $cache->set($cacheKey, $posts);
        }
    }
    $postsLeft = [];
    $postsRight = [];
    if(! empty($posts)){
        $i = 0;
        foreach($posts as $pid => $p){
            if($i > 4){
                $postsRight[$pid] = $p;
            }
            else {
                $postsLeft[$pid] = $p;
            }
            $i++;
        }
    }
@endphp
<div class="row s-2">
    @if(!empty($posts))
        <div class="col-sm-12">
            <section class="section-cat-title mt-3">
                <h3>{!! $category->name !!}</h3>
            </section>
        </div>
    @endif

    @if(! empty($postsLeft))
        @php $ix = 0; @endphp
        <div class="col-sm-12 col-md-6">
            @foreach($postsLeft as $postID => $post)
                <article class="hentry-loop mb-3">
                    @if(0 == $ix)
                        <header class="hentry-header">
                            {!! $newspaperHelper->getPostImageOrPlaceholder($post, '', 'image-responsive', ['alt' => $post->title]) !!}
                            <div class="hentry-category bg-danger">
                                <a href={{cp_get_category_link($category)}} class="text-light">
                                    {!! $category->name !!}
                                </a>
                            </div>
                        </header>
                        <section class="hentry-content">
                            <h4 class="hentry-title">
                                <a href={{cp_get_permalink($post)}} class="text-info">
                                    {!! cp_ellipsis($post->title, 100) !!}
                                </a>
                            </h4>
                        </section>

                    @else
                        <div class="row">
                            <div class="col-sm-12 col-md-4">
                                <header class="hentry-header full-h">
                                    {!! $newspaperHelper->getPostImageOrPlaceholder($post, '', 'image-responsive', ['alt' => $post->title]) !!}
                                </header>
                            </div>
                            <div class="col-sm-12 col-md-8">
                                <section class="hentry-content">
                                    <h4 class="hentry-title @if(!wp_is_mobile()) title-small font-default @endif">
                                        <a href={{cp_get_permalink($post)}} class="text-info">
                                            {!! cp_ellipsis($post->title, 70) !!}
                                        </a>
                                    </h4>
                                </section>
                            </div>
                        </div>
                    @endif
                </article>
                @php $ix++; @endphp
            @endforeach
        </div>
    @endif
    @if(! empty($postsRight))
        @php $ix = 0; @endphp
        <div class="col-sm-12 col-md-6">
            @foreach($postsRight as $postID => $post)
                <article class="hentry-loop mb-3">
                    @if(0 == $ix)
                        <header class="hentry-header">
                            {!! $newspaperHelper->getPostImageOrPlaceholder($post, '', 'image-responsive', ['alt' => $post->title]) !!}
                            <div class="hentry-category bg-danger">
                                <a href={{cp_get_category_link($category)}} class="text-light">
                                    {!! $category->name !!}
                                </a>
                            </div>
                        </header>
                        <section class="hentry-content">
                            <h4 class="hentry-title">
                                <a href={{cp_get_permalink($post)}} class="text-info">
                                    {!! cp_ellipsis($post->title, 100) !!}
                                </a>
                            </h4>
                        </section>

                    @else
                        <div class="row">
                            <div class="col-sm-12 col-md-4">
                                <header class="hentry-header full-h">
                                    {!! $newspaperHelper->getPostImageOrPlaceholder($post, '', 'image-responsive', ['alt' => $post->title]) !!}
                                </header>
                            </div>
                            <div class="col-sm-12 col-md-8">
                                <section class="hentry-content">
                                    <h4 class="hentry-title @if(!wp_is_mobile()) title-small font-default @endif">
                                        <a href={{cp_get_permalink($post)}} class="text-info">
                                            {!! cp_ellipsis($post->title, 60) !!}
                                        </a>
                                    </h4>
                                </section>
                            </div>
                        </div>
                    @endif
                </article>
                @php $ix++; @endphp
            @endforeach
        </div>
    @endif
</div>
