@extends('layouts.frontend')

@section('title')
    <title>{{esc_html(__('np::m.Not found'))}}</title>
@endsection


@section('content')
    <main class="site-page page-404">

        <div class="container">
            <h4>{{__("np::m.Oooops! We couldn't find what you were looking for.")}}</h4>
        </div>

    </main>
@endsection
