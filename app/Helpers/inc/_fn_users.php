<?php
/*
 * This file stores all functions related to users
 */

use App\Helpers\VPML;
use App\Models\User;
use App\Models\UserMeta;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\File;

/**
 * Utility method to check whether or not the current user is allowed to perform a specific action
 * @param string|array $capabilities
 * @param bool $matchAll Whether or not the current user must have all capabilities when provided as an array
 * @return bool
 */
function cp_current_user_can( $capabilities, $matchAll = false ): bool
{
    $user = cp_get_current_user();

    if ( !$user || empty( $capabilities ) ) {
        return false;
    }

    if ( is_string( $capabilities ) ) {
        return $user->can( $capabilities );
    }

    $failed = 0;
    foreach ( $capabilities as $capability ) {
        if ( $matchAll ) {
            if ( !$user->can( $capability ) ) {
                $failed++;
            }
        }
        elseif ( $user->can( $capability ) ) {
            return true;
        }
        else {
            $failed++;
        }
    }
    return empty( $failed );
}

/**
 * Check to see whether or not the specified user is allowed to perform the specified action
 * @param int|User $user
 * @param string $capability
 * @return bool
 */
function cp_user_can( $user, $capability ): bool
{

    if ( $user instanceof User ) {
        return $user->can( $capability );
    }
    $user = User::find( intval( $user ) );
    if ( $user ) {
        return $user->can( $capability );
    }
    return false;
}

/**
 * Retrieve the currently logged in user or null
 * @return Authenticatable|User|null
 */
function cp_get_current_user()
{
    return ( auth()->check() ? auth()->user() : null );
}

/**
 * Retrieve the ID of the current user
 * @return int
 */
function cp_get_current_user_id(): int
{
    if ( $user = cp_get_current_user() ) {
        return $user->id;
    }
    return 0;
}

function cp_is_user_logged_in()
{
    return ( $user = cp_get_current_user() );
}

function cp_get_user_profile_image_url( $userID, $languageID = null ): string
{
    $user = User::find( $userID );
    if ( !$user ) {
        return '';
    }

    $userMeta = $user->user_metas->where( 'meta_name', '_profile_image' );

    if ( empty( $languageID ) ) {
        $languageID = VPML::getDefaultLanguageID();
    }

    $userMeta = $userMeta->where( 'language_id', $languageID )->first();

    if ( !$userMeta ) {
        return '';
    }
    $filePath = path_combine( 'uploads/users', $userID, $languageID ? $languageID : '', $userMeta->meta_value );
    if ( !File::isFile( $filePath ) ) {
        return '';
    }
    return asset( $filePath );
}

function cp_get_user_meta( $meta_name, $userID = null, $languageID = null, $defaultValue = false )
{
    if ( empty( $userID ) ) {
        if ( !cp_is_user_logged_in() ) {
            return false;
        }
        $userID = cp_get_current_user()->getAuthIdentifier();
    }
    $user = User::find( $userID );
    if ( !$user ) {
        return false;
    }

    $meta = UserMeta::where( 'user_id', $userID )->where( 'meta_name', $meta_name );

    if ( !empty( $languageID ) ) {
        $meta = $meta->where( 'language_id', $languageID );
    }

    $meta = $meta->first();

    if ( $meta ) {
        return maybe_unserialize( $meta->meta_value );
    }
    return $defaultValue;
}

function cp_set_user_meta( $meta_name, $meta_value = null, $userID = null, $languageID = null ): bool
{
    if ( !cp_is_user_logged_in() ) {
        return false;
    }

    if ( empty( $userID ) ) {
        $userID = cp_get_current_user()->getAuthIdentifier();
    }
    $user = User::find( $userID );
    if ( !$user ) {
        return false;
    }

    $meta = UserMeta::where( 'user_id', $user->id )
        ->where( 'language_id', $languageID )
        ->where( 'meta_name', $meta_name )
        ->first();

    if ( !$meta ) {
        UserMeta::create( [
            'user_id' => $userID,
            'language_id' => ( $languageID ?? null ),
            'meta_name' => $meta_name,
            'meta_value' => ( is_null( $meta_value ) ? null : maybe_serialize( $meta_value ) ),
        ] );
    }
    else {
        $meta->meta_value = ( is_null( $meta_value ) ? null : maybe_serialize( $meta_value ) );
        $meta->update();
    }
    return true;
}

function cp_get_user_display_name( User $user ): string
{
    if ( empty( $user->display_name ) ) {
        return $user->name;
    }
    return $user->display_name;
}
