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
                <div class="col-sm-12 col-md-9">
                    <h3>{!! $category->name !!}</h3>
                    @if(empty($feeds))
                        @include('partials.no-content', ['class' => 'info', 'text' => __('np::m.No feeds found in this category.')])
                    @else
                        @foreach($feeds as $feed)
                            <p>
                                <a href="#">{{$feed->url}}</a>
                            </p>
                        @endforeach
                    @endif
                </div>

                {{-- SIDEBAR --}}
                <div class="col-md-3 d-none d-md-block d-lg-block">
                    <aside class="site-sidebar">
{{--                        @include('components.user-feeds-sidebar', [--}}
{{--                            'newspaperHelper' => $newspaperHelper,--}}
{{--                            'categories' => $categories,--}}
{{--                        ])--}}
                    </aside>
                </div>

            </div>

        </div>
    </main>
@endsection
