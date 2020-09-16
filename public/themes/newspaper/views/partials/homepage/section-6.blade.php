{{--
    Style #6
        6 stacked posts with image
--}}
@php
    /**@var App\Newspaper\NewspaperHelper $newspaperHelper*/
    /**@var App\Post $post */
    /**@var App\Category $category */
    $posts = $newspaperHelper->clearOutCache()->categoryTreeGetPosts($category, $postStatusID, 6);
@endphp
@if($posts)
    <div class="row">
        <div class="col-sm-12">
            <section class="section-cat-title mt-3">
                <h3>{!! $category->name !!}</h3>
            </section>
        </div>
        @foreach($posts as $postID => $post)
            <div class="col-sm-12">
                <article class="hentry-loop mb-3">
                    <div class="row">
                        <div class="col-sm-12 col-md-4">
                            <header class="hentry-header">
                                {!! $newspaperHelper->getPostImageOrPlaceholder($post, '', 'image-responsive', ['alt' => $post->title]) !!}
                            </header>
                        </div>
                        <div class="col-sm-12 col-md-8 np-relative">
                            <section class="hentry-content">
                                <h4 class="hentry-title">
                                    <a href={{cp_get_permalink($post)}} class="text-info">
                                        {!! cp_ellipsis($post->title, 70) !!}
                                    </a>
                                </h4>
                            </section>
                            <section class="hentry-excerpt">{!! $post->excerpt !!}</section>

                            <div class="hentry-category bg-danger mr-3">
                                <a href={{cp_get_category_link($category)}} class="text-light">
                                    {!! $category->name !!}
                                </a>
                            </div>
                        </div>
                    </div>
                </article>
            </div>
        @endforeach
    </div>
@endif
