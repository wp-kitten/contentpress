<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostType extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'display_name', 'plural_name', 'language_id', 'translated_id',
    ];

    public $timestamps = false;

    public function post()
    {
        return $this->hasOne( Post::class );
    }

    public function categories()
    {
        return $this->hasMany( Category::class );
    }

    public function language()
    {
        return $this->belongsTo( Language::class );
    }

    public function getID( $name )
    {
        $r = $this->where( 'name', $name )->first();
        return ( $r ? $r->id : 0 );
    }
}
