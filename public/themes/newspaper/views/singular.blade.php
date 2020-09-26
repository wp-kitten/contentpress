{{--
    The general template to display a single post type
--}}
@inject('newspaperHelper',App\Newspaper\NewspaperHelper)
@extends('layouts.frontend')
@php
    /**@var App\Newspaper\NewspaperHelper $newspaperHelper*/
    /**@var App\Models\Post $post*/

    $categories = $newspaperHelper->getTopCategories();
    $collection = collect([]);
    //! Make sure all categories have posts
    foreach($categories as $category){
        $numPosts = $newspaperHelper->categoryTreeCountPosts($category);
        if(! empty($numPosts)){
            $collection->push($category);
        }
    }
    $collection = $collection->reverse();
    $sections = [
        'left' => $collection->slice(0, 2), // 2
        'right' => $collection->slice(2, 2), // 2
    ];
@endphp

@section('sidenav')
    <aside class="site-sidebar">
        @include('components.blog-sidebar', ['newspaperHelper' => $newspaperHelper])
    </aside>
@endsection


@section('content')
    <main class="site-page page-singular">
        <div class="container">
            <div class="row">

                {{-- MAIN CONTENT --}}
                <div class="col-sm-12 col-md-9">
                    <!-- SINGLE POST START -->
                    <article class="post article-post article-single">

                        <!-- POST IMAGE -->
                        <header class="entry-header">
                            {!! $newspaperHelper->getPostImageOrPlaceholder($post, '', 'image-responsive', [ 'alt' => $post->title ]) !!}
                        </header>

                        <!-- POST TITLE -->
                        <h2 class="entry-title mt-2 mb-2">
                            {!! wp_kses_post($post->title) !!}
                        </h2>

                        <!-- POST META -->
                        <section class="entry-meta mt-2 mb-2">
                            <span><i class="fa fa-clock-o"></i> {{cp_the_date($post)}}</span>
                            <span><i class="fa fa-user"></i> {{$post->user->display_name}}</span>
                            @if($post->categories()->count())
                                <span>
                                <i class="fa fa-folder-open"></i>
                                @foreach($post->categories()->get() as $category)
                                    <a href="{{cp_get_category_link($category)}}" class="category-link">{!! $category->name !!}</a>
                                @endforeach
                            </span>
                            @endif
                        </section>

                        <!-- TEXT POST -->
                        <main class="entry-content mt-4 mb-4">
                            {!! $post->content !!}
                        </main>

                        {{-- Render tags & social links --}}
                        <footer class="entry-footer">
                            {!! do_action('contentpress/post/footer', $post) !!}
                        </footer>

                        {{-- Render the post Edit link --}}
                        @if(cp_current_user_can('edit_others_posts'))
                            <footer class="entry-footer mt-4 mb-4">
                                <a href="{{cp_get_post_edit_link($post)}}" class="btn bg-danger text-light">{{__('np::m.Edit')}}</a>
                            </footer>
                        @endif

                        {{-- RELATED POSTS --}}
                        @include('components.related-posts', [ 'post' => $post, 'title' => __('np::m.In the same category') ])

                    </article><!-- SINGLE POST END -->
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
