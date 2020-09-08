{{--

The template to display the front page or the blog page depending on what is set in Settings > Reading

--}}
@extends('layouts.app')

@section('title')
    <title>{{__('a.Under maintenance')}}</title>
@endsection

@section('content')
    <h3>{{__('a.Under maintenance')}}</h3>
    <p>{{__('a.The website is under maintenance. Please check back in a few minutes.')}}</p>
@endsection
