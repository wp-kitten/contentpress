<?php

namespace App\Helpers;

use App\PostComments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class CommentsWalker
{
    /**
     * Holds the id of the current post
     * @var int
     */
    private $postID;

    /**
     * Holds the base path to comments page
     * @var string
     */
    private $baseRoute = '';

    /**
     * The list of options to configure the comments
     * @var array
     */
    private $options = [];

    /**
     * CommentsWalker constructor.
     * @param int $postID The id of the current post
     * @param string $postTypeName The name of the post type for which we render comments
     * @param array $options The list of options to configure the comments
     */
    public function __construct( $postID, $postTypeName, $options = [] )
    {
        $this->postID = $postID;
        $this->options = $options;
        $this->baseRoute = "admin.{$postTypeName}";
    }

    /**
     * Lists all comments and their replies
     * @param bool $withReplies
     */
    public function renderComments( $withReplies = true )
    {
        $comments = null;
        if ( $this->hasComments() ) {
            $comments = $this->getComments();
            $numComments = $comments->count();
            // Limit the number of comments shown when the per_page option is not present
            if ( !isset( $this->options[ 'per_page' ] ) ) {
                $numComments = intval( $this->options[ 'per_page' ] );
            }
            $i = 0;
            foreach ( $comments as $comment ) {
                if ( $i >= $numComments ) {
                    break;
                }
                $this->renderComment( $comment, $withReplies );
                $i++;
            }
        }
        return $comments;
    }

    /**
     * Check to see whether or not the current post has any comments
     * @return bool
     */
    public function hasComments()
    {
        return ( $this->getComments()->count() > 0 );
    }

    //<editor-fold desc="Main Comments">

    /**
     * Retrieve all comments for the current post
     * @return LengthAwarePaginator|Model
     */
    public function getComments()
    {
        //#! Initially get only the top level comments
        $commentsQuery = PostComments::where( 'comment_id', null );

        if ( $this->postID ) {
            $commentsQuery->where( 'post_id', $this->postID );
        }
        if ( isset( $this->options[ 'comment_status' ] ) && !empty( $this->options[ 'comment_status' ] ) ) {
            $commentsQuery->where( 'comment_status_id', $this->options[ 'comment_status' ] );
        }
        if ( isset( $this->options[ 'user_type' ] ) && !empty( $this->options[ 'user_type' ] ) ) {
            if ( 'public' == $this->options[ 'user_type' ] ) {
                $commentsQuery->where( 'user_id', null );
            }
            else {
                $commentsQuery->where( 'user_id', '!=', null );
            }
        }
        if ( isset( $this->options[ 'sort' ] ) && !empty( $this->options[ 'sort' ] ) ) {
            $commentsQuery->orderBy( 'created_at', $this->options[ 'sort' ] );
        }

        if ( isset( $this->options[ 'per_page' ] ) && !empty( $this->options[ 'per_page' ] ) ) {
            return $commentsQuery->paginate( $this->options[ 'per_page' ] );
        }

        return $commentsQuery->get();
    }

    /**
     * Render the specified $comment
     * @param PostComments $comment
     * @param bool $withReplies
     */
    protected function renderComment( PostComments $comment, $withReplies = true )
    {
        if ( cp_is_admin() ) {
            ?>
            <div class="comment" id="comment-<?php esc_attr_e( $comment->id ); ?>">
                <p><?php esc_html_e( $comment->post->user->display_name ); ?></p>
                <div><?php echo $comment->content; ?></div>
                <?php $this->renderActions( $comment ); ?>
                <?php
                if ( $withReplies ) {
                    $this->renderReplies( $comment );
                }
                ?>
            </div>
            <?php
        }
        else {
            do_action( 'contentpress/comment/render', $comment, $withReplies );
        }
    }

    //</editor-fold>

    //<editor-fold desc="Replies">
    /**
     * Retrieve all replies for the given $comment
     * @param PostComments $comment
     * @return mixed
     */
    private function __getReplies( PostComments $comment )
    {
        return PostComments::where( 'post_id', $this->postID )->where( 'comment_id', $comment->id )->get();
    }

    /**
     * Recursively render all replies to the given $comment
     * @param PostComments $comment
     */
    protected function renderReplies( PostComments $comment )
    {
        $replies = $this->__getReplies( $comment );
        if ( cp_is_admin() ) {
            if ( $replies ) {
                echo '<ul class="bullet-line-list mt-4">';
                foreach ( $replies as $reply ) {
                    ?>
                    <li id="comment-<?php esc_attr_e( $reply->id ); ?>">
                        <p><?php esc_html_e( $reply->post->user->display_name ); ?></p>
                        <div><?php echo $reply->content; ?></div>

                        <?php $this->renderActions( $reply ); ?>
                        <?php $this->renderReplies( $reply ); ?>
                    </li>
                    <?php
                }
                echo '</ul>';
            }
        }
        else {
            do_action( 'contentpress/comment/replies', $comment );
        }
    }
    //</editor-fold>

    //<editor-fold desc="Helper methods">
    protected function renderActions( PostComments $comment )
    {
        if ( cp_is_admin() ) {
            ?>
            <div class="comment-actions">
                <?php if ( cp_current_user_can( 'moderate_comments' ) ) : ?>
                    <a href="<?php esc_attr_e( route( "{$this->baseRoute}.comment.delete", [ 'id' => $comment->id ] ) ); ?>"
                       data-confirm="<?php esc_html_e(__('a.Are you sure you want to delete this comment?  All its replies will also be deleted.'));?>"
                       class="text-danger"><?php esc_html_e( __( 'a.Delete' ) ); ?></a>
                    <a href="<?php esc_attr_e( route( "{$this->baseRoute}.comment.edit", [ 'id' => $comment->id ] ) ); ?>" class="text-info"><?php esc_html_e( __( 'a.Edit' ) ); ?></a>
                <?php endif; ?>
                <a href="<?php esc_attr_e( route( "{$this->baseRoute}.comment.reply", [ 'post_id' => $this->postID, 'comment_id' => $comment->id ] ) ); ?>" class="text-warning"><?php esc_html_e( __( 'a.Reply' ) ); ?></a>
            </div>
            <?php
        }
        else {
            do_action( 'contentpress/comment/actions', $comment, $this->postID );
        }
    }
    //</editor-fold>
}
