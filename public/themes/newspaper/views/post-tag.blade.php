@extends('layouts.frontend')
@inject('newspaperHelper',App\Newspaper\NewspaperHelper)

@section('title')
    <title>{!! $tag->name !!}</title>
@endsection

@php
    /**@var App\Newspaper\NewspaperHelper $newspaperHelper*/
@endphp


@section('sidenav')
    <aside class="site-sidebar">
        @include('components.tags-sidebar', ['newspaperHelper' => $newspaperHelper])
    </aside>
@endsection


@section('content')
    <main class="site-page page-category">

        <div class="container">
            <div class="row">

                {{-- MAIN CONTENT --}}
                <div class="col-sm-12 col-md-9">

                    {{-- PAGE TITLE --}}
                    <div class="row">
                        <div class="col-sm-12">
                            <h2 class="page-title">{!! $tag->name !!}</h2>
                        </div>
                    </div>

                    {{-- POSTS --}}
                    <div class="row">
                        <div class="col-sm-12">
                            @if(!$posts || ! $posts->count())
                                @include('partials.no-content', ['class' => 'info', 'text' => __('np::m.No posts under this tag.')])
                            @else
                                <div class="row masonry-grid js-masonry-init">
                                    <!-- The sizing element for columnWidth -->
                                    <div class="grid-sizer col-xs-12 col-sm-6 col-md-4"></div>
                                    @foreach($posts as $post)
                                        <div class="col-xs-12 col-sm-6 col-md-4 masonry-item">
                                            <article class="hentry-loop">
                                                <header class="hentry-header">
                                                    {!! $newspaperHelper->getPostImageOrPlaceholder($post, '', 'image-responsive', [ 'alt' => $post->title ]) !!}
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
                <div class="col-md-3 d-none d-md-block d-lg-block">
                    <aside class="site-sidebar">
                        @include('components.tags-sidebar', ['newspaperHelper' => $newspaperHelper])
                    </aside>
                </div>

            </div>
        </div>

    </main>
@endsection
