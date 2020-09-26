<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use App\Helpers\MetaFields;
use App\Helpers\ScriptsManager;
use App\Models\Language;
use App\Models\Post;
use App\Models\PostMeta;
use App\Models\PostStatus;
use App\Models\PostType;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/*
 * Global provider for all post types
 */

class PostsController extends AdminControllerBase
{
    /**
     * Holds the reference to the model for the requested post type
     * @var null|PostType
     */
    protected $_postType = null;

    /**
     * PostsController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $routeName = cp_get_current_route_name();
        $parts = explode( '.', $routeName );
        $postType = null;
        if ( isset( $parts[ 1 ] ) ) {
            $postType = PostType::where( 'name', $parts[ 1 ] )->first();
            if ( $postType ) {
                $this->_postType = $postType;
            }
        }
    }

    public function index()
    {
        if ( !$this->_postType ) {
            return $this->_forbidden( 'The specified post type was not found' );
        }

        if ( !cp_current_user_can( [ 'administrator', 'contributor' ] ) ) {
            return $this->_forbidden();
        }

        $enabledLanguages = $this->options->getOption( 'enabled_languages', [] );

        ScriptsManager::localizeScript( 'posts-script-locale', 'PostsLocale', [
            'isMultilanguage' => count( $enabledLanguages ) > 1,
            'text_collapse' => __( 'a.Collapse' ),
            'text_expand' => __( 'a.Expand' ),
            'confirm_delete_post' => __( 'a.Are you sure you want to delete this post? All items associated with it will also be deleted.' ),
            'element_not_found' => __( 'a.The element was not found.' ),
        ] );
        ScriptsManager::enqueueFooterScript( 'posts-index.js', asset( '_admin/js/posts/index.js' ) );

        $postsQuery = Post::where( 'translated_post_id', null )
            ->where( 'post_type_id', $this->_postType->id );

        //#! Filters
        if ( $this->request->has( '_user' ) && !empty( $this->request->get( '_user' ) ) ) {
            $postsQuery->where( 'user_id', $this->request->_user );
        }
        if ( $this->request->has( '_status' ) && !empty( $this->request->get( '_status' ) ) ) {
            $postsQuery->where( 'post_status_id', $this->request->_status );
        }
        $sort = ( $this->request->has( '_sort' ) && !empty( $this->request->get( '_sort' ) ) ? $this->request->_sort : 'desc' );
        $postsQuery->orderBy( 'created_at', $sort );

        $perPage = ( $this->request->has( '_paginate' ) && !empty( $this->request->get( '_paginate' ) ) ? $this->request->_paginate : 10 );
        $posts = $postsQuery->paginate( $perPage );

        return view( 'admin.post.index' )->with( [
            'posts' => $posts,
            'num_posts' => $postsQuery->count(),
            'enabled_languages' => $enabledLanguages,
            'languages' => Language::all(),
            'default_language_code' => $this->settings->getSetting( 'default_language' ),

            //#! Special entry
            //@required
            '__post_type' => $this->_postType,
            'date_format' => $this->settings->getSetting( 'date_format', 'M j, Y' ),
        ] );
    }

    public function showCreatePage( $id = null )
    {
        if ( !$this->_postType ) {
            return $this->_forbidden( 'The specified post type was not found' );
        }

        if ( !cp_current_user_can( [ 'administrator', 'contributor' ] ) ) {
            return $this->_forbidden();
        }

        // Create the post and redirect back
        if ( empty( $id ) ) {
            $defLangID = $this->language->getID( $this->settings->getSetting( 'default_language' ) );

            //#! Otherwise create the post and redirect
            $title = __( "a.New" ) . ' ' . $this->_postType->name;
            $post = Post::create( [
                'title' => $title,
                'slug' => Str::slug( $title . '-' . time() ),
                'content' => '<p>' . __( 'a.Hello! This is a sample paragraph to get you started! You can use the integrated page builder and customize this page the way you want.' ) . '</p>',
                'excerpt' => '',
                'user_id' => $this->current_user()->getAuthIdentifier(),
                'language_id' => $defLangID,
                'post_status_id' => PostStatus::where( 'name', 'autosave' )->first()->id,
                'post_type_id' => $this->_postType->id,
            ] );

            //#! Set post meta
            MetaFields::add( new PostMeta(), 'post_id', $post->id, '_comments_enabled', 1, $defLangID );

            do_action( 'contentpress/post/new', $post );

            return redirect()->route( "admin.{$this->_postType->name}.new", [
                'id' => $post->id,
            ] );
        }

        do_action( 'contentpress/enqueue_text_editor', $id, 'post-new' );

        cp_enqueue_media_scripts();

        $post = Post::findOrFail( $id );

        MetaFields::generateProtectedMetaFields( new PostMeta(), 'post_id', $post->id, MetaFields::SECTION_POST, $post->language_id );

        return view( 'admin.post.new' )->with( [
            'post' => $post,
            'language_name' => $this->language->getNameFrom( $post->language_id ),

            'post_statuses' => PostStatus::all(),
            'default_post_status' => $this->settings->getSetting( 'default_post_status' ),
            'current_post_status' => $post->post_status->name,

            'categories' => Category::where( 'language_id', $post->language_id )->where( 'post_type_id', $this->_postType->id )->get(),

            'tags' => Tag::where( 'language_id', $post->language_id )->where( 'post_type_id', $this->_postType->id )->get(),

            'meta_fields' => MetaFields::getAll( new PostMeta(), 'post_id', $id, $post->language_id ),

            'comments_enabled' => (bool)MetaFields::get( new PostMeta(), 'post_id', $post->id, '_comments_enabled', $post->language_id, true ),

            //#! Special entry
            //@required
            '__post_type' => $this->_postType,
        ] );
    }

    public function showEditPage( $id )
    {
        if ( !$this->_postType ) {
            return $this->_forbidden( 'The specified post type was not found' );
        }

        if ( !cp_current_user_can( [ 'administrator', 'contributor' ] ) ) {
            return $this->_forbidden();
        }

        $post = Post::findOrFail( $id );

        do_action( 'contentpress/enqueue_text_editor', $id, 'post-edit' );

        cp_enqueue_media_scripts();

        $postCategories = [];
        if ( $post->categories ) {
            foreach ( $post->categories as $entry ) {
                $postCategories[ $entry->id ] = $entry->slug;
            }
        }
        $__postTags = $post->tags;
        $postTags = [];
        if ( $__postTags ) {
            foreach ( $__postTags as $entry ) {
                $postTags[ $entry->id ] = $entry->slug;
            }
        }

        MetaFields::generateProtectedMetaFields( new PostMeta(), 'post_id', $post->id, MetaFields::SECTION_POST, $post->language_id );

        return view( 'admin.post.edit' )->with( [
            'post' => $post,
            'language_name' => $this->language->getNameFrom( $post->language_id ),

            'post_statuses' => PostStatus::all(),
            'default_post_status' => $this->settings->getSetting( 'default_post_status' ),
            'current_post_status' => $post->post_status->name,

            'categories' => $post->categories,
            'post_categories' => $postCategories,

            'tags' => $post->tags,
            'post_tags' => $postTags,

            'meta_fields' => MetaFields::getAll( new PostMeta(), 'post_id', $id, $post->language_id ),

            'comments_enabled' => (bool)MetaFields::get( new PostMeta(), 'post_id', $post->id, '_comments_enabled', $post->language_id, true ),

            //#! Special entry
            //@required
            '__post_type' => $this->_postType,
        ] );
    }

    /**
     * @param int $id The ID of the post being translated
     * @param string $code The language code to translate into
     * @param string $new_post_id The ID of the post just created
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showTranslatePage( $id, $code, $new_post_id = null )
    {
        if ( !$this->_postType ) {
            return $this->_forbidden( 'The specified post type was not found' );
        }

        if ( !cp_current_user_can( [ 'administrator', 'contributor' ] ) ) {
            return $this->_forbidden();
        }

        //#! Make sure the post exists
        $mainPost = Post::findOrFail( $id );
        $newLanguageID = $this->language->getID( $code );

        //#! Create the new post if not provided, then redirect back
        if ( empty( $new_post_id ) ) {

            //#! Check if there is an autosave
            $postMetaName = "_post_autosave_translation_{$code}";
            if ( $pm = PostMeta::where( 'post_id', $id )->where( 'language_id', $newLanguageID )->where( 'meta_name', $postMetaName )->first() ) {
                return redirect()->route( "admin.{$this->_postType->name}.translate", [
                    'id' => $id,
                    'code' => $code,
                    'new_post_id' => $pm->meta_value,
                ] );
            }

            //#! Otherwise create the post and redirect
            $title = "[{$code}] " . $mainPost->title;
            $post = Post::create( [
                'title' => $title,
                'slug' => Str::slug( $title . '-' . time() ),
                'content' => '<div class="row clearfix"><div class="column full"><p>Hello! This is a sample paragraph to get you started! You can use the integrated page builder and customize this page the way you want!</p></div></div>',
                'excerpt' => '',
                'user_id' => $this->current_user()->getAuthIdentifier(),
                'language_id' => $newLanguageID,
                'post_status_id' => PostStatus::where( 'name', 'autosave' )->first()->id,
                'post_type_id' => $this->_postType->id,
                'translated_post_id' => $id,
            ] );

            do_action( 'contentpress/post/new', $post );

            //#! Create the autosave post meta
            PostMeta::create( [
                'post_id' => $post->id,
                'language_id' => $newLanguageID,
                'meta_name' => '_autosave_post_id',
                'meta_value' => $post->id,
            ] );

            //#! Create the comments_enabled post meta
            if ( !MetaFields::getInstance( new PostMeta(), 'post_id', $post->id, '_comments_enabled', $newLanguageID ) ) {
                MetaFields::add( new PostMeta(), 'post_id', $post->id, '_comments_enabled', 1, $newLanguageID );
            }

            return redirect()->route( "admin.{$this->_postType->name}.translate", [
                'id' => $id,
                'code' => $code,
                'new_post_id' => $post->id,
            ] );
        }

        // get from  meta
        $post = Post::findOrFail( $new_post_id );

        do_action( 'contentpress/enqueue_text_editor', $post->id, 'post-translate', $mainPost->id, $newLanguageID );

        cp_enqueue_media_scripts();

        $__postCategories = $post->categories;
        $postCategories = [];
        if ( $__postCategories ) {
            foreach ( $__postCategories as $entry ) {
                $postCategories[ $entry->id ] = $entry->slug;
            }
        }

        $__postTags = $post->tags;
        $postTags = [];
        if ( $__postTags ) {
            foreach ( $__postTags as $entry ) {
                $postTags[ $entry->id ] = $entry->slug;
            }
        }

        MetaFields::generateProtectedMetaFields( new PostMeta(), 'post_id', $post->id, MetaFields::SECTION_POST, $post->language_id );

        return view( 'admin.post.translate' )->with( [
            'post' => $post,
            'parent_post_id' => $mainPost->id,
            'selected_language_code' => $code,

            'post_statuses' => PostStatus::all(),
            'default_post_status' => $this->settings->getSetting( 'default_post_status' ),
            'current_post_status' => $post->post_status->name,

            'categories' => Category::where( 'language_id', $newLanguageID )->where( 'post_type_id', $this->_postType->id )->get(),
            'post_categories' => $postCategories,

            'current_language_id' => $newLanguageID,

            'tags' => Tag::where( 'language_id', $newLanguageID )->where( 'post_type_id', $this->_postType->id )->get(),
            'post_tags' => $postTags,

            'meta_fields' => MetaFields::getAll( new PostMeta(), 'post_id', $post->id, $newLanguageID ),

            'comments_enabled' => (bool)MetaFields::get( new PostMeta(), 'post_id', $post->id, '_comments_enabled', $post->language_id, true ),

            //#! Special entry
            //@required
            '__post_type' => $this->_postType,
        ] );
    }

    public function __delete( $id )
    {
        if ( !$this->_postType ) {
            return $this->_forbidden( 'The specified post type was not found' );
        }

        if ( !cp_current_user_can( [ 'administrator', 'contributor' ] ) ) {
            return $this->_forbidden();
        }

        if ( empty( $id ) || !Post::find( $id ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.The specified post was not found.' ),
            ] );
        }

        //#! Delete the featured image if any & also of all translations (if any)
        //[::1] Get translations
        $posts = Post::where( 'translated_post_id', $id )->get();
        if ( $posts ) {
            foreach ( $posts as $post ) {
                $postMeta = PostMeta::where( 'post_id', $post->id )
                    ->where( 'language_id', $post->language_id )
                    ->where( 'meta_name', '_post_image' )
                    ->first();
                if ( $postMeta ) {
                    $fileName = $postMeta->meta_value;
                    if ( !empty( $fileName ) ) {
                        $uploadDir = public_path( "uploads/posts/{$post->id}/{$post->language_id}/" );
                        $filePath = "{$uploadDir}/{$fileName}";
                        if ( File::isFile( $filePath ) ) {
                            @File::delete( $filePath );
                        }
                    }
                }
            }
        }
        //[::2] Delete the featured image of the post being deleted
        $thePost = Post::find( $id );
        $postMeta = PostMeta::where( 'post_id', $thePost->id )
            ->where( 'language_id', $thePost->language_id )
            ->where( 'meta_name', '_post_image' )
            ->first();
        if ( $postMeta ) {
            $fileName = $postMeta->meta_value;
            if ( !empty( $fileName ) ) {
                $uploadDir = public_path( "uploads/posts/{$thePost->id}/{$thePost->language_id}/" );
                $filePath = "{$uploadDir}/{$fileName}";
                if ( File::isFile( $filePath ) ) {
                    @File::delete( $filePath );
                }
            }
        }
        //=====

        $result = Post::destroy( $id );
        if ( $result ) {

            do_action( 'contentpress/post/deleted', $id );

            return redirect()->back()->with( 'message', [
                'class' => 'success',
                'text' => __( 'a.Post deleted.' ),
            ] );
        }
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'a.The specified post could not be deleted.' ),
        ] );
    }
}
