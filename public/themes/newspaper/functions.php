<?php

use App\Models\CommentStatuses;
use App\Models\Feed;
use App\Http\Controllers\NewspaperAdminController;
use App\Models\Options;
use App\Models\Post;
use App\Models\PostComments;
use App\Models\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

define( 'NP_THEME_DIR_PATH', untrailingslashit( wp_normalize_path( dirname( __FILE__ ) ) ) );
define( 'NP_THEME_DIR_NAME', basename( dirname( __FILE__ ) ) );

require_once( NP_THEME_DIR_PATH . '/src/AdsManager.php' );
require_once( NP_THEME_DIR_PATH . '/src/NewspaperHelper.php' );
require_once( NP_THEME_DIR_PATH . '/src/NewspaperUserFeeds.php' );
require_once( NP_THEME_DIR_PATH . '/controllers/NewspaperAdminController.php' );
require_once( NP_THEME_DIR_PATH . '/controllers/NewspaperThemeController.php' );
require_once( NP_THEME_DIR_PATH . '/controllers/NewspaperAjaxController.php' );
require_once( NP_THEME_DIR_PATH . '/models/User.php' );
require_once( NP_THEME_DIR_PATH . '/theme-hooks.php' );

//#! If w = 0, then size will be ignored
cp_add_image_size( 'w60', [ 'w' => 60 ] );
cp_add_image_size( 'w150', [ 'w' => 150 ] );
cp_add_image_size( 'w240', [ 'w' => 240 ] );
cp_add_image_size( 'w350', [ 'w' => 350 ] );
cp_add_image_size( 'w510', [ 'w' => 510 ] );

/**
 * Submit a comment
 * @param Controller $controller
 * @param int $postID
 *
 * @hooked contentpress/submit_comment
 *
 * @return RedirectResponse
 */
function np_theme_submit_comment( Controller $controller, int $postID )
{
    $post = Post::find( $postID );
    if ( !$post ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'fr::m.Sorry, an error occurred.' ),
        ] );
    }

    //#! Make sure the comments are open for this post
    if ( !cp_get_post_meta( $post, '_comments_enabled' ) ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'fr::m.Sorry, the comments are closed for this post.' ),
        ] );
    }

    $request = $controller->getRequest();
    $settings = $controller->getSettings();
    $user = $controller->current_user();

    //#! Make sure the current user is allowed to comment
    if ( !cp_is_user_logged_in() && !$settings->getSetting( 'anyone_can_comment' ) ) {
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'fr::m.Sorry, you are not allowed to comment for this post.' ),
        ] );
    }

    $replyToCommentID = null;
    if ( isset( $request->reply_to_comment_id ) && !empty( $request->reply_to_comment_id ) ) {
        $replyToCommentID = intval( $request->reply_to_comment_id );
    }

    $commentApproved = false;

    if ( $user && cp_current_user_can( 'moderate_comments' ) ) {
        $commentStatusID = CommentStatuses::where( 'name', 'approve' )->first()->id;
        $commentApproved = true;
    }
    else {
        $csn = $settings->getSetting( 'default_comment_status', 'pending' );
        $commentStatusID = CommentStatuses::where( 'name', $csn )->first()->id;
    }

    $commentData = [
        'content' => $request->comment_content,
        'author_ip' => esc_html( $request->ip() ),
        'user_agent' => esc_html( $request->header( 'User-Agent' ) ),
        'post_id' => intval( $postID ),
        'comment_status_id' => intval( $commentStatusID ),
        'user_id' => ( $user ? $user->getAuthIdentifier() : null ),
        'comment_id' => ( is_null( $replyToCommentID ) ? null : intval( $replyToCommentID ) ),
    ];

    if ( !$user ) {
        $authorName = $request->get( 'author_name' );
        if ( empty( $authorName ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'fr::m.Your name is required' ),
                'data' => $request->post(),
            ] );
        }
        $authorEmail = $request->get( 'author_email' );
        if ( empty( $authorEmail ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'fr::m.Your email is required' ),
                'data' => $request->post(),
            ] );
        }
        if ( !filter_var( $authorEmail, FILTER_VALIDATE_EMAIL ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'fr::m.The specified email address is not valid' ),
                'data' => $request->post(),
            ] );
        }
        $authorUrl = $request->get( 'author_website' );
        if ( !empty( $authorUrl ) ) {
            if ( !filter_var( $authorUrl, FILTER_VALIDATE_URL ) || ( false === strpos( $authorUrl, '.' ) ) ) {
                return redirect()->back()->with( 'message', [
                    'class' => 'danger',
                    'text' => __( 'fr::m.The specified website URL is not valid' ),
                    'data' => $request->post(),
                ] );
            }
        }

        $commentData[ 'author_name' ] = $authorName;
        $commentData[ 'author_email' ] = $authorEmail;
        $commentData[ 'author_url' ] = ( empty( $authorUrl ) ? null : wp_strip_all_tags( $authorUrl ) );
        $commentData[ 'author_ip' ] = $request->ip();
        $commentData[ 'user_agent' ] = esc_html( $request->header( 'User-Agent' ) );
    }

    $comment = PostComments::create( $commentData );

    if ( $comment ) {
        //#! If approved
        $m = __( 'fr::m.Comment added' );
        if ( !$commentApproved ) {
            $m = __( 'fr::m.Your comment has been added and currently awaits moderation. Thank you!' );
        }

        return redirect()->back()->with( 'message', [
            'class' => 'success',
            'text' => $m,
        ] );
    }

    return redirect()->back()->with( 'message', [
        'class' => 'danger',
        'text' => __( 'fr::m.The comment could not be added' ),
    ] );
}

/**
 * Render the auth links in the main menu
 */
function np_menuRenderAuthLinks()
{
    $links = cp_login_logout_links();
    if ( cp_is_user_logged_in() ) {
        $user = cp_get_current_user();
        //#! Contributor & administrators
        if ( cp_current_user_can( 'delete_others_posts' ) ) {
            ?>
            <a href="<?php esc_attr_e( route( 'admin.dashboard' ) ); ?>"><?php esc_html_e( __( 'np::m.Dashboard' ) ); ?></a>
            <?php
        }
        else {
            ?>
            <a href="<?php esc_attr_e( route( 'admin.users.edit', $user->getAuthIdentifier() ) ); ?>"><?php esc_html_e( __( 'np::m.Your profile' ) ); ?></a>
            <?php
        }
        ?>
        <a href="<?php esc_attr_e( $links[ 'logout' ] ); ?>"
           class="text-danger"
           onclick='event.preventDefault(); document.getElementById("app-logout-form").submit();'>
            <?php esc_html_e( __( 'np::m.Logout' ) ); ?>
        </a>
        <form id="app-logout-form" action="<?php esc_attr_e( $links[ 'logout' ] ); ?>" method="POST" style="display: none;">
            <?php echo csrf_field(); ?>
        </form>
        <?php
    }
    else {
        echo '<a href="' . esc_attr( $links[ 'login' ] ) . '">' . esc_html( __( 'np::m.Log in' ) ) . '</a>';
        if ( !empty( $links[ 'register' ] ) ) {
            echo '<a href="' . esc_attr( $links[ 'register' ] ) . '">' . esc_html( __( 'np::m.Register' ) ) . '</a>';
        }
    }
}

/**
 * Check to see whether the specified $feedID is part of the provided $specialCategorySlug
 * @param int $feedID
 * @param string $specialCategorySlug
 * @return bool
 */
function np_isUserFeed( int $feedID, string $specialCategorySlug = NPFR_CATEGORY_PRIVATE )
{
    if ( !defined( 'NPFR_CATEGORY_PRIVATE' ) ) {
        return false;
    }
    $feed = Feed::find( $feedID );
    if ( $feed ) {
        $category = $feed->category()->first();
        if ( $category ) {
            $parentCat = $category->parent()->first();
            return ( $parentCat && $parentCat->slug == $specialCategorySlug );
        }
    }
    return false;
}

/**
 * Check to see whether the user custom home feature is enabled
 * @return bool
 */
function np_userCustomHomeEnabled()
{
    if ( !defined( 'NPFR_PLUGIN_DIR_NAME' ) ) {
        return false;
    }
    $userRegistrationOpen = ( new Settings() )->getSetting( 'user_registration_open' );
    if ( $userRegistrationOpen ) {
        $themeOptions = ( new Options() )->getOption( NewspaperAdminController::THEME_OPTIONS_OPT_NAME, [] );
        if ( !isset( $themeOptions[ 'general' ] ) ) {
            return false;
        }
        return ( is_array( $themeOptions[ 'general' ] ) && isset( $themeOptions[ 'general' ][ 'enable_user_custom_home' ] )
            ? $themeOptions[ 'general' ][ 'enable_user_custom_home' ]
            : false
        );
    }
    return false;
}
