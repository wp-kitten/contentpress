{{--
The template to display pages
--}}
@extends('layouts.frontend')

@section('content')
    <main id="main" class="site-content">
{{--        @include('inc.page-header', [ 'page' => $page ] )--}}

        <section class="page-content-wrap">
            <div class="container">
                <div class="section-title no-margin {{cp_post_classes()}}">
                    {!! $page->content !!}
                </div>

                @if(cp_current_user_can('edit_others_posts'))
                    <div class="hentry-content">
                        <p>
                            <a href="{{cp_get_post_edit_link($page)}}" class="text-link inline">{{__('np::m.Edit')}}</a>
                        </p>
                    </div>
                @endif
            </div> <!-- container -->
        </section> <!-- section-full -->
    </main>

@endsection
