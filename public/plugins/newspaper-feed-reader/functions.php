<?php

use App\Category;
use App\Helpers\CPML;
use App\Http\Controllers\Admin\AdminControllerBase;
use App\Options;
use App\PostType;
use Illuminate\Support\Arr;

if ( !defined( 'NPFR_PLUGIN_DIR_NAME' ) ) {
    exit;
}

/**
 * Check to see whether the import process has started
 * @return bool
 */
function cpfrImportingContent()
{
    //#! Check to see whether or not we're already importing
    $options = ( new Options() );
    $option = $options->where( 'name', NPFR_PROCESS_OPT_NAME )->first();
    if ( $option && $option->value > time() ) {
        return true;
    }
    return false;
}

function cpfrGetTopCategories()
{
    return Category::where( 'category_id', null )
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
function cpfrGetSubCategoriesTree( Category $category )
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

function cpfrGetCategoriesTree()
{
    $categories = cpfrGetTopCategories();
    $out = [];
    if ( !$categories || $categories->count() == 0 ) {
        return $out;
    }
    foreach ( $categories as $category ) {
        $out = cpfrGetSubCategoriesTree( $category );
    }
    return $out;
}

function getAdminBaseController()
{
    return new AdminControllerBase();
}
