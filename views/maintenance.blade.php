{{--

The template to display the front page or the blog page depending on what is set in Settings > Reading

--}}
@extends('layouts.frontend')

@section('title')
    <title>{{__('a.Under maintenance')}}</title>
@endsection

@section('content')

    <div class="section-full">
        <p>Under maintenance - Admin</p>
    </div>
@endsection


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <title>{{__('a.Under maintenance')}}</title>
</head>
<body>
    <h3>{{__('a.Under maintenance')}}</h3>
    <p>{{__('a.The website is under maintenance. Please check back in a few minutes.')}}</p>
</body>
</html>
