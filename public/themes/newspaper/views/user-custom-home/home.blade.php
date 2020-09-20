{{--
    The template to display a user's custom feeds
--}}
@inject('newspaperHelper',App\Newspaper\NewspaperHelper)
@extends('layouts.frontend')
@php
/**@var App\Newspaper\NewspaperHelper $newspaperHelper*/
@endphp
@section('content')
    <main class="site-page page-singular">
        <div class="container">

            <div class="row">

                {{-- MAIN CONTENT --}}
                <div class="col-sm-12 col-md-9">
                    <h3>todo</h3>
                    <p>Find the appropriate UI to display user's feeds</p>
                    @if(empty($feeds))
                        @include('partials.no-content', ['class' => 'info', 'text' => __('np::m.No feeds found. Why not add some?')])
                    @else
                        @foreach($feeds as $feed)
                            @php $posts = $feed->category()->first()->posts()->get(); @endphp
                            <section>
                                <header>
                                    <h3>{!! $feed->category()->first()->name !!}</h3>
                                </header>
                            </section>
                            <section>
                                @if(empty($posts))
                                    @include('partials.no-content', ['class' => 'info', 'text' => __('np::m.No posts found in this category.')])
                                @else
                                    <div class="row masonry-grid">
                                        <!-- The sizing element for columnWidth -->
                                        <div class="grid-sizer col-xs-12 col-sm-6 col-md-4"></div>
                                        @foreach($posts as $post)
                                            <div class="col-xs-12 col-sm-6 col-md-4 masonry-item">
                                                <article class="hentry-loop">
                                                    <header class="hentry-header">
                                                        {!! $newspaperHelper->getPostImageOrPlaceholder($post, '', 'image-responsive', ['alt' => $post->title]) !!}
                                                        <div class="hentry-category bg-danger">
                                                            <a href="{{cp_get_category_link($post->firstCategory())}}" class="text-light">
                                                                {!! $post->firstCategory()->name !!}
                                                            </a>
                                                        </div>
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
                            </section>
                        @endforeach
                    @endif
                </div>

                {{-- SIDEBAR --}}
                <div class="col-md-3 d-none d-md-block d-lg-block">
                    <aside class="site-sidebar">
                        @include('components.user-feeds-sidebar', [
                            'newspaperHelper' => $newspaperHelper,
                            'categories' => $categories,
                        ])
                    </aside>
                </div>

            </div>

        </div>
    </main>
@endsection
