<?php

namespace App\Http\Controllers;

use App\Helpers\CPML;
use App\Post;
use App\PostStatus;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class SiteController extends Controller
{
    /**
     * Render the website's homepage.
     *
     * @return View
     */
    public function index()
    {
        return view( 'index' );
    }

    public function error404()
    {
        return view( '404' );
    }

    public function error500()
    {
        return view( '500' );
    }

    /**
     * Display a post type based on the provided slug. The App Locale will be updated accordingly to the language the post is assigned to.
     * @param string $slug
     * @return Application|Factory|RedirectResponse|Redirector|View
     */
    public function post_view( string $slug )
    {
        //#! Get the current language ID
        $defaultLanguageID = CPML::getDefaultLanguageID();
        //#! Get the selected language in frontend
        $frontendLanguageID = cp_get_frontend_user_language_id();

        //#! Make sure the post is published if the current user is not allowed to "edit_private_posts"
        $_postStatuses = PostStatus::all();
        $postStatuses = [];
        if ( cp_current_user_can( 'edit_private_posts' ) ) {
            $postStatuses = Arr::pluck( $_postStatuses, 'id' );
        }
        else {
            $postStatuses[] = PostStatus::where( 'name', 'publish' )->first()->id;
        }

        //#! Check to see if we have a match for slug & $frontendLanguageID
        $thePost = Post::where( 'slug', $slug )->where( 'language_id', $frontendLanguageID );
        $postFound = $thePost->first();
        if ( cp_is_multilingual() ) {
            //#! Check to see if we have a translation for this post
            if ( !$postFound ) {
                $posts = Post::where( 'slug', $slug )->get();
                if ( $posts ) {
                    foreach ( $posts as $post ) {
                        $translatedPostID = $post->translated_post_id;

                        //#! Default language -> other language ( EN -> RO ) //
                        if ( empty( $translatedPostID ) ) {
                            $thePost = Post::where( 'translated_post_id', $post->id )->where( 'language_id', $frontendLanguageID )->first();
                            if ( !$thePost ) {
                                return $this->_not_found();
                            }
                            return redirect( cp_get_post_view_url( $thePost ) );
                        }

                        //#! Other language -> default language ( RO -> EN ) //
                        elseif ( $frontendLanguageID == $defaultLanguageID ) {
                            $thePost = Post::where( 'id', $post->translated_post_id )->where( 'language_id', $frontendLanguageID )->first();
                            if ( !$thePost ) {
                                return $this->_not_found();
                            }
                            return redirect( cp_get_post_view_url( $thePost ) );
                        }

                        //#! other language -> other language ( ES -> RO )
                        elseif ( !empty( $translatedPostID ) ) {
                            $thePost = Post::where( 'translated_post_id', $post->translated_post_id )->where( 'language_id', $frontendLanguageID )->first();
                            if ( !$thePost ) {
                                return $this->_not_found();
                            }
                            return redirect( cp_get_post_view_url( $thePost ) );
                        }
                        else {
                            return $this->_not_found();
                        }
                    }
                }
                else {
                    return $this->_not_found();
                }
            }
        }

        $thePost = $thePost->whereIn( 'post_status_id', $postStatuses )->first();
        if ( !$thePost ) {
            return $this->_not_found();
        }

        //#! Update the frontend locale so the post can be previewed correctly
        CPML::setFrontendLanguageCode( $thePost->language->code );

        //#! Check the post type
        $postType = $thePost->post_type->name;

        $GLOBALS[ 'cp_post' ] = $thePost;

        //#! If this is a page and has specified a template
        if ( 'page' == $postType && ( $template = cp_get_post_meta( $thePost, 'template' ) ) ) {
            return view( $template )->with( [ 'page' => $thePost ] );
        }

        //#! [::1] Check to see whether or not there is a specific template for this post type
        //#! Ex: views/page.blade.php to render all post type page
        if ( view()->exists( $postType ) ) {
            return view( $postType )->with( [ 'page' => $thePost ] );
        }

        //#! [::1] Check to see whether or not there is a specific template for this post type
        //#! Ex: views/singular-article.blade.php to render all post type article
        if ( view()->exists( 'singular-' . $postType ) ) {
            return view( 'singular-' . $postType )->with( [ 'page' => $thePost ] );
        }

        //#! Return the single general template
        return view( 'singular' )->with( [
            'post' => $thePost,
            'settings' => $this->settings,
        ] );
    }

}
