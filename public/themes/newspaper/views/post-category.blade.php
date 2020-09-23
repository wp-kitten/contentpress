@extends('layouts.frontend')
@inject('newspaperHelper',App\Newspaper\NewspaperHelper)

@section('title')
    <title>{!! $category->name !!}</title>
@endsection

@php
    /**@var App\Newspaper\NewspaperHelper $newspaperHelper*/
@endphp


@section('sidenav')
    <aside class="site-sidebar">
        <div class="widget widget-categories bg-white p-3">
            <div class="widget-title">
                <h3 class="text-danger">{{__('np::m.Categories')}}</h3>
            </div>
            <div class="widget-content">
                <ul class="list-unstyled mt-3 mb-3 categories-list">
                    @if(! empty($subcategories))
                        @forelse($subcategories as $cat)
                            <li>
                                <a class="category-name text-info" href="{{cp_get_category_link($cat)}}">{!! $cat->name !!}</a>
                                <span class="num-posts text-dark">{{$newspaperHelper->categoryTreeCountPosts($cat)}}</span>
                            </li>
                        @empty
                            <li>
                                @include('partials.no-content', ['class' => 'info', 'text' => __('np::m.No subcategories found.')])
                            </li>
                        @endforelse
                    @endif
                </ul>
            </div>
        </div>
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
                            <h2 class="page-title">{!!  $category->name !!}</h2>
                            @php
                                $parentCategories = $category->parentCategories();
                                $catsTree = [];
                                if( ! empty($parentCategories)){
                                    foreach($parentCategories as $cat){
                                        $catsTree[] = '<a href="'.esc_attr(cp_get_category_link($cat)).'">'.$cat->name.'</a>';
                                    }
                                }
                                $catsTree[] = '<a href="'.esc_attr(cp_get_category_link($category)).'">'. $category->name.'</a>';
                            @endphp
                            @if(count($catsTree) > 1)
                                <span class="d-block text-description">{!! implode('/', $catsTree) !!}</span>
                            @endif
                        </div>
                    </div>

                    {{-- POSTS --}}
                    <div class="row">
                        <div class="col-sm-12">
                            @if(!$posts || ! $posts->count())
                                @include('partials.no-content', ['class' => 'info', 'text' => __('np::m.No posts in this category.')])
                            @else
                                <div class="row masonry-grid js-masonry-init">
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
                        <div class="widget widget-categories bg-white p-3">
                            <div class="widget-title">
                                <h3 class="text-danger">{{__('np::m.Categories')}}</h3>
                            </div>
                            <div class="widget-content">
                                <ul class="list-unstyled mt-3 mb-3 categories-list">
                                    @if(! empty($subcategories))
                                        @forelse($subcategories as $cat)
                                            <li>
                                                <a class="category-name text-info text-capitalize" href="{{cp_get_category_link($cat)}}">{!! $cat->name !!}</a>
                                                <span class="num-posts text-dark">{{$newspaperHelper->categoryTreeCountPosts($cat)}}</span>
                                            </li>
                                        @empty
                                            <li>
                                                @include('partials.no-content', ['class' => 'info', 'text' => __('np::m.No subcategories found.')])
                                            </li>
                                        @endforelse
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </aside>
                </div>

            </div>
        </div>

    </main>
@endsection
