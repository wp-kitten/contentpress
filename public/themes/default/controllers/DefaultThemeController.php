<?php

namespace App\Http\Controllers;

use App\Helpers\CPML;
use App\Models\Category;
use App\Models\Post;
use App\Models\PostStatus;
use App\Models\PostType;
use App\Models\Tag;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class DefaultThemeController extends SiteController
{
    /**
     * Render the website's homepage.
     *
     * @return View
     */
    public function index()
    {
        $postsQuery = Post::where( 'post_type_id', PostType::where( 'name', 'post' )->first()->id )
            ->where( 'post_status_id', PostStatus::where( 'name', 'publish' )->first()->id );

        $s1_posts = $postsQuery->latest()->limit( 4 )->get();
        $excludeIds = $s1_posts->pluck( 'id' )->toArray();

        $s2_posts = $postsQuery->latest()->whereNotIn( 'id', $excludeIds )->limit( 5 )->get();
        $excludeIds = array_merge( $excludeIds, $s2_posts->pluck( 'id' )->toArray() );

        $s3_posts = $postsQuery->latest()->whereNotIn( 'id', $excludeIds )->limit( 5 )->get();
        $excludeIds = array_merge( $excludeIds, $s3_posts->pluck( 'id' )->toArray() );

        $s4_posts = $postsQuery->latest()->whereNotIn( 'id', $excludeIds )->limit( 3 )->get();
        $excludeIds = array_merge( $excludeIds, $s4_posts->pluck( 'id' )->toArray() );

        $s5_posts = $postsQuery->latest()->whereNotIn( 'id', $excludeIds )->limit( 6 )->get();
//        $excludeIds = array_merge($excludeIds,  $s5_posts->pluck( 'id' )->toArray());

        return view( 'index' )->with( [
            's1_posts' => $s1_posts,
            's2_posts' => $s2_posts,
            's3_posts' => $s3_posts,
            's4_posts' => $s4_posts,
            's5_posts' => $s5_posts,
        ] );
    }

    public function category( $slug )
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

    public function search()
    {
        $postType = PostType::where( 'name', '!=', 'page' )->get();

        $s = cp_get_search_query();

        if ( !$postType || empty( $s ) ) {
            return view( 'search' )->with( [
                'posts' => null,
                'numResults' => 0,
                'order' => 'desc',
            ] );
        }

        $postTypesArray = Arr::pluck( $postType, 'id' );

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
            ->whereDate( 'created_at', '>', now()->subMonth()->toDateString() )
            ->orderBy( 'id', $order );

        $numResults = $posts->count();

        $posts = $posts->paginate( $this->settings->getSetting( 'posts_per_page', 12 ) );

        return view( 'search' )->with( [
            'posts' => $posts,
            'numResults' => $numResults,
            'order' => $order,
        ] );
    }

}
