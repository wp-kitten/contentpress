<?php

use App\Helpers\ScriptsManager;
use App\Http\Controllers\Admin\AjaxController;
use App\Models\Post;
use App\Models\PostMeta;

add_action( 'contentpress/plugin/activated', function ( $pluginDirName, $pluginInfo ) {
//    logger( 'Plugin '.$pluginInfo->name.' activated!' );
}, 10, 2 );

add_action( 'contentpress/plugin/deactivated', function ( $pluginDirName, $pluginInfo ) {
//    logger( 'Plugin '.$pluginInfo->name.' deactivated!' );
}, 10, 2 );

//#! Remove actions registered by the App
remove_action( 'contentpress/enqueue_text_editor', 'cp_enqueue_text_editor_scripts' );
remove_action( 'contentpress/post_editor_content/before', 'contentPressTextEditorBefore' );
remove_action( 'contentpress/post_editor_content/after', 'contentPressTextEditorAfter' );

//#! Add Text editor actions
/*
 * @param int $postID
 * @param string $screen
 * @param $mainPostID
 * @param $languageID
 */
add_action( 'contentpress/enqueue_text_editor', 'tinymce_plugin_enqueueTextEditor', 40, 4 );
function tinymce_plugin_enqueueTextEditor( $postID = 0, $screen = '', $mainPostID = 0, $languageID = 0 )
{
    if ( empty( $postID ) ) {
        return;
    }

    $post = Post::find( $postID );
    $pluginDirName = basename( dirname( __FILE__ ) );

    ScriptsManager::localizeScript( 'posts-script-locale', 'PostsLocale', [
        'post_id' => $postID,
        'text_image_set' => __( 'cpte::m.Image set' ),
        'text_image_removed' => __( 'cpte::m.Image removed' ),
        'text_description' => __( 'cpte::m.Short description here...' ),
        'language_id' => ( empty( $languageID ) ? $post->post_type->language_id : $languageID ),
        'post_type_id' => $post->post_type->id,

        'editor_styles' => apply_filters( 'contentpress/texteditor/editor-styles', [
            'https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap',
            cp_plugin_url( $pluginDirName, 'assets/css/text-editor-styles.css' ),
        ] ),

        //#! Screen: translate
        'parent_post_id' => $mainPostID,
        'current_post_id' => $postID,
    ] );

    ScriptsManager::enqueueHeadScript( 'tinymce-js', cp_plugin_url( $pluginDirName, 'assets/tinymce/tinymce.min.js' ) );

    //#! Load the scripts to customize the text editor
    if ( 'post-new' == $screen ) {
        ScriptsManager::enqueueFooterScript( 'posts-create.js', asset( '_admin/js/posts/create.js' ) );
    }
    elseif ( 'post-edit' == $screen ) {
        ScriptsManager::enqueueFooterScript( 'posts-edit.js', asset( '_admin/js/posts/edit.js' ) );
    }
    elseif ( 'post-translate' == $screen ) {
        ScriptsManager::enqueueFooterScript( 'posts-translate.js', asset( '_admin/js/posts/translate.js' ) );
    }
    ScriptsManager::enqueueFooterScript( 'tinymce-init.js', cp_plugin_url( $pluginDirName, 'assets/js/editor.js' ) );
}

add_filter( 'contentpress/the_post_editor_content', 'tinymce_plugin_textEditorContent', 20, 1 );
function tinymce_plugin_textEditorContent( $postContent = '' )
{
    return trim( $postContent );
}

/**
 * Injects the markup before the post content
 * @hooked contentPressTextEditorBefore()
 */
add_action( 'contentpress/post_editor_content/before', 'tinymce_plugin_textEditorBefore' );

/**
 * Injects the markup after the post content
 * @hooked contentPressTextEditorAfter()
 */
add_action( 'contentpress/post_editor_content/after', 'tinymce_plugin_textEditorAfter' );

/**
 * Injects the markup before the post content
 */
function tinymce_plugin_textEditorBefore()
{
    echo '<textarea class="admin-text-editor" id="tinymce_text_editor" name="post_content">';
}

/**
 * Injects the markup after the post content
 */
function tinymce_plugin_textEditorAfter()
{
    echo '</textarea>';
}

/**
 * Helper method to save the images added to the text editor locally instead of inline as data-image
 * @param AjaxController $ajaxControllerClass
 * @return array
 */
function cb_ajax_tinymce_save_editor_image( AjaxController $ajaxControllerClass )
{
    if ( !cp_current_user_can( 'upload_files' ) ) {
        return $ajaxControllerClass->responseError( __( 'cpte::m.You are not allowed to perform this action.' ) );
    }

    $request = $ajaxControllerClass->getRequest();

    $post_id = $request->get( 'post_id' );

    $currentPost = Post::find( $post_id );
    if ( !$currentPost ) {
        return $ajaxControllerClass->responseError( __( 'cpte::m.Current post not found.' ) );
    }
    if ( !$request->has( 'editor_image' ) ) {
        return $ajaxControllerClass->responseError( __( 'cpte::m.Request not valid.' ) );
    }
    if ( !$request->editor_image->isValid() ) {
        return $ajaxControllerClass->responseError( __( 'cpte::m.Error uploading the file.' ) );
    }

    $y = date( 'Y' );
    $m = date( 'n' );
    $uploadsDir = 'uploads';
    $uploadFileName = $request->editor_image->getClientOriginalName();
    $fn = "{$y}/{$m}/" . md5( $uploadFileName . time() ) . '.' . $request->editor_image->extension();
    $saveDir = public_path( "{$uploadsDir}/{$y}/{$m}" );

    //#! Update meta if exists
    $postMeta = PostMeta::where( 'post_id', $post_id )
        ->where( 'language_id', $currentPost->language_id )
        ->where( 'meta_name', '_post_attachments' )
        ->first();
    if ( !$postMeta ) {
        PostMeta::create( [
            'post_id' => $post_id,
            'language_id' => $currentPost->language_id,
            'meta_name' => '_post_attachments',
            'meta_value' => maybe_serialize( [ $fn ] ),
        ] );
    }
    else {
        $attachments = maybe_unserialize( $postMeta->meta_value );
        //#! skip adding if present
        if ( $attachments && !in_array( $fn, $attachments ) ) {
            array_push( $attachments, $fn );
        }
        $postMeta->meta_value = maybe_serialize( $attachments );
        $postMeta->update();
    }

    $request->editor_image->move( $saveDir, $fn );

    //#! Resize image
//    $p = path_combine($saveDir, $fn);
//    ImageHelper::resizeImage($p);

    return $ajaxControllerClass->responseSuccess( [
        'image_name' => $uploadFileName,
        'image_url' => asset( "{$uploadsDir}/{$fn}" ),
    ] );
}
