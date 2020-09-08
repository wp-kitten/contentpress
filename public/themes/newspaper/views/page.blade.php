{{--
The template to display pages
--}}
@extends('layouts.frontend')

@section('content')
    <main class="site-page page-page">

        <section class="page-content-wrap">
            <div class="container">
                <div class="section-title no-margin {{cp_post_classes()}}">
                    {!! $page->content !!}
                </div>

                {{-- Render the post Edit link --}}
                @if(cp_current_user_can('edit_others_posts'))
                    <footer class="entry-footer mt-4 mb-4">
                        <a href="{{cp_get_post_edit_link($page)}}" class="btn bg-danger text-light">{{__('np::m.Edit')}}</a>
                    </footer>
                @endif
            </div> <!-- container -->
        </section> <!-- section-full -->
    </main>

@endsection
