@inject('newspaperHelper',App\Newspaper\NewspaperHelper)
@extends('layouts.frontend')
@php
    $categories = $newspaperHelper->getTopCategories();
    $collection = collect([]);
    //! Make sure all categories have posts
    foreach($categories as $category){
        $numPosts = $newspaperHelper->categoryTreeCountPosts($category);
        if(! empty($numPosts)){
            $collection->push($category);
        }
    }
    $sections = [
        'left' => collect([]), // 2
        'main' => collect([]), // 12
        'right' => collect([]), // 2
    ];
    if($collection->count()){
        $i = 0;
        foreach($collection as $category){
            if($i == 0 || $i == 1 ){
                $sections['left']->push( $category );
            }
            elseif($i > 1 && $i < 16){
                $sections['main']->push( $category );
            }
            elseif($i >= 16 && $i < 18){
                $sections['right']->push( $category );
            }
            $i++;
        }
    }
@endphp

@section('title')
    <title>{!! $page->title !!}</title>
@endsection

@section('content')
    <main class="site-page page-home">

        <!-- BIG SLIDER START -->
        <div class="container">
            <div class="row">
                @include('templates.inc.home.large-slider', [ 'newspaperHelper' => $newspaperHelper, ])
            </div>
        </div><!-- BIG SLIDER END -->

        <!-- RESPONSIVE AD START -->
        <div class="container">
            <div class="row">
                <div style="margin: 30px 0 0; padding: 0;">
                    <div style="background: #ddd; color: #bbbbbb; display: inline-block; font-family: roboto, sans-serif; font-size: 1.2rem; font-weight: 900; height: 30px; overflow: hidden; padding: 22px 0 15px; text-align: center; white-space: nowrap; width: 100%;">
                        RESPONSIVE AD AREA
                    </div>
                </div>
            </div>
        </div><!-- RESPONSIVE AD END -->

        <!-- HOMEPAGE CATEGORIES START -->
        <div class="container">
            <div class="row">

                <div class="tabs-menu clearfix">
                    <div class="left-area">
                        {{__('np::m.Left Sidebar')}}
                    </div>
                    <div class="left-area-2">
                        {{__('np::m.Left Sidebar')}}
                    </div>
                    <div class="center-area">
                        {{__('np::m.Featured News')}}
                    </div>
                    <div class="right-area">
                        {{__('np::m.Right Sidebar')}}
                    </div>
                    <div class="right-area-2">
                        {{__('np::m.Right Sidebar')}}
                    </div>
                </div>

                <div id="outer-wrapper" class="clearfix">

                    <div class="secondary-content clearfix">

                        {{-- LEFT SIDEBAR --}}
                        @include('templates.inc.home.left-sidebar', [ 'newspaperHelper' => $newspaperHelper, 'categories' => $sections['left'] ])

                        {{-- MAIN CONTENT --}}
                        @include('templates.inc.home.main-content', [ 'newspaperHelper' => $newspaperHelper, 'categories' => $sections['main'] ])

                    </div>

                    {{-- RIGHT SIDEBAR --}}
                    @include('templates.inc.home.right-sidebar', [ 'newspaperHelper' => $newspaperHelper, 'categories' => $sections['right'] ])

                </div>
            </div>
        </div><!-- HOMEPAGE CATEGORIES END -->

        <!-- RESPONSIVE AD START -->
        <div class="container">
            <div class="row">
                <div style="margin: 15px 0 10px; padding: 0;">
                    <div style="background: #ddd; color: #bbbbbb; display: inline-block; font-family: roboto, sans-serif; font-size: 1.2rem; font-weight: 900; height: 30px; overflow: hidden; padding: 22px 0 15px; text-align: center; white-space: nowrap; width: 100%;">
                        RESPONSIVE AD AREA
                    </div>
                </div>
            </div>
        </div><!-- RESPONSIVE AD END -->
    </main>
@endsection
