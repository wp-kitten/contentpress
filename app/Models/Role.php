<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'display_name', 'description', 'created_at', 'updated_at',
    ];

    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_ADMIN = 'administrator';
    const ROLE_CONTRIBUTOR = 'contributor';
    const ROLE_MEMBER = 'member';

    public function users()
    {
        return $this->hasMany( User::class );
    }

    public function capabilities()
    {
        return $this->belongsToMany( Capability::class );
    }
}
