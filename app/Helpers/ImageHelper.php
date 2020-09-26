<?php

namespace App\Helpers;

use App\Models\MediaFile;
use App\Models\MediaFileMeta;
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

    public static function addImageSize( $imageSizeName, array $size = [ 'w' => null ] )
    {
        if ( $size && isset( $size[ 'w' ] ) && !empty( $size[ 'w' ] ) ) {
            self::$sizes[ "$imageSizeName" ] = $size;
        }
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

    /**
     * Create image sizes
     * @param string $imagePath The system path to the image to resize
     * @param Model $mediaFileModel
     */
    public static function resizeImage( string $imagePath, Model $mediaFileModel )
    {
        $sizes = self::getSizes();
        if ( empty( $sizes ) ) {
            return;
        }

        $mh = new MediaHelper();

        foreach ( $sizes as $imageSizeName => $size ) {
            $width = ( isset( $size[ 'w' ] ) ? $size[ 'w' ] : 0 );
            if ( empty( $width ) ) {
                continue;
            }

            $helper = new ImageResizeHelper( $imagePath, $width );
            $newImagePath = $helper->resizeImage();

            if ( empty( $newImagePath ) ) {
                logger( 'An error occurred while resizing the image: ' . $imagePath );
                continue;
            }

            //#! Add/Update meta
            $meta = $mediaFileModel->media_file_metas()->where( 'meta_name', 'srcset' )->first();
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
                    'media_file_id' => $mediaFileModel->id,
                    'language_id' => CPML::getDefaultLanguageID(),
                    'meta_name' => 'srcset',
                    'meta_value' => serialize( [ "$imageSizeName" => $mh->getBaseUploadPath( $newImagePath ) ] ),
                ] );
            }
        }
    }

    /**
     * Retrieve the output of a responsive image element
     * @param Model $post
     * @param string $sizeName
     * @param string $imageClass
     * @param array $imageAttrs
     * @return string
     */
    public static function getResponsiveImage( Model $post, string $sizeName = '', $imageClass = '', $imageAttrs = [] )
    {
        if ( !$post ) {
            return '';
        }

        $postInfo = cp_post_get_featured_image_info( $post->id );
        if ( !isset( $postInfo[ 'id' ] ) || empty( $postInfo[ 'url' ] ) ) {
            return '';
        }

        $entry = MediaFile::where( 'id', $postInfo[ 'id' ] )->where( 'language_id', $post->language_id )->first();
        if ( !$entry ) {
            return '';
        }

        $meta = $entry->media_file_metas()->where( 'meta_name', 'srcset' )->first();
        if ( !$meta ) {
            return '';
        }

        $srcsetArray = maybe_unserialize( $meta->meta_value );

        $srcset = '';
        $imageSizes = self::getSizes();
        if ( !empty( $srcsetArray ) ) {
            $mh = new MediaHelper();
            $s = [];

            //#! Get all
            if ( empty( $sizeName ) ) {
                foreach ( $srcsetArray as $sizeName => $partialFilePath ) {
                    if ( isset( $imageSizes[ $sizeName ] ) && isset( $imageSizes[ $sizeName ][ 'w' ] ) ) {
                        $url = $mh->getUrl( $srcsetArray[ $sizeName ] );
                        $w = $imageSizes[ $sizeName ][ 'w' ];
                        $s[] = "{$url} {$w}w";
                    }
                }
            }
            //#! Get the specified size
            elseif ( isset( $srcsetArray[ $sizeName ] ) && ( isset( $imageSizes[ $sizeName ] ) && isset( $imageSizes[ $sizeName ][ 'w' ] ) ) ) {
                $url = $mh->getUrl( $srcsetArray[ $sizeName ] );
                $w = $imageSizes[ $sizeName ][ 'w' ];
                $s[] = "{$url} {$w}w";
            }

            if ( !empty( $s ) ) {
                $srcset = implode( ', ', $s );
            }
        }

        //#! The default image
        $html = '<img src="' . esc_attr( $postInfo[ 'url' ] ) . '"';

        $attrs = array_merge( [
            'alt' => ( empty( $entry->alt ) ? $entry->slug : $entry->alt ),
            'title' => $entry->title,
            'class' => $imageClass,
        ], $imageAttrs, [ 'srcset' => $srcset ] );

        foreach ( $attrs as $k => $v ) {
            $html .= ' ' . $k . '="' . esc_attr( $v ) . '"';
        }
        $html .= '/>';

        return $html;
    }

}
