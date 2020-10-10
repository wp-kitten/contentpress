<?php

use App\Helpers\CPML;
use App\Helpers\ImageHelper;
use App\Helpers\ImageResizeHelper;
use App\Helpers\MediaHelper;
use App\Http\Controllers\Admin\AjaxController;
use App\Models\MediaFile;
use App\Models\MediaFileMeta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

if ( !defined( 'CPRT_PLUGIN_DIR_NAME' ) ) {
    exit;
}

/**
 * Helper method to save the images added to the text editor locally instead of inline as data-image
 * @param AjaxController $ajaxControllerClass
 * @return array
 */
function cb_ajax_cprt_regenerate_thumbnail( AjaxController $ajaxControllerClass )
{
    $request = $ajaxControllerClass->getRequest();

    //#! If there are no image sizes registered
    $newImageSizes = ImageHelper::getSizes();

    if ( empty( $newImageSizes ) ) {
        return $ajaxControllerClass->responseError( __( 'cprt::m.No registered images sizes found.' ) );
    }

    $files = $request->get( 'files' );

    if ( empty( $files ) ) {
        return $ajaxControllerClass->responseError( __( 'cprt::m.No files provided.' ) );
    }

    $mh = new MediaHelper();

    foreach ( $files as $fileID ) {
        $mediaFile = MediaFile::find( $fileID );
        if ( !$mediaFile ) {
            return $ajaxControllerClass->responseError( __( 'cprt::m.The specified file was not found.' ) );
        }
        cprt_plugin_resize_image( $mediaFile, $newImageSizes, $mh );
    }
    return $ajaxControllerClass->responseSuccess();
}

/**
 * Helper method to regenerate a media file
 * @param Model $mediaFile
 * @param array $newImageSizes
 * @param MediaHelper $mediaHelper
 */
function cprt_plugin_resize_image( Model $mediaFile, array $newImageSizes, MediaHelper $mediaHelper )
{
    //#! The base image
    $mediaFilePath = path_combine( $mediaHelper->getUploadsDir(), $mediaFile->path );

    //#! If the image doesn't exist, remove it from database
    if ( !File::isFile( $mediaFilePath ) ) {
        $mediaFile->destroy( [ $mediaFile->id ] );
    }
    //#! Delete any existent resized images
    $meta = $mediaFile->media_file_metas()->where( 'meta_name', 'srcset' )->first();

    $skip = true;
    if ( $meta ) {
        //#! Delete all generated image sizes, if any
        $sizes = maybe_unserialize( $meta->meta_value );
        if ( !empty( $sizes ) ) {
            // Check to see if the new image sizes exist, so we'll save some resources
            foreach ( $newImageSizes as $name => $p ) {
                if ( !isset( $sizes[ $name ] ) ) {
                    $skip = false;
                    break;
                }
            }

            if ( !$skip ) {
                foreach ( $sizes as $imageSizeName => $partialPath ) {
                    $filepath = path_combine( $mediaHelper->getUploadsDir(), $partialPath );
                    if ( File::isFile( $filepath ) ) {
                        File::delete( $filepath );
                    }
                }
                //#! Delete meta (we don't need it since it will be regenerated)
                $meta->destroy( [ $meta->id ] );
            }
        }
    }
    else {
        $skip = false;
    }

    //#! Generate new images & update meta
    if ( !$skip ) {
        $mh = new MediaHelper();
        foreach ( $newImageSizes as $imageSizeName => $info ) {
            $helper = new ImageResizeHelper( $mediaFilePath, $info[ 'w' ] );
            $newImagePath = $helper->resizeImage();
            if ( !empty( $newImagePath ) ) {
                //#! Add/Update meta
                $meta = $mediaFile->media_file_metas()->where( 'meta_name', 'srcset' )->first();
                if ( $meta ) {
                    $metaValue = maybe_unserialize( $meta->meta_value );
                    if ( !is_array( $metaValue ) ) {
                        $metaValue = [];
                    }
                    $metaValue[ "$imageSizeName" ] = $mh->getBaseUploadPath( $newImagePath );
                    $meta->meta_value = serialize( $metaValue );
                    $meta->update();
                }
                else {
                    MediaFileMeta::create( [
                        'media_file_id' => $mediaFile->id,
                        'language_id' => CPML::getDefaultLanguageID(),
                        'meta_name' => 'srcset',
                        'meta_value' => serialize( [ "$imageSizeName" => $mh->getBaseUploadPath( $newImagePath ) ] ),
                    ] );
                }
            }
        }
    }
}
