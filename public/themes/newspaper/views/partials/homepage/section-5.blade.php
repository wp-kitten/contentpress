{{--
    Style #5
        6 inline posts with image
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
            <div class="col-xs-12 col-md-4">
                <article class="hentry-loop special mb-3">
                    <header class="hentry-header">
                        <img src="{{$newspaperHelper->getPostImageOrPlaceholder($post)}}" alt="{{$post->title}}" class="image-responsive"/>
                        <div class="hentry-category bg-danger">
                            <a href={{cp_get_category_link($category)}} class="text-light">
                                {!! $category->name !!}
                            </a>
                        </div>
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
