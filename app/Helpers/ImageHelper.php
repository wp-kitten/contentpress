<?php

namespace App\Helpers;

use App\MediaFile;
use App\MediaFileMeta;
use App\Post;
use Illuminate\Database\Eloquent\Model;

class ImageHelper
{
    /**
     * Stores the registered image sizes
     * @var array
     */
    private static $sizes = [];

    /**
     * Bootstrap breakpoints
     * @var array
     */
    private static $breakpoints = [
        //#! max-width
        '576px',
        '767px',
        '991px',
        '1199px',
    ];

    public static function addImageSize( $imageSizeName, array $size = [ 'w' => null, 'h' => null ] )
    {
        self::$sizes[ "$imageSizeName" ] = $size;
    }

    public static function removeImageSize( $imageSizeName )
    {
        if ( isset( self::$sizes[ "$imageSizeName" ] ) ) {
            unset( self::$sizes[ "$imageSizeName" ] );
        }
    }

    /**
     * @return array
     */
    public static function getSizes(): array
    {
        return apply_filters( 'contentpress::image-sizes', self::$sizes );
    }

    /**
     * @return array
     */
    public static function getBreakpoints(): array
    {
        return apply_filters( 'contentpress::breakpoints', self::$breakpoints );
    }

    public static function resizeImage( $imagePath, Model $mediaFileModel )
    {
        $sizes = self::getSizes();
        if ( empty( $sizes ) ) {
            return;
        }

        foreach ( $sizes as $imageSizeName => $size ) {
            $width = ( isset( $size[ 'w' ] ) ? $size[ 'w' ] : 0 );
            $height = ( isset( $size[ 'h' ] ) ? $size[ 'h' ] : 0 );

            $r = self::__resizeImage( $imagePath, $mediaFileModel, $imageSizeName, $width, $height );

            if ( $r !== true ) {
                logger( 'An error occurred while resizing the image: ' . var_export( [
                        'image path' => $imagePath,
                        'error' => $r,
                    ], 1 ) );
            }
        }
    }

    /**
     * Create image sizes
     * @param string $imagePath The system path to the image
     * @param Model $mediaFileModel
     * @param string $imageSizeName
     * @param null|int|float $_width
     * @param null|int|float $_height
     * @return array|bool|string|null
     */
    public static function __resizeImage( $imagePath, Model $mediaFileModel, $imageSizeName, $_width = null, $_height = null )
    {
        if ( empty( $imagePath ) ) {
            return __( 'a.Please specify the image path.' );
        }
        if ( is_null( $_width ) && is_null( $_height ) ) {
            return __( 'a.Please specify the image width or height.' );
        }

        /* [1 - INIT & SETTINGS] */
        $source = $imagePath;
        $ext = strtolower( pathinfo( $imagePath, PATHINFO_EXTENSION ) );
        $destination = Util::basename( $imagePath );
        $max_width = $_width;
        $max_height = $_height;

        // Image quality
        // JPG files - 0 is lowest, 100 is highest
        // PNG files - 0 no compression to 9 most compression
        $quality = [ "jpg" => 100, "jpeg" => 100, "png" => 9 ];
        $allowed = [ "gif", "jpg", "jpeg", "png" ];
        $size = getimagesize( $source );

        /* [2 - FILE CHECKS] */
        // Invalid file type
        if ( !in_array( $ext, $allowed ) ) {
            return "$ext file type not allowed - $source";
        }
        // Invalid image
        if ( $size == false ) {
            return "$source is not a valid image file.";
        }

        /* [3 - RESIZE] */
        $uploadedImageWidth = $size[ 0 ];
        $uploadedImageHeight = $size[ 1 ];

        if ( empty( $max_width ) ) {
            $max_width = $uploadedImageWidth;
        }
        if ( empty( $max_height ) ) {
            $max_height = $uploadedImageHeight;
        }

        $destination .= "-{$max_width}x{$max_height}.{$ext}";

        // Resize only if source is larger than the specified sizes
        if ( $uploadedImageWidth >= $max_width || $uploadedImageHeight >= $max_height ) {
            // Landscape image
            if ( $uploadedImageWidth > $uploadedImageHeight ) {
                $new_width = $max_width;
                $new_height = ceil( $uploadedImageHeight / ( $uploadedImageWidth / $max_width ) );
            }
            // Square or portrait
            else {
                $new_height = $max_height;
                $new_width = ceil( $uploadedImageWidth / ( $uploadedImageHeight / $max_height ) );
            }

            // Create new resized image
            $fn = "imagecreatefrom" . ( $ext == "jpg" ? "jpeg" : $ext );
            $original = $fn( $source );
            $resize = imagecreatetruecolor( $new_width, $new_height );
            imagecopyresampled( $resize, $original, 0, 0, 0, 0, $new_width, $new_height, $uploadedImageWidth, $uploadedImageHeight );

            // Save resized to file
            $fn = "image" . ( $ext == "jpg" ? "jpeg" : $ext );
            if ( isset( $quality[ $ext ] ) && is_numeric( $quality[ $ext ] ) ) {
                $fn( $resize, $destination, $quality[ $ext ] );
            }
            else {
                $fn( $resize, $destination );
            }

            $mh = new MediaHelper();

            $meta = $mediaFileModel->media_file_metas()->where( 'meta_name', 'srcset' )->first();
            if ( $meta ) {
                $metaValue = maybe_unserialize( $meta->meta_value );
                if ( !is_array( $metaValue ) ) {
                    $metaValue = [];
                }
                $metaValue[ "$imageSizeName" ] = $mh->getBaseUploadPath( $destination );
                $meta->meta_value = serialize( $metaValue );
                $meta->update();
            }
            else {
                MediaFileMeta::create( [
                    'media_file_id' => $mediaFileModel->id,
                    'language_id' => CPML::getDefaultLanguageID(),
                    'meta_name' => 'srcset',
                    'meta_value' => serialize( [ "$imageSizeName" => $mh->getBaseUploadPath( $destination ) ] ),
                ] );
            }

            imagedestroy( $original );
            imagedestroy( $resize );
        }
        return true;
    }

    /**
     * Retrieve the post's featured image (with srcset)
     * @param Post $post
     * @param string $pictureClass
     * @param string $imageClass
     * @param array $imageAttrs The list of attributes to set for the image tag
     * @param string $afterImage Content to be injected after the image tag
     * @return string
     */
    public static function getImageSrcset( $post, $pictureClass = '', $imageClass = '', $imageAttrs = [], $afterImage = '' )
    {
        if ( !$post ) {
            return '';
        }
        $postInfo = cp_post_get_featured_image_info( $post->id );
        if ( !isset( $postInfo[ 'url' ] ) || empty( $postInfo[ 'url' ] ) ) {
            return '';
        }

        $html = '<figure class="' . esc_attr( $pictureClass ) . '">';

        $imageSizes = self::getSizes();

        $entry = MediaFile::where( 'id', $postInfo[ 'id' ] )->where( 'language_id', CPML::getDefaultLanguageID() )->first();
        if ( !$entry ) {
            return '';
        }

        $meta = $entry->media_file_metas()->where( 'meta_name', 'srcset' )->first();
        if ( !$meta ) {
            return '';
        }
        $srcsetArray = maybe_unserialize( $meta->meta_value );

        if ( !empty( $srcsetArray ) ) {
            if ( !empty( $imageSizes ) ) {
                $mh = new MediaHelper();
                foreach ( $imageSizes as $sizeName => $info ) {
                    $w = ( isset( $info[ 'w' ] ) ? $info[ 'w' ] : 0 );
                    if ( isset( $srcsetArray[ $sizeName ] ) ) {
                        $html .= '<source media="(max-width: ' . esc_attr( $w ) . 'px)"';
                        $url = $mh->getUploadsUrl() . $srcsetArray[ $sizeName ];
                        $html .= 'srcset="' . esc_attr( $url ) . '">';
                    }
                }
            }
        }

        //#! The default image
        $html .= '<img src="' . esc_attr( $postInfo[ 'url' ] ) . '"';

        $attrs = array_merge( [
            'alt' => $entry->alt,
            'title' => $entry->title,
            'class' => $imageClass,
        ], $imageAttrs );

        foreach ( $attrs as $k => $v ) {
            $html .= ' ' . $k . '="' . esc_attr( $v ) . '"';
        }
        $html .= '/>';

        $html .= $afterImage;

        $html .= '</figure>';
        return $html;
    }
}
