{{--
    Style #1
        1 column, 3 inline posts

    col 1
        3 x small posts
--}}
@php
    /**@var App\Newspaper\NewspaperHelper $newspaperHelper*/
    /**@var App\Post $post */
    /**@var App\Category $category */
    $posts = $newspaperHelper->clearOutCache()->categoryTreeGetPosts($category, $postStatusID, 3);
@endphp
@if($posts)
    <div class="row">
        <div class="col-sm-12">
            <section class="section-cat-title mt-3">
                <h3>{!! $category->name !!}</h3>
            </section>
        </div>
        @foreach($posts as $postID => $post)
            <div class="col-xs-12 col-md-4">
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
                                {!! cp_ellipsis($post->title, 40) !!}
                            </a>
                        </h4>
                    </section>
                </article>
            </div>
        @endforeach
    </div>
@endif
