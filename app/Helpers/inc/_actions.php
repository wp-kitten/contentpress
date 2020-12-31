<?php
/*
 * This file stores all actions registered by our application
 * ==============================================================================
 */

use App\Helpers\AdminBar;
use App\Helpers\ScriptsManager;
use App\Models\Post;
use App\Models\PostComments;

add_action( 'valpress/admin/head', function () {
    ScriptsManager::enqueueHeadScript( 'admin-js', asset( '_admin/js/admin.js' ) );
} );

/*
 * @see #57, #38
 */
add_action( 'valpress/post/deleted', function ( $postID ) {
    //#! TODO: Implement the post_deleted action
} );

add_action( 'valpress/post/new', function ( Post $post ) {
    //#! TODO: Implement the post_new action
} );

add_action( 'valpress/comment/status_changed', function ( PostComments $comment, $oldStatusID ) {
    //#! TODO: Implement the comment_status_changed action
}, 10, 2 );

/*
 * Frontend :: Comments
 *
 * These can be overridden by themes/plugins
 */
add_action( 'valpress/comment/render', '__valpress_render_comment', 10, 2 );
function __valpress_render_comment( PostComments $comment, $withReplies = true )
{
    $commentUserID = $comment->user_id;
    $commentAuthorName = ( $commentUserID ? $comment->user->display_name : $comment->author_name );
    $commentAuthorUrl = ( $commentUserID ? vp_get_user_meta( '_website_url', $commentUserID ) : $comment->author_url );
    ?>
    <div class="comment" id="comment-<?php esc_attr_e( $comment->id ); ?>">
        <div class="comment-body">
            <div class="author-vcard mr-3">
                <?php
                if ( $commentUserID ) {
                    $authorImageUrl = vp_get_user_profile_image_url( $commentUserID );
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
                    <time datetime="<?php esc_attr_e( $comment->created_at ); ?>" class=""><?php esc_html_e( vp_the_date( $comment ) ); ?></time>
                </div>
                <div class="comment-text">
                    <?php echo $comment->content; ?>
                </div>
                <?php do_action( 'valpress/comment/actions', $comment, $comment->post->id ); ?>
            </div> <!-- //.comment-content -->

        </div> <!-- //.comment-body -->

        <?php
        if ( $withReplies ) {
            echo '<div class="comment-replies">';
            do_action( 'valpress/comment/replies', $comment );
            echo '</div>';
        }
        ?>
    </div>
    <?php
}

add_action( 'valpress/comment/replies', '__valpress_render_comment_replies', 10, 1 );
function __valpress_render_comment_replies( PostComments $comment )
{
    $replies = PostComments::where( 'post_id', $comment->post->id )->where( 'comment_id', $comment->id )->get();
    if ( $replies ) {
        foreach ( $replies as $reply ) {
            $commentUserID = $reply->user_id;
            $commentAuthorName = ( $commentUserID ? $reply->user->display_name : $reply->author_name );
            $commentAuthorUrl = ( $commentUserID ? vp_get_user_meta( '_website_url', $commentUserID ) : $reply->author_url );
            ?>
            <div class="comment-body comment-reply">
                <div class="author-vcard mr-3">
                    <?php
                    if ( $commentUserID ) {
                        $authorImageUrl = vp_get_user_profile_image_url( $commentUserID );
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
                        <time datetime="<?php esc_attr_e( $reply->created_at ); ?>" class=""><?php esc_html_e( vp_the_date( $reply ) ); ?></time>
                    </div>
                    <div class="comment-text">
                        <?php echo $reply->content; ?>
                    </div>
                    <?php do_action( 'valpress/comment/actions', $reply, $reply->post->id ); ?>
                </div> <!-- //.comment-content -->
            </div> <!-- //.comment-body -->
            <div class="comment-replies">
                <?php do_action( 'valpress/comment/replies', $reply ); ?>
            </div>
            <?php
        }
    }
}

add_action( 'valpress/comment/actions', '__valpress_render_comment_actions', 10, 2 );
function __valpress_render_comment_actions( PostComments $comment, $postID )
{
    echo '<div class="comment-actions">';
    if ( vp_current_user_can( 'moderate_comments' ) ) {
        $editLink = vp_get_comment_edit_link( $comment->post, $comment->id );

        echo '<a href="#!" class="text-danger js-comment-delete" data-post-id="' . esc_attr( $postID ) . '" data-comment-id="' . esc_attr( $comment->id ) . '">' . esc_html( __( 'a.Delete' ) ) . '</a>';
        echo '<a href="' . esc_attr( $editLink ) . '" class="text-warning js-comment-edit" data-post-id="' . esc_attr( $postID ) . '" data-comment-id="' . esc_attr( $comment->id ) . '">' . esc_html( __( 'a.Edit' ) ) . '</a>';
    }

    if ( vp_comments_open( Post::find( $postID ) ) ) {
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
add_action( 'valpress/enqueue_text_editor', 'cp_enqueue_text_editor_scripts', 10, 4 );

/**
 * Render the post editor
 * @uses filter valpress/the_post_editor_content
 */
add_action( 'valpress/post_editor_content', '__valpress_render_text_editor_content', 10, 1 );
function __valpress_render_text_editor_content( $postContent = '' )
{
    echo apply_filters( 'valpress/the_post_editor_content', trim( $postContent ) );
}

/**
 * Injects the markup before the post content
 * @hooked valPressTextEditorBefore()
 */
add_action( 'valpress/post_editor_content/before', 'valPressTextEditorBefore' );

/**
 * Injects the markup after the post content
 * @hooked valPressTextEditorAfter()
 */
add_action( 'valpress/post_editor_content/after', 'valPressTextEditorAfter' );

/**
 * Injects the markup before the post content
 */
function valPressTextEditorBefore()
{
    echo '<textarea class="admin-text-editor" style="width: 100%; height: 500px; resize: vertical;" id="plugin_text_editor" name="post_content">';
}

/**
 * Injects the markup after the post content
 */
function valPressTextEditorAfter()
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
add_filter( 'valpress/body-class', '__valpress_body_class', 10, 1 );
function __valpress_body_class( $classes = [] ): array
{
    array_push( $classes, 'valpress' );

    if ( vp_is_singular() || vp_is_page() ) {
        array_push( $classes, "valpress-singular" );

        $post = vp_get_post();
        if ( $post ) {
            array_push( $classes, "valpress-{$post->post_type->name}" );
        }
    }
    return $classes;
}

add_filter( 'valpress/post-class', '__valpress_post_class', 10, 1 );
function __valpress_post_class( $classes = [] ): array
{
    if ( vp_is_singular() || vp_is_page() ) {
        $post = vp_get_post();
        if ( $post ) {
            array_push( $classes, "valpress-{$post->post_type->name}" );
        }
    }
    return $classes;
}

add_action( 'valpress/frontend/init', '__valpress_admin_bar' );
function __valpress_admin_bar()
{
    if ( vp_is_user_logged_in() ) {
        AdminBar::getInstance();
    }
}
