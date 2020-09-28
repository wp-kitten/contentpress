<?php
namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryMeta;
use App\Models\Feed;
use App\Helpers\CPML;
use App\Models\Post;
use App\Models\PostMeta;
use App\Models\PostStatus;
use App\Models\PostType;
use App\Models\Settings;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FeedSeeder extends Seeder
{
    public static $categories = [
        'Portland Press Herald' => [
            "News" => 'https://www.pressherald.com/feed/',
            "American Journal" => 'https://www.pressherald.com/american-journal/feed',
        ],

        'ABC 7 Chicago' => 'https://abc7chicago.com/feed/',

        'Aljazeera' => 'https://www.aljazeera.com/xml/rss/all.xml',

        'Teslarati' => 'https://www.teslarati.com/feed/',

        'Times of Israel' => 'https://www.timesofisrael.com/feed',

        'Daily Mail' => 'https://www.dailymail.co.uk/articles.rss',

        'Billboard' => 'https://www.billboard.com/rss',

        'Vox' => 'https://www.vox.com/rss/index.xml',

        'Digital Trends' => 'https://www.digitaltrends.com/feed',

        'BBCI' => 'http://feeds.bbci.co.uk/sport/rss.xml',

        'Quartz' => 'https://cms.qz.com/feed/',

        "Tom's guide" => 'https://www.tomsguide.com/feeds/all',

        'The Guardian' => 'https://www.theguardian.com/international/rss',

        'NY Times' => 'https://rss.nytimes.com/services/xml/rss/nyt/HomePage.xml',

        'Politico' => 'https://www.politico.com/rss/politicopicks.xml',

        'The Daily Beast' => 'https://feeds.thedailybeast.com/rss/articles',

        'Yahoo!' => [
            "News" => 'https://news.yahoo.com/rss',
            "Sports" => 'https://sports.yahoo.com/rss/',
        ],

        'Google News' => 'https://news.google.com/rss?hl=en-US&gl=US&ceid=US%3Aen&oc=11',

        'NPR' => [
            'Top Stories' => 'https://feeds.npr.org/1002/rss.xml',
            'News' => 'https://feeds.npr.org/1001/rss.xml',
            'Music' => 'https://feeds.npr.org/1039/rss.xml',
            'Books' => 'https://feeds.npr.org/1032/rss.xml',
            'Morning Edition' => 'https://feeds.npr.org/3/rss.xml',
            'All things considered' => 'https://feeds.npr.org/2/rss.xml',
            "Wait Wait... Don't Tell Me!" => 'https://feeds.npr.org/35/rss.xml',
        ],

        'Phys' => [
            'Latest News' => 'https://phys.org/rss-feed',
            'Breaking News' => 'https://phys.org/rss-feed/breaking',
            'Editorials' => 'https://phys.org/rss-feed/editorials',
        ],

        'Los Angeles' => [
            'Daily News' => 'https://www.dailynews.com/feed',
        ],
        'Independent' => 'https://www.independent.co.uk/rss',
        'Romania' => [
            'Film Now' => 'https://www.filmnow.ro/rss',
            'Digi24' => 'https://www.digi24.ro/rss',
            'Digi Life' => 'https://www.digi-life.tv/rss',
            'Music Channel' => 'https://1music.ro/feed/',
            'ProFM' => 'https://www.profm.ro/rss',
            'Digi FM' => 'https://www.digifm.ro/rss',
            'Digi Sport' => 'https://www.digisport.ro/rss',
            'Discovery' => 'https://discovery.ro/feed/',
            'Mediafax' => 'https://www.mediafax.ro/rss',
            'Stirile ProTV' => 'https://stirileprotv.ro/rss',
            'news.ro' => 'https://www.news.ro/rss',
            'Ziarul Libertatea' => 'https://www.libertatea.ro/stiri/feed',
            'Cancan.ro' => 'https://www.cancan.ro/stiri/feed',
            'Ziarul Financiar' => 'https://www.zf.ro/rss',
            'newsin.ro' => 'https://newsin.ro/feed/',
            'Realitatea.net' => 'https://rss.realitatea.net/homepage.xml',
            'Newsweek' => 'https://newsweek.ro/rss',
            'Economica.net' => 'https://www.economica.net/rss/stiri.xml',
            'AgerPress' => 'https://www.agerpres.ro/rss',
            'RFI' => 'https://www.rfi.ro/rss.xml',
            'GSP' => 'https://www.gsp.ro/rss.xml',
            'Sport.ro' => 'https://www.sport.ro/rss',
            'Vremea' => 'https://vremea.ido.ro/stiri/feed/',
            'RomaniaTV' => 'https://www.romaniatv.net/rss/stiri.xml',
            'Stirile TVR' => 'http://stiri.tvr.ro/rss/stiri.xml',
            'Capitalul.ro' => 'https://capitalul.ro/feed/',
        ],

        'Sports' => [
            'Marca' => 'https://e00-marca.uecdn.es/rss/portada.xml',
            'Sporting News' => 'http://www.sportingnews.com/us/rss',
        ],
        'NASA' => [
            'Science' => 'https://science.nasa.gov/rss.xml',
        ],
    ];

    public static $pages = [
        'Home',
        'Blog',
        'About',
        'Contact',
        'Thank you',
        'Cookie policy',
        'Privacy policy',
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $postTypeID = PostType::where( 'name', 'post' )->first()->id;
        $languageID = CPML::getDefaultLanguageID();

        //#! Create the special categories and add meta fields
        $specialCategories = [ NPFR_CATEGORY_PUBLIC, NPFR_CATEGORY_PRIVATE ];
        foreach ( $specialCategories as $categoryName ) {
            Category::create( [
                'name' => $categoryName,
                'slug' => Str::slug( $categoryName ),
                'post_type_id' => PostType::where( 'name', 'post' )->first()->id,
                'language_id' => CPML::getDefaultLanguageID(),
            ] );
        }

        ///===============================
        //#! Create categories, subcategories & feeds
        foreach ( self::$categories as $categoryName => $catInfo ) {
            /*
             * [::1] Create the main category if it doesn't already exist
             */
            $theCatName = Str::title( mb_convert_encoding( $categoryName, 'utf-8', 'auto' ) );
            $theCat = Category::where( 'name', $theCatName )
                ->where( 'language_id', $languageID )
                ->where( 'post_type_id', $postTypeID )
                ->where( 'category_id', null )
                ->first();

            if ( !$theCat ) {
                $theCat = Category::create( [
                    'name' => $theCatName,
                    'slug' => Str::slug( $theCatName ),
                    'language_id' => $languageID,
                    'post_type_id' => $postTypeID,
                ] );
                if ( !$theCat ) {
                    logger( 'The category "' . $categoryName . '" could not be created' );
                    return;
                }

                //#! Add meta fields
                $meta = CategoryMeta::create( [
                    'meta_name' => '_category_image',
                    'meta_value' => '',
                    'category_id' => $theCat->id,
                    'language_id' => $theCat->language_id,
                ] );
                if ( !$meta ) {
                    logger( 'The category meta for: "' . $categoryName . '" could not be created' );
                    return;
                }
            }

            /*
             * [::2] Add the category's feed
             */
            if ( is_string( $catInfo ) ) {
                $feedUrl = untrailingslashit( Str::lower( $catInfo ) );
                if ( !filter_var( $feedUrl, FILTER_VALIDATE_URL ) ) {
                    logger( 'The feed "' . $feedUrl . '" could not be created: Not a valid url.' );
                    continue;
                }
                $feed = Feed::create( [
                    'url' => $feedUrl,
                    'hash' => md5( $feedUrl ),
                    'user_id' => cp_get_current_user_id(),
                    'category_id' => $theCat->id,
                ] );
                if ( !$feed ) {
                    logger( 'The feed "' . $feedUrl . '" could not be created' );
                    return;
                }
            }
            /*
             * [::3] Process category's subcategories/feeds
             */
            else {
                foreach ( $catInfo as $subcategoryName => $feedUrls ) {
                    $theSubcatName = Str::title( mb_convert_encoding( $subcategoryName, 'utf-8', 'auto' ) );
                    $theSubCat = Category::where( 'name', $theSubcatName )
                        ->where( 'language_id', $languageID )
                        ->where( 'post_type_id', $postTypeID )
                        ->where( 'category_id', $theCat->id )
                        ->first();

                    if ( !$theSubCat ) {
                        $theSubCat = Category::create( [
                            'name' => $theSubcatName,
                            'slug' => Str::slug( $theCat->name . '-' . $theSubcatName ),
                            'language_id' => $languageID,
                            'post_type_id' => $postTypeID,
                            'category_id' => $theCat->id,
                        ] );
                        if ( !$theSubCat ) {
                            logger( 'The subcategory "' . $subcategoryName . '" could not be created' );
                            return;
                        }

                        //#! Add meta fields
                        $meta = CategoryMeta::create( [
                            'meta_name' => '_category_image',
                            'meta_value' => '',
                            'category_id' => $theSubCat->id,
                            'language_id' => $theSubCat->language_id,
                        ] );
                        if ( !$meta ) {
                            logger( 'The subcategory meta for: "' . $subcategoryName . '" could not be created' );
                            return;
                        }
                    }

                    if ( is_string( $feedUrls ) ) {
                        $feedUrl = untrailingslashit( strtolower( $feedUrls ) );
                        if ( !filter_var( $feedUrl, FILTER_VALIDATE_URL ) ) {
                            logger( 'The feed "' . $feedUrl . '" could not be created: Not a valid url.' );
                            continue;
                        }
                        $feed = Feed::create( [
                            'url' => $feedUrl,
                            'hash' => md5( $feedUrl ),
                            'user_id' => cp_get_current_user_id(),
                            'category_id' => $theSubCat->id,
                        ] );
                        if ( !$feed ) {
                            logger( 'The feed "' . $feedUrl . '" could not be created' );
                            return;
                        }
                    }
                    else {
                        foreach ( $feedUrls as $feedUrl ) {
                            $feedUrl = untrailingslashit( strtolower( $feedUrl ) );
                            if ( !filter_var( $feedUrl, FILTER_VALIDATE_URL ) ) {
                                logger( 'The feed "' . $feedUrl . '" could not be created: Not a valid url.' );
                                continue;
                            }
                            $feed = Feed::createOrUpdate( [
                                'url' => $feedUrl,
                                'hash' => md5( $feedUrl ),
                                'user_id' => cp_get_current_user_id(),
                                'category_id' => $theSubCat->id,
                            ] );
                            if ( !$feed ) {
                                logger( 'The feed "' . $feedUrl . '" could not be created' );
                                return;
                            }
                        }
                    }
                }
            }
        }

        //=======================================
        //#! Pages
        $postClass = new Post();
        $postStatusID = PostStatus::where( 'name', 'publish' )->first()->id;
        $currentUserID = cp_get_current_user()->getAuthIdentifier();
        $defaultLanguageID = CPML::getDefaultLanguageID();
        $postTypeId = PostType::where( 'name', 'page' )->first()->id;

        $blogPageID = 0;

        foreach ( self::$pages as $title ) {
            if ( !$postClass->exists( Str::slug( $title ) ) ) {
                $slug = Str::slug( $title );
                $page = $postClass->create( [
                    'title' => Str::title( $title ),
                    'slug' => $slug,
                    'content' => '',
                    'user_id' => $currentUserID,
                    'language_id' => $defaultLanguageID,
                    'post_type_id' => $postTypeId,
                    'post_status_id' => $postStatusID,
                ] );
                //#! Set templates
                if ( $page && 'home' == $slug ) {
                    PostMeta::create( [
                        'post_id' => $page->id,
                        'language_id' => CPML::getDefaultLanguageID(),
                        'meta_name' => 'template',
                        'meta_value' => 'templates.home',
                    ] );
                }
                elseif ( $page && 'blog' == $slug ) {
                    PostMeta::create( [
                        'post_id' => $page->id,
                        'language_id' => CPML::getDefaultLanguageID(),
                        'meta_name' => 'template',
                        'meta_value' => 'templates.blog',
                    ] );
                    $blogPageID = $page->id;
                }
            }
        }

        //#! Settings > reading
        $settings = new Settings();
        $settings->updateSetting( 'show_on_front', 'blog' );
        $settings->updateSetting( 'page_on_front', $blogPageID );
        $settings->updateSetting( 'blog_page', $blogPageID );
    }
}
