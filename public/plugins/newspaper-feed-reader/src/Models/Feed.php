<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Feed extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'url', 'hash', 'category_id', 'created_at', 'updated_at',
    ];

    public function category()
    {
        return $this->belongsTo( Category::class );
    }

    public function exists( $url )
    {
        $entry = $this->where( 'url', $url )->first();
        return ( $entry && $entry->id );
    }
}
