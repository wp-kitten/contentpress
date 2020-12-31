<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\VPML;
use App\Helpers\ImageHelper;
use App\Helpers\MediaHelper;
use App\Helpers\MetaFields;
use App\Helpers\Theme;
use App\Helpers\Util;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Language;
use App\Models\MediaFile;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\MenuItemMeta;
use App\Models\MenuItemType;
use App\Models\Options;
use App\Models\Post;
use App\Models\PostMeta;
use App\Models\PostStatus;
use App\Models\PostType;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserMeta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Class AjaxController
 * @package App\Http\controllers\Admin
 */
class AjaxController extends Controller
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
            return $this->responseError( __( 'a.Action is missing.' ) );
        }

        //#! Check to see whether or not this is an external function
        $callback = "cb_ajax_{$action}";
        if ( is_callable( $callback ) ) {
            return call_user_func( $callback, $this );
        }

        //#! Check to see whether or not this is an internal function
        $callback = "action_{$action}";
        if ( is_callable( [ $this, $callback ] ) ) {
            return call_user_func( [ $this, $callback ] );
        }

        return $this->responseError( __( 'a.Invalid action. Method not found.' ) );
    }

    /**
     * Add action to verify the user is logged in
     * @implements #36
     * @return array
     */
    public function action_heartbeat()
    {
        if ( $this->current_user()->getAuthIdentifier() ) {
            app()->get( 'cp.updater' )->run();
            return $this->responseSuccess();
        }
        return $this->responseError();
    }

    private function action_get_app_info()
    {
        return $this->responseSuccess( [
            'laravel_version' => app()->version(),
            'app_version' => cp_get_app_version(),
        ] );
    }

    private function action_update_post()
    {
        if ( !cp_current_user_can( 'edit_posts' ) ) {
            return $this->responseError( __( 'a.You are not allowed to perform this action.' ) );
        }

        $post_id = intval( $this->request->get( 'post_id' ) );

        $currentPost = Post::find( $post_id );
        if ( !$currentPost ) {
            return $this->responseError( __( 'a.Current post not found.' ) );
        }

        //#! Check these as well for existence
        $post_status = sanitize_text_field( $this->request->get( 'post_status' ) );
        $post_title = esc_html( $this->request->get( 'post_title' ) );
        $post_content = $this->request->get( 'post_content' );
        $post_excerpt = $this->request->get( 'post_excerpt' );

        if ( !empty( $post_excerpt ) ) {
            $post_excerpt = wp_kses_post( $post_excerpt );
            if ( strlen( $post_excerpt ) > 190 ) {
                $post_excerpt = substr( wp_strip_all_tags( $post_excerpt ), 0, 190 );
            }
        }
        else {
            $post_excerpt = substr( wp_strip_all_tags( $post_content ), 0, 190 );
        }

        $post_categories = $this->request->get( 'post_categories' );
        $post_tags = $this->request->get( 'post_tags' );

        if ( !empty( $post_categories ) ) {
            $post_categories = array_map( 'intval', $post_categories );
        }
        if ( !empty( $post_tags ) ) {
            $post_tags = array_map( 'intval', $post_tags );
        }

        $sticky_featured = esc_html( $this->request->sticky_featured );
        $comments_enabled = intval( $this->request->comments_enabled );

        //#! Update post
        $currentPost->title = ucfirst( $post_title );

        $post_slug = Str::slug( $post_title );
        if ( !Util::isUniquePostSlug( $post_slug, $post_id ) ) {
            $post_slug = Str::slug( $post_title . '-' . time() );
        }

        $currentPost->slug = $post_slug;
        $currentPost->content = $post_content;
        $currentPost->excerpt = $post_excerpt;

        $currentPost->user_id = auth()->user()->getAuthIdentifier();
        $currentPost->post_status_id = $post_status;

        if ( $sticky_featured == 'sticky' ) {
            $currentPost->is_sticky = 1;
            $currentPost->is_featured = 0;
        }
        elseif ( $sticky_featured == 'featured' ) {
            $currentPost->is_featured = 1;
            $currentPost->is_sticky = 0;
        }
        else {
            $currentPost->is_sticky = 0;
            $currentPost->is_featured = 0;
        }

        $postUpdated = $currentPost->update();

        //#! Save post featured image
        $featuredImageID = intval( $this->request->get( '__post_image_id' ) );
        $postMeta = PostMeta::where( 'post_id', $post_id )
            ->where( 'language_id', $currentPost->language_id )
            ->where( 'meta_name', '_post_image' )
            ->first();
        if ( $postMeta ) {
            $postMeta->meta_value = $featuredImageID;
            $postMeta->update();
        }
        else {
            PostMeta::create( [
                'post_id' => $post_id,
                'language_id' => $currentPost->language_id,
                'meta_name' => '_post_image',
                'meta_value' => $featuredImageID,
            ] );
        }

        // Update categories & tags
        if ( cp_current_user_can( 'manage_taxonomies' ) ) {
            $currentPost->categories()->detach();
            $currentPost->categories()->attach( $post_categories );
            $currentPost->tags()->detach();
            $currentPost->tags()->attach( $post_tags );
        }

        //#! Update post meta
        if ( cp_current_user_can( 'manage_custom_fields' ) ) {
            if ( $meta = MetaFields::getInstance( new PostMeta(), 'post_id', $currentPost->id, '_comments_enabled', $currentPost->language_id ) ) {
                $meta->meta_value = $comments_enabled;
                $meta->update();
            }
            else {
                MetaFields::add( new PostMeta(), 'post_id', $currentPost->id, '_comments_enabled', $comments_enabled, $currentPost->language_id );
            }
        }

        if ( $postUpdated ) {
            return $this->responseSuccess( [
                'message' => __( 'a.Post saved.' ),
                'preview_url' => cp_get_post_view_url( $currentPost ),
            ] );
        }
        return $this->responseError( __( 'a.Post not saved.' ) );
    }

    private function action_update_post_type()
    {
        if ( !cp_current_user_can( 'edit_posts' ) ) {
            return $this->responseError( __( 'a.You are not allowed to perform this action.' ) );
        }

        $id = $this->request->get( 'id' );
        $name = $this->request->get( 'name' );
        $displayName = $this->request->get( 'display_name' );
        $pluralName = $this->request->get( 'plural_name' );
        $languageID = $this->request->get( 'language_id' );

        if ( empty( $name ) ) {
            return $this->responseError( __( 'a.Post type name is required.' ) );
        }
        if ( empty( $displayName ) ) {
            return $this->responseError( __( 'a.Post type display name is required.' ) );
        }
        if ( empty( $pluralName ) ) {
            return $this->responseError( __( 'a.Post type plural name is required.' ) );
        }
        if ( empty( $languageID ) ) {
            $languageID = VPML::getDefaultLanguageID();
        }

        $name = Str::lower( $name );
        //#! Make sure the name doesn't contain spaces or any other characters. One word only or at least separated by underscores
        if ( !preg_match( "/^[a-zA-Z0-9_]+$/", $name ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.The post type name is not valid. Only underscores allowed to separate names.' ),
            ] );
        }

        $postType = PostType::find( $id );

        if ( !$postType ) {
            return $this->responseError( __( 'a.Entry not found.' ) );
        }

        $language = Language::find( $id );

        if ( !$language ) {
            return $this->responseError( __( 'a.The language is not valid.' ) );
        }

        $postType->name = Str::lower( $name );
        $postType->display_name = ucfirst( $displayName );
        $postType->plural_name = ucfirst( $pluralName );
        $postType->language_id = $languageID;
        $r = $postType->save();

        //#! Update options
        $optionNames = [
            //#! request var -> option name
            'allow_categories' => "post_type_{$name}_has_categories",
            'allow_comments' => "post_type_{$name}_has_comments",
            'allow_tags' => "post_type_{$name}_has_tags",
        ];
        foreach ( $optionNames as $requestVar => $optionName ) {
            $opt = $this->options->where( 'name', $optionName )->first();
            if ( $opt ) {
                $opt->value = ( $this->request->has( $requestVar ) ? '1' : '0' );
                $opt->update();
            }
            else {
                Options::create( [
                    'name' => $optionName,
                    'value' => ( $this->request->has( $requestVar ) ? '1' : '0' ),
                ] );
            }
        }

        if ( $r ) {
            return $this->responseSuccess();
        }
        return $this->responseError( __( 'a.Post type not added.' ) );
    }

    private function action_save_post_translation()
    {
        if ( !cp_current_user_can( 'edit_posts' ) ) {
            return $this->responseError( __( 'a.You are not allowed to perform this action.' ) );
        }

        $parent_post_id = $this->request->parent_post_id;
        $current_post_id = $this->request->current_post_id;

        $parentPost = Post::find( $parent_post_id );
        if ( !$parentPost ) {
            return $this->responseError( __( 'a.Parent post not found.' ) );
        }

        $currentPost = Post::find( $current_post_id );
        if ( !$currentPost ) {
            return $this->responseError( __( 'a.Current post not found.' ) );
        }

        //#! Check these as well for existence
        $language_id = $this->request->get( 'language_id' );
        $post_status = $this->request->get( 'post_status' );
        $post_title = esc_html( $this->request->get( 'post_title' ) );
        $post_content = $this->request->get( 'post_content' );
        $post_excerpt = $this->request->get( 'post_excerpt' );
        $post_categories = $this->request->get( 'post_categories' );
        $post_tags = $this->request->get( 'post_tags' );

        //#! Update post
        $currentPost->title = ucfirst( $post_title );

        $post_slug = Str::slug( $post_title );
        if ( !Util::isUniquePostSlug( $post_slug, $current_post_id ) ) {
            $post_slug = Str::slug( $post_title . '-' . time() );
        }

        if ( !empty( $post_excerpt ) ) {
            $post_excerpt = wp_kses_post( $post_excerpt );
            if ( strlen( $post_excerpt ) > 190 ) {
                $post_excerpt = substr( wp_strip_all_tags( $post_excerpt ), 0, 190 );
            }
        }
        else {
            $post_excerpt = substr( wp_strip_all_tags( $post_content ), 0, 190 );
        }

        $currentPost->slug = $post_slug;
        $currentPost->content = $post_content;
        $currentPost->excerpt = $post_excerpt;
        $currentPost->translated_post_id = $parent_post_id;
        $currentPost->user_id = $this->current_user()->getAuthIdentifier();
        $currentPost->language_id = $language_id;
        $currentPost->post_status_id = $post_status;

        $r = $currentPost->update();

        //#! Save post featured image
        $featuredImageID = intval( $this->request->get( '__post_image_id' ) );
        $postMeta = PostMeta::where( 'post_id', $current_post_id )
            ->where( 'language_id', $currentPost->language_id )
            ->where( 'meta_name', '_post_image' )
            ->first();
        if ( $postMeta ) {
            $postMeta->meta_value = $featuredImageID;
            $postMeta->update();
        }
        else {
            PostMeta::create( [
                'post_id' => $current_post_id,
                'language_id' => $currentPost->language_id,
                'meta_name' => '_post_image',
                'meta_value' => $featuredImageID,
            ] );
        }

        //#! Loop through each categories and see if we need to add any (if any were created in the frontend)
        //@implements: #42

        // add categories & tags
        if ( cp_current_user_can( 'manage_taxonomies' ) ) {
            $currentPost->categories()->detach();
            $currentPost->categories()->attach( $post_categories );
            $currentPost->tags()->detach();
            $currentPost->tags()->attach( $post_tags );
        }

        if ( $r ) {
            // Delete the autosave post meta as well
            $pm = PostMeta::where( 'post_id', $parent_post_id )
                ->where( 'language_id', $language_id )
                ->where( 'meta_name', '_autosave_post_id' )
                ->where( 'meta_value', $current_post_id )
                ->first();
            if ( $pm ) {
                PostMeta::destroy( $pm->id );
            }

            return $this->responseSuccess( [
                'message' => __( 'a.Post saved.' ),
                'preview_url' => cp_get_post_view_url( $currentPost ),
            ] );
        }
        return $this->responseError( __( 'a.Post not saved.' ) );
    }

    private function action_update_post_title()
    {
        if ( !cp_current_user_can( 'edit_posts' ) ) {
            return $this->responseError( __( 'a.You are not allowed to perform this action.' ) );
        }

        $post_id = $this->request->get( 'post_id' );
        $post_title = esc_html( trim( $this->request->get( 'post_title' ) ) );
        $post_type = $this->request->get( 'post_type' );

        if ( empty( $post_id ) ) {
            return $this->responseError( __( 'a.The post id is missing.' ) );
        }
        if ( empty( $post_title ) ) {
            return $this->responseError( __( 'a.The post title is missing.' ) );
        }
        if ( empty( $post_type ) ) {
            return $this->responseError( __( 'a.The post type is missing.' ) );
        }

        $currentPost = Post::find( $post_id );
        if ( !$currentPost ) {
            return $this->responseError( __( 'a.Entry not found.' ) );
        }

        //#! Check these as well for existence

        //#! Update post
        $post_slug = Str::slug( $post_title );
        if ( !Util::isUniquePostSlug( $post_slug, $post_id ) ) {
            $post_slug = Str::slug( $post_title . '-' . time() );
        }

        $currentPost->title = ucfirst( $post_title );
        $currentPost->slug = $post_slug;

        $r = $currentPost->update();

        if ( $r ) {
            return $this->responseSuccess( [
                'message' => __( 'a.:post_type saved', [ 'post_type' => $post_type ] ),
                'preview_url' => cp_get_post_view_url( $currentPost ),
            ] );
        }
        return $this->responseError( __( 'a.:post_type not saved.', [ 'post_type' => $post_type ] ) );
    }

    /**
     * Create a draft post
     * Request coming from dashboard edit -> post draft widget
     * @return array
     */
    private function action_create_post_draft()
    {
        if ( !cp_current_user_can( 'edit_posts' ) ) {
            return $this->responseError( __( 'a.You are not allowed to perform this action.' ) );
        }

        $post_title = esc_html( $this->request->post_title );
        $post_content = $this->request->post_content;

        if ( empty( $post_title ) ) {
            return $this->responseError( __( 'a.The post title is required.' ) );
        }

        $post_title = ucfirst( $post_title );
        $post_slug = Str::slug( $post_title );
        if ( !Util::isUniquePostSlug( $post_slug ) ) {
            $post_slug = Str::slug( $post_title . '-' . time() );
        }

        $post_excerpt = substr( wp_strip_all_tags( $post_content ), 0, 190 );

        $r = Post::create( [
            'title' => $post_title,
            'slug' => $post_slug,
            'content' => $post_content,
            'excerpt' => $post_excerpt,
            'user_id' => $this->current_user()->getAuthIdentifier(),
            'language_id' => VPML::getDefaultLanguageID(),
            'post_type_id' => PostType::where( 'name', 'post' )->first()->id,
            'post_status_id' => PostStatus::where( 'name', 'draft' )->first()->id,
        ] );

        if ( $r->id ) {
            do_action( 'valpress/post_new', $r );
            return $this->responseSuccess( __( 'a.Post saved.' ) );
        }
        return $this->responseError( __( 'a.Post not saved.' ) );
    }

    private function action_set_user_image()
    {
        if ( !cp_current_user_can( [ 'edit_users', 'upload_files' ] ) ) {
            return $this->responseError( __( 'a.You are not allowed to perform this action.' ) );
        }

        $user_id = $this->request->user_id;
        $languageID = VPML::getDefaultLanguageID();

        $user = User::find( $user_id );
        if ( !$user ) {
            return $this->responseError( __( 'a.The specified user was not found.' ) );
        }

        if ( !$this->request->has( 'user_image' ) ) {
            return $this->responseError( __( 'a.Request not valid.' ) );
        }
        if ( !$this->request->user_image->isValid() ) {
            return $this->responseError( __( 'a.Error uploading the file.' ) );
        }

        $uploadDir = public_path( "uploads/users/{$user_id}/{$languageID}/" );

        //#! Check to see whether or not we should remove a previously set one
        $userMeta = UserMeta::where( 'user_id', $user_id )
            ->where( 'language_id', $languageID )
            ->where( 'meta_name', '_profile_image' )
            ->first();

        //#! Delete existent
        if ( $userMeta ) {
            $fileName = $userMeta->meta_value;
            $filePath = "{$uploadDir}/{$fileName}";
            if ( File::isFile( $filePath ) ) {
                @File::delete( $filePath );
            }
        }

        //
        $fn = md5( $user_id . $this->request->user_image->getClientOriginalName() . time() ) . '.' . $this->request->user_image->extension();

        $this->request->user_image->move( $uploadDir, $fn );

        //#! Set post meta

        if ( $userMeta ) {
            $userMeta->meta_value = $fn;
            $r = $userMeta->save();
        }
        else {
            $r = UserMeta::create( [
                'user_id' => $user_id,
                'meta_name' => '_profile_image',
                'meta_value' => $fn,
                'language_id' => $languageID,
            ] );
        }

        if ( $r ) {
            return $this->responseSuccess( asset( "uploads/users/{$user_id}/{$languageID}/{$fn}" ) );
        }
        return $this->responseError( __( 'a.Image not saved.' ) );
    }

    private function action_delete_user_image()
    {
        if ( !cp_current_user_can( [ 'edit_users', 'upload_files' ] ) ) {
            return $this->responseError( __( 'a.You are not allowed to perform this action.' ) );
        }

        $user_id = $this->request->get( 'user_id' );
        $languageID = VPML::getDefaultLanguageID();

        $user = User::find( $user_id );
        if ( !$user ) {
            return $this->responseError( __( 'a.User not found.' ) );
        }

        //#! Check to see whether or not we should remove a previously set one
        $userMeta = UserMeta::where( 'user_id', $user_id )
            ->where( 'language_id', $languageID )
            ->where( 'meta_name', '_profile_image' )
            ->first();

        if ( !$userMeta ) {
            return $this->responseSuccess();
        }

        //#! Delete post meta
        UserMeta::destroy( [ $userMeta->id ] );

        $filePath = public_path( "uploads/users/{$user_id}/{$languageID}/{$userMeta->meta_value}" );
        if ( File::isFile( $filePath ) ) {
            File::delete( $filePath );
        }

        return $this->responseSuccess();
    }

    private function action_create_category()
    {
        if ( !cp_current_user_can( 'manage_taxonomies' ) ) {
            return $this->responseError( __( 'a.You are not allowed to perform this action.' ) );
        }

        $name = esc_html( $this->request->get( 'name' ) );
        $language_id = $this->request->get( 'language_id' );
        $post_type_id = $this->request->get( 'post_type_id' );

        if ( empty( $name ) ) {
            return $this->responseError( __( 'a.Please provide a category name.' ) );
        }
        if ( empty( $language_id ) ) {
            return $this->responseError( __( 'a.Please provide the language id.' ) );
        }
        if ( empty( $post_type_id ) ) {
            return $this->responseError( __( 'a.Please provide a post type id.' ) );
        }

        $name = ucfirst( $name );
        $slug = Str::slug( $name );
        if ( !Util::isUniqueCategorySlug( $slug, $language_id, $post_type_id ) ) {
            $slug = Str::slug( $name . '-' . time() );
        }

        $r = Category::create( [
            'name' => $name,
            'slug' => $slug,
            'description' => '',
            'language_id' => $language_id,
            'post_type_id' => $post_type_id,
            'category_id' => null,
        ] );

        if ( $r ) {
            return $this->responseSuccess( $r->id );
        }
        return $this->responseError( __( 'a.Category could not be added.' ) );
    }

    private function action_create_tag()
    {
        if ( !cp_current_user_can( 'manage_taxonomies' ) ) {
            return $this->responseError( __( 'a.You are not allowed to perform this action.' ) );
        }

        $name = esc_html( $this->request->get( 'name' ) );
        $language_id = $this->request->get( 'language_id' );
        $post_type_id = $this->request->get( 'post_type_id' );

        if ( empty( $name ) ) {
            return $this->responseError( __( 'a.Please provide a tag name.' ) );
        }
        if ( empty( $language_id ) ) {
            return $this->responseError( __( 'a.Please provide the language id.' ) );
        }
        if ( empty( $post_type_id ) ) {
            return $this->responseError( __( 'a.Please provide a post type id.' ) );
        }

        $name = ucfirst( $name );
        $slug = Str::slug( $name );
        if ( !Util::isUniqueTagSlug( $slug, $language_id, $post_type_id ) ) {
            $slug = Str::slug( $name . '-' . time() );
        }

        $r = Tag::create( [
            'name' => $name,
            'slug' => $slug,
            'language_id' => $language_id,
            'post_type_id' => $post_type_id,
        ] );

        if ( $r ) {
            return $this->responseSuccess( $r->id );
        }
        return $this->responseError( __( 'a.Tag could not be added.' ) );
    }

    /**
     * This action should only be called from the Categories page when sorting categories
     */
    private function action_update_category_parent()
    {
        if ( !cp_current_user_can( 'manage_taxonomies' ) ) {
            return $this->responseError( __( 'a.You are not allowed to perform this action.' ) );
        }

        $category_id = $this->request->get( 'category_id' );
        $parent_cat_id = $this->request->get( 'parent_category_id' );

        if ( empty( $category_id ) ) {
            return $this->responseError( __( 'a.The category ID is missing.' ) );
        }

        $category = Category::find( $category_id );
        if ( !$category ) {
            return $this->responseError( __( 'a.The specified category could not be found.' ) );
        }

        $category->category_id = ( empty( $parent_cat_id ) ? null : $parent_cat_id );
        $category->update();
        return $this->responseSuccess( __( 'a.Changes saved.' ) );
    }

    //#! Categories
    private function action_get_category_translations()
    {
        if ( !cp_current_user_can( 'manage_taxonomies' ) ) {
            return $this->responseError( __( 'a.You are not allowed to perform this action.' ) );
        }
        $categoryID = wp_kses( $this->request->get( 'category_id' ), [] );
        if ( empty( $categoryID ) ) {
            return $this->responseError( __( 'a.The category ID is missing.' ) );
        }
        $category = Category::find( $categoryID );
        if ( !$category ) {
            return $this->responseError( __( 'a.The specified category could not be found.' ) );
        }

        //#! Load template
        return $this->responseSuccess( view( 'admin.post.partials.category-translations' )->with( [
            'enabled_languages' => VPML::getLanguages(),
            'default_language_code' => VPML::getDefaultLanguageCode(),
            'category' => $category,
            'postType' => PostType::find( $category->post_type_id ),
        ] )->toHtml() );
    }

    //#! Custom fields
    private function action_add_custom_field()
    {
        if ( !cp_current_user_can( 'manage_custom_fields' ) ) {
            return $this->responseError( __( 'a.You are not allowed to perform this action.' ) );
        }

        $cf_name = strip_tags( $this->request->cf_name );
        $cf_value = $this->request->cf_value;
        $fk_name = strip_tags( $this->request->fk_name );
        $fk_value = $this->request->fk_value;
        $model = $this->request->model;
        $languageID = intval( $this->request->language );

        if ( empty( $cf_name ) ) {
            return $this->responseError( __( 'a.Please provide a name.' ) );
        }
        if ( empty( $fk_name ) ) {
            return $this->responseError( __( 'a.Invalid request: fk name is missing.' ) );
        }
        if ( empty( $fk_value ) ) {
            return $this->responseError( __( 'a.Invalid request: fk value is missing.' ) );
        }
        if ( empty( $languageID ) ) {
            return $this->responseError( __( 'a.Invalid request: language id is missing.' ) );
        }

        if ( !class_exists( $model ) ) {
            return $this->responseError( __( 'a.The specified model was not found.' ) );
        }

        $model = new $model;

        //#! Check if the field already exists
        if ( MetaFields::get( $model, $fk_name, $fk_value, $cf_name, $languageID ) ) {
            return $this->responseError( __( 'a.A custom field with this name already exists.' ) );
        }

        $r = MetaFields::add( $model, $fk_name, $fk_value, $cf_name, $cf_value, $languageID );

        if ( !$r ) {
            return $this->responseError( __( 'a.The custom field could not be added.' ) );
        }

        return $this->responseSuccess( [
            'id' => $r->id,
            'name' => $r->meta_name,
        ] );
    }

    private function action_update_custom_field()
    {
        if ( !cp_current_user_can( 'manage_custom_fields' ) ) {
            return $this->responseError( __( 'a.You are not allowed to perform this action.' ) );
        }

        $cf_id = strip_tags( $this->request->cf_id );
        $cf_name = strip_tags( $this->request->cf_name );
        $cf_value = $this->request->cf_value;
        $model = $this->request->model;
        $languageID = intval( $this->request->language );

        if ( empty( $cf_name ) ) {
            return $this->responseError( __( 'a.Please provide a name.' ) );
        }
        if ( empty( $languageID ) ) {
            return $this->responseError( __( 'a.Invalid request: language id is missing.' ) );
        }

        if ( !class_exists( $model ) ) {
            return $this->responseError( __( 'a.The specified model was not found.' ) );
        }

        $model = new $model;

        //#! Check if exists
        if ( !MetaFields::is( $model, $cf_id ) ) {
            return $this->responseError( __( 'a.The specified custom field could not be found.' ) );
        }

        $r = MetaFields::update( $model, $cf_id, $cf_name, $cf_value, $languageID );

        if ( !$r ) {
            return $this->responseError( __( 'a.The custom field could not be updated.' ) );
        }

        return $this->responseSuccess( __( 'a.Custom field updated.' ) );
    }

    private function action_delete_custom_field()
    {
        if ( !cp_current_user_can( 'manage_custom_fields' ) ) {
            return $this->responseError( __( 'a.You are not allowed to perform this action.' ) );
        }

        $cf_id = strip_tags( $this->request->cf_id );
        $model = $this->request->model;

        if ( empty( $cf_id ) ) {
            return $this->responseError( __( 'a.Custom field id is missing.' ) );
        }

        if ( !class_exists( $model ) ) {
            return $this->responseError( __( 'a.The specified model was not found.' ) );
        }

        $model = new $model;

        //#! Check if exists
        if ( !MetaFields::is( $model, $cf_id ) ) {
            return $this->responseError( __( 'a.The specified custom field could not be found.' ) );
        }

        $r = MetaFields::delete( $model, $cf_id );

        if ( !$r ) {
            return $this->responseError( __( 'a.The custom field could not be deleted.' ) );
        }

        return $this->responseSuccess( __( 'a.Custom field deleted.' ) );
    }

    /**
     * Save the updated dashboard UI
     * @return array
     */
    private function action_update_dashboard_ui()
    {
        if ( !cp_current_user_can( 'edit_dashboard' ) ) {
            return $this->responseError( __( 'a.You are not allowed to perform this action.' ) );
        }

        $content = $this->request->dash_content;
        if ( empty( $content ) ) {
            $content = [];
        }

        //#! Get the option
        $option = $this->options->where( 'name', '_dashboard_widgets' )->first();

        if ( $option ) {
            $option->value = maybe_serialize( $content );
            $option->update();
        }
        else {
            Options::create( [
                'name' => '_dashboard_widgets',
                'value' => maybe_serialize( $content ),
            ] );
        }

        return $this->responseSuccess( __( 'a.Changes saved.' ) );
    }

    //<editor-fold desc=":: MENUS ::">

    /**
     * Internal variable storing the ID of the custom post type. Only used when saving menus.
     * @var int|null
     * @internal
     */
    private $_postTypeCustomID = null;

    /**
     * Save the menu
     * @return array
     */
    private function action_menu_save()
    {
        if ( !cp_current_user_can( 'manage_menus' ) ) {
            return $this->responseError( __( 'a.You are not allowed to perform this action.' ) );
        }

        $this->_postTypeCustomID = $menuItemType = MenuItemType::where( 'name', 'custom' )->first()->id;

        $menuID = strip_tags( $this->request->get( 'menu_id' ) );
        $menuItems = $this->request->get( 'menu_items' );

        //#! Empty menu
        $menu = Menu::find( $menuID );
        if ( !$menu ) {
            return $this->responseError( __( 'a.The menu was not found.' ) );
        }

        $this->__emptyMenu( $menuID );

        if ( !empty( $menuItems ) ) {
            $failed = 0;
            foreach ( $menuItems as $menuOrder => $menuItem ) {
                $newMenuItem = $this->__createMenuItem( $menuID, $menuItem, null, $menuOrder );
                if ( $newMenuItem ) {
                    //#! Recursive process all children if any
                    $this->__processChildren( $menuID, $newMenuItem->id, $menuItem );
                }
                else {
                    $failed++;
                }
            }

            if ( !empty( $failed ) ) {
                return $this->responseError( __( 'a.Some menu items could not be saved.' ) );
            }
        }
        return $this->responseSuccess( __( 'a.Menu updated.' ) );
    }

    private function action_save_menu_name()
    {
        if ( !cp_current_user_can( 'manage_menus' ) ) {
            return $this->responseError( __( 'a.You are not allowed to perform this action.' ) );
        }

        $menuID = wp_kses( $this->request->get( 'menu_id' ), [] );
        $menu = Menu::find( $menuID );
        if ( !$menu ) {
            return $this->responseError( __( 'a.The menu was not found.' ) );
        }

        $menuName = wp_kses( $this->request->get( 'menu_name' ), [] );
        if ( empty( $menuName ) ) {
            return $this->responseError( __( 'a.Please provide a name.' ) );
        }

        $menuSlug = Str::slug( $menuName );

        if ( $menu->where( 'slug', $menuSlug )->where( 'id', '!=', $menuID )->first() ) {
            return $this->responseError( __( 'a.A menu with the same name already exists.' ) );
        }
        $menu->slug = $menuSlug;
        $menu->name = ucfirst( $menuName );

        $updated = $menu->update();

        if ( $updated ) {
            return $this->responseSuccess( __( 'a.Menu updated.' ) );
        }
        return $this->responseError( __( 'a.The menu name could not be updated.' ) );
    }

    private function action_save_menu_options()
    {
        if ( !cp_current_user_can( 'manage_menus' ) ) {
            return $this->responseError( __( 'a.You are not allowed to perform this action.' ) );
        }

        $menuID = wp_kses( $this->request->get( 'menu_id' ), [] );
        $menu = Menu::find( $menuID );
        if ( !$menu ) {
            return $this->responseError( __( 'a.The menu was not found.' ) );
        }

        $displayAs = wp_kses( $this->request->get( 'display_as' ), [] );
        if ( !$displayAs || !in_array( $displayAs, [ 'dropdown', 'megamenu', 'basic' ] ) ) {
            return $this->responseError( __( 'a.The specified display type is not valid.' ) );
        }

        $this->options->addOption( "menu-{$menuID}-display-as", $displayAs );

        return $this->responseSuccess( __( 'a.Options saved.' ) );
    }

    /**
     * Create a menu item
     * @param int $menuID
     * @param array $menuItem
     * @param int $parentMenuItemID
     * @param int $menuOrder
     * @return MenuItem|false MenuItem instance on success, boolean false otherwise
     */
    private function __createMenuItem( $menuID, $menuItem = [], $parentMenuItemID = null, $menuOrder = 0 )
    {
        //#! If this is a child menu item
        if ( isset( $menuItem[ 0 ] ) ) {
            $menuItem = $menuItem[ 0 ];
        }

        if ( !isset( $menuItem[ 'type' ] ) ) {
            return false;
        }

        $menuItemTypeID = $this->__getMenuItemType( $menuItem );

        if ( $menuItem[ 'type' ] == 'custom' ) {
            $title = ( isset( $menuItem[ 'title' ] ) && !empty( $menuItem[ 'title' ] ) ? strip_tags( $menuItem[ 'title' ] ) : '' );
            $url = ( isset( $menuItem[ 'url' ] ) && !empty( $menuItem[ 'url' ] ) ? strip_tags( $menuItem[ 'url' ] ) : '' );

            if ( empty( $title ) || empty( $url ) ) {
                return false;
            }

            //#! Create
            $newMenuItem = MenuItem::create( [
                'menu_order' => $menuOrder,
                'ref_item_id' => null,
                'menu_id' => $menuID,
                'menu_item_id' => $parentMenuItemID,
                'menu_item_type_id' => $menuItemTypeID,
            ] );
            //#! Set meta
            if ( $newMenuItem && $newMenuItem->id ) {
                $x = MenuItemMeta::create( [
                    'menu_order' => $menuOrder,
                    'menu_item_id' => $newMenuItem->id,
                    'meta_name' => '_menu_item_data',
                    'meta_value' => serialize( [
                        'title' => $title,
                        'url' => $url,
                    ] ),
                ] );
                if ( !$x ) {
                    MenuItem::destroy( [ $newMenuItem->id ] );
                    return false;
                }
            }
        }
        else {
            //#! Post/Page/Category/Post Type custom
            $newMenuItem = MenuItem::create( [
                'menu_order' => $menuOrder,
                'ref_item_id' => intval( $menuItem[ 'id' ] ),
                'menu_id' => $menuID,
                'menu_item_id' => $parentMenuItemID,
                'menu_item_type_id' => $menuItemTypeID,
            ] );
            if ( !$newMenuItem ) {
                return false;
            }
        }
        return $newMenuItem;
    }

    /**
     * Recursively process all children for the specified menu item.
     * @param int $menuID
     * @param int $parentMenuItemID
     * @param array $menuItem
     * @return bool
     */
    private function __processChildren( $menuID, $parentMenuItemID, array $menuItem = [] )
    {
        if ( !isset( $menuItem[ 'children' ] ) || empty( $menuItem[ 'children' ] ) ) {
            return true;
        }
        $children = $menuItem[ 'children' ];
        if ( empty( $children ) ) {
            return false;
        }

        foreach ( $children as $menuOrder => $child ) {
            $childMenuItem = $this->__createMenuItem( $menuID, $child, $parentMenuItemID, $menuOrder );
            if ( false !== $childMenuItem ) {
                $this->__processChildren( $menuID, $childMenuItem->id, $child );
            }
        }
        return true;
    }

    /**
     * Clear all menu items from the specified menu
     * @param int $menuID
     */
    private function __emptyMenu( $menuID )
    {
        MenuItem::where( 'menu_id', $menuID )->delete();
    }

    /**
     * Retrieve the type of the menu item
     * @param array $menuItem
     * @return mixed Integer on success, boolean false otherwise
     */
    private function __getMenuItemType( array $menuItem = [] )
    {
        if ( !isset( $menuItem[ 'type' ] ) ) {
            return false;
        }

        $type = strip_tags( $menuItem[ 'type' ] );
        $menuItemType = MenuItemType::where( 'name', $type )->first();
        if ( !$menuItemType ) {
            return false;
        }
        return $menuItemType->id;
    }
    //</editor-fold desc=":: MENUS ::">

    //#! Media
    private function action_media_upload_image()
    {
        if ( !cp_current_user_can( [ 'add_media', 'upload_files' ], true ) ) {
            return $this->responseError( __( 'a.You are not allowed to perform this action.' ) );
        }

        if ( !$this->request->has( 'media_image' ) ) {
            return $this->responseError( __( 'a.Request not valid.' ) );
        }
        if ( !$this->request->media_image->isValid() ) {
            return $this->responseError( __( 'a.Error uploading the file.' ) );
        }

        $subdirs = date( 'Y' ) . '/' . date( 'n' );
        $fileName = Util::basename( $this->request->media_image->getClientOriginalName() ) . '.' . $this->request->media_image->extension();

        $uploadPath = path_combine( $this->media->getUploadsDir(), $subdirs );
        if ( File::exists( $uploadPath . '/' . $fileName ) ) {
            $fileName = Util::basename( $this->request->media_image->getClientOriginalName() ) . time() . '.' . $this->request->media_image->extension();
        }

        $mediaFileModel = $this->request->media_image->move( $uploadPath, $fileName );

        if ( $mediaFileModel ) {
            //#! Add entry to database
            $model = new MediaFile();
            $mediaFileModel = $model->create( [
                'slug' => Str::slug( $fileName ),
                'path' => $subdirs . '/' . $fileName,
                'language_id' => VPML::getDefaultLanguageID(),
            ] );

            if ( !$mediaFileModel ) {
                File::delete( path_combine( $uploadPath, $fileName ) );
                return $this->responseError( __( 'a.Image not uploaded.' ) );
            }

            //#! Ensure the mime type is supported
            $filePath = path_combine( untrailingslashit( $uploadPath ), $fileName );

            //!# Only jpeg, png & gif files can be resized here
            $mimeType = File::mimeType( $filePath );
            if ( $mimeType && in_array( $mimeType, [ 'image/jpeg', 'image/png', 'image/gif' ] ) ) {
                ImageHelper::resizeImage( $filePath, $mediaFileModel );
            }

            return $this->responseSuccess( [
                'path' => $filePath,
                'url' => $this->media->getUrl( $uploadPath . '/' . $fileName ),
                'id' => $mediaFileModel->id,
            ] );
        }
        return $this->responseError( __( 'a.Image not uploaded.' ) );
    }

    private function action_media_delete_image()
    {
        if ( !cp_current_user_can( 'delete_media' ) ) {
            return $this->responseError( __( 'a.You are not allowed to perform this action.' ) );
        }

        $path = $this->request->get( 'path' );
        if ( empty( $path ) ) {
            return $this->responseError( __( 'a.The file path is required.' ) );
        }
        if ( !File::exists( $path ) ) {
            return $this->responseError( __( 'a.The specified file was not found.' ) );
        }
        //#! Make sure the path is pointing to the uploads dir
        if ( !$this->media->isValidUploadsDirPath( $path ) ) {
            return $this->responseError( __( 'a.The specified file path is not valid.' ) );
        }
        $r = File::delete( $path );
        if ( $r ) {
            //#! Delete from database
            $model = new MediaFile();
            $path = ltrim( $this->media->getBaseUploadPath( $path ), '/' );
            $mf = $model->where( 'path', $path )->where( 'language_id', VPML::getDefaultLanguageID() )->first();

            if ( $mf ) {
                $this->__deleteImageSizes( $mf );
                $model->destroy( $mf->id );
            }

            return $this->responseSuccess( __( 'a.Image deleted.' ) );
        }
        return $this->responseError( __( 'a.Image not deleted.' ) );
    }

    private function action_modal_delete_image()
    {
        if ( !cp_current_user_can( 'delete_media' ) ) {
            return $this->responseError( __( 'a.You are not allowed to perform this action.' ) );
        }

        $imageID = $this->request->get( 'id' );
        if ( empty( $imageID ) ) {
            return $this->responseError( __( 'a.The file id is required.' ) );
        }

        $model = new MediaFile();
        //#!
        $image = $model->find( $imageID );
        if ( !$image ) {
            return $this->responseError( __( 'a.The specified file was not found.' ) );
        }

        $mediaHelper = new MediaHelper();
        $filePath = $mediaHelper->getPath( $image->path );

        //==========
        if ( !File::exists( $filePath ) ) {
            return $this->responseError( __( 'a.The specified file was not found.' ) );
        }
        //#! Make sure the path is pointing to the uploads dir
        if ( !$this->media->isValidUploadsDirPath( $filePath ) ) {
            return $this->responseError( __( 'a.The specified file path is not valid.' ) );
        }
        $r = File::delete( $filePath );
        if ( $r ) {
            $this->__deleteImageSizes( $image );

            //#! Delete from database
            $model->destroy( $imageID );

            return $this->responseSuccess( __( 'a.Image deleted.' ) );
        }
        return $this->responseError( __( 'a.Image not deleted.' ) );
    }

    /**
     * Helper method to delete the associated image sizes of the specified image
     * @param $mediaFileModel
     */
    private function __deleteImageSizes( $mediaFileModel )
    {
        $meta = $mediaFileModel->media_file_metas()->where( 'meta_name', 'srcset' )->first();
        if ( $meta ) {
            $metaValue = maybe_unserialize( $meta->meta_value );
            if ( !empty( $metaValue ) && is_array( $metaValue ) ) {
                $mh = new MediaHelper();
                foreach ( $metaValue as $k => $partialPath ) {
                    $filePath = $mh->getPath( $partialPath );
                    if ( File::isFile( $filePath ) ) {
                        File::delete( $filePath );
                    }
                }
            }
        }
    }

    //#! Plugins
    private function action_upload_plugin()
    {
        if ( !cp_current_user_can( [ 'install_plugins', 'upload_files' ], true ) ) {
            return $this->responseError( __( 'a.You are not allowed to perform this action.' ) );
        }

        if ( !$this->request->has( 'the_file' ) ) {
            return $this->responseError( __( 'a.Request not valid.' ) );
        }
        if ( !$this->request->the_file->isValid() ) {
            return $this->responseError( __( 'a.Error uploading the file.' ) );
        }

        //#! Setup vars
        $uploadFilePath = $this->request->the_file->getRealPath();
        $archiveName = basename( $uploadFilePath, '.zip' );

        $zip = new \ZipArchive();
        $tmpDirPath = public_path( 'uploads/tmp/' . $archiveName );
        if ( !File::isDirectory( $tmpDirPath ) ) {
            File::makeDirectory( $tmpDirPath, 0777, true );
        }

        if ( $zip->open( $uploadFilePath ) ) {
            $zip->extractTo( $tmpDirPath );
            $zip->close();

            try {
                //#! Get the directory inside the uploads/tmp/$archiveName
                $pluginTmpDirPath = path_combine( $tmpDirPath, $archiveName );

                //#! Move to the plugins directory
                $pluginDestDirPath = path_combine( $this->pluginsManager->getPluginsDir(), $archiveName );

                File::moveDirectory( $pluginTmpDirPath, $pluginDestDirPath );
                File::deleteDirectory( $tmpDirPath );

                //#! Validate the uploaded plugin
                $pluginInfo = $this->pluginsManager->getPluginInfo( $archiveName );
                if ( false === $pluginInfo ) {
                    File::deleteDirectory( $pluginDestDirPath );
                    return $this->responseError( __( 'a.The uploaded plugin is not valid.' ) );
                }

                return $this->responseSuccess( [
                    'path' => $archiveName,
                ] );
            }
            catch ( \Exception $e ) {
                return $this->responseError( $e->getMessage() );
            }
        }
        else {
            File::deleteDirectory( $tmpDirPath );
        }
        return $this->responseError( __( 'a.Error uploading the file.' ) );
    }

    private function action_delete_plugin()
    {
        if ( !cp_current_user_can( 'delete_plugins' ) ) {
            return $this->responseError( __( 'a.You are not allowed to perform this action.' ) );
        }

        if ( !$this->request->has( 'path' ) ) {
            return $this->responseError( __( 'a.Request not valid. Path is missing.' ) );
        }

        $plugin_dir_name = sanitize_file_name( $this->request->get( 'path' ) );
        if ( $this->pluginsManager->isActivePlugin( $plugin_dir_name ) ) {
            return $this->responseError( __( 'a.You cannot delete an active plugin.' ) );
        }

        $pluginDirPath = $this->pluginsManager->getPluginDirPath( $plugin_dir_name );
        if ( File::deleteDirectory( $pluginDirPath ) ) {
            return $this->responseSuccess( __( 'a.Plugin deleted.' ) );
        }
        return $this->responseError( __( 'a.The plugin directory was not found.' ) );
    }

    private function action_get_plugin_info()
    {
        if ( !cp_current_user_can( 'list_plugins' ) ) {
            return $this->responseError( __( 'a.You are not allowed to perform this action.' ) );
        }

        if ( !$this->request->has( 'plugin_name' ) ) {
            return $this->responseError( __( 'a.Request not valid.' ) );
        }

        $pluginName = sanitize_file_name( $this->request->get( 'plugin_name' ) );
        try {
            $pluginInfo = $this->pluginsManager->getPluginInfo( $pluginName );
            if ( $pluginInfo ) {
                return $this->responseSuccess( view( 'admin.plugins.partials.info' )->with( [
                    'plugin' => $pluginInfo,
                ] )->toHtml() );
            }
        }
        catch ( \Exception $e ) {
            return $this->responseError( $e->getMessage() );
        }
        return $this->responseError( __( 'a.An error occurred.' ) );
    }

    //#! Themes
    private function action_upload_theme()
    {
        if ( !cp_current_user_can( [ 'install_themes', 'upload_files' ], true ) ) {
            return $this->responseError( __( 'a.You are not allowed to perform this action.' ) );
        }

        if ( !$this->request->has( 'the_file' ) ) {
            return $this->responseError( __( 'a.Request not valid.' ) );
        }
        if ( !$this->request->the_file->isValid() ) {
            return $this->responseError( __( 'a.Error uploading the file.' ) );
        }

        //#! Setup vars
        $uploadFilePath = $this->request->the_file->getRealPath();
        $archiveName = basename( $uploadFilePath, '.zip' );

        $zip = new \ZipArchive();
        $tmpDirPath = public_path( 'uploads/tmp/' . $archiveName );
        if ( !File::isDirectory( $tmpDirPath ) ) {
            File::makeDirectory( $tmpDirPath, 0777, true );
        }

        if ( $zip->open( $uploadFilePath ) ) {
            $zip->extractTo( $tmpDirPath );
            $zip->close();

            //#! Get the directory
            $dirs = File::directories( $tmpDirPath );
            if ( empty( $dirs ) ) {
                return $this->responseError( __( 'a.The uploaded file is not valid.' ) );
            }
            $themeTmpDirPath = wp_normalize_path( $dirs[ 0 ] );
            $themeDirName = basename( $themeTmpDirPath );

            //#! Validate the uploaded theme
            $errors = $this->themesManager->checkThemeUploadDir( $themeTmpDirPath );
            if ( !empty( $errors ) ) {
                File::deleteDirectory( $tmpDirPath );
                return $this->responseError( __( 'a.The uploaded file is not a valid theme.' ) );
            }

            //#! Move to the themes directory
            $themeDestDirPath = path_combine( $this->themesManager->getThemesDirectoryPath(), $themeDirName );

            if ( File::isDirectory( $themeDestDirPath ) ) {
                return $this->responseError( __( 'a.A theme with the same name already exists.' ) );
            }

            File::moveDirectory( $themeTmpDirPath, $themeDestDirPath );
            File::deleteDirectory( $tmpDirPath );
            $this->themesManager->updateCache();

            return $this->responseSuccess( [
                'path' => $themeDirName,
            ] );
        }
        return $this->responseError( __( 'a.Error uploading the file.' ) );
    }

    private function action_get_theme_info()
    {
        if ( !cp_current_user_can( 'list_themes' ) ) {
            return $this->responseError( __( 'a.You are not allowed to perform this action.' ) );
        }

        if ( !$this->request->has( 'theme_name' ) ) {
            return $this->responseError( __( 'a.Request not valid.' ) );
        }

        $themeName = sanitize_file_name( $this->request->get( 'theme_name' ) );
        $theme = new Theme( $themeName );
        $themeInfo = $theme->getThemeData();
        if ( !empty( $themeInfo ) ) {
            return $this->responseSuccess( view( 'admin.themes.partials.info' )->with( [
                'theme' => $themeInfo,
            ] )->toHtml() );
        }
        return $this->responseError( __( 'a.An error occurred.' ) );
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
