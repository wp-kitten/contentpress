{{--
    Render a comment entry

    @require $comment
    @require $__post_type
    @require $date_format
    @require $time_format
--}}
<tr>
    <td class="comment-author text-center">
        @if($comment->user_id)
            <a href="#" class="d-block text-info">{{$comment->user->display_name}}</a>
            <a href="mailto:{{$comment->user->email}}" class="d-block mt-3 text-info">{{__('a.(Email)')}}</a>
        @else
            <a href="{{$comment->author_url}}" class="d-block text-info">{{$comment->author_name}}</a>
            <a href="mailto:{{$comment->author_email}}" class="d-block mt-3 text-info">{{__('a.(Email)')}}</a>
        @endif
    </td>
    <td class="comment-content">
        <div class="comment-content-wrap">
            @if(vp_is_reply($comment))
                @php
                    $parentComment = App\Models\PostComments::find($comment->comment_id);
                    $commentUrl = '<a href="'.vp_get_comment_url($parentComment).'">'.vp_get_comment_author_name($parentComment).'</a>';
                @endphp
                <span class="d-block mb-3">{!! __('a.In reply to :comment_link.', [ 'comment_link' => $commentUrl]) !!}</span>
            @endif
            <div class="comment-content text-dark">{!! trim($comment->content) !!}</div>
        </div>
        <div class="comment-actions mt-3 text-right">
            <a href="{{route("admin.{$__post_type->name}.comment.reply", [$comment->post->id, $comment->id])}}"
               class="text-primary">
                {{__('a.Reply')}}
            </a>
            <a href="{{route("admin.{$__post_type->name}.comment.edit", $comment->id)}}"
               class="text-primary">
                {{__('a.Edit')}}
            </a>
            <a href="{{route("admin.{$__post_type->name}.comment.delete", $comment->id)}}"
               data-confirm="{{__('a.Are you sure you want to delete this comment?  All its replies will also be deleted.')}}"
               class="text-danger">
                {{__('a.Delete')}}
            </a>
        </div>
    </td>
    <td class="comment-response-to text-center">
        <a class="d-block text-info" href="{{route("admin.{$__post_type->name}.comment.all", $comment->post->id)}}">
            {{$comment->post->title}}
        </a>
        <a class="d-block mt-3 text-info"
           href="{{route("app.{$__post_type->name}.view", $comment->post->slug)}}">
            {{__('a.View :post_type', ['post_type' => $__post_type->display_name])}}
        </a>
    </td>
    <td class="comment-date text-center">
        <span class="d-block text-dark">
            {{vp_get_comment_status_name($comment)}}
        </span>
        <span class="d-block mt-3 text-dark">
            {{date("{$date_format} {$time_format}", strtotime($comment->created_at))}}
        </span>
    </td>
</tr>
