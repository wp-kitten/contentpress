{{--
    Style #3
        2 columns

    col 1
        1 x large post
    col 2
        4 x small posts
--}}
@inject('postStatus', App\Models\PostStatus)
@php
    /**@var App\Newspaper\NewspaperHelper $newspaperHelper*/
    /**@var App\Models\Post $post */

    $cacheKey = "home-section-3-{$category->id}";
    $posts = $cache->get($cacheKey);
    if( ! $posts ) {
        $posts = $newspaperHelper->clearOutCache()->categoryTreeGetPosts($category, $postStatusID, 5);
         if( ! empty( $posts ) ) {
            $cache->set($cacheKey, $posts);
        }
   }
    $postsLeft = [];
    $postsRight = [];
    if(! empty($posts)){
        $i = 0;
        foreach($posts as $pid => $p){
            if($i >= 1){
                $postsRight[$pid] = $p;
            }
            else {
                $postsLeft[$pid] = $p;
            }
            $i++;
        }
    }
@endphp
<div class="row s-3">
    @if(!empty($posts))
        <div class="col-sm-12">
            <section class="section-cat-title mt-3">
                <h3>{!! $category->name !!}</h3>
            </section>
        </div>
    @endif

    @if(! empty($postsLeft))
        <div class="col-sm-12 col-md-6">
            @foreach($postsLeft as $postID => $post)
                <article class="hentry-loop mb-3">
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
                </article>
            @endforeach
        </div>
    @endif
    @if(! empty($postsRight))
        @php $ix = 0; @endphp
        <div class="col-sm-12 col-md-6">
            @foreach($postsRight as $postID => $post)
                <article class="hentry-loop mb-3">
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
                </article>
            @endforeach
        </div>
    @endif
</div>
