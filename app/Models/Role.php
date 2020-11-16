<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

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

    /**
     * Get users assigned to this role
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany( User::class );
    }

    /**
     * Get capabilities assigned to the current role
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function capabilities()
    {
        return $this->belongsToMany( Capability::class );
    }

    /**
     * Check to see whether the specified capability ID is assigned to the current role
     * @param int $capId
     * @return bool
     */
    public function hasCap( int $capId )
    {
        $capIds = Arr::pluck( $this->capabilities, 'id' );
        return in_array( $capId, $capIds );
    }
}
