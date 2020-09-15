{{--
    Style #7
        12 carousel posts with image
--}}
@php
    /**@var App\Newspaper\NewspaperHelper $newspaperHelper*/
    /**@var App\Post $post */
    /**@var App\Category $category */
    $posts = $newspaperHelper->clearOutCache()->categoryTreeGetPosts($category, $postStatusID, 12);
@endphp
@if($posts)
    <div class="row">
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
                                    <img alt="{{$post->title}}" src="{{$newspaperHelper->getPostImageOrPlaceholder($post)}}" class="image-responsive"/>
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
                                    <div class="pt-0 pl-3 pb-3 pr-3">{!! $post->excerpt !!}</div>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
@endif