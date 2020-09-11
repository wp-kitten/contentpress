{{--
    The template to display all tags for teh post type: post
--}}
@extends('layouts.frontend')
@inject('newspaperHelper',App\Newspaper\NewspaperHelper)
@php /**@var App\Newspaper\NewspaperHelper $newspaperHelper*/ @endphp

@section('title')
    <title>{{__('np::m.Tags')}}</title>
@endsection


@section('sidenav')
    <aside class="site-sidebar">
        @include('components.tags-sidebar', ['newspaperHelper' => $newspaperHelper])
    </aside>
@endsection



@section('content')
    <main class="site-page page-post-tags">

        <section class="page-content-wrap">
            <div class="container">
                <div class="row">

                    {{-- MAIN CONTENT --}}
                    <div class="col-sm-12 col-md-9">
                        <div class="{{cp_post_classes()}}">
                            @if(empty($tags))
                                @include('partials.no-content', [ 'class' => 'info', 'text' => __('np::m.No tags found')])
                            @else
                                @foreach($tags as $tag)
                                    @if($tag->posts()->count())
                                        <a href="{{cp_get_tag_link($tag)}}" class="tag-link">
                                            {!! $tag->name !!}
                                        </a>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>

                    {{-- SIDEBAR --}}
                    <div class="col-md-3 d-none d-md-block d-lg-block">
                        <aside class="site-sidebar">
                            @include('components.tags-sidebar', ['newspaperHelper' => $newspaperHelper])
                        </aside>
                    </div>
                </div>
            </div>
        </section>
    </main>

@endsection
