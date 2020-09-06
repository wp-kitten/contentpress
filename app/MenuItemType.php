<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MenuItemType extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'slug', 'created_at', 'updated_at',
    ];

}
