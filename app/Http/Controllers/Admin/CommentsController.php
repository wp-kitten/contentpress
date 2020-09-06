<?php

namespace App\Http\Controllers\Admin;

use App\CommentStatuses;
use App\Helpers\ScriptsManager;
use App\Post;
use App\PostComments;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CommentsController extends PostsController
{
    /**
     * Displays comments for all posts or comments for a specific $post_id
     * @param int $post_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index( $post_id = 0 )
    {
        if ( !cp_current_user_can( 'moderate_comments' ) ) {
            return $this->_forbidden();
        }

        ScriptsManager::localizeScript( 'categories-index-scripts', 'CommentsLocale', [
            'confirm_delete_comment' => __( 'a.Are you sure you want to delete this comment?  All its replies will also be deleted.' ),
        ] );
        ScriptsManager::enqueueFooterScript( 'comments.js', asset( '_admin/js/comments/index.js' ) );

        //#! Validate filters
        $request = \request();
        $filterCommentStatusID = ( \request( '_status' ) ?? 0 );
        $filterUserType = Str::lower( \request( '_user_type' ) ? wp_kses( \request( '_user_type' ), [] ) : '' ); // member || public
        $filterSort = Str::lower( ( \request( '_sort' ) ?? 'desc' ) );
        $filterPaginate = intval( ( \request( '_paginate' ) ?? $this->settings->getSetting( 'comments_per_page', 10 ) ) );
        $filteredComments = false;

        $filterSort = ( in_array( $filterSort, [ 'asc', 'desc' ] ) ? $filterSort : 'desc' );
        if ( $filterPaginate > 100 ) {
            $filterPaginate = 100;
        }

        if ( $request->has( '_status' ) ) {
            $publicComments = false;
            $filterByUserType = false;
            if ( !empty( $filterUserType ) && in_array( $filterUserType, [ 'public', 'member' ] ) ) {
                $filterByUserType = true;
                if ( 'public' == $filterUserType ) {
                    $publicComments = true;
                }
            }

            //#! Filter All comments
            if ( empty( $post_id ) ) {
                $filteredComments = PostComments::where( function ( $query ) use ( $filterCommentStatusID, $filterSort, $filterByUserType, $publicComments, $request ) {
                    if ( empty( $request->get( '_status' ) ) ) {
                        $query->whereIn( 'comment_status_id', ( new CommentStatuses() )->getIds() );
                    }
                    else {
                        $query->where( 'comment_status_id', intval( $filterCommentStatusID ) );
                    }

                    if ( $filterByUserType ) {
                        if ( $publicComments ) {
                            $query->where( 'user_id', null );
                        }
                        else {
                            $query->where( 'user_id', '!=', null );
                        }
                    }

                    $query->orderBy( 'id', $filterSort );

                    return $query;
                } )->paginate( $filterPaginate );
            }
            else {
                $post = Post::findOrFail( $post_id );
                $filteredComments = $post->post_comments()->where( function ( $query ) use ( $filterCommentStatusID, $filterSort, $filterByUserType, $publicComments, $request ) {
                    if ( empty( $request->get( '_status' ) ) ) {
                        $query->whereIn( 'comment_status_id', ( new CommentStatuses() )->getIds() );
                    }
                    else {
                        $query->where( 'comment_status_id', intval( $filterCommentStatusID ) );
                    }

                    if ( $filterByUserType ) {
                        if ( $publicComments ) {
                            $query->where( 'user_id', null );
                        }
                        else {
                            $query->where( 'user_id', '!=', null );
                        }
                    }

                    $query->orderBy( 'id', $filterSort );

                    return $query;
                } )->paginate( $filterPaginate );
            }
        }

        //! All comments
        if ( empty( $post_id ) ) {
            return view( 'admin.comments.all' )->with( [
                'enabled_languages' => $this->options->getOption( 'enabled_languages', [] ),
                'comments' => ( $filteredComments ? $filteredComments : PostComments::paginate( $this->settings->getSetting( 'comments_per_page', 10 ) ) ),

                //#! Special entries
                //@required
                'comment_statuses' => CommentStatuses::all(),
                '__post_type' => $this->_postType,
                'date_format' => $this->settings->getSetting( 'date_format' ),
                'time_format' => $this->settings->getSetting( 'time_format' ),
                'filters' => [
                    '_status' => $filterCommentStatusID,
                    '_user_type' => $filterUserType,
                    '_sort' => $filterSort,
                    '_paginate' => $filterPaginate,
                ],
            ] );
        }

        //#! A specific post's comments
        if ( !$filteredComments ) {
            $post = Post::findOrFail( $post_id );
            $filteredComments = $post->post_comments()->paginate( $this->settings->getSetting( 'comments_per_page', 10 ) );
        }
        return view( 'admin.comments.post' )->with( [
            'comments' => $filteredComments,

            //#! Special entries
            //@required
            'comment_statuses' => CommentStatuses::all(),
            '__post_type' => $this->_postType,
            'date_format' => $this->settings->getSetting( 'date_format' ),
            'time_format' => $this->settings->getSetting( 'time_format' ),
            'filters' => [
                '_status' => $filterCommentStatusID,
                '_user_type' => $filterUserType,
                '_sort' => $filterSort,
                '_paginate' => $filterPaginate,
            ],
        ] );
    }

    public function showCommentReplyPage( $post_id, $comment_id = null )
    {
        if ( !cp_current_user_can( 'moderate_comments' ) ) {
            return $this->_forbidden();
        }

        ScriptsManager::localizeScript( 'categories-edit-scripts', 'CommentsLocale', [
            'confirm_delete_comment' => __( 'a.Are you sure you want to delete this comment?  All its replies will also be deleted.' ),
            'comment_placeholder' => __( 'a.Your thoughts...' ),
        ] );
        ScriptsManager::enqueueFooterScript( 'comments-index.js', asset( '_admin/js/comments/index.js' ) );

        return view( 'admin.comments.reply' )->with( [
            'enabled_languages' => $this->options->getOption( 'enabled_languages', [] ),
            'comment_statuses' => CommentStatuses::all(),
            'post_id' => $post_id,
            'reply_to_comment_id' => $comment_id,

            'comment' => ( empty( $comment_id ) ? null : PostComments::find( $comment_id ) ),

            //#! Special entry
            //@required
            '__post_type' => $this->_postType,
            'date_format' => $this->settings->getSetting( 'date_format' ),
            'time_format' => $this->settings->getSetting( 'time_format' ),
        ] );
    }

    public function showCommentEditPage( $id )
    {
        if ( !cp_current_user_can( 'moderate_comments' ) ) {
            return $this->_forbidden();
        }

        ScriptsManager::localizeScript( 'categories-edit-scripts', 'CommentsLocale', [
            'confirm_delete_comment' => __( 'a.Are you sure you want to delete this comment?  All its replies will also be deleted.' ),
        ] );
        ScriptsManager::enqueueFooterScript( 'comments.js', asset( '_admin/js/comments/index.js' ) );

        return view( 'admin.comments/edit' )->with( [
            'comment' => PostComments::find( $id ),
            'comment_statuses' => CommentStatuses::all(),

            //#! Special entry
            //@required
            '__post_type' => $this->_postType,
        ] );
    }

    public function __insertComment()
    {
        if ( !cp_current_user_can( 'moderate_comments' ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger', // success or danger on error
                'text' => __( 'a.You are not allowed to perform this action.' ),
            ] );
        }

        $this->validate( $this->request, [
            'comment_content' => 'required|string',
            'status' => 'required|int',
            'post_id' => 'required|int',
        ] );

        $user = User::findOrFail( $this->current_user()->getAuthIdentifier() );

        $content = $this->request->comment_content;
        if ( isset( $content[ 4096 ] ) ) {
            $content = substr( $content, 0, 4096 );
        }

        $comment = PostComments::create( [
            'content' => $content,
            'author_ip' => $this->request->ip(),
            'user_agent' => $this->request->header( 'User-Agent' ),
            'post_id' => $this->request->post_id,
            'comment_status_id' => $this->request->status,
            'user_id' => $user->id,
            'comment_id' => ( $this->request->reply_to_comment_id ? $this->request->reply_to_comment_id : null ),
        ] );

        if ( $comment ) {
            do_action( 'contentpress/comment/added', $comment );
            return redirect()->back()->with( 'message', [
                'class' => 'success',
                'text' => __( 'a.Comment added.' ),
            ] );
        }

        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'a.The comment could not be added.' ),
        ] );
    }

    public function __updateComment( $id )
    {
        if ( !cp_current_user_can( 'moderate_comments' ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger', // success or danger on error
                'text' => __( 'a.You are not allowed to perform this action.' ),
            ] );
        }

        $this->validate( $this->request, [
            'comment_content' => 'required|string',
            'comment_status' => 'required|int',
        ] );

        $content = $this->request->comment_content;
        if ( isset( $content[ 4096 ] ) ) {
            $content = substr( $content, 0, 4096 );
        }

        $comment = PostComments::findOrFail( $id );

        $oldStatus = $comment->comment_status_id;

        $comment->content = $content;
        $comment->comment_status_id = $this->request->comment_status;

        $r = $comment->update();

        if ( $r ) {
            do_action( 'contentpress/comment/status_changed', $comment, $oldStatus );

            return redirect()->back()->with( 'message', [
                'class' => 'success',
                'text' => __( 'a.Comment updated.' ),
            ] );
        }

        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'a.The comment could not be updated.' ),
        ] );
    }

    public function __deleteComment( $id )
    {
        if ( !cp_current_user_can( 'moderate_comments' ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger', // success or danger on error
                'text' => __( 'a.You are not allowed to perform this action.' ),
            ] );
        }

        $result = PostComments::destroy( $id );
        if ( $result ) {
            do_action( 'contentpress/comment/deleted', $id );
            return redirect()->route( "admin.{$this->_postType->name}.comment.all" )->with( 'message', [
                'class' => 'success',
                'text' => __( 'a.Comment deleted.' ),
            ] );
        }
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'a.The specified comment could not be deleted.' ),
        ] );
    }

}
