@inject('postStatus',App\Models\PostStatus)
@inject('postType',App\Models\PostType)
@inject('newspaperHelper',App\Newspaper\NewspaperHelper)
@extends('layouts.frontend')

@section('title')
    <title>{!! $page->title !!}</title>
@endsection

@php
    /**@var App\Newspaper\NewspaperHelper $newspaperHelper*/
@endphp

@section('sidenav')
    <aside class="site-sidebar">
        @include('components.blog-sidebar', ['newspaperHelper' => $newspaperHelper])
    </aside>
@endsection

@section('content')
    <main class="site-page page-blog">

        <div class="container">

            <div class="row">
                {{-- MAIN CONTENT --}}
                <div class="col-xs-12 col-md-9">
                    <div id="blog-app-root"></div>
                </div>

                {{-- SIDEBAR --}}
                <div class="col-md-3 d-none d-md-block d-lg-block">
                    <aside class="site-sidebar">
                        @include('components.blog-sidebar', ['newspaperHelper' => $newspaperHelper])
                    </aside>
                </div>
            </div>

        </div>

    </main>
@endsection
