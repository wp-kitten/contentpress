<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMeta extends Model
{
    protected $fillable = [ 'meta_name', 'meta_value', 'language_id', 'user_id' ];

    public $timestamps = false;

    public function user()
    {
        return $this->hasMany( User::class );
    }
}
