@inject('postStatus',App\PostStatus)
@inject('postType',App\PostType)
@inject('newspaperHelper',App\Newspaper\NewspaperHelper)
@extends('layouts.frontend')

@section('title')
    <title>{!! $page->title !!}</title>
@endsection

@php
    /**@var App\Newspaper\NewspaperHelper $newspaperHelper*/
    use Carbon\Carbon;
    $statusPublishID = $postStatus->where('name', 'publish')->first()->id;
    $typeID = $postType->where('name', 'post')->first()->id;
    $posts = App\Post::latest()
    ->where('post_status_id', $statusPublishID)
    ->where('post_type_id', $typeID)
    ->whereDate( 'created_at', '>', Carbon::now()->subMonth() )
    ->paginate(30);

@endphp

@section('content')
    <main class="site-page page-blog">

        <div class="container">

            <div class="row">
                {{-- MAIN CONTENT --}}
                <div class="col-xs-12 col-md-9">
                    <div class="row masonry-grid">
                        <!-- The sizing element for columnWidth -->
                        <div class="grid-sizer col-xs-12 col-sm-6 col-md-4"></div>
                        @forelse($posts as $post)
                            <div class="col-xs-12 col-sm-6 col-md-4 masonry-item">
                                <article class="hentry-loop">
                                    @if($imageUrl = $newspaperHelper->getPostImageOrPlaceholder($post))
                                        <header class="hentry-header">
                                            <img src="{{$imageUrl}}" class="image-responsive" alt="{{$post->title}}"/>
                                            <div class="hentry-category bg-danger">
                                                <a href="{{cp_get_category_link($post->firstCategory())}}" class="text-light">
                                                    {!! cp_cat_name($post->firstCategory()->name) !!}
                                                </a>
                                            </div>
                                        </header>
                                    @endif
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
                <div class="col-xs-12 col-md-3">
                    <div class="row">
                        <div class="col-sm-12">
                            <aside class="site-sidebar">
                                @include('components.blog-sidebar', ['newspaperHelper' => $newspaperHelper])
                            </aside>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </main>
@endsection
