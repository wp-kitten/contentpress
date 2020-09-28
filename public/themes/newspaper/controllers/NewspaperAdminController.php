<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CategoryMeta;
use App\Models\Feed;
use App\Helpers\CPML;
use App\Helpers\Util;
use App\Http\Controllers\Admin\AdminControllerBase;
use App\Newspaper\NewspaperHelper;
use App\Models\Options;
use App\Models\PostType;
use DOMDocument;
use Illuminate\Support\Str;

class NewspaperAdminController extends AdminControllerBase
{
    /**
     * Stores the name of the option holding the theme's options
     * @var string
     */
    const THEME_OPTIONS_OPT_NAME = 'newspaper-theme-options';

    /**
     * Stores the list of the theme's default options
     * @var array[]
     */
    private static $_defaultOptions = [
        'featured_categories' => [],
    ];

    /**
     * Store the theme's options
     * @var array[]
     */
    private static $_themeOptions = [];

    public function __construct()
    {
        parent::__construct();

        self::$_themeOptions = $this->options->getOption( self::THEME_OPTIONS_OPT_NAME, self::$_defaultOptions );
    }

    public function themeOptionsPageView()
    {
        $nh = new NewspaperHelper();
        return view( '_admin/theme-options' )->with( [
            'categories' => $nh->getCategoriesTree(),
            'options' => self::$_themeOptions,
            'settings' => $this->settings,
        ] );
    }

    //#! [post]
    public function themeOptionsSave()
    {
        $themeOptions = $this->options->getOption( self::THEME_OPTIONS_OPT_NAME, [] );

        //#! General options
        if ( !isset( $themeOptions[ 'general' ] ) ) {
            $themeOptions[ 'general' ] = [];
        }
        $generalOptions = $this->request->get( 'general' );
        $themeOptions[ 'general' ][ 'enable_user_custom_home' ] = ( is_array( $generalOptions ) && isset( $generalOptions[ 'enable_user_custom_home' ] ) );

        //#! Homepage options
        $options = $this->request->get( 'homepage' );
        if ( !isset( $themeOptions[ 'homepage' ] ) ) {
            $themeOptions[ 'homepage' ] = [];
        }
        $homepageOptions = $themeOptions[ 'homepage' ];
        if ( !empty( $options ) ) {
            foreach ( $options as $sectionID => $catID ) {
                $theCat = Category::find( $catID );
                if ( $theCat ) {
                    $homepageOptions[ $sectionID ] = $catID;
                }
            }
        }
        $themeOptions[ 'homepage' ] = $homepageOptions;

        //...

        //#! Save options
        $this->options->addOption( self::THEME_OPTIONS_OPT_NAME, $themeOptions );

        return redirect()->back()->with( 'message', [
            'class' => 'success',
            'text' => __( 'np::m.Options saved.' ),
        ] );
    }

    /**
     * Retrieve all theme options
     * @return array
     */
    public static function getThemeOptions(): array
    {
        if ( empty( self::$_themeOptions ) ) {
            $option = Options::where( 'name', self::THEME_OPTIONS_OPT_NAME )->first();
            if ( $option ) {
                self::$_themeOptions = maybe_unserialize( $option->value );
            }
            else {
                self::$_themeOptions = self::$_defaultOptions;
            }
        }
        return self::$_themeOptions;
    }

    /**
     * Retrieve the default theme options
     * @return array[]
     */
    public static function getDefaultThemeOptions(): array
    {
        return self::$_defaultOptions;
    }

    /*
     * Admin > Dashboard > Users > Feeds
     */
    public function userFeedsView()
    {
        return view( '_admin.user-feeds' )->with( [
            'feeds' => Feed::where( 'user_id', cp_get_current_user_id() )->paginate( $this->settings->getSetting( 'posts_per_page' ) ),
        ] );
    }

    /*
     * [POST]
     * Feed submission
     */
    public function userFeedSubmit()
    {
        // website_url, feed_private
        $this->request->validate( [
            'website_url' => 'required|string|max:255',
        ] );
        $websiteUrl = $this->request->get( 'website_url' );
        $private = $this->request->get( 'feed_private', false );
        $isFeedUrl = $this->request->get( 'is_feed', false );
        $userID = cp_get_current_user_id();

        $feedUrl = $this->__validateUrl( $websiteUrl );
        if ( !$feedUrl ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'np::m.The url is not valid.' ),
            ] );
        }

        //#! Check the website for feeds
        if( ! $isFeedUrl){
            $feedUrl = $this->__getFeedFromWebsite( $feedUrl );
            if ( empty( $feedUrl ) ) {
                return redirect()->back()->with( 'message', [
                    'class' => 'danger',
                    'text' => __( 'np::m.We could not find any feed urls in that website.' ),
                ] );
            }
            $domain = parse_url( $websiteUrl, PHP_URL_HOST );
            $scheme = parse_url( $websiteUrl, PHP_URL_SCHEME );
            $feedUrl = "{$scheme}://{$domain}/{$feedUrl}";
            if( ! $this->__validateUrl($feedUrl)){
                return redirect()->back()->with( 'message', [
                    'class' => 'danger',
                    'text' => __( 'np::m.The url is not valid.' ),
                ] );
            }
        }


        //#! Check if feed exists
        $hash = md5( $feedUrl );
        $feed = $this->__feedExists( $hash, $userID, $private );
        if ( $feed && $feed->id ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( "np::m.Another feed with the same url has already been registered." ),
            ] );
        }

        $feedCategoryName = $this->request->get( 'feed_category' );
        $feedCategory = $this->__getCreateCategory( $feedCategoryName, $private );

        if ( is_string( $feedCategory ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => $feedCategory,
            ] );
        }

        //#! Public by default
        $feedData = [
            'hash' => md5( $feedUrl ),
            'url' => $feedUrl,
            'category_id' => $feedCategory->id,
            'user_id' => $userID,
        ];

        $result = Feed::create( $feedData );

        if ( $result ) {
            return redirect()->back()->with( 'message', [
                'class' => 'success',
                'text' => __( 'np::m.Feed successfully registered.' ),
            ] );
        }

        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'np::m.An error occurred and the feed could not be added.' ),
        ] );
    }

    public function userFeedDelete( $id )
    {
        if ( empty( $id ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'np::m.An error occurred, the feed was not specified.' ),
            ] );
        }

        if ( !defined( 'NPFR_CATEGORY_PRIVATE' ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'np::m.An error occurred.' ),
            ] );
        }

        if ( !np_isUserFeed( $id, NPFR_CATEGORY_PRIVATE ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'np::m.An error occurred, you can only delete your private feeds.' ),
            ] );
        }

        $userID = cp_get_current_user_id();
        $defaultLanguageID = CPML::getDefaultLanguageID();

        $feed = Feed::where( 'id', $id )->where( 'user_id', $userID )->first();
        if ( !$feed ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'np::m.An error occurred, the feed was not found.' ),
            ] );
        }

        //#! Only private feeds can be deleted
        $category = $feed->category()->first();
        if ( !$category ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'np::m.An error occurred.' ),
            ] );
        }

        $categoryMeta = $category->category_metas()
            ->where( 'meta_name', 'np_private' )
            ->where( 'meta_value', $userID )
            ->where( 'language_id', $defaultLanguageID )
            ->first();
        if ( !$categoryMeta ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'np::m.An error occurred, the feed was not found.' ),
            ] );
        }

        if ( $feed->forceDelete() ) {
            return redirect()->back()->with( 'message', [
                'class' => 'success',
                'text' => __( 'np::m.The feed has been deleted.' ),
            ] );
        }
        return redirect()->back()->with( 'message', [
            'class' => 'danger',
            'text' => __( 'np::m.An error occurred and the feed could not be deleted.' ),
        ] );
    }

    /*
     * Feed edit view
     * @param int $id
     */
    public function userFeedsEdit( int $id )
    {
        if ( empty( $id ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'np::m.An error occurred, the feed was not specified.' ),
            ] );
        }

        if ( !defined( 'NPFR_CATEGORY_PRIVATE' ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'np::m.An error occurred.' ),
            ] );
        }

        $feed = Feed::findOrFail( $id );
        $private = false;
        $category = $feed->category()->first();
        $defaultLanguageID = CPML::getDefaultLanguageID();
        $userID = cp_get_current_user_id();

        if ( $category ) {
            $cm = CategoryMeta::where( 'meta_name', 'np_private' )
                ->where( 'meta_value', $userID )
                ->where( 'language_id', $defaultLanguageID )
                ->where( 'category_id', $category->id )
                ->first();
            if ( $cm ) {
                $private = true;
            }
        }

        return view( '_admin.user-feeds-edit' )->with( [
            'feed' => $feed,
            'category' => $category,
            'private' => $private,
        ] );
    }

    /*
     * [POST]
     * Update feed
     */
    public function userFeedsUpdate( $id )
    {
        $this->request->validate( [
            'feed_url' => 'required|string|max:255',
            'feed_category_id' => 'required|int',
            'feed_category' => 'required|string|max:50',
        ] );
        $private = $this->request->get( 'feed_private', false );
        $userID = cp_get_current_user_id();

        $url = $this->__validateUrl( $this->request->get( 'feed_url' ) );
        if ( !$url ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( 'np::m.The url is not valid.' ),
            ] );
        }

        //#! Check if feed exists
        $hash = md5( $url );
        $feed = $this->__feedExists( $hash, $userID, $private, $id );
        if ( $feed ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( "np::m.Another feed with the same url has already been registered." ),
            ] );
        }

        //#! Find the category and see if we need to update its name
        $category = Category::find( $this->request->get( 'feed_category_id' ) );
        if ( !$category ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( "np::m.The specified category was not found." ),
            ] );
        }

        $defaultLanguageID = CPML::getDefaultLanguageID();
        $postTypeID = PostType::where( 'name', 'post' )->first()->id;

        $categoryName = mb_convert_encoding( $this->request->get( 'feed_category' ), 'utf-8', 'auto' );
        $categoryName = wp_kses( $categoryName, [] );
        if ( empty( $categoryName ) ) {
            return redirect()->back()->with( 'message', [
                'class' => 'danger',
                'text' => __( "np::m.The category name is required." ),
            ] );
        }
        $newSlug = Str::slug( $categoryName );
        //! Update slug if changed
        $category->name = $categoryName;
        if ( $category->slug != $newSlug ) {
            if ( !Util::isUniqueCategorySlug( $newSlug, $defaultLanguageID, $postTypeID ) ) {
                $newSlug .= '-' . time();
            }
            $category->slug = $newSlug;
        }
        $category->update();

        //#! Check if we need to update the category meta
        $cm = CategoryMeta::where( 'meta_name', 'np_private' )
            ->where( 'meta_value', $userID )
            ->where( 'language_id', $defaultLanguageID )
            ->where( 'category_id', $category->id )
            ->first();
        //#! Transition: private -> public
        if ( $cm ) {
            if ( !$private ) {
                $cm->delete();
                //#! Update the parent category
                $category->category_id = npfrGetCategoryPublic()->id;
                $category->update();
            }
        }
        //#! Transition: public -> private
        elseif ( $private ) {
            CategoryMeta::create( [
                'meta_name' => 'np_private',
                'meta_value' => $userID,
                'language_id' => $defaultLanguageID,
                'category_id' => $category->id,
            ] );
            //#! Update the parent category
            $category->category_id = npfrGetCategoryPrivate()->id;
            $category->update();
        }

        return redirect()->back()->with( 'message', [
            'class' => 'success',
            'text' => __( "np::m.Feed updated." ),
        ] );
    }

    /**
     * Retrieve the subcategory if it exists otherwise attempt to create it
     * @param string $categoryName
     * @param false $isPrivate
     * @return array|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Translation\Translator|string|null
     */
    private function __getCreateCategory( string $categoryName, $isPrivate = false )
    {
        $categoryName = mb_convert_encoding( $categoryName, 'utf-8', 'auto' );
        $categoryName = wp_kses( $categoryName, [] );

        $parentCategory = ( $isPrivate ? npfrGetCategoryPrivate() : npfrGetCategoryPublic() );
        if ( !$parentCategory ) {
            return __( 'np::m.An error occurred and the category could not be created.' );
        }

        $defaultLanguageID = CPML::getDefaultLanguageID();
        $postTypeID = PostType::where( 'name', 'post' )->first()->id;
        $userID = cp_get_current_user_id();

        //#! Check to see whether the category exists
        $subcat = Category::where( 'name', strtolower( $categoryName ) )
            ->where( 'language_id', $defaultLanguageID )
            ->where( 'post_type_id', $postTypeID )
            ->where( 'category_id', $parentCategory->id )
            ->first();

        if ( $subcat ) {
            return $subcat;
        }

        //#! Create
        $slug = Str::slug( $categoryName );
        if ( !Util::isUniqueCategorySlug( $slug, $defaultLanguageID, $postTypeID ) ) {
            $slug = $slug . '-' . time();
        }
        $subcat = Category::create( [
            'name' => strtolower( $categoryName ),
            'slug' => $slug,
            'language_id' => $defaultLanguageID,
            'post_type_id' => $postTypeID,
            'category_id' => $parentCategory->id,
        ] );
        if ( !$subcat ) {
            return __( 'np::m.An error occurred and the category could not be created.' );
        }
        $cm = CategoryMeta::create( [
            'meta_name' => 'np_private',
            'meta_value' => $userID,
            'language_id' => $defaultLanguageID,
            'category_id' => $subcat->id,
        ] );
        if ( !$cm ) {
            $subcat->delete();
            return __( 'np::m.An error occurred and the category could not be created.' );
        }
        return $subcat;
    }

    /**
     * Check to see whether the specified feed exists
     * @param string $feedUrlHash
     * @param int $userID
     * @param bool $private
     * @param int $excludeFeedID
     * @return mixed
     */
    private function __feedExists( string $feedUrlHash, int $userID, bool $private = false, int $excludeFeedID = 0 )
    {
        return Feed::where( 'hash', $feedUrlHash )->where( 'user_id', $userID )->where( 'id', '!=', $excludeFeedID )->withTrashed()->first();
    }

    /**
     * Ensure the specified feed url is valid
     * @param string $feedUrl
     * @return false|string
     */
    private function __validateUrl( string $feedUrl )
    {
        $feedUrl = untrailingslashit( strtolower( $feedUrl ) );
        return ( filter_var( $feedUrl, FILTER_VALIDATE_URL ) ? $feedUrl : false );
    }

    private function __getFeedFromWebsite( string $websiteUrl ): string
    {
        $html = file_get_contents( $websiteUrl );
        if ( !$html || empty( $html ) ) {
            return '';
        }

        $htmlDom = new DOMDocument();

        $loaded = @$htmlDom->loadHTML( $html );
        if ( false === $loaded ) {
            return '';
        }

        $links = $htmlDom->getElementsByTagName( 'link' );
        if ( $links ) {
            foreach ( $links as $link ) {
                $attrType = $link->getAttribute( 'type' );
                if ( strlen( trim( $attrType ) ) == 0 ) {
                    continue;
                }
                $href = $link->getAttribute( 'href' );
                if ( strlen( trim( $href ) ) == 0 ) {
                    continue;
                }

                $types = [ 'application/rss+xml', 'application/atom+xml' ];
                $attrType = trim( $attrType );
                if ( in_array( $attrType, $types ) ) {
                    return strtolower( trim( $href ) );
                }
            }
        }
        return '';
    }
}
