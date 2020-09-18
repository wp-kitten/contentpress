<?php

namespace App\Newspaper;

use App\Category;
use App\Helpers\CPML;
use App\Helpers\ImageHelper;
use App\Http\Controllers\NewspaperAdminController;
use App\Post;
use App\PostMeta;
use App\PostStatus;
use App\PostType;
use App\Settings;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class NewspaperHelper
{
    public function getTopCategories()
    {
        return Category::where( 'category_id', null )
            ->where( 'language_id', CPML::getDefaultLanguageID() )
            ->where( 'post_type_id', PostType::where( 'name', 'post' )->first()->id )
            ->orderBy( 'name', 'ASC' )
            ->get();
    }

    public function getPostStatusPublishID()
    {
        return PostStatus::where( 'name', 'publish' )->first()->id;
    }

    public function getPostImageOrPlaceholder( Post $post, $sizeName = '', $imageClass = 'image-responsive', $imageAttributes = [] )
    {
        $placeholder = '<img src="' . asset( 'themes/newspaper/assets/img/placeholder.png' ) . '" alt="" class="' . $imageClass . '"/>';
        if ( cp_post_has_featured_image( $post ) ) {
            $img = ImageHelper::getResponsiveImage( $post, $sizeName, $imageClass, $imageAttributes );
            if ( empty( $img ) ) {
                return $placeholder;
            }
        }
        else {
            $img = $placeholder;
        }
        return $img;
    }

    public function getCategoryImageOrPlaceholder( Category $category )
    {
        if ( $imageUrl = cp_get_category_image_url( $category->id ) ) {
            return $imageUrl;
        }
        return asset( 'themes/newspaper/assets/img/placeholder.png' );
    }

    /**
     * Retrieve a random post
     * @param bool|false $hasFeaturedImage Whether the post should have a featured image
     * @return Post|null
     */
    public function get_random_post( $hasFeaturedImage = false )
    {
        $post = null;
        $found = false;
        $postStatusPublish = PostStatus::where( 'name', 'publish' )->first();
        $iterations = 0;
        $maxIterations = 5;

        while ( !$found ) {
            if ( $iterations >= $maxIterations ) {
                break;
            }
            if ( $hasFeaturedImage ) {
                $pm = PostMeta::where( 'meta_name', '_post_image' )->where( 'meta_value', '!=', '' )->inRandomOrder()->limit( 1 )->first();
                if ( !$pm ) {
                    return null;
                }
                if ( $pm->post() ) {
                    $thePost = $pm->post()->first();
                    if ( $thePost->post_status_id == $postStatusPublish->id ) {
                        $found = true;
                        $post = $thePost;
                    }
                }
            }
            else {
                $thePost = Post::inRandomOrder()
                    ->where( 'post_status_id', $postStatusPublish->id )
                    ->limit( 1 )
                    ->first();
                if ( $thePost ) {
                    $found = true;
                    $post = $thePost;
                }
            }
            $iterations++;
        }
        return $post;
    }

    /**
     * Retrieve a collection of randomly selected posts (Selects only posts published this month)
     * @param int $number
     * @return mixed
     */
    public function getRandomPosts( int $number = 10 )
    {
        if ( empty( $number ) ) {
            $settingsClass = new Settings();
            $number = $settingsClass->getSetting( 'posts_per_page', 10 );
        }
        return Post::where( 'post_status_id', PostStatus::where( 'name', 'publish' )->first()->id )
            ->where( 'post_type_id', ( new PostType() )->where( 'name', 'post' )->first()->id )
            ->whereDate( 'created_at', '>', Carbon::now()->subMonth() )
            ->limit( $number )
            ->inRandomOrder()
            ->get();
    }

    public static function printSocialMetaTags()
    {
        $post = cp_get_post();
        if ( $post ) {
            if ( cp_post_has_featured_image( $post ) ) {
                $postImageUrl = cp_post_get_featured_image_url( $post->id );
            }
            else {
                $postImageUrl = asset( 'themes/newspaper/assets/img/placeholder.png' );
            }
            $settings = new Settings();
            ?>
            <!-- Schema.org for Google -->
            <meta itemprop="name" content="<?php esc_attr_e( $post->title ); ?>">
            <meta itemprop="description" content="<?php esc_attr_e( $post->escerpt ); ?>">
            <meta itemprop="image" content="<?php esc_attr_e( $postImageUrl ); ?>">
            <!-- Twitter -->
            <meta name="twitter:card" content="summary">
            <meta name="twitter:title" content="<?php esc_attr_e( $post->title ); ?>">
            <meta name="twitter:description" content="<?php esc_attr_e( $post->escerpt ); ?>">
            <!-- Open Graph general (Facebook, Pinterest & Twitter) -->
            <meta name="og:title" content="<?php esc_attr_e( $post->title ); ?>">
            <meta name="og:description" content="<?php esc_attr_e( $post->escerpt ); ?>">
            <meta name="og:image" content="<?php esc_attr_e( $postImageUrl ); ?>">
            <meta name="og:url" content="<?php esc_attr_e( env( 'APP_URL' ) ); ?>">
            <meta name="og:site_name" content="<?php esc_attr_e( $settings->getSetting( 'site_title' ) ); ?>">
            <meta name="og:type" content="website">
            <meta name="twitter:title" content="<?php esc_attr_e( $post->title ); ?> ">
            <meta name="twitter:description" content="<?php esc_attr_e( $post->escerpt ); ?>">
            <meta name="twitter:image" content="<?php esc_attr_e( $postImageUrl ); ?>">
            <meta name="twitter:card" content="<?php esc_attr_e( $post->title ); ?>">
            <?php
        }
    }

    public static function getShareUrls( $post )
    {
        $postPermalink = cp_get_permalink( $post );
        $postTitle = urlencode( $post->title );
        if ( cp_post_has_featured_image( $post ) ) {
            $postImageUrl = cp_post_get_featured_image_url( $post->id );
        }
        else {
            $postImageUrl = asset( 'themes/newspaper/assets/img/placeholder.png' );
        }
        $fbUrl = 'https://www.facebook.com/sharer.php?u=' . urlencode( $postPermalink );
        $twitterUrl = 'https://twitter.com/share?url=' . urlencode( $postPermalink ) . '&text=' . $postTitle;
        $linkedinUrl = 'https://www.linkedin.com/shareArticle?url=' . urlencode( $postPermalink ) . '&title=' . $postTitle;
        $pinterestUrl = 'https://pinterest.com/pin/create/button/?url=' . urlencode( $postPermalink ) . '&media=' . urlencode( $postImageUrl ) . '&description=' . $postTitle;
        $whatsAppUrl = 'https://api.whatsapp.com/send?text=' . $postTitle . ' ' . $postPermalink;

        return [
            'facebook' => $fbUrl,
            'twitter' => $twitterUrl,
            'linkedin' => $linkedinUrl,
            'pinterest' => $pinterestUrl,
            'whatsapp' => $whatsAppUrl,
        ];
    }

    /**
     * Internal variable to store posts
     * @see getCategoryTreePosts()
     * @see clearOutCache()
     * @see categoryTreeCountPosts()
     * @var array
     */
    private static $out = [];

    /**
     * Internal function to reset the class var $out that stores the list of posts recursively collected by methods of this class
     * @return $this
     */
    public function clearOutCache()
    {
        self::$out = [];
        return $this;
    }

    /**
     * Retrieve all posts from the specified $category and its subcategories
     * @param Category $category
     * @param int $postStatusID
     * @param int $numPosts The number of psots to retrieve. -1 or 0 gets them all
     * @return array|mixed
     */
    public function categoryTreeGetPosts( Category $category, $postStatusID = 1, $numPosts = -1 )
    {
        $posts = $category->posts()->latest()->where( 'post_status_id', $postStatusID )->get();

        //#! Get posts from the given category if any
        if ( $posts && $posts->first() ) {
            foreach ( $posts as $post ) {
                self::$out[ $post->id ] = $post;
            }
        }
        //#! Recurse into the category tree
        if ( $subcategories = $category->childrenCategories()->get() ) {
            foreach ( $subcategories as $subcategory ) {
                $posts = $subcategory->posts()->latest()->where( 'post_status_id', $postStatusID )->get();
                if ( $posts && $posts->first() ) {
                    foreach ( $posts as $post ) {
                        self::$out[ $post->id ] = $post;
                    }
                }
                self::$out = $this->categoryTreeGetPosts( $subcategory, $postStatusID );
            }
        }

        if ( !empty( $numPosts ) ) {
            $e = [];
            $i = 0;
            foreach ( self::$out as $pid => $post ) {
                if ( $i == $numPosts ) {
                    break;
                }
                $e[ $pid ] = $post;
                $i++;
            }
            self::$out = $e;
            unset( $e );
        }

        return self::$out;
    }

    /**
     * Retrieve the number of posts from the specified category
     * @param Category $category
     * @return int
     */
    public function categoryTreeCountPosts( Category $category )
    {
        $postStatus = PostStatus::where( 'name', 'publish' )->first();
        $posts = $this->clearOutCache()->categoryTreeGetPosts( $category, $postStatus->id );
        return count( $posts );
    }

    /**
     * Retrieve the number of posts from the specified category. It does not recurse into subcategories.
     * If that is needed, use categoryTreeCountPosts() instead.
     * @param Category $category
     * @return int
     */
    public function categoryCountPosts( Category $category )
    {
        return $category->posts()->count();
    }

    /**
     * Internal variable to store a subcategory's tree
     * @see getCategoriesTree()
     * @var array
     */
    private static $out_subcategories = [];

    /**
     * Retrieve the subcategories, 1 level deep of the specified $category
     * @param Category $category
     * @return array
     */
    public function getSubCategoriesTree( Category $category )
    {
        static $out_subcategories = [];

        if ( !$category ) {
            return $out_subcategories;
        }

        if ( $subcategories = $category->childrenCategories()->get() ) {
            $out_subcategories[ $category->id ] = Arr::pluck( $subcategories, 'id' );
        }
        return $out_subcategories;
    }

    public function getCategoriesTree()
    {
        $categories = $this->getTopCategories();
        $out = [];
        if ( !$categories || $categories->count() == 0 ) {
            return $out;
        }
        else {
            foreach ( $categories as $category ) {
                $out = $this->getSubCategoriesTree( $category );
            }
        }
        self::$out_subcategories = [];
        return $out;
    }

    /**
     * Retrieve the value of the specified theme option or the $default value if the option doesn't exist
     * @param string $name
     * @param false $default
     * @return array|false|mixed
     */
    public function getThemeOption( string $name, $default = false )
    {
        $options = NewspaperAdminController::getThemeOptions();
        if ( isset( $options[ $name ] ) ) {
            return $options[ $name ];
        }
        return $default;
    }
}
