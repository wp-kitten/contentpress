<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CPML;
use App\Helpers\ScriptsManager;
use App\Helpers\Util;
use App\Models\MediaFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MediaController extends AdminControllerBase
{
    public function index()
    {
        if ( !cp_current_user_can( 'list_media' ) ) {
            return $this->_forbidden();
        }

        ScriptsManager::enqueueStylesheet( 'admin.media-styles', asset( '_admin/css/media/index.css' ) );
        ScriptsManager::enqueueStylesheet( 'light-gallery-styles', asset( 'vendor/lightgallery/css/lightgallery.min.css' ) );

        ScriptsManager::localizeScript( 'media-script-locale', 'MediaLocale', [
            'text_image_set' => __( 'a.Image uploaded.' ),
            'text_image_removed' => __( 'a.Image removed.' ),
            'confirm_image_delete' => __( 'a.Are you sure you want to remove this image?' ),
        ] );
        ScriptsManager::enqueueFooterScript( 'light-gallery.js', asset( 'vendor/lightgallery/js/lightgallery.min.js' ) );
        ScriptsManager::enqueueFooterScript( 'media-index.js', asset( '_admin/js/media/index.js' ) );

        $model = new MediaFile();

        $perPage = 36;
        if ( $this->request->has( '_paginate' ) ) {
            $page = intval( wp_kses( $this->request->get( '_paginate' ), [] ) );
            $perPage = ( !empty( $page ) ? $page : $perPage );
        }

        return view( 'admin.media.index' )->with( [
            'files' => $model->where( 'language_id', CPML::getDefaultLanguageID() )->paginate( $perPage ),
        ] );
    }

    public function showAddView()
    {
        if ( !cp_current_user_can( 'list_media' ) ) {
            return $this->_forbidden();
        }

        ScriptsManager::enqueueStylesheet( 'media.css', asset( '_admin/css/media/index.css' ) );
        ScriptsManager::enqueueStylesheet( 'dropify.min.css', asset( 'vendor/dropify/css/dropify.min.css' ) );
        ScriptsManager::enqueueFooterScript( 'dropify.min.js', asset( 'vendor/dropify/js/dropify.min.js' ) );
        ScriptsManager::enqueueFooterScript( 'DropifyImageUploader.js', asset( '_admin/js/DropifyImageUploader.js' ) );
        ScriptsManager::localizeScript( 'media-script-locale', 'MediaLocale', [
            'text_image_set' => __( 'a.Image uploaded.' ),
            'text_image_removed' => __( 'a.Image removed.' ),
        ] );
        ScriptsManager::enqueueFooterScript( 'media-add.js', asset( '_admin/js/media/add.js' ) );

        return view( 'admin.media.add' );
    }

    public function showEditView( $id )
    {
        if ( !cp_current_user_can( 'update_media' ) ) {
            return $this->_forbidden();
        }

        ScriptsManager::enqueueStylesheet( 'media.css', asset( '_admin/css/media/index.css' ) );

        ScriptsManager::localizeScript( 'media-script-locale', 'MediaLocale', [
            'text_confirm_delete' => __( 'a.Are you sure you want to delete this file?' ),
            'text_copied' => __( 'a.The URL has been copied to clipboard.' ),
        ] );
        ScriptsManager::enqueueFooterScript( 'media-edit.js', asset( '_admin/js/media/edit.js' ) );

        return view( 'admin.media.edit' )->with( [
            'file' => MediaFile::find( $id ),
        ] );
    }

    public function showSearchView($s = '')
    {
        if ( !cp_current_user_can( 'list_media' ) ) {
            return $this->_forbidden();
        }

        $searchQuery = trim( wp_kses( $this->request->get( 's' ), [] ) );
        $results = [];

        if ( !empty( $searchQuery ) ) {
            ScriptsManager::enqueueStylesheet( 'admin.media-styles', asset( '_admin/css/media/index.css' ) );
            ScriptsManager::enqueueStylesheet( 'light-gallery-styles', asset( 'vendor/lightgallery/css/lightgallery.min.css' ) );

            ScriptsManager::localizeScript( 'media-script-locale', 'MediaLocale', [
                'text_image_set' => __( 'a.Image uploaded.' ),
                'text_image_removed' => __( 'a.Image removed.' ),
                'confirm_image_delete' => __( 'a.Are you sure you want to remove this image?' ),
            ] );
            ScriptsManager::enqueueFooterScript( 'light-gallery.js', asset( 'vendor/lightgallery/js/lightgallery.min.js' ) );
            ScriptsManager::enqueueFooterScript( 'media-index.js', asset( '_admin/js/media/index.js' ) );

            $model = new MediaFile();

            $results = $model->where( 'language_id', CPML::getDefaultLanguageID() )
                ->where( function ( $query ) use ( $searchQuery ) {
                    $query->where( 'slug', 'LIKE', '%' . $searchQuery . '%' )
                        ->orWhere( 'path', 'LIKE', "%{$searchQuery}%" )
                        ->orWhere( 'title', 'LIKE', "%{$searchQuery}%" )
                        ->orWhere( 'alt', 'LIKE', "%{$searchQuery}%" )
                        ->orWhere( 'caption', 'LIKE', "%{$searchQuery}%" );
                    return $query;
                } )
                ->paginate( $this->settings->getSetting( 'posts_per_page' ) );
        }

        return view( 'admin.media.search' )->with( [
            'files' => $results,
        ] );
    }

    public function __update( $id )
    {
        if ( !cp_current_user_can( [ 'update_media', 'delete_media' ], true ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.You are not allowed to perform this action.' ),
            ] );
        }

        $mediaFile = MediaFile::find( $id );
        if ( !$mediaFile ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.Sorry, the specified media file was not found.' ),
            ] );
        }

        $mediaFile->title = Str::title( strip_tags( $this->request->get( 'title' ) ) );
        $mediaFile->alt = strip_tags( $this->request->get( 'alt' ) );
        $mediaFile->caption = strip_tags( $this->request->get( 'caption' ) );

        if ( $mediaFile->update() ) {
            return redirect()->back()->with( 'message', [
                'class' => 'success',
                'text' => __( 'a.Media file updated.' ),
            ] );
        }

        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'a.Sorry, an error occurred.' ),
        ] );
    }

    public function __delete( $id )
    {
        if ( !cp_current_user_can( [ 'update_media', 'delete_media' ], true ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.You are not allowed to perform this action.' ),
            ] );
        }

        $mediaFile = MediaFile::find( $id );
        if ( !$mediaFile ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.Sorry, the specified media file was not found.' ),
            ] );
        }

        $filePath = $this->media->getPath( $mediaFile->path );

        $model = new MediaFile();
        $r = $model->destroy( $id );

        if ( $r ) {
            //#! Delete from file system
            File::delete( $filePath );
            return redirect()->route( 'admin.media.all' )->with( 'message', [
                'class' => 'danger',
                'text' => __( 'a.Media file deleted.' ),
            ] );
        }

        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'a.Sorry, the specified media file could not be deleted.' ),
        ] );
    }

}
