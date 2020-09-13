{{--
    Style #4
        1 column, 5 posts
--}}
@php
    /**@var App\Newspaper\NewspaperHelper $newspaperHelper*/
    /**@var App\Post $post */

@endphp

<div class="col-sm-12 col-md-6">
    @if($posts)
        <section class="section-cat-title">
            <h3>{!! $category->name !!}</h3>
        </section>
        @foreach($posts as $postID => $post)
            <article class="hentry-loop mb-3">
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
                            <h4 class="hentry-title title-small font-default">
                                <a href={{cp_get_permalink($post)}} class="text-info">
                                    {!! cp_ellipsis($post->title, 50) !!}
                                </a>
                            </h4>
                        </section>
                    </div>
                </div>
            </article>
        @endforeach
    @endif
</div>
