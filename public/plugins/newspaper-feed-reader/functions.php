<?php

use App\Models\Category;
use App\Helpers\CPML;
use App\Http\Controllers\Admin\AdminControllerBase;
use App\Models\Options;
use App\Models\PostType;
use Illuminate\Support\Arr;

if ( !defined( 'NPFR_PLUGIN_DIR_NAME' ) ) {
    exit;
}

/**
 * Check to see whether the import process has started
 * @return bool
 */
function npfrImportingContent()
{
    //#! Check to see whether or not we're already importing
    $options = ( new Options() );
    $option = $options->where( 'name', NPFR_PROCESS_OPT_NAME )->first();
    return ( $option && $option->value >= time() );
}

/**
 * Retrieve the top categories (categories without parent) excluding the custom ones: Public & Private
 * @return mixed
 */
function npfrGetTopCategories()
{
    $query = Category::where( 'category_id', null );

    $publicCat = npfrGetCategoryPublic();
    $privateCat = npfrGetCategoryPrivate();

    if ( $publicCat && $privateCat ) {
        $query = $query->where( function ( $q ) use ( $publicCat, $privateCat ) {
            return $q->whereNotIn( 'id', [ $publicCat->id, $privateCat->id ] );
        } );
    }

    return $query
        ->where( 'language_id', CPML::getDefaultLanguageID() )
        ->where( 'post_type_id', PostType::where( 'name', 'post' )->first()->id )
        ->orderBy( 'name', 'ASC' )
        ->get();
}

/**
 * Retrieve the subcategories, 1 level deep of the specified $category
 * @param Category $category
 * @return array
 */
function npfrGetSubCategoriesTree( Category $category )
{
    static $out = [];

    if ( !$category ) {
        return $out;
    }

    if ( $subcategories = $category->childrenCategories()->get() ) {
        $out[ $category->id ] = Arr::pluck( $subcategories, 'id' );
    }
    return $out;
}

function npfrGetCategoriesTree()
{
    $categories = npfrGetTopCategories();
    $out = [];
    if ( !$categories || $categories->count() == 0 ) {
        return $out;
    }
    foreach ( $categories as $category ) {
        $out = npfrGetSubCategoriesTree( $category );
    }
    return $out;
}

function npfrGetAdminBaseController()
{
    return new AdminControllerBase();
}

/**
 * Retrieve the Model for the special category: public
 * @return mixed
 */
function npfrGetCategoryPublic()
{
    return Category::where( 'slug', NPFR_CATEGORY_PUBLIC )
        ->where( 'language_id', CPML::getDefaultLanguageID() )
        ->where( 'post_type_id', PostType::where( 'name', 'post' )->first()->id )
        ->first();
}

/**
 * Retrieve the Model for the special category: private
 * @return mixed
 */
function npfrGetCategoryPrivate()
{
    return Category::where( 'slug', NPFR_CATEGORY_PRIVATE )
        ->where( 'language_id', CPML::getDefaultLanguageID() )
        ->where( 'post_type_id', PostType::where( 'name', 'post' )->first()->id )
        ->first();
}
