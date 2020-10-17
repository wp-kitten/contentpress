<?php
/*
 * This file stores all actions registered by our application
 * ==============================================================================
 */

use App\Helpers\ScriptsManager;
use App\Models\Post;
use App\Models\PostComments;

add_action( 'contentpress/admin/head', function () {
    ScriptsManager::enqueueHeadScript( 'admin-js', asset( '_admin/js/admin.js' ) );
} );

/*
 * @see #57, #38
 */
add_action( 'contentpress/post/deleted', function ( $postID ) {
    //#! TODO: Implement the post_deleted action
} );

add_action( 'contentpress/post/new', function ( Post $post ) {
    //#! TODO: Implement the post_new action
} );

add_action( 'contentpress/comment/status_changed', function ( PostComments $comment, $oldStatusID ) {
    //#! TODO: Implement the comment_status_changed action
}, 10, 2 );

/*
 * Frontend :: Comments
 *
 * These can be overridden by themes/plugins
 */
add_action( 'contentpress/comment/render', '__contentpress_render_comment', 10, 2 );
function __contentpress_render_comment( PostComments $comment, $withReplies = true )
{
    $commentUserID = $comment->user_id;
    $commentAuthorName = ( $commentUserID ? $comment->user->display_name : $comment->author_name );
    $commentAuthorUrl = ( $commentUserID ? cp_get_user_meta( '_website_url', $commentUserID ) : $comment->author_url );
    ?>
    <div class="comment" id="comment-<?php esc_attr_e( $comment->id ); ?>">
        <div class="comment-body">
            <div class="author-vcard mr-3">
                <?php
                if ( $commentUserID ) {
                    $authorImageUrl = cp_get_user_profile_image_url( $commentUserID );
                }
                else {
                    $authorImageUrl = asset( 'images/placeholder-200.jpg' );
                }
                ?>
                <img src="<?php esc_attr_e( $authorImageUrl ); ?>" class="img-circle" width="120" height="120" alt=""/>
            </div>
            <div class="comment-content">
                <div class="comment-meta mb-1">
                    <h4 class="mb-5">
                        <a href="<?php esc_attr_e( $commentAuthorUrl ); ?>" class="title-link"><?php esc_html_e( $commentAuthorName ); ?></a>
                    </h4>
                    <time datetime="<?php esc_attr_e( $comment->created_at ); ?>" class=""><?php esc_html_e( cp_the_date( $comment ) ); ?></time>
                </div>
                <div class="comment-text">
                    <?php echo $comment->content; ?>
                </div>
                <?php do_action( 'contentpress/comment/actions', $comment, $comment->post->id ); ?>
            </div> <!-- //.comment-content -->

        </div> <!-- //.comment-body -->

        <?php
        if ( $withReplies ) {
            echo '<div class="comment-replies">';
            do_action( 'contentpress/comment/replies', $comment );
            echo '</div>';
        }
        ?>
    </div>
    <?php
}

add_action( 'contentpress/comment/replies', '__contentpress_render_comment_replies', 10, 1 );
function __contentpress_render_comment_replies( PostComments $comment )
{
    $replies = PostComments::where( 'post_id', $comment->post->id )->where( 'comment_id', $comment->id )->get();
    if ( $replies ) {
        foreach ( $replies as $reply ) {
            $commentUserID = $reply->user_id;
            $commentAuthorName = ( $commentUserID ? $reply->user->display_name : $reply->author_name );
            $commentAuthorUrl = ( $commentUserID ? cp_get_user_meta( '_website_url', $commentUserID ) : $reply->author_url );
            ?>
            <div class="comment-body comment-reply">
                <div class="author-vcard mr-3">
                    <?php
                    if ( $commentUserID ) {
                        $authorImageUrl = cp_get_user_profile_image_url( $commentUserID );
                    }
                    else {
                        $authorImageUrl = asset( 'images/placeholder-200.jpg' );
                    }
                    ?>
                    <img src="<?php esc_attr_e( $authorImageUrl ); ?>" class="img-circle" width="120" height="120" alt=""/>
                </div>
                <div class="comment-content">
                    <div class="comment-meta mb-1">
                        <h4 class="mb-5">
                            <a href="<?php esc_attr_e( $commentAuthorUrl ); ?>" class="title-link"><?php esc_html_e( $commentAuthorName ); ?></a>
                        </h4>
                        <time datetime="<?php esc_attr_e( $reply->created_at ); ?>" class=""><?php esc_html_e( cp_the_date( $reply ) ); ?></time>
                    </div>
                    <div class="comment-text">
                        <?php echo $reply->content; ?>
                    </div>
                    <?php do_action( 'contentpress/comment/actions', $reply, $reply->post->id ); ?>
                </div> <!-- //.comment-content -->
            </div> <!-- //.comment-body -->
            <div class="comment-replies">
                <?php do_action( 'contentpress/comment/replies', $reply ); ?>
            </div>
            <?php
        }
    }
}

add_action( 'contentpress/comment/actions', '__contentpress_render_comment_actions', 10, 2 );
function __contentpress_render_comment_actions( PostComments $comment, $postID )
{
    echo '<div class="comment-actions">';
    if ( cp_current_user_can( 'moderate_comments' ) ) {
        $editLink = cp_get_comment_edit_link( $comment->post, $comment->id );

        echo '<a href="#!" class="text-danger js-comment-delete" data-post-id="' . esc_attr( $postID ) . '" data-comment-id="' . esc_attr( $comment->id ) . '">' . esc_html( __( 'a.Delete' ) ) . '</a>';
        echo '<a href="' . esc_attr( $editLink ) . '" class="text-warning js-comment-edit" data-post-id="' . esc_attr( $postID ) . '" data-comment-id="' . esc_attr( $comment->id ) . '">' . esc_html( __( 'a.Edit' ) ) . '</a>';
    }

    if ( cp_comments_open( Post::find( $postID ) ) ) {
        echo '<a href="#!" class="text-info js-comment-reply" data-post-id="' . esc_attr( $postID ) . '" data-comment-id="' . esc_attr( $comment->id ) . '">' . esc_html( __( 'a.Reply' ) ) . '</a>';
    }
    echo '</div>';
}

/*
 * Admin :: Posts
 */
/**
 * Enqueue resources used for the post editor
 * @param int $postID
 * @param string $screen
 * @param $mainPostID
 * @param $languageID
 */
add_action( 'contentpress/enqueue_text_editor', 'cp_enqueue_text_editor_scripts', 10, 4 );

/**
 * Render the post editor
 * @uses filter contentpress/the_post_editor_content
 */
add_action( 'contentpress/post_editor_content', '__contentpress_render_text_editor_content', 10, 1 );
function __contentpress_render_text_editor_content( $postContent = '' )
{
    echo apply_filters( 'contentpress/the_post_editor_content', trim( $postContent ) );
}

/**
 * Injects the markup before the post content
 * @hooked contentPressTextEditorBefore()
 */
add_action( 'contentpress/post_editor_content/before', 'contentPressTextEditorBefore' );

/**
 * Injects the markup after the post content
 * @hooked contentPressTextEditorAfter()
 */
add_action( 'contentpress/post_editor_content/after', 'contentPressTextEditorAfter' );

/**
 * Injects the markup before the post content
 */
function contentPressTextEditorBefore()
{
    echo '<textarea class="admin-text-editor" style="width: 100%; height: 500px; resize: vertical;" id="plugin_text_editor" name="post_content">';
}

/**
 * Injects the markup after the post content
 */
function contentPressTextEditorAfter()
{
    echo '</textarea>';
}

/*
 * TODO: Add actions for users
 * Users
 *
 * user_new
 * user_deleted
 * user_registered
 * user_blocked
 * user_unblocked
 */

/*
 * Frontend
 * ============================================
 */
add_filter( 'contentpress/body-class', '__contentpress_body_class', 10, 1 );
function __contentpress_body_class( $classes = [] )
{
    array_push( $classes, 'contentpress' );

    if ( cp_is_singular() || cp_is_page() ) {
        array_push( $classes, "contentpress-singular" );

        $post = cp_get_post();
        if ( $post ) {
            array_push( $classes, "contentpress-{$post->post_type->name}" );
        }
    }
    return $classes;
}

add_filter( 'contentpress/post-class', '__contentpress_post_class', 10, 1 );
function __contentpress_post_class( $classes = [] )
{
    if ( cp_is_singular() || cp_is_page() ) {
        $post = cp_get_post();
        if ( $post ) {
            array_push( $classes, "contentpress-{$post->post_type->name}" );
        }
    }
    return $classes;
}
