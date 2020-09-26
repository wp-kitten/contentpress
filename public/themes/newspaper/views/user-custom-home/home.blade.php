{{--
    The template to display a user's custom feeds
--}}
@inject('newspaperHelper',App\Newspaper\NewspaperHelper)
@inject('postStatus',App\Models\PostStatus)
@extends('layouts.frontend')
@php
    /**@var App\Newspaper\NewspaperHelper $newspaperHelper*/
    /**@var App\Models\PostStatus $postStatus */

    $postStatusID = $postStatus->where('name', 'publish')->first()->id;
@endphp
@section('content')
    <main class="site-page page-singular">
        <div class="container">

            <div class="row">

                {{-- MAIN CONTENT --}}
                <div class="col-sm-12">
                    <p>content to be decided, since we cannot show posts from feeds if more than one feed added to a
                        single category</p>
                    @if(empty($categories))
                        no categories found
                    @else
                        @foreach($categories as $catID => $catInfo)
                            <section>
                                <header>
                                    <h3>
                                        <a href="{{route('app.my_feeds.category', $catInfo['category']->slug)}}">
                                            {!! $catInfo['category']->name !!}
                                        </a>
                                    </h3>
                                </header>
                            </section>
                            @php
                                $posts = $newspaperHelper->clearOutCache()->categoryTreeGetPosts($catInfo['category'], $postStatusID, 6);
                            @endphp
                            @if(empty($posts))
                                @include('partials.no-content', ['class' => 'info', 'text' => __('np::m.No posts found.')])
                            @else
                                <div class="row masonry-grid js-masonry-init">
                                    <!-- The sizing element for columnWidth -->
                                    <div class="grid-sizer col-xs-12 col-sm-6 col-md-4"></div>
                                    @foreach($posts as $postID => $post)
                                        <div class="col-xs-12 col-sm-6 col-md-4 masonry-item">
                                            <article class="hentry-loop">
                                                <header class="hentry-header">
                                                    {!! $newspaperHelper->getPostImageOrPlaceholder($post, '', 'image-responsive', ['alt' => $post->title]) !!}
                                                </header>

                                                <section class="hentry-content">
                                                    <h4 class="hentry-title">
                                                        <a href="{{cp_get_permalink($post)}}" class="text-info">
                                                            {!! wp_kses_post($post->title) !!}
                                                        </a>
                                                    </h4>
                                                </section>
                                            </article>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>
            </div>

        </div>
    </main>
@endsection
