<?php
/*
 * This file stores all functions related to templates
 */

use App\Helpers\VPML;
use App\Models\Category;
use App\Models\Post;
use App\Models\PostComments;
use App\Models\PostStatus;
use App\Models\PostType;
use App\Models\Settings;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/**
 * Retrieve the route to the specified $post
 * @param Model $post A specific PostType model (ex: Post, Page, Article, whatever)
 * @return string
 */
function vp_get_permalink( Model $post ): string
{
    $postType = $post->post_type->name;

    if ( Route::has( "app.{$postType}.view" ) ) {
        return route( "app.{$postType}.view", $post->slug );
    }
    return '#';
}

/**
 * Retrieve the category link
 * @param Category $category
 * @return string
 */
function vp_get_category_link( Category $category ): string
{
    if ( Route::has( "blog.category" ) ) {
        return route( "blog.category", $category->slug );
    }
    return '#';
}

/**
 * Retrieve the author link
 * @param User $user
 * @return string
 */
function vp_get_author_link( User $user ): string
{
    if ( Route::has( "blog.author" ) ) {
        return route( "blog.author", $user->id );
    }
    return '#';
}

/**
 * Retrieve the tag link
 * @param Tag $tag
 * @return string
 */
function vp_get_tag_link( Tag $tag ): string
{
    if ( Route::has( "blog.tag" ) ) {
        return route( "blog.tag", $tag->slug );
    }
    return '#';
}

/**
 * Retrieve the edit link for a post type
 * @param Post $post
 * @return string
 */
function vp_get_post_edit_link( Post $post ): string
{
    return route( "admin.{$post->post_type->name}.edit", $post->id );
}

/**
 * Retrieve the edit link for a comment
 * @param Post $post
 * @param int $commentID
 * @return string
 */
function vp_get_comment_edit_link( Post $post, $commentID ): string
{
    return route( "admin.{$post->post_type->name}.comment.edit", $commentID );
}

/**
 * Retrieve the post's date
 * @param Post|PostComments $post
 * @param bool $withTimestamp
 * @return false|string
 */
function vp_the_date( $post, $withTimestamp = false ): string
{
    $settings = new Settings();
    $dateFormat = $settings->getSetting( 'date_format', 'M j, Y' );
    if ( $withTimestamp ) {
        $timeFormat = $settings->getSetting( 'time_format', 'H:i:s' );
        $dateFormat = "{$dateFormat} @{$timeFormat}";
    }
    return date( $dateFormat, strtotime( $post->created_at ) );
}

/**
 * Retrieve a user's related posts
 * @param int $userID
 * @param int $howMany The number of posts to retrieve. Set to -1 to get them all
 * @param int|null $excludePostID
 * @param int|null $languageID
 * @param string $postTypeName
 * @return mixed
 */
function vp_get_user_related_posts( $userID, $howMany = 4, $excludePostID = null, $languageID = null, $postTypeName = 'post' )
{
    if ( empty( $languageID ) ) {
        $languageID = VPML::getDefaultLanguageID();
    }

    $user = User::find( $userID );
    if ( !$user ) {
        return false;
    }

    $postType = PostType::where( 'name', $postTypeName )->first();
    if ( !$postType ) {
        return false;
    }

    $postStatus = PostStatus::where( 'name', 'publish' )->first();
    if ( !$postStatus ) {
        return false;
    }

    $posts = $user->posts();

    if ( $excludePostID ) {
        $posts = $posts->where( 'id', '!=', $excludePostID );
    }

    $posts = $posts
        ->where( 'post_type_id', $postType->id )
        ->where( 'post_status_id', $postStatus->id )
        ->where( 'language_id', $languageID );

    if ( $howMany > 0 ) {
        $posts = $posts->take( $howMany );
    }

    return $posts->get();
}

/**
 * Render the search form
 * @param string $placeholderText
 * @param string $searchButtonText
 */
function vp_search_form( $placeholderText = 'Search', $searchButtonText = 'Search' )
{
    $fid = vp_get_global_id();
    ?>
    <form method="get" action="<?php esc_attr_e( route( 'blog.search' ) ); ?>" class="search-form">
        <label class="hidden" for="search-field-<?php esc_attr_e( $fid ); ?>"><?php esc_html_e( __( 'a.Search' ) ); ?></label>
        <input type="text" name="s" id="search-field-<?php esc_attr_e( $fid ); ?>" class="search-field"
               placeholder="<?php esc_attr_e( $placeholderText ); ?>"
               value="<?php esc_attr_e( vp_get_search_query() ); ?>"/>
        <button type="submit" class="search-button"><?php echo $searchButtonText; ?></button>
    </form>
    <?php
}

/**
 * Retrieve the sanitized search query
 * @return mixed|string
 */
function vp_get_search_query(): string
{
    return wp_kses( \request()->get( 's', '' ), [] );
}

/**
 * Retrieve or render the post excerpt
 * @param Post $post
 * @param bool $showEllipsis
 * @return string
 *
 * @uses filter valpress/post/excerpt
 */
function vp_post_excerpt( $post, $showEllipsis = true )
{
    $excerpt = apply_filters( 'valpress/post/excerpt', $post->excerpt );
    if ( !empty( $excerpt ) && $showEllipsis ) {
        $excerpt = trim( $excerpt ) . ' [...]';
    }
    return html_entity_decode( $excerpt, ENT_QUOTES );
}

/**
 * Check to see whether or not the current route renders a singular post template
 * @param string $postType
 * @return bool|false|int
 */
function vp_is_singular( $postType = 'post' )
{
    $currentRoute = vp_get_current_route_name();

    if ( !empty( $postType ) ) {
        return preg_match( '/' . preg_quote( $postType ) . '/', $currentRoute );
    }

    $postTypes = PostType::all();
    foreach ( $postTypes as $postType ) {
        if ( preg_match( '/' . preg_quote( $postType ) . '/', $currentRoute ) ) {
            return true;
        }
    }
    return false;
}

/**
 * Check to see if the current request is for a page
 * @return false|int
 */
function vp_is_page()
{
    $currentRoute = vp_get_current_route_name();
    return preg_match( '/\bpage\b/', $currentRoute );
}

/**
 * Retrieve body tag classes
 * @param array $classes
 * @return string
 * @uses filter valpress/body-class
 */
function vp_body_classes( array $classes = [] ): string
{
    $classes = apply_filters( 'valpress/body-class', $classes );
    if ( !empty( $classes ) ) {
        $classes = array_unique( $classes );
        $classes = array_map( 'trim', $classes );
        return implode( ' ', array_map( 'esc_attr', $classes ) );
    }
    return '';
}

/**
 * Print post classes
 * @param array $classes
 * @return string
 * @uses filter valpress/body-class
 */
function vp_post_classes( array $classes = [] ): string
{
    $classes = apply_filters( 'valpress/post-class', $classes );
    if ( !empty( $classes ) ) {
        $classes = array_unique( $classes );
        $classes = array_map( 'trim', $classes );
        return implode( ' ', array_map( 'esc_attr', $classes ) );
    }
    return '';
}

/**
 * Retrieve the instance of an App\Models\Post if $postID is specified otherwise the current App\Models\Post if viewing a singular template
 * @param null|int $postID
 * @return App\Models\Post|null
 */
function vp_get_post( $postID = null ): ?Post
{
    if ( !empty( $postID ) ) {
        $post = Post::find( $postID );
        return ( $post ?? null );
    }
    return $GLOBALS[ 'cp_post' ];
}

/**
 * Display archive pagination
 * @param Illuminate\Pagination\Paginator|Illuminate\Pagination\LengthAwarePaginator $paginator
 * @param string $cssClass
 */
function vp_paginate_archive( $paginator, $cssClass = '' )
{
    if ( !$paginator ) {
        return;
    }
    ?>
    <div class="<?php echo esc_attr( $cssClass ); ?>">
        <?php echo $paginator->appends( request()->except( 'page' ) )->links(); ?>
    </div>
    <?php
}

/**
 * Display the Prev/Next post links
 * @param Post $post
 * @param string $cssClass
 * @param bool $sameCategory Whether or not to get posts in the same category
 * @param array $options
 */
function vp_posts_navigation( Post $post, $cssClass = '', $sameCategory = true, array $options = [] )
{
    if ( !$post ) {
        return;
    }
    if ( $sameCategory ) {
        $category = $post->categories()->first();

        $previous_id = DB::table( 'category_post' )
            ->where( function ( $query ) use ( $category ) {
                if ( $category ) {
                    $query->where( 'category_id', $category->id );
                }
                return $query;
            } )
            ->where( 'post_id', '<', $post->id )
            ->orderBy( 'post_id', 'DESC' )
            ->max( 'post_id' );

        $next_id = DB::table( 'category_post' )
            ->where( function ( $query ) use ( $category ) {
                if ( $category ) {
                    $query->where( 'category_id', $category->id );
                }
                return $query;
            } )
            ->where( 'post_id', '>', $post->id )
            ->orderBy( 'post_id', 'DESC' )
            ->max( 'post_id' );

        $previous = Post::find( $previous_id );
        $next = Post::find( $next_id );
    }
    else {
        $previous_id = Post::where( 'id', '<', $post->id )->max( 'id' );
        $next_id = Post::where( 'id', '>', $post->id )->min( 'id' );

        $next = Post::find( $next_id );
        $previous = Post::find( $previous_id );
    }
    if ( !$next && !$previous ) {
        return;
    }
    ?>
    <div class="posts-navigation">
        <span class="previous-link-wrap">
            <?php if ( $previous ) { ?>
                <a href="<?php esc_attr_e( vp_get_permalink( $previous ) ); ?>" class="previous-post-link">
                    <i class="fas fa-chevron-left nav-icon"></i>
                    <span class="the-title" title="<?php esc_attr_e( $previous->title ); ?>">
                        <?php echo vp_ellipsis( wp_kses_post( $previous->title ), 80 ); ?>
                    </span>
                </a>
            <?php } ?>
        </span>

        <span class="next-link-wrap">
            <?php if ( $next ) { ?>
                <a href="<?php esc_attr_e( vp_get_permalink( $next ) ); ?>" class="next-post-link">
                    <span class="the-title" title="<?php esc_attr_e( $next->title ); ?>">
                        <?php echo vp_ellipsis( wp_kses_post( $next->title ), 80 ); ?>
                    </span>
                     <i class="fas fa-chevron-right nav-icon"></i>
                </a>
            <?php } ?>
        </span>
    </div>
    <?php
}

/**
 * Adds the active class for the menu item if the urls match
 * @param string $menuItemUrl
 * @param string $activeClass
 * @return string
 */
function vp_activate_menu_item( string $menuItemUrl, $activeClass = 'active' ): string
{
    $uri = strtolower( getenv( 'REQUEST_URI' ) );
    $miu = strtolower( parse_url( $menuItemUrl, PHP_URL_PATH ) );
    return ( trailingslashit( $uri ) == trailingslashit( $miu ) ? $activeClass : '' );
}

/**
 * Retrieve a collection of posts from the specified category
 * @param Category $category
 * @param int $howMany
 * @param null|int $excludePostID
 * @param null|int $languageID
 * @param string $postTypeName
 * @return false|Collection
 */
function vp_get_related_posts( Category $category, $howMany = 4, $excludePostID = null, $languageID = null, $postTypeName = 'post' )
{
    if ( empty( $languageID ) ) {
        $languageID = VPML::getDefaultLanguageID();
    }

    $postType = PostType::where( 'name', $postTypeName )->first();
    if ( !$postType ) {
        return false;
    }

    $postStatus = PostStatus::where( 'name', 'publish' )->first();
    if ( !$postStatus ) {
        return false;
    }

    return $category->posts()->limit( $howMany )->get()->filter( function ( $post ) use ( $postType, $postStatus, $languageID, $excludePostID ) {
        if ( $excludePostID && $post->id == $excludePostID ) {
            return false;
        }
        if ( $post->post_type_id != $postType->id ) {
            return false;
        }
        elseif ( $post->post_status_id != $postStatus->id ) {
            return false;
        }
        elseif ( $post->language_id != $languageID ) {
            return false;
        }
        return true;
    } );
}
