<?php

use App\Helpers\MediaHelper;
use App\Models\MediaFile;

/**
 * This image size is used in the Media screen to display images.
 * A small size is used to improve the overall page load and
 * minimize the resource consuming.
 */
cp_add_image_size( 'cp_media_thumb', [ 'w' => 150, 'h' => 150 ] );

/**
 * @param MediaFile $mediaFile Model $mediaFile
 * @param $imageSize
 * @return string
 */
function cp_image( MediaFile $mediaFile, $imageSize ): string
{
    $mh = new MediaHelper();
    $meta = $mediaFile->media_file_metas()->where( 'meta_name', 'srcset' )->first();
    if ( !$meta ) {
        return path_combine( $mh->getUploadsUrl(), $mediaFile->path );
    }
    $sizes = maybe_unserialize( $meta->meta_value );
    if ( !empty( $sizes ) ) {
        if ( is_array( $sizes ) && isset( $sizes[ $imageSize ] ) ) {
            return path_combine( $mh->getUploadsUrl(), $sizes[ $imageSize ] );
        }
    }
    return path_combine( $mh->getUploadsUrl(), $mediaFile->path );
}
