@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Forbidden')}}</title>
@endsection

@section('main')
    @if( $message)
        <div class="bs-component">
            <div class="alert alert-{{$message['class']}}">
                <span>{{$message['text']}}</span>
            </div>
        </div>
    @endif
@endsection
