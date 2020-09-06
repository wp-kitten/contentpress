{{--
    Render the comment reply screen
--}}
@php
    $baseRoute = "admin.{$__post_type->name}";
@endphp

@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Reply to comment')}}</title>
@endsection

@section('main')

    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('a.Reply to comment')}}</h1>
            </div>
            <ul class="list-unstyled list-inline mb-0">
                <li class="">
                    <a class="btn btn-primary d-none d-md-block" href="{{route("{$baseRoute}.comment.all")}}">{{__('a.Back')}}</a>
                </li>
            </ul>
        </div>
    </div>

    @include('admin.partials.notices')

    @if(cp_current_user_can('moderate_comments'))
        {{-- Render the original comment --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="tile">
                    <div class="tile-title">
                        <h3 class="title">{{__('a.Original comment')}}</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-responsive table-borderless comments-list">
                            <thead>
                            <tr>
                                <th scope="col" class="text-center">{{__('a.Author')}}</th>
                                <th scope="col" class="">{{__('a.Comment')}}</th>
                                <th scope="col" class="text-center">{{__('a.In response to')}}</th>
                                <th scope="col" class="text-center">{{__('a.Submitted on')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @include('admin.comments.partials.comment', [
                                    'comment' => $comment,
                                    '__post_type' => $__post_type,
                                    'date_format' => $date_format,
                                    'time_format' => $time_format,
                                ])
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="tile">
                    <h3 class="tile-title">{{__('a.Your thoughts...')}}</h3>

                    <div class="card-body">
                        <form method="post" action="{{route("{$baseRoute}.comment.insert")}}">
                            <div class="form-group">
                                <label class="" for="comment-status-field">{{__('a.Comment status')}}</label>
                                <select class="form-control border" id="comment-status-field" name="status">
                                    @foreach($comment_statuses as $commentStatus)
                                        <option value="{{$commentStatus->id}}">{{$commentStatus->display_name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="" for="comment-content-field">{{__('a.Comment')}}</label>
                                <div class="quill-scrolling-container">
                                    <div id="comment_content-editor">{!! old('comment_content') !!}</div>
                                </div>
                                <textarea class="form-control hidden" id="comment-content-field" name="comment_content">{!! old('comment_content') !!}</textarea>
                            </div>

                            <button type="submit" id="js-comment-submit-button" class="btn btn-primary">{{__('a.Submit')}}</button>
                            <input type="hidden" name="post_id" value="{{$post_id}}"/>
                            <input type="hidden" name="reply_to_comment_id" value="{{$reply_to_comment_id}}"/>
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
