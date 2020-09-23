{{--
    The template to display a user's custom feeds
--}}
@inject('newspaperHelper',App\Newspaper\NewspaperHelper)
@extends('layouts.frontend')
@php
    /**@var App\Newspaper\NewspaperHelper $newspaperHelper*/
@endphp
@section('content')
    <main class="site-page page-singular">
        <div class="container">

            <div class="row">

                {{-- MAIN CONTENT --}}
                <div class="col-sm-12">
                    <h3>{!! $category->name !!}</h3>
                    @if(empty($feeds))
                        @include('partials.no-content', ['class' => 'info', 'text' => __('np::m.No feeds found in this category.')])
                    @else
                        <p class="uh-category-list">
                            @foreach($feeds as $feed)
                                <a href="{{route('app.my_feeds.feed', [ 'category_slug' => $category->slug, 'feed_hash' => $feed->hash])}}"
                                   class="category-item">
                                    {{App\Helpers\Util::getDomain($feed->url)}}
                                </a>
                            @endforeach
                        </p>
                    @endif
                </div>

            </div>

        </div>
    </main>
@endsection
