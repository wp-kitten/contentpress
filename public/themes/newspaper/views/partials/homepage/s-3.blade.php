{{--
    Style #3 (same as 2)
        1 column, 5 vertical posts

    col 1
        1 x large post
        5 x small posts
--}}
@inject('postStatus', App\PostStatus)
@php
    /**@var App\Newspaper\NewspaperHelper $newspaperHelper*/
    /**@var App\Post $post */
$ix = 0;
@endphp

<div class="col-sm-12 col-md-6">
    @if($posts)
        <section class="section-cat-title mt-3">
            <h3>{!! $category->name !!}</h3>
        </section>
        @foreach($posts as $postID => $post)
            <article class="hentry-loop mb-3">
                @if(0 == $ix)
                    <header class="hentry-header">
                        <img src="{{$newspaperHelper->getPostImageOrPlaceholder($post)}}" alt="{{$post->title}}" class="image-responsive"/>
                        <div class="hentry-category bg-danger">
                            <a href={{cp_get_category_link($category)}} class="text-light">
                                {!! $category->name !!}
                            </a>
                        </div>
                    </header>
                    <section class="hentry-content">
                        <h4 class="hentry-title">
                            <a href={{cp_get_permalink($post)}} class="text-info">
                                {!! cp_ellipsis($post->title, 50) !!}
                            </a>
                        </h4>
                    </section>

                @else
                    <div class="row">
                        <div class="col-sm-12 col-md-4">
                            <header class="hentry-header full-h">
                                <img src="{{$newspaperHelper->getPostImageOrPlaceholder($post)}}"
                                     alt="{{$post->title}}"
                                     class="image-responsive full-h full-w"/>
                            </header>
                        </div>
                        <div class="col-sm-12 col-md-8">
                            <section class="hentry-content">
                                <h4 class="hentry-title @if(!wp_is_mobile()) title-small font-default @endif">
                                    <a href={{cp_get_permalink($post)}} class="text-info">
                                        {!! cp_ellipsis($post->title, 50) !!}
                                    </a>
                                </h4>
                            </section>
                        </div>
                    </div>
                @endif
            </article>
            @php $ix++; @endphp
        @endforeach
    @endif
</div>

@if($index == 3) </div><!--// END .row --> @endif
