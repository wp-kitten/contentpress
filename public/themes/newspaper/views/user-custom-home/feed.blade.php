{{--
    The template to display a feed
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
                <div class="col-sm-12">
                    <h3>{!! $category->name !!}</h3>
                    @if(!$posts || ! $posts->count())
                        @include('partials.no-content', ['class' => 'info', 'text' => __('np::m.No posts in this feed.')])
                    @else
                        <div class="row masonry-grid js-masonry-init">
                            <!-- The sizing element for columnWidth -->
                            <div class="grid-sizer col-xs-12 col-sm-6 col-md-4"></div>
                            @foreach($posts as $post)
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
    </main>
@endsection
