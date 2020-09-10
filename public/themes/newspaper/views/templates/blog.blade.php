@inject('postStatus',App\PostStatus)
@inject('postType',App\PostType)
@inject('newspaperHelper',App\Newspaper\NewspaperHelper)
@extends('layouts.frontend')

@section('title')
    <title>{!! $page->title !!}</title>
@endsection

@php
    /**@var App\Newspaper\NewspaperHelper $newspaperHelper*/
@endphp

@section('content')
    <main class="site-page page-blog">

        <div class="container">

            <div class="row">
                {{-- MAIN CONTENT --}}
                <div class="col-xs-12 col-md-9">
                    <div id="root"></div>
                </div>

                {{-- SIDEBAR --}}
                <div class="col-xs-12 col-md-3">
                    <aside class="site-sidebar">
                        @include('components.blog-sidebar', ['newspaperHelper' => $newspaperHelper])
                    </aside>
                </div>
            </div>

        </div>

    </main>
@endsection
