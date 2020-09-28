<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Feed;
use App\Helpers\CPML;
use App\Newspaper\NewspaperUserFeeds;
use App\Models\Post;
use App\Models\PostStatus;
use App\Models\PostType;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class NewspaperThemeController extends SiteController
{
    /**
     * Render the website's homepage.
     *
     * @return View
     */
    public function index()
    {
        return view( 'index' )->with( [
            'options' => NewspaperAdminController::getThemeOptions(),
        ] );
    }

    public function maintenance()
    {
        if ( cp_is_under_maintenance() ) {
            return view( 'maintenance' );
        }
        return view( '404' );
    }

    /**
     * Frontend. Switch the current language
     * @param string $code Language code
     * @return RedirectResponse
     */
    public function lang( $code )
    {
        //#! Ensure this is a valid language code
        CPML::setFrontendLanguageCode( $code );

        return redirect()->back();
    }

    /**
     * Render the category page
     * @param string $slug
     * @return Application|\Illuminate\Contracts\View\Factory|RedirectResponse|\Illuminate\Routing\Redirector|View
     */
    public function category( string $slug )
    {
        //#! Get the current language ID
        $defaultLanguageID = CPML::getDefaultLanguageID();
        //#! Get the selected language in frontend
        $frontendLanguageID = cp_get_frontend_user_language_id();

        $category = Category::where( 'slug', $slug )->where( 'language_id', $frontendLanguageID )->first();

        //#! Redirect to the translated category if exists
        if ( !$category ) {
            $categories = Category::where( 'slug', $slug )->get();
            if ( $categories ) {
                foreach ( $categories as $_category ) {
                    $translatedCatID = $_category->translated_category_id;

                    //#! Default language -> other language ( EN -> RO ) //
                    if ( empty( $translatedCatID ) ) {
                        $category = Category::where( 'translated_category_id', $_category->id )->where( 'language_id', $frontendLanguageID )->first();
                        if ( !$category ) {
                            return $this->_not_found();
                        }
                        return redirect( cp_get_category_link( $category ) );
                    }

                    //#! Other language -> default language ( RO -> EN ) //
                    elseif ( $frontendLanguageID == $defaultLanguageID ) {
                        $category = Category::where( 'id', $_category->translated_category_id )->where( 'language_id', $frontendLanguageID )->first();
                        if ( !$category ) {
                            return $this->_not_found();
                        }
                        return redirect( cp_get_category_link( $category ) );
                    }

                    //#! other language -> other language ( ES -> RO )
                    $category = Category::where( 'translated_category_id', $_category->translated_category_id )->where( 'language_id', $frontendLanguageID )->first();
                    if ( !$category ) {
                        return $this->_not_found();
                    }
                    return redirect( cp_get_category_link( $category ) );
                }
            }
            else {
                return $this->_not_found();
            }
        }

        $cacheName = "blog:children-categories:{$category->id}:{$frontendLanguageID}";

        $childrenCategories = $this->cache->get( $cacheName );
        if ( !$childrenCategories ) {
            $childrenCategories = $category->childrenCategories()->where( 'language_id', $frontendLanguageID )->latest()->get();
            $this->cache->set( $cacheName, $childrenCategories );
        }

        //#! Specific template
        $view = $category->post_type()->first()->name . '-category';
        if ( view()->exists( $view ) ) {
            return view( $view )->with( [
                'category' => $category,
                'subcategories' => $childrenCategories,
                'posts' => $category->posts()->latest()->paginate( $this->settings->getSetting( 'posts_per_page' ) ),
            ] );
        }

        //#! General template
        return view( 'category' )->with( [
            'category' => $category,
            'subcategories' => $childrenCategories,
            'posts' => $category->posts()->latest()->paginate( $this->settings->getSetting( 'posts_per_page' ) ),
        ] );
    }

    public function tag( $slug )
    {
        //#! Get the current language ID
        $defaultLanguageID = CPML::getDefaultLanguageID();
        //#! Get the selected language in frontend
        $frontendLanguageID = cp_get_frontend_user_language_id();

        $tag = Tag::where( 'slug', $slug )->where( 'language_id', $frontendLanguageID )->first();

        //#! Redirect to the translated tag if exists
        if ( !$tag ) {
            $tags = Tag::where( 'slug', $slug )->get();
            if ( $tags ) {
                foreach ( $tags as $_tag ) {
                    $translatedTagID = $_tag->translated_tag_id;

                    //#! Default language -> other language ( EN -> RO ) //
                    if ( empty( $translatedTagID ) ) {
                        $tag = Tag::where( 'translated_tag_id', $_tag->id )->where( 'language_id', $frontendLanguageID )->first();
                        if ( !$tag ) {
                            return $this->_not_found();
                        }
                        return redirect( cp_get_tag_link( $tag ) );
                    }

                    //#! Other language -> default language ( RO -> EN ) //
                    elseif ( $frontendLanguageID == $defaultLanguageID ) {
                        $tag = Tag::where( 'id', $_tag->translated_tag_id )->where( 'language_id', $frontendLanguageID )->first();
                        if ( !$tag ) {
                            return $this->_not_found();
                        }
                        return redirect( cp_get_tag_link( $tag ) );
                    }

                    //#! other language -> other language ( ES -> RO )
                    $tag = Tag::where( 'translated_tag_id', $_tag->translated_tag_id )->where( 'language_id', $frontendLanguageID )->first();
                    if ( !$tag ) {
                        return $this->_not_found();
                    }
                    return redirect( cp_get_tag_link( $tag ) );
                }
            }
            else {
                return $this->_not_found();
            }
        }

        $postType = PostType::where( 'name', 'post' )->first();
        if ( !$postType ) {
            return $this->_not_found();
        }

        $postStatus = PostStatus::where( 'name', 'publish' )->first();
        if ( !$postStatus ) {
            return $this->_not_found();
        }

        //#! Make sure the post is published if the current user is not allowed to "edit_private_posts"
        $_postStatuses = PostStatus::all();
        $postStatuses = [];
        if ( cp_current_user_can( 'edit_private_posts' ) ) {
            $postStatuses = Arr::pluck( $_postStatuses, 'id' );
        }
        else {
            $postStatuses[] = PostStatus::where( 'name', 'publish' )->first()->id;
        }

        $posts = $tag->posts()
            ->where( 'language_id', $tag->language_id )
            ->where( 'post_type_id', $postType->id )
            ->whereIn( 'post_status_id', $postStatuses )
            ->latest()
            ->paginate( $this->settings->getSetting( 'posts_per_page' ) );

        //#! Specific template
        $view = $tag->post_type()->first()->name . '-tag';
        if ( view()->exists( $view ) ) {
            return view( $view )->with( [
                'tag' => $tag,
                'posts' => $posts,
            ] );
        }

        return view( 'tag' )->with( [
            'tag' => $tag,
            'posts' => $posts,
        ] );
    }

    /**
     * Dynamic method "[post_type]_tags". Displays all tags relative to the post type "post"
     * @return Application|\Illuminate\Contracts\View\Factory|View
     */
    public function post_tags()
    {
        //#! Get the selected language in frontend
        $languageID = cp_get_frontend_user_language_id();
        if ( !$languageID ) {
            //#! Get the current language ID
            $languageID = CPML::getDefaultLanguageID();
        }
        $postType = ( new PostType() )->where( 'name', 'post' )->first();

        $tags = Tag::where( 'language_id', $languageID )->where( 'post_type_id', $postType->id )->get();

        //#! Specific template
        $view = "{$postType->name}-tags";

        if ( !view()->exists( $view ) ) {
            $view = 'tags';
        }

        return view( $view )->with( [
            'tags' => $tags,
        ] );
    }

    public function search( $s = '' )
    {
        $postType = PostType::where( 'name', '!=', 'page' )->get();
        if ( !$postType ) {
            return view( 'search' )->with( [ 'posts' => [] ] );
        }

        $postTypesArray = Arr::pluck( $postType, 'id' );

        if ( empty( $s ) ) {
            $s = $this->request->get( 's' );
        }

        if ( !empty( $s ) ) {
            $s = wp_kses( $s, [] );
        }

        //#! Filters
        $order = $this->request->get( 'order' );
        if ( empty( $order ) ) {
            $order = 'desc';
        }
        else {
            $order = strtolower( wp_kses( $order, [] ) );
            $order = ( in_array( $order, [ 'asc', 'desc' ] ) ? $order : 'desc' );
        }

        $posts = Post::where( 'language_id', cp_get_frontend_user_language_id() )
            ->where( 'post_status_id', PostStatus::where( 'name', 'publish' )->first()->id )
            ->whereIn( 'post_type_id', $postTypesArray )
            ->where( function ( $query ) use ( $s ) {
                return
                    $query->where( 'title', 'LIKE', '%' . $s . '%' )
                        ->orWhere( 'content', 'LIKE', '%' . $s . '%' )
                        ->orWhere( 'excerpt', 'LIKE', '%' . $s . '%' );
            } )
            //#! Only include results from within the last month
            ->whereDate( 'created_at', '>', Carbon::now()->subMonth() )
            ->orderBy( 'id', $order );

        $numResults = $posts->count();

        $posts = $posts->paginate( 30 );

        return view( 'search' )->with( [
            'posts' => $posts,
            'numResults' => $numResults,
            'order' => $order,
        ] );
    }

    public function __submitComment( $post_id )
    {
        do_action( 'contentpress/submit_comment', $this, $post_id );
        return redirect()->back();
    }

    /*
     * Render the user's custom home if the feature is enabled
     */
    public function userCustomHome()
    {
        $userID = cp_get_current_user_id();
        $categories = NewspaperUserFeeds::getUserCategories( $userID );

        return view( 'user-custom-home/home' )->with( [
            'user_id' => $userID,
            'categories' => $categories,
        ] );
    }

    public function userCustomHomeCategoryView( $category_slug )
    {
        if ( !defined( 'NPFR_CATEGORY_PRIVATE' ) ) {
            return view( '404' );
        }
        $category = Category::where( 'slug', $category_slug )
            ->where( 'language_id', cp_get_frontend_user_language_id() )
            ->where( 'post_type_id', PostType::where( 'name', 'post' )->first()->id )
            ->first();

        if ( !$category ) {
            return view( '404' );
        }

        $parentCategory = $category->parent()->first();
        if ( !$parentCategory ) {
            return view( '404' );
        }
        if ( !in_array( $parentCategory->slug, [ NPFR_CATEGORY_PRIVATE, NPFR_CATEGORY_PUBLIC ] ) ) {
            return view( '404' );
        }

        return view( 'user-custom-home/category' )->with( [
            'category' => $category,
            'parent_category' => $parentCategory,
            'feeds' => Feed::where( 'user_id', cp_get_current_user_id() )->where( 'category_id', $category->id )->get(),
        ] );
    }

    public function userCustomHomeFeedView( string $category_slug, string $feed_hash )
    {
        $category = null;
        $posts = [];
        $feed = null;
        $feeds = Feed::where( 'hash', $feed_hash )->get();
        if ( $feeds ) {
            foreach ( $feeds as $_feed ) {
                $cat = $_feed->category()->first();
                if ( $cat && $cat->slug == $category_slug ) {
                    $feed = $_feed;
                    $category = $cat;
                    $posts = $cat->posts()->latest()->paginate( $this->settings->getSetting( 'posts_per_page' ) );
                    break;
                }
            }
        }

        return view( 'user-custom-home/feed' )->with( [
            'posts' => $posts,
            'category' => $category,
            'feed' => $feed,
        ] );
    }
}
