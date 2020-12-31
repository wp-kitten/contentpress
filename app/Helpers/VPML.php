<?php

namespace App\Helpers;

use App\Models\Category;
use App\Models\Language;
use App\Models\Options;
use App\Models\Post;
use App\Models\PostType;
use App\Models\Settings;
use App\Models\Tag;

/**
 * Class VPML
 * @package App\Helpers
 *
 * Helper class that provides methods to interact with application in a multi language instance
 */
class VPML
{
    public static function getDefaultLanguageCode()
    {
        return ( new Settings() )->getSetting( 'default_language' );
    }

    /**
     * Helper method to retrieve the default language id
     * @return mixed
     */
    public static function getDefaultLanguageID()
    {
        return ( new Language() )->getID( self::getDefaultLanguageCode() );
    }

    /**
     * Check to see if this instance is Multilingual
     * @return bool
     */
    public static function isMultilingual()
    {
        return ( count( self::getLanguages() ) > 1 );
    }

    /**
     * Retrieve all enabled languages
     * @return mixed
     */
    public static function getLanguages()
    {
        return ( new Options() )->getEnabledLanguages();
    }

    /**
     * Retrieve the reference to the model that matches the provided query
     * @param int $translatedPostID
     * @param string|int $languageCodeID The code name or the ID of the language
     * @return mixed
     */
    public static function getTranslatedPost( $translatedPostID, $languageCodeID )
    {
        $languageID = ( is_int( $languageCodeID ) ? $languageCodeID : ( new Language() )->getID( $languageCodeID ) );
        return Post::where( 'translated_post_id', $translatedPostID )->where( 'language_id', $languageID )->first();
    }

    /**
     * Check to see if the specified post has any missing translations
     * @param int $postID
     * @return bool
     */
    public static function postMissingTranslations( $postID )
    {
        $language = new Language();
        $enabledLanguages = self::getLanguages();
        $defaultLanguageID = self::getDefaultLanguageID();

        $result = 0;

        foreach ( $enabledLanguages as $languageCode ) {
            $languageID = $language->getID( $languageCode );
            if ( $defaultLanguageID == $languageID ) {
                continue;
            }

            $translation = self::getTranslatedPost( $postID, $languageCode );

            if ( $translation && !empty( $translation->slug ) ) {
                continue;
            }
            $result++;
        }
        return ( !empty( $result ) );
    }

    public static function getTranslatedCategory( $categoryID, $languageID )
    {
        return Category::where( 'translated_category_id', intval( $categoryID ) )
            ->where( 'language_id', $languageID )
            ->first();
    }

    public static function categoryGetTranslations( $categoryID )
    {
        return Category::where( 'translated_category_id', intval( $categoryID ) )
            ->where( 'language_id', '!=', self::getDefaultLanguageID() )
            ->get();
    }

    public static function categoryMissingTranslations( $categoryID )
    {
        $language = new Language();
        $enabledLanguages = self::getLanguages();
        $defaultLanguageID = self::getDefaultLanguageID();

        $result = 0;

        foreach ( $enabledLanguages as $languageCode ) {
            $languageID = $language->getID( $languageCode );
            if ( $defaultLanguageID == $languageID ) {
                continue;
            }

            $translation = self::getTranslatedCategory( $categoryID, $languageID );

            if ( $translation && $translation->slug ) {
                continue;
            }
            $result++;
        }
        return ( !empty( $result ) );
    }

    public static function tagMissingTranslations( $tagID )
    {
        $language = new Language();
        $enabledLanguages = self::getLanguages();
        $defaultLanguageID = self::getDefaultLanguageID();

        $result = 0;

        foreach ( $enabledLanguages as $languageCode ) {
            $languageID = $language->getID( $languageCode );
            if ( $defaultLanguageID == $languageID ) {
                continue;
            }

            $translation = self::getTranslatedTag( $tagID, $languageID );

            if ( $translation && $translation->slug ) {
                continue;
            }
            $result++;
        }
        return ( !empty( $result ) );
    }

    public static function getTranslatedTag( $categoryID, $languageID )
    {
        return Tag::where( 'translated_tag_id', intval( $categoryID ) )
            ->where( 'language_id', $languageID )
            ->first();
    }

    public static function postTypeGetTranslation( $postTypeID, $languageCode )
    {
        $languageID = ( new Language() )->getID( $languageCode );

        return PostType::where( 'translated_id', intval( $postTypeID ) )
            ->where( 'language_id', $languageID )
            ->first();
    }

    /**
     * Retrieve the current user's selected language code for application's backend
     * @return string
     */
    public static function getBackendUserLanguageCode()
    {
        return cp_get_user_meta( 'backend_user_current_language', null, null, 'en' );
    }

    /**
     * Retrieve the preferred language code of the current user for the frontend part of the application
     * @return string
     */
    public static function getFrontendLanguageCode()
    {
        $defaultLanguageCode = VPML::getDefaultLanguageCode();
        if ( cp_is_user_logged_in() ) {
            return cp_get_user_meta( 'frontend_user_language_code', null, null, $defaultLanguageCode );
        }
        $sessVal = session()->get( 'frontend_user_language_code' );
        return ( empty( $sessVal ) ? $defaultLanguageCode : $sessVal );
    }

    /**
     * Set the frontend language
     * @param string $code The language code
     */
    public static function setFrontendLanguageCode( $code )
    {
        //#! Ensure this is a valid language code
        $entry = Language::where( 'code', wp_kses( $code, [] ) )->first();
        if ( $entry && $entry->id ) {
            app()->setLocale( $code );
            session()->put( 'frontend_user_language_code', $code );
            //#! If authenticated user, update their user meta field
            if ( cp_is_user_logged_in() ) {
                cp_set_user_meta( 'frontend_user_language_code', $code );
            }
        }
    }
}
