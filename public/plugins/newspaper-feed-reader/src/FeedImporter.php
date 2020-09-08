<?php

namespace App\Helpers;

use App\Category;
use App\Feed;
use App\Helpers\Syndication\FeedReader;
use App\MediaFile;
use App\Post;
use App\PostMeta;
use App\PostStatus;
use App\PostType;
use App\Tag;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 *
 * @package App\Helpers
 */
class FeedImporter
{
    /**
     * Stores the list of registered feed urls
     * @var array
     */
    private $_feedUrls = [];

    /**
     *
     * @var PostType
     */
    private $postType;

    /**
     * @var PostStatus
     */
    private $publishPostStatus;

    /**
     * @var PostStatus
     */
    private $draftPostStatus;

    /**
     * The ID of the default language
     * @var int
     */
    private $languageID;

    /**
     * Holds the ID of the user to be set as psot author
     * @var int|null
     */
    private $currentUserID;

    /**
     * @var MediaFile
     */
    private $mediaFile;

    /**
     * @var PostMeta
     */
    private $postMeta;

    public function __construct()
    {
        $this->languageID = CPML::getDefaultLanguageID();
        $this->postType = ( new PostType() )->where( 'name', 'post' )->first();
        $this->publishPostStatus = ( new PostStatus() )->where( 'name', 'publish' )->first();
        $this->draftPostStatus = ( new PostStatus() )->where( 'name', 'draft' )->first();
        $this->currentUserID = cp_get_current_user_id();
        $this->mediaFile = new MediaFile();
        $this->postMeta = new PostMeta();
    }

    /**
     * Register a feed url
     * @param array
     * @return $this
     */
    public function register( array $feedUrls = [] ): FeedImporter
    {
        $this->_feedUrls = array_merge( $this->_feedUrls, $feedUrls );
        return $this;
    }

    /**
     * Retrieve the list of registered feeds
     */
    public function getRegisteredFeeds(): array
    {
        return array_unique( $this->_feedUrls );
    }

    /**
     * Process feeds and import content
     * @throws \Exception
     */
    public function process()
    {
        if ( empty( $this->_feedUrls ) ) {
            throw new \Exception( __( 'a.[FeedImporter] Please register some feeds first.' ) );
        }

        $syn = new FeedReader();

        foreach ( $this->_feedUrls as $feedUrl ) {
            $feed = Feed::where( 'url', $feedUrl )->first();
            if ( !$feed ) {
                continue;
            }

            $categoryID = $feed->category->id;
            if ( empty( $categoryID ) ) {
                logger( 'Category not found: ' . $feedUrl . '. Skipping' );
                continue;
            }

            try {
                $syn->open( $feedUrl );
                if ( $syn->isLoaded() ) {

                    $entries = $syn->getEntries();
                    if ( !empty( $entries ) ) {
                        foreach ( $entries as $entry ) {
                            $postData = [
                                'title' => trim( $entry[ 'title' ] ),
                                'content' => trim( $entry[ 'description' ] ),
                            ];

                            //#! Attempt to create the post as draft
                            $currentPost = $this->__insertPost( $postData, $this->draftPostStatus->id );
                            if ( !$currentPost ) {
                                continue;
                            }

                            $linkBack = ( isset( $entry[ 'link' ] ) ? trim( $entry[ 'link' ] ) : false );
                            $entryCategory = ( isset( $entry[ 'category' ] ) ? trim( $entry[ 'category' ] ) : false );
                            $keywords = ( isset( $entry[ 'media:keywords' ] ) ? trim( $entry[ 'media:keywords' ] ) : false );
                            $imageUrl = '';
                            if ( isset( $entry[ 'image' ] ) && !empty( $entry[ 'image' ] ) ) {
                                $imageUrl = $entry[ 'image' ];
                            }
                            elseif ( isset( $entry[ 'media:thumbnail' ] ) && !empty( $entry[ 'media:thumbnail' ] ) ) {
                                $imageUrl = $entry[ 'media:thumbnail' ];
                            }
                            $videoUrl = ( isset( $entry[ 'enclosure' ] ) ? trim( $entry[ 'enclosure' ] ) : false );
                            $postTags = [];
                            $postCategories = [ $categoryID ];

                            //#! Try to create the subcategory
                            if ( $entryCategory ) {
                                //#! Some feeds might provide their categories separated by commas, so if that's the case
                                //#! we only need the first one
                                $_categories = explode( ',', $entryCategory );
                                if ( !empty( $_categories ) ) {
                                    $entryCategory = ( isset( $_categories[ 0 ] ) && !empty( $_categories[ 0 ] ) ? preg_replace( '/\s+/', ' ', $_categories[ 0 ] ) : '' );
                                }
                                //#! If the entry sub-category is different than the parent category, use it instead
                                if ( !empty( $entryCategory ) && ( $feed->category->name != Str::title( $entryCategory ) ) ) {
                                    if ( $entrySubcategoryID = $this->__getCreateCategoryID( $entryCategory, $categoryID ) ) {
                                        $postCategories = [ $entrySubcategoryID ];
                                    }
                                }
                            }

                            //#! Set categories
                            $currentPost->categories()->detach();
                            $currentPost->categories()->attach( $postCategories );

                            //#! Set tags, if any
                            if ( $keywords ) {
                                $tags = explode( ',', $keywords );
                                $tags = array_map( 'trim', $tags );
                                $tags = array_map( 'wp_kses_post', $tags );
                                $tags = array_unique( $tags );

                                if ( $tags ) {
                                    foreach ( $tags as $tag ) {
                                        if ( $tagID = $this->__getCreateTagID( $tag ) ) {
                                            array_push( $postTags, $tagID );
                                        }
                                    }
                                }
                                if ( !empty( $postTags ) ) {
                                    $postTags = array_unique( $postTags );
                                    $currentPost->tags()->detach();
                                    $currentPost->tags()->attach( $postTags );
                                }
                            }

                            //#! Set featured image
                            if ( !empty( $imageUrl ) ) {
                                $featuredImageID = $this->__importImage( trim( $imageUrl ) );
                                if ( $featuredImageID ) {
                                    $postMeta = $this->postMeta->where( 'post_id', $currentPost->id )
                                        ->where( 'language_id', $currentPost->language_id )
                                        ->where( 'meta_name', '_post_image' )
                                        ->first();
                                    if ( $postMeta ) {
                                        $postMeta->meta_value = $featuredImageID;
                                        $postMeta->update();
                                    }
                                    else {
                                        $this->postMeta->create( [
                                            'post_id' => $currentPost->id,
                                            'language_id' => $currentPost->language_id,
                                            'meta_name' => '_post_image',
                                            'meta_value' => $featuredImageID,
                                        ] );
                                    }
                                }
                            }

                            //#! Set the $link back meta
                            if ( !empty( $linkBack ) ) {
                                $postMeta = $this->postMeta->where( 'post_id', $currentPost->id )
                                    ->where( 'language_id', $currentPost->language_id )
                                    ->where( 'meta_name', '_link_back' )
                                    ->first();
                                if ( $postMeta ) {
                                    $postMeta->meta_value = $linkBack;
                                    $postMeta->update();
                                }
                                else {
                                    $this->postMeta->create( [
                                        'post_id' => $currentPost->id,
                                        'language_id' => $currentPost->language_id,
                                        'meta_name' => '_link_back',
                                        'meta_value' => $linkBack,
                                    ] );
                                }
                            }

                            //#! Set the video meta
                            if ( !empty( $videoUrl ) ) {
                                $postMeta = $this->postMeta->where( 'post_id', $currentPost->id )
                                    ->where( 'language_id', $currentPost->language_id )
                                    ->where( 'meta_name', '_video_url' )
                                    ->first();
                                if ( $postMeta ) {
                                    $postMeta->meta_value = $videoUrl;
                                    $postMeta->update();
                                }
                                else {
                                    $this->postMeta->create( [
                                        'post_id' => $currentPost->id,
                                        'language_id' => $currentPost->language_id,
                                        'meta_name' => '_video_url',
                                        'meta_value' => $videoUrl,
                                    ] );
                                }
                            }

                            //#! Publish post
                            $currentPost->post_status_id = $this->publishPostStatus->id;
                            $currentPost->update();

                            //# Last
                            do_action( 'contentpress/post_new', $currentPost );
                        }
                    }
                }
            }
            catch ( \Exception $e ) {
                logger( 'Error processing feed: ' . $feedUrl . '. Error: ' . $e->getMessage() );
            }
        }
    }

    /**
     * Retrieve the ID of the specified category. Attempts to create it if it doesn't exist.
     * @param string $categoryName
     * @param null $parentID
     * @return false|int category ID on success, boolean false otherwise
     */
    private function __getCreateCategoryID( string $categoryName, int $parentID = null )
    {
        $categoryName = wp_kses( $categoryName, [] );

        $catTitle = Str::title( $categoryName );

        $category = Category::where( 'name', $catTitle )
            ->where( 'category_id', $parentID )
            ->where( 'post_type_id', $this->postType->id )
            ->where( 'language_id', $this->languageID )
            ->first();
        if ( $category ) {
            return $category->id;
        }

        $slug = Str::slug( $categoryName );
        if ( !Util::isUniqueCategorySlug( $slug, $this->languageID, $this->postType->id ) ) {
            usleep( 250 );
            $slug = Str::slug( Category::find( $parentID )->name . '-' . $categoryName );
        }
        $r = Category::create( [
            'name' => $catTitle,
            'slug' => $slug,
            'description' => '',
            'language_id' => $this->languageID,
            'post_type_id' => $this->postType->id,
            'category_id' => ( empty( $parentID ) ? null : $parentID ),
        ] );
        return ( $r ? $r->id : false );
    }

    /**
     * Retrieve the ID for the specified tag name. Attempts to create it if it doesn't exist.
     * @param string $name
     * @return false|int The tag id on success, false otherwise
     */
    private function __getCreateTagID( $name )
    {
        $name = wp_kses( $name, [] );
        $slug = Str::slug( $name );
        if ( !Util::isUniqueTagSlug( $slug, $this->languageID, $this->postType->id ) ) {
            return Tag::where( 'slug', $slug )->first()->id;
        }

        $r = Tag::create( [
            'name' => $name,
            'slug' => $slug,
            'language_id' => $this->languageID,
            'post_type_id' => $this->postType->id,
        ] );
        return ( $r ? $r->id : false );
    }

    /**
     * Insert a post
     * @param array $postData
     * @param $postStatusID
     * @return int|false|Post
     */
    private function __insertPost( array $postData, $postStatusID )
    {
        $title = wp_kses_post( $postData[ 'title' ] );
        $post_slug = Str::slug( $title );
        if ( !Util::isUniquePostSlug( $post_slug ) ) {
            return false;
        }
        $content = wp_kses_post( $postData[ 'content' ] );
        $excerpt = substr( wp_kses( $postData[ 'content' ], [] ), 0, 180 );
        $r = Post::create( [
            'title' => $title,
            'slug' => $post_slug,
            'content' => $content,
            'excerpt' => $excerpt,
            'user_id' => $this->currentUserID,
            'language_id' => $this->languageID,
            'post_type_id' => $this->postType->id,
            'post_status_id' => $postStatusID,
        ] );

        return ( $r ? $r : false );
    }

    /**
     * Import an image locally
     * @param string $imageUrl
     * @return int|false The ID of the attachment on success, false otherwise
     */
    private function __importImage( string $imageUrl )
    {
        $extension = pathinfo( $imageUrl, PATHINFO_EXTENSION );
        $fn = md5( basename( $imageUrl ) );

        //#! If the file already exists
        $slug = Str::slug( $fn );
        $entry = $this->mediaFile->where( 'slug', $slug )->first();
        if ( $entry ) {
            return $entry->id;
        }

        //#! Download & import the file
        $fileData = file_get_contents( $imageUrl );
        if ( !empty( $fileData ) ) {
            //#! Year /month/day since we're importing lots of feeds and the number of images
            //#! might get gigantic over a month period. This avoids reaching the max number of files
            //#! per directory
            $subdirs = date( 'Y' ) . '/' . date( 'n' ) . '/' . date( 'j' );
            $saveDirPath = public_path( "uploads/files/{$subdirs}" );
            if ( !File::isDirectory( $saveDirPath ) ) {
                File::makeDirectory( $saveDirPath, 0777, true );
            }

            $saveFilePath = "{$saveDirPath}/{$fn}.{$extension}";
            file_put_contents( $saveFilePath, $fileData );
            if ( !File::isFile( $saveFilePath ) ) {
                return false;
            }

            $r = $this->mediaFile->create( [
                'slug' => $slug,
                'path' => $subdirs . '/' . "{$fn}.{$extension}",
                'language_id' => $this->languageID,
            ] );
            return ( $r ? $r->id : false );
        }
        return false;
    }
}
