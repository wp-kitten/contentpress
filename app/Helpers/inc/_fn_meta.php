<?php
/*
 * This file stores all functions related to meta
 */

use App\Models\Category;
use App\Helpers\CPML;
use App\Helpers\ImageHelper;
use App\Helpers\MediaHelper;
use App\Models\MediaFile;
use App\Models\Post;
use App\Models\PostMeta;

function cp_get_category_image_url( $categoryID, $languageID = 0 )
{
    if ( empty( $languageID ) ) {
        $languageID = CPML::getDefaultLanguageID();
    }

    $info = cp_get_category_image_info( $categoryID, $languageID );
    if ( $info ) {
        return $info[ 'url' ];
    }
    return '';
}

function cp_get_category_image_info( $categoryID, $languageID = 0 )
{
    if ( empty( $languageID ) ) {
        $languageID = CPML::getDefaultLanguageID();
    }

    $category = Category::find( $categoryID );
    if ( !$category ) {
        return false;
    }
    $meta = $category->category_metas
        ->where( 'language_id', $languageID )
        ->where( 'meta_name', '_category_image' )
        ->first();
    if ( !$meta ) {
        return false;
    }

    $image = MediaFile::find( $meta->meta_value );
    if ( !$image ) {
        return false;
    }

    return [
        'id' => $image->id,
        'url' => ( new MediaHelper() )->getUrl( $image->path ),
    ];
}

function cp_post_has_featured_image( Post $post )
{
    $info = cp_post_get_featured_image_info( $post->id );
    return ( $info && !empty( $info[ 'url' ] ) );
}

/**
 * Retrieve the post's featured image (with srcset)
 * @param App\Models\Post $post
 * @param string $pictureClass
 * @param string $imageClass
 * @param array $imageAttrs The list of attributes to set for the image tag
 * @param string $afterImage Content to be injected after the image tag
 * @return string
 */
function cp_post_get_featured_image( Post $post, $pictureClass = '', $imageClass = '', $imageAttrs = [], $afterImage = '' )
{
    return ImageHelper::getImageSrcset( $post, $pictureClass, $imageClass, $imageAttrs, $afterImage );
}

function cp_post_get_featured_image_url( $postID )
{
    $info = cp_post_get_featured_image_info( $postID );

    if ( $info ) {
        return $info[ 'url' ];
    }
    return '';
}

function cp_post_get_featured_image_id( $postID )
{
    $info = cp_post_get_featured_image_info( $postID );

    if ( $info ) {
        return $info[ 'id' ];
    }
    return 0;
}

function cp_post_get_featured_image_info( $postID )
{
    $post = Post::find( $postID );
    if ( !$post ) {
        return false;
    }
    $postMeta = $post->post_metas
        ->where( 'language_id', $post->language_id )
        ->where( 'meta_name', '_post_image' )
        ->first();
    if ( !$postMeta ) {
        return false;
    }

    $image = MediaFile::find( $postMeta->meta_value );
    if ( !$image ) {
        return false;
    }
    return [
        'id' => $image->id,
        'url' => ( new MediaHelper() )->getUrl( $image->path ),
    ];
}

function cp_get_post_meta( $post, $metaName = false, $languageID = null )
{
    if ( !$post ) {
        return false;
    }
    if ( !( $post instanceof Post ) ) {
        $post = Post::find( $post );
        if ( !$post ) {
            return false;
        }
    }

    if ( empty( $languageID ) ) {
        $languageID = $post->language->id;
    }

    //#! If all meta
    if ( empty( $metaName ) ) {
        return $post->post_metas()->where( 'language_id', $languageID )->get();
    }

    $pm = PostMeta::where( 'post_id', $post->id )
        ->where( 'language_id', $languageID )
        ->where( 'meta_name', $metaName )
        ->first();

    if ( !$pm ) {
        return false;
    }
    return maybe_unserialize( $pm->meta_value );
}

/**
 * Retrieve the IMG markup for the specified image attachment
 *
 * @param string $size The registered image size
 * @param int $mediaFileID The ID of the media file to display
 * @param string $pictureClass
 * @param string $imageClass
 * @param array $imageAttrs
 * @param string $afterImage The content to display before closing the </figure> tag
 * @return string The IMG html tag in a FIGURE tag
 */
function cp_get_the_image( $size, $mediaFileID, $pictureClass = '', $imageClass = '', $imageAttrs = [], $afterImage = '' )
{
    $html = '';
    $mediaFile = MediaFile::find( $mediaFileID );
    if ( !$mediaFile ) {
        return $html;
    }
    $imageMeta = $mediaFile->media_file_metas()->where( 'meta_name', 'srcset' )->first();
    if ( $imageMeta ) {
        $mh = new MediaHelper();
        $metaInfo = maybe_unserialize( $imageMeta->meta_value );

        if ( isset( $metaInfo[ $size ] ) ) {
            $url = $mh->getUploadsUrl() . $metaInfo[ $size ];
            $html .= '<figure class="' . esc_attr( $pictureClass ) . '">';
            $html .= '<img src="' . esc_attr( $url ) . '"';

            $attrs = array_merge( [
                'alt' => $mediaFile->alt,
                'title' => $mediaFile->title,
                'class' => $imageClass,
            ], $imageAttrs );

            foreach ( $attrs as $k => $v ) {
                $html .= ' ' . $k . '="' . esc_attr( $v ) . '"';
            }
            $html .= '/>';

            $html .= $afterImage;

            $html .= '</figure>';
        }
    }
    return $html;
}

