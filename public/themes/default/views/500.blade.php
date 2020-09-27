{{--
    The template to display 500 error page
--}}
@extends('layouts.frontend')

@section('title')
    <title>{{__('cpdt::m.500')}}</title>
@endsection

@section('content')
    <main class="site-page page-page page-500">

        <section class="page-content-wrap">
            <div class="container">
                <h2 class="font-weight-bold text-center title">{{__('cpdt::m.500')}}</h2>
                <h4 class="text-center subtitle">{{__("cpdt::m.Oooops! An internal error occurred.")}}</h4>
            </div> <!-- container -->
        </section> <!-- section-full -->
    </main>

@endsection
