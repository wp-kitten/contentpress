<?php

namespace App\Http\Controllers;

use App\Helpers\Cache;
use App\Newspaper\NewspaperHelper;
use App\Post;
use App\PostStatus;
use App\PostType;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Class NewspaperAjaxController
 * @package App\Http\controllers\Admin
 */
class NewspaperAjaxController extends Controller
{
    // the main entry point
    /**
     * The main access point. All actions must be in the format: "action_callback"; ex: action_create_post_draft
     * @param Request $request
     * @return array|mixed
     */
    public function index( Request $request )
    {
        $action = $request->get( 'action' );

        if ( empty( $action ) ) {
            return $this->responseError( __( 'np::m.Action is missing.' ) );
        }

        //#! Check to see whether or not this is an external function
        $callback = "cb_ajax_{$action}";
        if ( is_callable( $callback ) ) {
            return call_user_func( $callback, $this );
        }

        //#! Check to see whether or not this is an internal function
        $callback = "action_{$action}";
        if ( is_callable( [ $this, $callback ] ) ) {
            return call_user_func( [ $this, $callback ], $request );
        }

        return $this->responseError( __( 'np::m.Invalid action. Method not found.' ) );
    }

    /**
     * Infinite scroll for blog page
     * @param Request $request
     * @return array
     */
    private function action_get_blog_entries( Request $request )
    {
        $excludeIDS = $request->get( 'exclude', [] );
        $page = $request->get( 'page', 0 );
        $perPage = 30;

        /**@var Cache $cacheClass */
        $cacheClass = app( 'cp.cache' );
        $cacheKey = 'blog-pagination-' . $page . '-' . md5( serialize( $excludeIDS ) );

        $cache = $cacheClass->get( $cacheKey, false );

        if ( !$cache ) {
            $statusPublishID = PostStatus::where( 'name', 'publish' )->first()->id;
            $typeID = PostType::where( 'name', 'post' )->first()->id;
            $posts = Post::latest()
                ->where( 'post_status_id', $statusPublishID )
                ->where( 'post_type_id', $typeID )
                ->whereDate( 'created_at', '>', Carbon::now()->subMonth() )
                ->whereNotIn( 'id', $excludeIDS )
                ->limit( $perPage )
                ->get();

            if ( empty( $posts ) ) {
                return $this->responseSuccess();
            }

            $cache = [
                'ids' => [],
                'posts' => [],
            ];
            $newspaperHelper = new NewspaperHelper();
            foreach ( $posts as $post ) {
                array_push( $cache[ 'ids' ], $post->id );
                $cache[ 'posts' ][ $post->id ] = [
                    'image_url' => $newspaperHelper->getPostImageOrPlaceholder( $post ),
                    'post_title' => $post->title,
                    'post_url' => cp_get_permalink( $post ),
                    'category_name' => cp_cat_name( $post->firstCategory()->name ),
                    'category_url' => cp_get_category_link( $post->firstCategory() ),
                ];
            }
            $cacheClass->set( $cacheKey, $cache );
        }

        return $this->responseSuccess( [
            'ids' => array_unique( $cache[ 'ids' ] ),
            'page' => $page + 1,
            'entries' => $cache[ 'posts' ],
        ] );
    }

    //#! HELPER METHODS
    //=====================================================

    /**
     * Helper method to send a success message
     * @param mixed $data data to be sent client side
     * @return array
     */
    public function responseSuccess( $data = false )
    {
        return [ 'success' => true, 'data' => $data ];
    }

    /**
     * Helper method to send an error message
     * @param mixed $data data to be sent client side
     * @return array
     */
    public function responseError( $data = false )
    {
        return [ 'success' => false, 'data' => $data ];
    }

}
