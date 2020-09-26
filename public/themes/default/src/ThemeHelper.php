<?php

namespace App\DefaultTheme;

use App\Helpers\ImageHelper;
use App\Helpers\Theme;
use App\Models\Category;
use App\Models\Post;

class ThemeHelper
{
    /**
     * @var Theme
     */
    private $themeClass;

    public function __construct()
    {
        $this->themeClass = app( 'cp.theme' );
    }

    public function getPostImageOrPlaceholder( Post $post, $sizeName = '', $imageClass = 'image-responsive', $imageAttributes = [] )
    {
        $placeholder = '<img src="' . $this->themeClass->url( 'assets/img/placeholder.png' ) . '" alt="" class="' . $imageClass . '"/>';
        if ( cp_post_has_featured_image( $post ) ) {
            $img = ImageHelper::getResponsiveImage( $post, $sizeName, $imageClass, $imageAttributes );
            if ( empty( $img ) ) {
                return $placeholder;
            }
            return $img;
        }
        return $placeholder;
    }

    public function getCategoryImageOrPlaceholder( Category $category )
    {
        if ( $imageUrl = cp_get_category_image_url( $category->id ) ) {
            return $imageUrl;
        }
        return $this->themeClass->url( 'assets/img/placeholder.png' );
    }

    /**
     * @return Theme
     */
    public function getThemeClass()
    {
        return $this->themeClass;
    }

}
