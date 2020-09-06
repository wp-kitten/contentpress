<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostMeta extends Model
{
    protected $fillable = [ 'post_id', 'language_id', 'meta_name', 'meta_value', ];

    public $timestamps = false;

    public function post()
    {
        return $this->belongsTo( Post::class );
    }

}
