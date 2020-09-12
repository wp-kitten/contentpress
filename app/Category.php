<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Category extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'slug', 'description', 'category_id', 'post_type_id', 'language_id', 'translated_category_id', 'created_at', 'updated_at',
    ];

    /**
     * ...so all categories eager load their children
     * @var string[]
     */
    protected $with = [ 'categories' ];

    public function exists( $idNameSlug, $get = false )
    {
        $category = $this->where( 'id', intval( $idNameSlug ) )
            ->orWhere( 'name', $idNameSlug )
            ->orWhere( 'slug', $idNameSlug )
            ->first();
        if ( $category && $category->id ) {
            return ( $get ? $category : true );
        }
        return false;
    }

    public function existsWithParent( $idNameSlug, $parentCategoryID, $get = false )
    {
        $category = $this->where( function ( $query ) use ( $idNameSlug, $parentCategoryID ) {
            return $query->where( 'id', intval( $idNameSlug ) )
                ->where( 'category_id', $parentCategoryID );
        } )
            ->orWhere( 'name', $idNameSlug )
            ->orWhere( 'slug', $idNameSlug )
            ->first();
        if ( $category && $category->id ) {
            return ( $get ? $category : true );
        }
        return false;
    }

    public function posts()
    {
        return $this->belongsToMany( Post::class );
    }

    public function category_metas()
    {
        return $this->hasMany( CategoryMeta::class );
    }

    public function language()
    {
        return $this->belongsTo( Language::class );
    }

    public function post_type()
    {
        return $this->belongsTo( PostType::class );
    }

    public function categories()
    {
        return $this->hasMany( Category::class );
    }

    public function childrenCategories()
    {
        return $this->hasMany( Category::class )->with( 'categories' );
    }

    public function parent()
    {
        return $this->belongsTo( Category::class, 'category_id' );
    }

    /**
     * Retrieve all parent categories of the current category. DOES NOT retrieve the top most category though.
     * If that's expected, use parentCategories() instead
     *
     * @return Collection
     * @see parentCategories()
     */
    public function parents()
    {
        $parents = collect( [] );

        $parent = $this->parent;

        while ( !is_null( $parent ) ) {
            $parents->push( $parent );
            $parent = $parent->parent;
        }

        return $parents;
    }

    public function getParentCategory( $category = null )
    {
        if ( !$category ) {
            $category = $this;
        }
        return $category->parent()->first();
    }

    /**
     * Retrieve a collection of all parent categories of the current $category. It includes the top most category
     * @return Collection
     */
    public function parentCategories()
    {
        $out = collect( [] );
        $parent = $this->parent()->first();
        while ( $parent && $parent->id ) {
            $out->push( $parent );
            $parent = $parent->parent()->first();
        }
        return ( $out->count() ? $out->reverse() : $out );
    }
}
