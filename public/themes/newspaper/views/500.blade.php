@extends('layouts.frontend')

@section('title')
    <title>{{esc_html(__('np::m.Internal Server Error'))}}</title>
@endsection


@section('content')
    <main class="site-page page-500">
        <div class="container">
            <h4>{{__("np::m.Oooops! An error occurred.")}}</h4>
        </div>
    </main>
@endsection
