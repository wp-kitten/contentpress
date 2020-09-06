<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CategoryMeta extends Model
{
    protected $fillable = [ 'meta_name', 'meta_value', 'language_id', 'category_id' ];

    public $timestamps = false;

    public function category()
    {
        return $this->belongsTo( Category::class );
    }
}
