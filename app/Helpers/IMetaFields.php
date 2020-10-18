<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Model;

interface IMetaFields
{
    const SECTION_USER = 'user';
    const SECTION_POST = 'post';
    const SECTION_CATEGORY = 'category';

    /**
     * Automatically regenerate protected meta fields
     *
     * @param Model $model
     * @param string $fkName
     * @param int $fkValue
     * @param string $section The name of the section the custom field resides
     * @param int $languageID
     */
    public static function generateProtectedMetaFields( Model $model, string $fkName, int $fkValue, string $section = self::SECTION_USER, int $languageID = 0 );

    /**
     * Check to see whether or not a specific meta field is protected
     *
     * @param Model $model
     * @param string $section The name of the section the custom field resides
     * @param string $metaFieldName
     * @return bool
     */
    public static function isProtectedMetaField( Model $model, string $section = self::SECTION_USER, string $metaFieldName = '' );

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
    public static function getInstance( Model $model, string $fkName, int $fkValue, string $customFieldNameOrID, int $languageID = 0, $defaultValue = false );

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
    public static function get( Model $model, string $fkName, int $fkValue, string $customFieldName, int $languageID = 0, $defaultValue = false );

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
    public static function getAll( Model $model, string $fkName, int $fkValue, int $languageID = 0, $defaultValue = false );

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
    public static function add( Model $model, string $fkName, int $fkValue, string $customFieldName, string $customFieldValue = '', int $languageID = 0 );

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
    public static function update( Model $model, int $customFieldID, string $customFieldName, string $customFieldValue = '', int $languageID = 0 );

    /**
     * Delete the specified custom field.
     *
     * @param Model $model The DB Model to be used for retrieval
     * @param int $customFieldID The ID of the custom field to update
     * @return mixed
     */
    public static function delete( Model $model, int $customFieldID );

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
    public static function exists( Model $model, string $fkName, int $fkValue, string $customFieldName, int $languageID = 0 );

    /**
     * Check to see whether or not a given custom field exists by ID
     * @param Model $model
     * @param int $customFieldID The ID of the custom field
     * @return mixed
     */
    public static function is( Model $model, int $customFieldID );
}
