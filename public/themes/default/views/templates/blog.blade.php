{{--
    The template to display the blog page
--}}
@extends('layouts.frontend')

@inject('themeHelper', App\DefaultTheme\ThemeHelper)
@inject('settings', App\Models\Settings)
@inject('postStatusClass', App\Models\PostStatus)
@inject('postTypeClass', App\Models\PostType)

@php
    $postStatus = $postStatusClass->where( 'name', 'publish' )->first();
    $postType = $postTypeClass->where( 'name', 'post' )->first();
    $posts = \App\Models\Post::latest()
           ->where( 'post_status_id', $postStatus->id )
           ->where( 'post_type_id', $postType->id )
           ->whereDate( 'created_at', '>', now()->subMonth()->toDateString() )
           ->paginate( 12 );
@endphp


@section('title')
    <title>{!! $page->title !!}</title>
@endsection

@section('content')
    <main class="site-page page-blog">

        <div class="container">
            <div class="row">

                {{-- MAIN CONTENT --}}
                <div class="col-sm-12 col-md-9">

                    {{-- PAGE TITLE --}}
                    <div class="row">
                        <div class="col-sm-12">
                            <h2 class="page-title mb-4">{!!  $settings->getSetting('blog_title') !!}</h2>
                        </div>

                        {{-- POSTS --}}
                        <div class="col-sm-12">
                            @if(!$posts || ! $posts->count())
                                @include('inc.no-content', ['class' => 'info', 'text' => __('cpdt::m.No posts found.')])
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

    </main>
@endsection
