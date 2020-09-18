<?php

namespace App\Helpers;

/**
 * Class ImageResizeHelper
 * @package App\Helpers
 */
class ImageResizeHelper
{
    private $width;
    private $height;
    private $newWidth;
    private $imageResized;
    private $imageType;
    private $sourceImageFilePath;
    private $saveDirPath;
    private $saveImagePath;

    public function __construct( $filePath, $width )
    {
        $this->sourceImageFilePath = $filePath;
        $this->newWidth = $width;
        $this->saveDirPath = dirname( $filePath );
        $this->saveImagePath = $this->__setNewImagePath( $width );

        $ext = strtolower( pathinfo( $filePath, PATHINFO_EXTENSION ) );
        if ( in_array( $ext, [ 'jpg', 'jpeg' ] ) ) {
            $this->imageType = IMAGETYPE_JPEG;
        }
        elseif ( 'gif' == $ext ) {
            $this->imageType = IMAGETYPE_GIF;
        }
        elseif ( 'png' == $ext ) {
            $this->imageType = IMAGETYPE_PNG;
        }

        switch ( $this->imageType ) {
            case IMAGETYPE_JPEG:
                $this->saveImagePath .= '.jpg';
                break;

            case IMAGETYPE_GIF:
                $this->saveImagePath .= '.gif';
                break;

            case IMAGETYPE_PNG:
                $this->saveImagePath .= '.png';
                break;

            default:
                // *** Not recognised
                break;
        }
    }

    /**
     * Resize image & save it to the specified directory.
     * @return string
     */
    public function resizeImage(): string
    {
        $image = $this->__openImage( $this->sourceImageFilePath );

        // *** Get width and height
        if ( $image ) {
            $this->width = imagesx( $image );
            $this->height = imagesy( $image );
        }

        if ( empty( $this->height ) ) {
            return '';
        }

        $ratio = $this->height / $this->width;
        $newHeight = $this->newWidth * $ratio;

        // *** Resample - create image canvas of x, y size
        $this->imageResized = imagecreatetruecolor( $this->newWidth, $newHeight );

        // Preserve transparency
        if ( $this->imageType == ( IMAGETYPE_PNG || IMAGETYPE_GIF ) ) {
            $transparent_index = imagecolortransparent( $image );
            if ( $transparent_index >= 0 ) {  // GIF
                imagepalettecopy( $image, $this->imageResized );
                imagefill( $this->imageResized, 0, 0, $transparent_index );
                imagecolortransparent( $this->imageResized, $transparent_index );
                imagetruecolortopalette( $this->imageResized, true, 256 );
            }
            else {
                // PNG
                imagealphablending( $this->imageResized, false );
                imagesavealpha( $this->imageResized, true );
                $transparent = imagecolorallocatealpha( $this->imageResized, 255, 255, 255, 127 );
                imagefilledrectangle( $this->imageResized, 0, 0, $this->width, $this->height, $transparent );
            }
        }

        imagecopyresampled( $this->imageResized, $image, 0, 0, 0, 0, $this->newWidth, $newHeight, $this->width, $this->height );

        $this->saveImage();
        imagedestroy( $this->imageResized );
        return $this->saveImagePath;
    }

    protected function saveImage(): string
    {
        switch ( $this->imageType ) {
            case IMAGETYPE_JPEG:
                if ( imagetypes() & IMG_JPG ) {
                    imagejpeg( $this->imageResized, $this->saveImagePath, 100 );
                }
                break;

            case IMAGETYPE_GIF:
                if ( imagetypes() & IMG_GIF ) {
                    imagegif( $this->imageResized, $this->saveImagePath );
                }
                break;

            case IMAGETYPE_PNG:
                if ( imagetypes() & IMG_PNG ) {
                    imagepng( $this->imageResized, $this->saveImagePath, 0 );
                }
                break;

            default:
                // *** Not recognised
                break;
        }
        return $this->saveImagePath;
    }

    private function __openImage( $file )
    {
        switch ( $this->imageType ) {
            case IMAGETYPE_JPEG:
                $img = @imagecreatefromjpeg( $file );
                break;
            case IMAGETYPE_GIF:
                $img = @imagecreatefromgif( $file );
                break;
            case IMAGETYPE_PNG:
                $img = @imagecreatefrompng( $file );
                break;
            default:
                $img = false;
                break;
        }
        return $img;
    }

    private function __setNewImagePath( $width ): string
    {
        $ext = strtolower( pathinfo( $this->sourceImageFilePath, PATHINFO_EXTENSION ) );
        return trailingslashit( $this->saveDirPath ) . basename( $this->sourceImageFilePath, '.' . $ext ) . '_x' . $width;
    }

}
