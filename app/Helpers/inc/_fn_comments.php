<?php

use App\Models\CommentStatuses;
use App\Models\Post;
use App\Models\PostComments;
use App\Models\Settings;

function vp_comments_open( ?Post $post ): bool
{
    if ( !$post ) {
        return false;
    }

    if ( !vp_get_post_meta( $post, '_comments_enabled' ) ) {
        return false;
    }

    if ( vp_is_user_logged_in() ) {
        return true;
    }

    return ( new Settings() )->getSetting( 'anyone_can_comment' );
}

function vp_has_comments( ?Post $post ): bool
{
    return ( vp_get_comments_count( $post ) > 0 );
}

function vp_get_comments_count( ?Post $post ): int
{
    if ( !$post ) {
        return 0;
    }
    return $post->post_comments->count();
}

function vp_comments_reply_form( ?Post $post, string $commentFormViewPath = 'partials.comment_form' ): string
{
    if ( !$post ) {
        return '';
    }
    return view( $commentFormViewPath )->with( [ 'post' => $post ] );
}

function vp_get_comment_status_name( ?PostComments $comment ): string
{
    if ( !$comment ) {
        return '';
    }
    $commentStatus = CommentStatuses::find( $comment->comment_status_id );
    if ( !$commentStatus ) {
        return '';
    }
    return $commentStatus->display_name;
}

function vp_is_reply( ?PostComments $comment ): bool
{
    if ( !$comment ) {
        return false;
    }
    return ( !empty( $comment->comment_id ) );
}

function vp_get_comment_url( ?PostComments $comment ): string
{
    if ( !$comment ) {
        return '';
    }
    $postType = $comment->post->post_type;
    $postUrl = route( "app.{$postType->name}.view", [ $comment->post->slug . '#comment-' . $comment->id ] );
    return $postUrl;
}

function vp_get_comment_author_name( ?PostComments $comment ): string
{
    if ( !$comment ) {
        return '';
    }
    if ( !empty( $comment->user_id ) ) {
        return vp_get_user_display_name( $comment->user );
    }
    return $comment->author_name;
}
