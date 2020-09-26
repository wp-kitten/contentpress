{{--
    Display the Under Maintenance page
--}}
@inject('settings', App\Models\Settings)
@extends('layouts.frontend')

@section('title')
    <title>{{__('np::m.Under maintenance')}}</title>
@endsection

@section('content')

    @php
        /**@var App\Models\Settings $settings*/

        if(! isset($title) || empty($title)){
            $title =  $settings->getSetting('under_maintenance_page_title');
        }
        if(! isset($message) || empty($message)){
            $message =  $settings->getSetting('under_maintenance_message');
        }
    @endphp


    <main class="site-page page-under-maintenance">
        <div class="container">
            <div class="row flex flex-middle fitscreen">
                <div class="col-xs-12 col-md-12 text-center">
                    <h1 class="mb-3">
                        @if(! empty($title))
                            {{$title}}
                        @else
                            {{__('np::m.Under maintenance')}}
                        @endif
                    </h1>

                    <p class="">
                        @if(! empty($message))
                            {{$message}}
                        @else
                            {{__('np::m.We are really sorry for the inconvenience, but we are making some updates and the website is currently unavailable. This should not take long so please check back in a couple of minutes.')}}
                        @endif
                    </p>

                </div>
            </div>
        </div>
    </main>
@endsection
