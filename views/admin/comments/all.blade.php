{{--
    Render all comments
--}}
@php
    $baseRoute = "admin.{$__post_type->name}";
    $range = range(10, 100, 10);
    $post_id = (request('post_id') ?? null);
@endphp

@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.All Comments')}}</title>
@endsection

@section('main')
    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('a.All Comments')}}</h1>
            </div>
            {{--[[ FILTERS --}}
            <div>
                <div class="d-flex align-items-center justify-content-between">
                    @php
                        $formUrl = ($post_id ? route("{$baseRoute}.comment.all", ['post_id' => $post_id]) : route("{$baseRoute}.comment.all"));
                    @endphp
                    <form method="get" action="{{$formUrl}}" class="form-inline js-form-filters">
                        <label class="label mr-2" for="comment-status-field">{{__('a.Status')}}</label>
                        <select id="comment-status-field" name="_status" class="form-control border ml-2">
                            <option value="">{{__('a.All')}}</option>
                            @foreach($comment_statuses as $commentStatus)
                                @php
                                    $selected = ($filters['_status'] == $commentStatus->id ? 'selected="selected"' : '');
                                @endphp
                                <option value="{{$commentStatus->id}}" {!! $selected !!}>
                                    {{$commentStatus->display_name}}
                                </option>
                            @endforeach
                        </select>

                        <label class="label ml-3 mr-2" for="user-select-field">{{__('a.Type')}}</label>
                        <select id="user-select-field" class="form-control border ml-2" name="_user_type">
                            <option value="">{{__('a.All')}}</option>
                            @php
                                $selected = ($filters['_user_type'] == 'public' ? 'selected="selected"' : '');
                            @endphp
                            <option value="public" {!! $selected !!}>{{__('a.Anonymous')}}</option>
                            @php
                                $selected = ($filters['_user_type'] == 'member' ? 'selected="selected"' : '');
                            @endphp
                            <option value="member" {!! $selected !!}>{{__('a.Members')}}</option>
                        </select>

                        <label class="label ml-3 mr-2" for="sort-select-field">{{__('a.Sort')}}</label>
                        <select id="sort-select-field" class="form-control border ml-2" name="_sort">
                            @php
                                $selected = ($filters['_sort'] == 'asc' ? 'selected="selected"' : '');
                            @endphp
                            <option value="asc" {!! $selected !!}>{{__('a.Asc')}}</option>
                            @php
                                $selected = ($filters['_sort'] == 'desc' ? 'selected="selected"' : '');
                            @endphp
                            <option value="desc" {!! $selected !!}>{{__('a.Desc')}}</option>
                        </select>

                        <label class="label ml-3 mr-2" for="paginate-select-field">{{__('a.Per page')}}</label>
                        <select id="paginate-select-field" class="form-control border ml-2" name="_paginate">
                            @foreach($range as $number)
                                @php
                                    $selected = ($filters['_paginate'] == $number ? 'selected="selected"' : '');
                                @endphp
                                <option value="{{$number}}" {!! $selected !!}>{{$number}}</option>
                            @endforeach
                        </select>

                        <button type="submit" class="btn btn-md btn-primary ml-5">{{__('a.Filter')}}</button>
                        <button type="submit" class="btn btn-md btn-dark ml-2 js-btn-form-filters-clear" data-url="{{$formUrl}}">{{__('a.Clear')}}</button>

                        @csrf
                    </form>
                </div>
            </div>
            {{--[[ END FILTERS --}}
        </div>
    </div>

    @include('admin.partials.notices')

    @if(vp_current_user_can('moderate_comments'))
        {{-- COMMENTS LIST --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="tile">
                    @if($comments->count() >= 1 )
                        <table class="table table-striped table-borderless comments-list">
                            <thead>
                            <tr>
                                <th scope="col" class="text-center">{{__('a.Author')}}</th>
                                <th scope="col" class="">{{__('a.Comment')}}</th>
                                <th scope="col" class="text-center">{{__('a.In response to')}}</th>
                                <th scope="col" class="text-center">{{__('a.Submitted on')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($comments as $comment)
                                @include('admin.comments.partials.comment', [
                                    'comment' => $comment,
                                    '__post_type' => $__post_type,
                                    'date_format' => $date_format,
                                    'time_format' => $time_format,
                                ])
                            @endforeach
                            </tbody>
                        </table>
                        <div class="comments-pagination">
                            {{$comments->render()}}
                        </div>
                    @else
                        <div class="alert alert-info">
                            {{__('a.No comments found.')}}
                        </div>
                    @endif
                </div>
            </div>
        </div>
        {{-- END COMMENTS LIST --}}
    @endif

@endsection
