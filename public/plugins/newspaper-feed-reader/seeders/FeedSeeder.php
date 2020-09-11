<?php

use App\Category;
use App\CategoryMeta;
use App\Feed;
use App\Helpers\CPML;
use App\Post;
use App\PostStatus;
use App\PostType;
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

        'Billboard' => 'https://www.billboard.com/feed',

        'Vox' => 'https://www.vox.com/rss/index.xml',

        'Digital Trends' => 'https://www.digitaltrends.com/feed',

        'BBCI' => 'http://feeds.bbci.co.uk/sport/rss.xml',

        'Quartz' => 'https://cms.qz.com/feed/',

        "Tom's guide" => 'https://www.tomsguide.com/feeds/all',

        'The Guardian' => 'https://www.theguardian.com/international/rss',

        'NY Times' => 'https://rss.nytimes.com/services/xml/rss/nyt/HomePage.xml',

        'Politico' => 'https://www.politico.com/rss/politicopicks.xml',

        'Newsweek' => 'https://newsweek.ro/rss',

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

        'Los Angeles' => 'https://www.dailynews.com/feed',
        'Independent' => 'https://www.independent.co.uk/rss',
        'Sporting News' => 'http://www.sportingnews.com/us/rss',
        'Romania' => [
            'Film Now' => 'https://www.filmnow.ro/rss',
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

        //#! Create categories and add meta fields
        $catClass = new Category();

        ///===============================
        //#! Create categories, subcategories & feeds
        foreach ( self::$categories as $categoryName => $catInfo ) {
            $theCat = $catClass->exists( $categoryName, true );
            if ( !$theCat ) {
                $theCat = Category::create( [
                    'name' => Str::title( $categoryName ),
                    'slug' => Str::slug( $categoryName ),
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

            //#!
            if ( is_string( $catInfo ) ) {
                $feedUrl = untrailingslashit( strtolower( $catInfo ) );
                $feed = Feed::create( [
                    'url' => $feedUrl,
                    'hash' => md5( $feedUrl ),
                    'category_id' => $theCat->id,
                ] );
                if ( !$feed ) {
                    logger( 'The feed "' . $feedUrl . '" could not be created' );
                    return;
                }
            }
            //#! process subcategories & feeds
            else {
                foreach ( $catInfo as $subcategoryName => $feedUrls ) {
                    $theSubCat = Category::where( 'slug', Str::slug( $subcategoryName ) )->where( 'category_id', $theCat->id )->first();
                    if ( !$theSubCat ) {
                        $theSubCat = Category::create( [
                            'name' => Str::title( $subcategoryName ),
                            'slug' => Str::slug( $theCat->name . '-' . $subcategoryName ),
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
                        $feed = Feed::create( [
                            'url' => $feedUrl,
                            'hash' => md5( $feedUrl ),
                            'category_id' => $theSubCat->id,
                        ] );
                        if ( !$feed ) {
                            logger( 'The feed "' . $feedUrl . '" could not be created' );
                            return;
                        }
                    }
                    else {
                        foreach ( $feedUrls as $feedUrl ) {
                            $feedUrl = untrailingslashit( strtolower( $feedUrls ) );
                            $feed = Feed::createOrUpdate( [
                                'url' => $feedUrl,
                                'hash' => md5( $feedUrl ),
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

        foreach ( self::$pages as $title ) {
            if ( !$postClass->exists( Str::slug( $title ) ) ) {
                $postClass->create( [
                    'title' => Str::title( $title ),
                    'slug' => Str::slug( $title ),
                    'content' => '',
                    'user_id' => $currentUserID,
                    'language_id' => $defaultLanguageID,
                    'post_type_id' => $postTypeId,
                    'post_status_id' => $postStatusID,
                ] );
            }
        }
    }
}
