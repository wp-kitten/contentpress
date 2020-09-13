@inject('newspaperHelper',App\Newspaper\NewspaperHelper)
@inject('postStatus', App\PostStatus)
@extends('layouts.frontend')

@php
    /**@var App\Newspaper\NewspaperHelper $newspaperHelper*/
    /**@var App\PostStatus $postStatus */

    $postStatusID = $postStatus->where('name', 'publish')->first()->id;
    $featuredCategories = $newspaperHelper->getThemeOption('featured_categories', []);



$categories = [];
$mainCategories = $newspaperHelper->getTopCategories();
if($mainCategories){
    foreach($mainCategories as $mainCategory){
        $numPosts = $newspaperHelper->categoryTreeCountPosts($mainCategory);
        if($numPosts >= 6 ){
            $categories[] = $mainCategory;
        }
    }
}

$loopIndex = 1;
$index = 0;
@endphp



@section('title')
    <title>{!! $page->title !!}</title>
@endsection

@section('sidenav')
    <aside class="site-sidebar">
        @include('components.blog-sidebar', ['newspaperHelper' => $newspaperHelper])
    </aside>
@endsection

@section('content')
    <main class="site-page page-home">

        <div class="container">

            <div class="row">
                {{-- MAIN CONTENT --}}
                <div class="col-xs-12 col-md-9">

                    @if(! empty($categories))
                        @foreach($categories as $category)
                            @php
                                //#! Helps keeping track of the template to load
                                if($loopIndex > 4){ $loopIndex = 1; }
                                //#! Helps creating columns
                                if($index > 1){ $index = 0; }

                                $posts = $newspaperHelper->clearOutCache()->categoryTreeGetPosts($category, $postStatusID, 6);
                            @endphp


                            @include('partials.homepage.s-'.$loopIndex, [
                                'category' => $category,
                                'posts' => $posts,
                                'newspaperHelper' => $newspaperHelper,
                                'post_status_id' => $postStatusID,
                                'index' => $loopIndex,
                            ])


                            @php $loopIndex++; $index++; @endphp
                        @endforeach
                    @endif










{{--                    @if(! empty($featuredCategories))--}}

{{--                        --}}{{-- BUILD THE HOMEPAGE LAYOUT --}}
{{--                        @foreach($featuredCategories as $index => $catID)--}}

{{--                            @if( in_array( $index, [ 0, 1 ] ) )--}}
{{--                                @include('partials.homepage.s-1', [--}}
{{--                                    'newspaperHelper' => $newspaperHelper,--}}
{{--                                    'index' => $index,--}}
{{--                                    'cat_id' => $catID,--}}
{{--                                    'post_status_id' => $postStatusID,--}}
{{--                                ])--}}

{{--                            @elseif( 2 == $index )--}}
{{--                                @include('partials.homepage.s-2', [--}}
{{--                                    'newspaperHelper' => $newspaperHelper,--}}
{{--                                    'index' => $index,--}}
{{--                                    'cat_id' => $catID,--}}
{{--                                    'post_status_id' => $postStatusID,--}}
{{--                                ])--}}

{{--                            @elseif( 3 == $index )--}}
{{--                                @include('partials.homepage.s-3', [--}}
{{--                                    'newspaperHelper' => $newspaperHelper,--}}
{{--                                    'index' => $index,--}}
{{--                                    'cat_id' => $catID,--}}
{{--                                    'post_status_id' => $postStatusID,--}}
{{--                                ])--}}

{{--                            @elseif( in_array( $index, [ 4, 5 ] ) )--}}
{{--                                <div class="row">--}}
{{--                                    @include('partials.homepage.s-4', [--}}
{{--                                        'newspaperHelper' => $newspaperHelper,--}}
{{--                                        'index' => $index,--}}
{{--                                        'cat_id' => $catID,--}}
{{--                                        'post_status_id' => $postStatusID,--}}
{{--                                    ])--}}
{{--                                </div>--}}

{{--                            @endif--}}
{{--                        @endforeach--}}
{{--                    @endif--}}
                </div>

                {{-- SIDEBAR --}}
                <div class="col-md-3 d-none d-md-block d-lg-block">
                    <aside class="site-sidebar">
                        @include('components.home-sidebar', ['newspaperHelper' => $newspaperHelper])
                    </aside>
                </div>
            </div>

        </div>

    </main>
@endsection
