{{--
    [post type: post]
    The template to display posts from a tag
--}}
@extends('layouts.frontend')

@inject('themeHelper', App\DefaultTheme\ThemeHelper)

@section('title')
    <title>{!! $tag->name !!}</title>
@endsection


@section('content')
    <main class="site-page page-tag">

        <div class="container">
            <div class="row">

                {{-- MAIN CONTENT --}}
                <div class="col-sm-12 col-md-9">

                    {{-- PAGE TITLE --}}
                    <div class="row">
                        <div class="col-sm-12">
                            <h2 class="page-title mb-4">{{esc_html(__('cpdt::m.Tag:'))}} {!! $tag->name !!}</h2>
                        </div>
                    </div>

                    {{-- POSTS --}}
                    <div class="row">
                        <div class="col-sm-12">
                            @if(!$posts || ! $posts->count())
                                @include('inc.no-content', ['class' => 'info', 'text' => __('cpdt::m.No posts under this tag.')])
                            @else
                                <div class="row">
                                    @foreach($posts as $post)
                                        <div class="col-xs-12 col-sm-6 col-md-4">
                                            <article class="loop-post mb-4">
                                                <header class="article-header">
                                                    {!! $themeHelper->getPostImageOrPlaceholder($post, '', 'image-responsive') !!}
                                                </header>
                                                <section class="article-meta">
                                                    <a href="{{cp_get_category_link($post->firstCategory())}}" class="text-danger">
                                                        {!! $post->firstCategory()->name !!}
                                                    </a>
                                                    <span class="text-grey">{{cp_the_date($post)}}</span>
                                                </section>
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
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="pagination-wrap mt-4 mb-4">
                                            {!! $posts->render() !!}
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- SIDEBAR --}}
                <div class="col-sm-12 col-md-3">
                    @include('inc.blog-sidebar')
                </div>
            </div>
        </div>

    </main>
@endsection
