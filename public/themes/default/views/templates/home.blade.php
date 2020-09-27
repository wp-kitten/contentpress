{{--
    The template to display the front page
--}}
@inject('themeHelper', App\DefaultTheme\ThemeHelper)
@extends('layouts.frontend')

@section('title')
    <title>{!! $page->title !!}</title>
@endsection

@section('content')

    <main class="page-content-wrap mt-4 mb-4">
        @if($s1_posts && $s1_posts->count())
            <div class="container">
                <div class="row">
                    @foreach($s1_posts as $post)
                        <div class="col-sm-3">
                            <article class="loop-post">
                                <header class="article-header">
                                    {!! $themeHelper->getPostImageOrPlaceholder($post, '', 'image-responsive') !!}
                                </header>
                                <section class="article-content">
                                    <h2 class="entry-title">
                                        <a href="{{cp_get_permalink($post)}}">
                                            {!! wp_kses_post($post->title) !!}
                                        </a>
                                    </h2>
                                </section>
                            </article>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="section-wrap mt-4 mb-4">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12 col-md-3">
                        @if($s2_posts && $s2_posts->count())
                            <header>
                                <h3 class="section-title">{{__('cpdt::m.Section :n', ['n' => 2])}}</h3>
                            </header>
                            @foreach($s2_posts as $post)
                                <article class="loop-post mb-4">
                                    <header class="article-header">
                                        {!! $themeHelper->getPostImageOrPlaceholder($post, '', 'image-responsive') !!}
                                    </header>
                                    <section class="article-content">
                                        <h2 class="entry-title">
                                            <a href="{{cp_get_permalink($post)}}">
                                                {!! wp_kses_post($post->title) !!}
                                            </a>
                                        </h2>
                                    </section>
                                </article>
                            @endforeach
                        @endif
                    </div>
                    <div class="col-sm-12 col-md-3">
                        <header>
                            <h3 class="section-title">{{__('cpdt::m.Section :n', ['n' => 3])}}</h3>
                        </header>
                        @if($s3_posts && $s3_posts->count())
                            @foreach($s3_posts as $post)
                                <article class="loop-post mb-4">
                                    <header class="article-header">
                                        {!! $themeHelper->getPostImageOrPlaceholder($post, '', 'image-responsive') !!}
                                    </header>
                                    <section class="article-content">
                                        <h2 class="entry-title">
                                            <a href="{{cp_get_permalink($post)}}">
                                                {!! wp_kses_post($post->title) !!}
                                            </a>
                                        </h2>
                                    </section>
                                </article>
                            @endforeach
                        @endif
                    </div>
                    <div class="col-sm-12 col-md-6">
                        <header>
                            <h3 class="section-title">{{__('cpdt::m.Section :n', ['n' => 4])}}</h3>
                        </header>
                        @if($s4_posts && $s4_posts->count())
                            @foreach($s4_posts as $post)
                                <article class="loop-post mb-4">
                                    <header class="article-header">
                                        {!! $themeHelper->getPostImageOrPlaceholder($post, '', 'image-responsive') !!}
                                    </header>
                                    <section class="article-content">
                                        <h2 class="entry-title">
                                            <a href="{{cp_get_permalink($post)}}">
                                                {!! wp_kses_post($post->title) !!}
                                            </a>
                                        </h2>
                                    </section>
                                </article>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="section-wrap mt-4 mb-4">
            <div class="container">
                <div class="row">

                    <div class="col-sm-12 col-md-9">
                        <header>
                            <h3 class="section-title">{{__('cpdt::m.Section :n', ['n' => 5])}}</h3>
                        </header>
                        <div class="row">
                            @if($s5_posts && $s5_posts->count())
                                @foreach($s5_posts as $post)
                                    <div class="col-sm-12 col-md-4">
                                        <article class="loop-post mb-4">
                                            <header class="article-header">
                                                {!! $themeHelper->getPostImageOrPlaceholder($post, '', 'image-responsive') !!}
                                            </header>
                                            <section class="article-content">
                                                <h2 class="entry-title">
                                                    <a href="{{cp_get_permalink($post)}}">
                                                        {!! wp_kses_post($post->title) !!}
                                                    </a>
                                                </h2>
                                            </section>
                                        </article>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    {{-- SIDEBAR --}}
                    <div class="col-sm-12 col-md-3">
                        @include('inc.blog-sidebar')
                    </div>

                </div>
            </div>
        </div>
    </main>
@endsection
