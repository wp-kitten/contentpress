<?php

namespace App\Helpers;

use App\Models\Category;
use App\Models\Language;
use App\Models\MenuItemType;
use App\Models\Post;
use App\Models\PostStatus;
use App\Models\PostType;
use App\Models\Settings;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Util
{
    /**
     * Retrieve an associated array in the format: table_name: number_of_rows
     */
    public static function getCountTableRows()
    {
        return DB::table( 'INFORMATION_SCHEMA.TABLES' )
            ->select( 'table_name', 'table_rows' )
            ->where( 'TABLE_SCHEMA', '=', env( 'DB_DATABASE' ) )
            ->get();
    }

    /**
     * Retrieve a custom set of posts, crafted for react UI
     * @param int|string $languageID The language ID or language code
     * @param string $postStatus
     * @param int $perPage defaults to 10. Set -1 to retrieve all
     * @param int $page The page to retrieve data from (as in "next page")
     * @param string $postType
     * @return array
     */
    public static function getPosts( $languageID = 0, $postStatus = null, $perPage = 0, $page = 1, $postType = 'post' )
    {
        $settings = new Settings();
        $language_class = new Language();

        $cfgPerPage = $settings->getSetting( 'posts_per_page' );
        $output = [ 'posts' => [], 'pagination' => [] ];
        $posts = [];
        $__posts = null;
        $pages = 0;
        $offset = ( ( $perPage == -1 ) ? 0 : ( ( $page > 1 ) ? ( ( $page - 1 ) * $perPage ) : 0 ) );
        $all = ( $perPage == -1 );
        $perPage = ( ( $perPage == 0 ) ? $cfgPerPage : $perPage );

        if ( empty( $languageID ) ) {
            $languageID = self::getDefaultLanguageID();
        }
        else {
            $languageID = $language_class->getFrom( $languageID )->id;
        }

        $postStatus = ( empty( $postStatus ) ? $settings->getSetting( 'default_post_status' ) : $postStatus );
        $postStatusID = PostStatus::where( 'name', $postStatus )->first()->id;
        $postTypeID = PostType::where( 'name', $postType )->first()->id;

        //#! Query posts
        $qp = Post::where( 'language_id', $languageID )->where( 'post_status_id', $postStatusID )->where( 'post_type_id', $postTypeID );

        $allNumPosts = $qp->count();

        if ( $all ) {
            $__posts = $qp->get();
        }
        else {
            $__posts = $qp->skip( $offset )->take( $perPage )->get();
            $pages = ( $allNumPosts ? ceil( $allNumPosts / $perPage ) : 0 );
        }

        if ( $__posts ) {
            foreach ( $__posts as $post ) {
                $postID = $post->id;

                $posts[ $postID ] = [
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'language_id' => $post->language->id,
                    'language_name' => $post->language->name,
                    'view_link' => cp_get_post_view_url( $post ),
                    'edit_link' => route( 'admin.post.edit', [ 'id' => $postID ] ),
                    'delete_link' => route( 'admin.post.delete', [ 'id' => $postID ] ),

                    'user' => [
                        'name' => $post->user->display_name,
                        'edit_link' => '#', // link to edit user page
                        'view_link' => '#', // link to frontend, list all posts of this user
                    ],
                ];
            }

            $output = [
                'posts' => $posts,
                'pagination' => [
                    'num_pages' => intval( $pages ),
                    'current_page' => intval( $page ),
                    //#! Sets the number of pages that are visible before and after the current page
                    //#! Ex: <first><prev> 1 2 3 4 [5] 6 7 8 9 <next><last>
                    'num_pages_side' => config( 'blog.num_pages_side' ), // default 4
                    'show_first' => config( 'blog.show_first' ),
                    'show_last' => config( 'blog.show_last' ),
                    'show_next' => config( 'blog.show_next' ),
                    'show_prev' => config( 'blog.show_prev' ),

                    'language_id' => intval( $languageID ),
                    'post_status' => $postStatus,
                    'per_page' => intval( $perPage ),
                    'post_type' => $postType,
                ],
            ];
        }
        return $output;
    }

    public static function getMonthName( $number, $short = false )
    {
        switch ( $number ) {
            case 1:
            {
                return ( $short ? __( 'a.Jan' ) : __( 'a.January' ) );
            }
            case 2:
            {
                return ( $short ? __( 'a.Feb' ) : __( 'a.February' ) );
            }
            case 3:
            {
                return ( $short ? __( 'a.Mar' ) : __( 'a.March' ) );
            }
            case 4:
            {
                return ( $short ? __( 'a.Apr' ) : __( 'a.April' ) );
            }
            case 5:
            {
                return ( $short ? __( 'a.May' ) : __( 'a.May' ) );
            }
            case 6:
            {
                return ( $short ? __( 'a.Jun' ) : __( 'a.June' ) );
            }
            case 7:
            {
                return ( $short ? __( 'a.Jul' ) : __( 'a.July' ) );
            }
            case 8:
            {
                return ( $short ? __( 'a.Aug' ) : __( 'a.August' ) );
            }
            case 9:
            {
                return ( $short ? __( 'a.Sep' ) : __( 'a.September' ) );
            }
            case 10:
            {
                return ( $short ? __( 'a.Oct' ) : __( 'a.October' ) );
            }
            case 11:
            {
                return ( $short ? __( 'a.Nov' ) : __( 'a.November' ) );
            }
            case 12:
            {
                return ( $short ? __( 'a.Dec' ) : __( 'a.December' ) );
            }

            default:
            {
                return '';
            }
        }
    }

    public static function getPercentageChange( $oldValue, $newValue )
    {
        // (v2 - v1 / $v1 * 100)
        $newValue = ( $newValue - $oldValue );
        return ( $oldValue ? ( $newValue / $oldValue ) * 100 : 0 );
    }

    public static function basename( $path )
    {
        return substr( $path, 0, strrpos( $path, '.' ) );
    }

    /**
     * Remove last item on a pipe-delimited string.
     *
     * Meant for removing the last item in a string, such as 'Role name|User role'. The original
     * string will be returned if no pipe '|' characters are found in the string.
     *
     * @param string $string A pipe-delimited string.
     * @return string Either $string or everything before the last pipe.
     */
    public static function cpRemoveLastBar( $string )
    {
        $last_bar = strrpos( $string, '|' );
        if ( false === $last_bar ) {
            return $string;
        }
        else {
            return substr( $string, 0, $last_bar );
        }
    }

    /**
     * Check to see whether or not this is a unique slug for the specified post id
     * @param string $slug The slug to search for
     * @param int $excludePostID The id of the post to exclude from search
     * @return bool
     */
    public static function isUniquePostSlug( $slug, $excludePostID = 0 )
    {
        if ( !empty( $excludePostID ) ) {
            $post = Post::find( $excludePostID );
            return !Post::where( 'slug', $slug )
                ->where( 'id', '!=', $excludePostID )
                ->where( 'post_type_id', $post->post_type_id )
                ->where( 'language_id', $post->language_id )
                ->first();
        }
        //#! If this request comes from an autosaved post, check if there's another post with this slug
        $posts = Post::where( 'slug', $slug )->get()->count();
        return empty( $posts );
    }

    /**
     * @param $slug
     * @param $language_id
     * @param $post_type_id
     * @param int $excludeCatID
     * @return bool
     */
    public static function isUniqueCategorySlug( $slug, $language_id, $post_type_id, $excludeCatID = 0 )
    {
        if ( !empty( $excludeCatID ) ) {
            return ( !Category::where( 'slug', $slug )
                ->where( 'id', '!=', $excludeCatID )
                ->where( 'language_id', $language_id )
                ->where( 'post_type_id', $post_type_id )
                ->first()
            );
        }
        //#! If this request comes from an autosaved post, check if the's an other post with this slug
        $results = Category::where( 'slug', $slug )
            ->where( 'language_id', $language_id )
            ->where( 'post_type_id', $post_type_id )
            ->get()->count();
        return empty( $results );
    }

    /**
     * @param $slug
     * @param $language_id
     * @param $post_type_id
     * @param int $excludeTagID
     * @return bool
     */
    public static function isUniqueTagSlug( $slug, $language_id, $post_type_id, $excludeTagID = 0 )
    {
        if ( !empty( $excludeTagID ) ) {
            return ( !Tag::where( 'slug', $slug )
                ->where( 'id', '!=', $excludeTagID )
                ->where( 'language_id', $language_id )
                ->where( 'post_type_id', $post_type_id )
                ->first()
            );
        }
        //#! If this request comes from an autosaved post, check if the's an other post with this slug
        $results = Tag::where( 'slug', $slug )
            ->where( 'language_id', $language_id )
            ->where( 'post_type_id', $post_type_id )
            ->get()->count();
        return empty( $results );
    }

    /**
     * Scan the Widgets directory and retrieve all widgets
     * @return array
     */
    public static function getAvailableWidgets()
    {
        $dirPath = app_path( 'Widgets' );
        $files = File::files( $dirPath );
        $widgets = [];
        foreach ( $files as $file ) {
            /***@var SplFileInfo $file */
            $fn = $file->getBasename( '.php' );
            if ( false === stripos( $fn, 'Abstract' ) ) {
                $c = "App\\Widgets\\{$fn}";
                array_push( $widgets, new $c );
            }
        }
        return $widgets;
    }

    /**
     * Get the appropriate url for the given $post
     * @param Post $post
     * @return string
     */
    public static function getPostViewUrl( Post $post )
    {
        if ( 'post' == $post->post_type->name ) {
            return route( 'app.post.view', [ 'slug' => $post->slug ] );
        }
        elseif ( 'page' == $post->post_type->name ) {
            return route( 'app.page.view', [ 'slug' => $post->slug ] );
        }
        return route( "app.{$post->post_type->name}.view", [ 'slug' => $post->slug ] );
    }

    public static function cp_get_menu_item_types()
    {
        return MenuItemType::all();
    }

    public static function cp_insert_menu_item_post_type( $name )
    {
        return MenuItemType::create( [
            'name' => $name,
            'slug' => Str::slug( $name ),
        ] );
    }

    public static function cp_update_menu_item_post_type( $oldName, $newName )
    {
        $entry = MenuItemType::where( 'name', $oldName )->first();
        if ( $entry ) {
            $entry->name = $newName;
            $entry->slug = Str::slug( $newName );
            return $entry->update();
        }
        return false;
    }

    public static function cp_delete_menu_item_post_type( $name )
    {
        $entry = MenuItemType::where( 'name', $name )->first();
        if ( $entry ) {
            return MenuItemType::destroy( [ $entry->id ] );
        }
        return false;
    }

    /**
     * Retrieve the raw SQL query from the Eloquent Builder
     * @param Builder $eloquentBuilder
     * @return string
     */
    public static function getQuery( $eloquentBuilder )
    {
        $query = str_replace( [ '?' ], [ '\'%s\'' ], $eloquentBuilder->toSql() );
        return vsprintf( $query, $eloquentBuilder->getBindings() );
    }

    /**
     * Update the Under Maintenance state of the application
     * @param bool $yesNo
     * @return mixed
     */
    public static function setUnderMaintenance( $yesNo )
    {
        return ( new Settings() )->updateSetting( 'is_under_maintenance', $yesNo );
    }

    /**
     * Retrieve the domain from the specified url
     * @param string $url
     * @param bool|true $stripWWW
     * @return string
     */
    public static function getDomain( string $url, $stripWWW = true ): string
    {
        $domain = parse_url( $url, PHP_URL_HOST );
        if ( $stripWWW ) {
            $domain = str_ireplace( 'www.', '', $domain );
        }
        return ( is_string( $domain ) ? $domain : '' );
    }
}
