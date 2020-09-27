{{--
        The template to display the search results
--}}
@extends('layouts.frontend')
@inject('themeHelper', App\DefaultTheme\ThemeHelper)


@section('title')
    <title>{{__('cpdt::m.Search for: :query_string', [ 'query_string' => cp_get_search_query()]) }}</title>
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
                                            <small class="text-dark">{{__('cpdt::m.Found :num_results results for:', [ 'num_results' => number_format( $numResults, 0, ',', '.') ])}}</small>
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
                                                <input name="s" value="{{cp_get_search_query()}}" class="hidden"/>
                                                <select name="order" id="js-sort-results" data-form-id="form-filter-search">
                                                    @php $selected = ('desc' == $order ? 'selected' : ''); @endphp
                                                    <option value="desc" {!! $selected !!}>{{__('cpdt::m.Sort by Newest')}}</option>
                                                    @php $selected = ('asc' == $order ? 'selected' : ''); @endphp
                                                    <option value="asc" {!! $selected !!}>{{__('cpdt::m.Sort by Oldest')}}</option>
                                                </select>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- POSTS GRID --}}
                    <div class="row">
                        @if($posts)
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
                        @else
                            @include('inc.no-content', ['class' => 'info', 'text' => __('cpdt::m.No posts found.')])
                        @endif
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="pagination-wrap mt-4 mb-4">
                                {!! $posts ? $posts->appends([ 's' => cp_get_search_query() ])->render() : '' !!}
                            </div>
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
