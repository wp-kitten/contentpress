{{--
        The template to display the search results
--}}
@inject('newspaperHelper',App\Newspaper\NewspaperHelper)
@extends('layouts.frontend')
@php
    /**@var App\Newspaper\NewspaperHelper $newspaperHelper*/
    $orderBy = (empty($order) ? 'desc' : $order );
@endphp

@section('title')
    <title>{{__('np::m.Search for: :query_string', [ 'query_string' => cp_get_search_query()]) }}</title>
@endsection


@section('sidenav')
    <aside class="site-sidebar">
        @include('components.blog-sidebar', ['newspaperHelper' => $newspaperHelper])
    </aside>
@endsection


@section('content')
    <main class="site-page page-search">

        <div class="container">

            <div class="row">

                {{-- MAIN CONTENT --}}
                <div class="col-xs-12 col-md-9">

                    {{-- FILTERS --}}
                    <div class="row">
                        <div class="col-xs-12 col-sm-12">
                            <div class="search-filters-wrap bg-light pt-3 pb-3 mb-4">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12">
                                        <div class="search-results pl-3">
                                            <small class="text-dark">{{__('np::m.Found :num_results results for:', [ 'num_results' => number_format( $numResults, 0, ',', '.') ])}}</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12 col-md-8">
                                        <div class="search-form-wrap pl-3">
                                            {!! cp_search_form() !!}
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-md-4 text-right">
                                        <div class="orderby-wrap mr-3">
                                            <form id="form-filter-search" method="get" action="<?php esc_attr_e( route( 'blog.search' ) ); ?>">
                                                <input name="s" value="{{cp_get_search_query()}}" class="d-none"/>
                                                <select name="order" id="js-sort-results" data-form-id="form-filter-search">
                                                    @php $selected = ('desc' == $order ? 'selected' : ''); @endphp
                                                    <option value="desc" {!! $selected !!}>{{__('np::m.Sort by Newest')}}</option>
                                                    @php $selected = ('asc' == $order ? 'selected' : ''); @endphp
                                                    <option value="asc" {!! $selected !!}>{{__('np::m.Sort by Oldest')}}</option>
                                                </select>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- POSTS GRID --}}
                    <div class="row masonry-grid js-masonry-init">
                        <!-- The sizing element for columnWidth -->
                        <div class="grid-sizer col-xs-12 col-sm-6 col-md-4"></div>
                        @forelse($posts as $post)
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
                        @empty
                        @endforelse
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="pagination-wrap mt-4 mb-4">
                                {!! $posts->render() !!}
                            </div>
                        </div>
                    </div>
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
