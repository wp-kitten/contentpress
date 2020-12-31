<?php

use App\Helpers\VPML;
use App\Models\Language;

if ( !defined( 'VALPRESS_VERSION' ) ) {
    exit;
}

/**
 * Check to see whether or not the current instance is a multilingual one
 * @return bool
 */
function cp_is_multilingual(): bool
{
    return VPML::isMultilingual();
}

/**
 * Retrieve the classes to display the flag for the specified locale
 * @param string $langCode
 * @return string
 */
function cp_get_flag_class( string $langCode ): string
{
    //#! Since the vendor we use for flags doesn't have a flag for "en"...
    if ( 'en' == $langCode ) {
        $langCode = 'us';
    }
    return ( 'flag-icon flag-icon-' . $langCode );
}

/**
 * Retrieve the Language entry
 * @param int|string $idCode
 * @return mixed
 */
function cp_get_language( $idCode )
{
    return Language::where( 'id', intval( $idCode ) )->orWhere( 'code', esc_html( $idCode ) )->first();
}

/**
 * Retrieve the currently enabled language ID for the user in the backend
 * @return int
 */
function cp_get_backend_user_language_id(): int
{
    $languageCode = VPML::getBackendUserLanguageCode();
    $languageID = ( new Language() )->getID( $languageCode );
    return $languageID;
}

/**
 * Retrieve the currently enabled language ID for the user in the frontend
 * @return int
 */
function cp_get_frontend_user_language_id(): int
{
    $languageCode = VPML::getFrontendLanguageCode();
    $languageID = ( new Language() )->getID( $languageCode );
    return $languageID;
}
