<?php

namespace App\Newspaper;

use App\Models\Feed;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    public function feeds()
    {
        return $this->hasMany( Feed::class );
    }
}
