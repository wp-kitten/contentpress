<?php

use App\Models\CommentStatuses;
use App\Models\Post;
use App\Models\PostComments;
use App\Models\Settings;

function cp_comments_open( ?Post $post )
{
    if ( !$post ) {
        return false;
    }

    if ( !cp_get_post_meta( $post, '_comments_enabled' ) ) {
        return false;
    }

    if ( cp_is_user_logged_in() ) {
        return true;
    }

    return ( new Settings() )->getSetting( 'anyone_can_comment' );
}

function cp_has_comments( ?Post $post )
{
    return ( cp_get_comments_count( $post ) > 0 );
}

function cp_get_comments_count( ?Post $post )
{
    if ( !$post ) {
        return 0;
    }
    return $post->post_comments->count();
}

function cp_comments_reply_form( ?Post $post, string $commentFormViewPath = 'partials.comment_form' )
{
    if ( !$post ) {
        return '';
    }
    return view( $commentFormViewPath )->with( [ 'post' => $post ] );
}

function cp_get_comment_status_name( ?PostComments $comment )
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

function cp_is_reply( ?PostComments $comment )
{
    if ( !$comment ) {
        return false;
    }
    return ( !empty( $comment->comment_id ) );
}

function cp_get_comment_url( ?PostComments $comment )
{
    if ( !$comment ) {
        return '';
    }
    $postType = $comment->post->post_type;
    $postUrl = route( "app.{$postType->name}.view", [ $comment->post->slug . '#comment-' . $comment->id ] );
    return $postUrl;
}

function cp_get_comment_author_name( ?PostComments $comment )
{
    if ( !$comment ) {
        return '';
    }
    if ( !empty( $comment->user_id ) ) {
        return cp_get_user_display_name( $comment->user );
    }
    return $comment->author_name;
}
