<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'slug', 'language_id', 'post_type_id', 'translated_tag_id', 'created_at', 'updated_at',
    ];

    public function exists( $idNameSlug )
    {
        $category = $this->where( 'id', intval( $idNameSlug ) )
            ->orWhere( 'name', $idNameSlug )
            ->orWhere( 'slug', $idNameSlug )
            ->first();

        return ( $category && $category->id );
    }

    public function posts()
    {
        return $this->belongsToMany( Post::class );
    }

    public function language()
    {
        return $this->belongsTo( Language::class );
    }

    public function post_type()
    {
        return $this->belongsTo( PostType::class );
    }
}
