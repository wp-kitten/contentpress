<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'slug', 'content', 'excerpt', 'translated_post_id', 'user_id', 'language_id', 'post_type_id', 'post_status_id', 'created_at', 'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo( User::class );
    }

    public function language()
    {
        return $this->belongsTo( Language::class );
    }

    public function post_status()
    {
        return $this->belongsTo( PostStatus::class );
    }

    public function post_type()
    {
        return $this->belongsTo( PostType::class );
    }

    public function categories()
    {
        return $this->belongsToMany( Category::class );
    }

    public function firstCategory()
    {
        return $this->categories()->first();
    }

    public function tags()
    {
        return $this->belongsToMany( Tag::class );
    }

    public function post_metas()
    {
        return $this->hasMany( PostMeta::class );
    }

    public function post_comments()
    {
        return $this->hasMany( PostComments::class );
    }

    public function exists( $idTitleSlug, $get = false )
    {
        $entry = $this->where( 'id', intval( $idTitleSlug ) )
            ->orWhere( 'title', $idTitleSlug )
            ->orWhere( 'slug', $idTitleSlug )
            ->first();
        if ( $entry && $entry->id ) {
            return ( $get ? $entry : true );
        }
        return false;
    }
}
