<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Capability extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description'
    ];


    public function roles()
    {
        return $this->belongsToMany( Role::class );
    }
}
