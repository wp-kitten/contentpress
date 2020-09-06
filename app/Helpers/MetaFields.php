<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

/**
 * Class MetaFields
 *
 * Base class for all Models providing this functionality
 *
 * @package App\Helpers
 */
class MetaFields implements IMetaFields
{
    const SECTION_USER = 'user';
    const SECTION_POST = 'post';
    const SECTION_CATEGORY = 'category';

    /*
    * Required meta fields, these will always be re-created if any of them are deleted
    */
    private static $_protectedMetaFields = [
        'user' => [
            '_website_url',
            '_user_bio',
            '_profile_image',
        ],
        'post' => [
            '_comments_enabled',
            '_post_image',
        ],
        'category' => [
            '_category_image',
        ],
    ];

    /**
     * Automatically regenerate protected meta fields
     *
     * @param Model $model
     * @param string $fkName
     * @param int $fkValue
     * @param string $section The name of the section the custom field resides
     * @param int $languageID
     */
    public static function generateProtectedMetaFields( Model $model, $fkName, $fkValue, $section = self::SECTION_USER, $languageID = 0 )
    {
        $languageID = ( empty( $languageID ) ? CPML::getDefaultLanguageID() : $languageID );

        $metaFields = ( isset( self::$_protectedMetaFields[$section] ) ? self::$_protectedMetaFields[$section] : null );
        if ( ! $metaFields ) {
            return;
        }

        foreach ( $metaFields as $metaName ) {
            $meta = $model->where( $fkName, $fkValue )
                ->where( 'language_id', $languageID )
                ->where( 'meta_name', $metaName )
                ->first();
            if ( ! $meta ) {
                $model->create( [
                    $fkName => $fkValue,
                    'language_id' => $languageID,
                    'meta_name' => $metaName,
                    'meta_value' => '',
                ] );
            }
        }
    }

    /**
     * Check to see whether or not a specific meta field is protected
     *
     * @param Model $model
     * @param string $section The name of the section the custom field resides
     * @param string $metaFieldName
     * @return bool
     */
    public static function isProtectedMetaField( Model $model, $section = self::SECTION_USER, $metaFieldName = '' )
    {
        if ( empty( $metaFieldName ) ) {
            return false;
        }

        return isset( self::$_protectedMetaFields[$section][$metaFieldName] );
    }

    /**
     * Retrieve the reference to the Model associated with the specified fields
     *
     * @param Model $model The DB Model to be used for retrieval
     * @param string $fkName The name of the foreign key to use
     * @param int $fkValue The value for the foreign key to use
     * @param string $customFieldNameOrID The name or the ID of the custom field
     * @param int $languageID The language id. If omitted, the default language will be used
     * @param bool|mixed $defaultValue The value to return if the custom field doesn't exist
     * @return bool|Model
     */
    public static function getInstance( Model $model, $fkName, $fkValue, $customFieldNameOrID, $languageID = 0, $defaultValue = false )
    {
        $languageID = ( empty( $languageID ) ? CPML::getDefaultLanguageID() : $languageID );
        $r = $model->where( $fkName, $fkValue )->where( 'language_id', $languageID )
            ->where( 'meta_name', self::getValidName( $customFieldNameOrID ) )
            ->orWhere( 'id', $customFieldNameOrID )
            ->first();
        return ( $r ? $r : $defaultValue );
    }

    /**
     * Retrieve a specific custom field
     *
     * @param Model $model The DB Model to be used for retrieval
     * @param string $fkName The name of the foreign key to use
     * @param int $fkValue The value for the foreign key to use
     * @param string $customFieldName The name or the ID of the custom field which value to retrieve
     * @param int $languageID The language id. If omitted, the default language will be used
     * @param bool|mixed $defaultValue The value to return if the custom field doesn't exist
     * @return mixed
     */
    public static function get( Model $model, $fkName, $fkValue, $customFieldName, $languageID = 0, $defaultValue = false )
    {
        $languageID = ( empty( $languageID ) ? CPML::getDefaultLanguageID() : $languageID );
        $r = $model->where( $fkName, $fkValue )->where( 'language_id', $languageID )
            ->where( 'meta_name', self::getValidName( $customFieldName ) )
            ->orWhere( 'id', $customFieldName )
            ->first();
        if ( $r ) {
            return $r->meta_value;
        }
        return $defaultValue;
    }

    /**
     * Retrieve all custom fields matching the specified arguments
     *
     * @param Model $model The DB Model to be used for retrieval
     * @param string $fkName The name of the foreign key to use
     * @param int $fkValue The value for the foreign key to use
     * @param int $languageID The language id. If omitted, the default language will be used
     * @param bool|mixed $defaultValue The value to return if the custom field doesn't exist
     * @return mixed
     */
    public static function getAll( Model $model, $fkName, $fkValue, $languageID = 0, $defaultValue = false )
    {
        $languageID = ( empty( $languageID ) ? CPML::getDefaultLanguageID() : $languageID );
        return $model->where( $fkName, $fkValue )->where( 'language_id', $languageID )->get();
    }

    /**
     * Add a new custom field. This method assumes you have checked for existence
     *
     * @param Model $model The DB Model to be used for retrieval
     * @param string $fkName The name of the foreign key to use
     * @param int $fkValue The value for the foreign key to use
     * @param string $customFieldName The name of the custom field
     * @param mixed $customFieldValue The value of the custom field
     * @param int $languageID The language id. If omitted, the default language will be used
     * @return mixed
     */
    public static function add( Model $model, $fkName, $fkValue, $customFieldName, $customFieldValue = '', $languageID = 0 )
    {
        return $model->create( [
            $fkName => $fkValue,
            'language_id' => $languageID,
            'meta_name' => self::getValidName( $customFieldName ),
            'meta_value' => $customFieldValue,
        ] );
    }

    /**
     * Update a custom field. This method assumes you have checked for existence
     *
     * @param Model $model The DB Model to be used for retrieval
     * @param int $customFieldID The ID of the custom field to update
     * @param string $customFieldName The name of the custom field
     * @param mixed $customFieldValue The value of the custom field
     * @param int $languageID The language id. If omitted, the default language will be used
     * @return mixed
     */
    public static function update( Model $model, $customFieldID, $customFieldName, $customFieldValue = '', $languageID = 0 )
    {
        $meta = $model->find( $customFieldID );

        if ( ! $meta ) {
            return false;
        }

        if ( $meta->meta_name != self::getValidName( $customFieldName ) ) {
            $meta->meta_name = self::getValidName( $customFieldName );
        }
        $meta->meta_value = $customFieldValue;
        if ( ! empty( $languageID ) ) {
            $meta->language_id = $languageID;
        }
        return $meta->update();
    }

    /**
     * Delete the specified custom field.
     *
     * @param Model $model The DB Model to be used for retrieval
     * @param int $customFieldID The ID of the custom field to update
     * @return mixed
     */
    public static function delete( Model $model, $customFieldID )
    {
        $meta = $model->find( $customFieldID );

        if ( ! $meta ) {
            return false;
        }

        //# Prevent deleting protected meta fields
        $metaFields = Arr::flatten( self::$_protectedMetaFields );
        if ( in_array( $meta->meta_name, $metaFields ) ) {
            return false;
        }

        return $meta->destroy( $customFieldID );
    }

    /**
     * Check to see whether or not the specified custom field $customFieldName already exists.
     *
     * @param Model $model The DB Model to be used for retrieval
     * @param string $fkName The name of the foreign key to use
     * @param int $fkValue The value for the foreign key to use
     * @param string $customFieldName The name of the custom field
     * @param int $languageID The language id. If omitted, the default language will be used
     * @return bool
     */
    public static function exists( Model $model, $fkName, $fkValue, $customFieldName, $languageID = 0 )
    {
        $meta = self::get( $model, $fkName, $fkValue, $customFieldName, $languageID, false );
        return ( $meta != false );
    }

    /**
     * Check to see whether or not a given custom field exists by ID
     * @param Model $model
     * @param int $customFieldID The ID of the custom field
     * @return mixed
     */
    public static function is( Model $model, $customFieldID )
    {
        return $model->find( $customFieldID );
    }

    /**
     * Helper method used to update the name of the provided meta field to match the naming convention for custom fields
     *
     * @param string $customFieldName The name of the custom field
     * @return string
     */
    final static function getValidName( $customFieldName )
    {
        $customFieldName = preg_replace( '/\s+/', '_', $customFieldName );
        return $customFieldName;
    }
}
