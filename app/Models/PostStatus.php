<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostStatus extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'display_name'
    ];

    public $timestamps = false;

    public function posts()
    {
        return $this->hasMany( Post::class );
    }

    public function getID( $name )
    {
        $r = $this->where( 'name', $name )->first();
        return ( $r ? $r->id : 0 );
    }
}
