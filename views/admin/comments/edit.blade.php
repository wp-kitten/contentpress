{{--
    Render the comment edit view
--}}
@php
    $baseRoute = "admin.{$__post_type->name}";
@endphp

@extends('admin.layouts.base')

@section('page-title')
    <title>{{__('a.Edit comment')}}</title>
@endsection

@section('main')

    <div class="app-title">
        <div class="cp-flex cp-flex--center cp-flex--space-between">
            <div>
                <h1>{{__('a.Edit Comment')}}</h1>
            </div>
            <ul class="list-unstyled list-inline mb-0">
                <li class="">
                    <a class="btn btn-primary d-none d-md-block" href="{{route("{$baseRoute}.comment.all")}}">{{__('a.Back')}}</a>
                </li>
            </ul>
        </div>
    </div>

    @include('admin.partials.notices')

    @if(vp_current_user_can('moderate_comments'))
        <div class="row">
            <div class="col-lg-12">
                <div class="tile">
                    <form method="post" action="{{route("{$baseRoute}.comment.update", ['id' => $comment->id])}}">
                        <div class="form-group">
                            <label class="" for="comment-status-field">{{__('a.Comment status')}}</label>
                            <select class="form-control border" id="comment-status-field" name="comment_status">
                                @foreach($comment_statuses as $commentStatus)
                                    @php $selected = ( ($comment->comment_status_id == $commentStatus->id) ? 'selected="selected"' : '' ); @endphp
                                    <option value="{{$commentStatus->id}}" {!! $selected !!}>{{$commentStatus->display_name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="" for="comment-content-field">{{__('a.Comment')}}</label>
                            <textarea class="form-control" id="comment-content-field" name="comment_content">{!! $comment->content !!}</textarea>
                        </div>

                        <div class="d-flex align-items-center justify-content-between">
                            <button type="submit" id="js-comment-submit-button" class="btn btn-primary">{{__('a.Update')}}</button>
                            <a href="{{route("{$baseRoute}.comment.delete", $comment->id)}}"
                               data-confirm="{{__('a.Are you sure you want to delete this comment?  All its replies will also be deleted.')}}"
                               class="btn btn-danger d-md-block">{{__('a.Delete')}}</a>
                        </div>

                        @csrf
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection
