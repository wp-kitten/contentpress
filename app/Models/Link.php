<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    protected $fillable = [ 'title', 'hash', 'url' ];

    public $timestamps = false;

    public function exists( $i )
    {
        return $this
            ->where( 'id', intval( $i ) )
            ->orWhere( 'title', $i )
            ->orWhere( 'hash', $i )
            ->orWhere( 'url', $i )
            ->first();
    }
}
