<?php

namespace App\Newspaper;

use App\Feed;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    public function feeds()
    {
        return $this->hasMany( Feed::class );
    }
}
