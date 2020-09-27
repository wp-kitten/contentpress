{{--
    The template to display 404 error page
--}}
@extends('layouts.frontend')

@section('title')
    <title>{{__('cpdt::m.404')}}</title>
@endsection

@section('content')
    <main class="site-page page-page page-404">

        <section class="page-content-wrap">
            <div class="container">
                <h2 class="font-weight-bold text-center title">{{__('cpdt::m.404')}}</h2>
                <h4 class="text-center subtitle">{{__("cpdt::m.Oooops! We couldn't find what you were looking for.")}}</h4>
            </div> <!-- container -->
        </section> <!-- section-full -->

    </main>

@endsection
