{{--
The template to display the front page or the blog page depending on what is set in Settings > Reading
--}}
@inject('settings', App\Models\Settings)
@php
     //#! Check what we need to display on homepage

    /**@var App\Models\Settings $settings*/

    $thePage = null;

    $showOnFront = $settings->getSetting( 'show_on_front', 'blog' );
    //#! Specific page
    if ( 'page' == $showOnFront ) {
        $pageOnFront = $settings->getSetting( 'page_on_front', 0 );
        if ( $page = App\Models\Post::find( $pageOnFront ) ) {
            $thePage = $page;
        }
    }
    //#! Blog
    else {
        $blogPage = $settings->getSetting( 'blog_page', 0 );
        if ( $blogPage && $page = App\Models\Post::find( $blogPage ) ) {
            $thePage = $page;
        }
    }
@endphp
@extends('layouts.frontend')

@section('title')
    <title>
        @if($thePage)
            {{$thePage->title}}
        @endif
    </title>
@endsection

@section('content')

    @if($thePage)
        @if( $template = cp_get_post_meta( $thePage, 'template') )
            @include($template, [ 'page' => $thePage ] )
        @else
            @include('page', [ 'page' => $thePage ] )
        @endif
    @else
        @include('404')
    @endif

@endsection
