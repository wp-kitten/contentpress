{{--
    The template to display the front page or the blog page depending on what is set in Settings > Reading
--}}
@extends('layouts.frontend')
@inject('settings', App\Models\Settings)

@section('title')
    <title>{{__('cpdt::m.Under maintenance')}}</title>
@endsection

@section('content')

    @php
        $title = $settings->getSetting('under_maintenance_page_title', __('cpdt::m.Under maintenance'));
        $message = $settings->getSetting('under_maintenance_message', __("cpdt::m.The website is under maintenance. Please check back in a few minutes."));
    @endphp

    <main class="site-page page-page page-under-maintenance">
        <section class="page-content-wrap">
            <div class="container">
                <h2 class="font-weight-bold text-center title">{{$title}}</h2>
                <h4 class="text-center subtitle">{{$message}}</h4>
            </div> <!-- container -->
        </section> <!-- section-full -->
    </main>

@endsection
