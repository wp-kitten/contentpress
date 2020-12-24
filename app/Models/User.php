<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Events\UserRegisteredEvent;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'role_id', 'is_blocked', 'display_name',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The event map for the model
     * @var array
     */
    protected $dispatchesEvents = [
        //#! Used to set the default role & meta data upon registration
        'created' => UserRegisteredEvent::class,
    ];

    public function posts()
    {
        return $this->hasMany( Post::class );
    }

    public function role()
    {
        return $this->belongsTo( Role::class );
    }

    public function user_metas()
    {
        return $this->hasMany( UserMeta::class );
    }

    public function post_comments()
    {
        return $this->hasMany( PostComments::class );
    }

    /**
     * Check to see whether or not the current user has a specific role identified by either role ID or name
     * @param null|int $roleID
     * @param null|string $roleName
     * @return bool
     */
    public function hasRole( $roleID = null, $roleName = null )
    {
        if ( !$this->role ) {
            return false;
        }
        if ( empty( $roleID ) && empty( $roleName ) ) {
            return false;
        }
        if ( !empty( $roleID ) ) {
            return ( $this->role->id == $roleID );
        }

        $role = Role::where( 'name', $roleName )->first();
        if ( $role ) {
            return ( $this->role->id == $role->id );
        }
        return false;
    }

    /**
     * @param array|string $roles
     * @return bool
     */
    public function isInRole( $roles = [] )
    {
        if ( empty( $roles ) ) {
            return false;
        }
        if ( is_string( $roles ) ) {
            return ( $this->role->name == $roles );
        }

        foreach ( $roles as $roleName ) {
            if ( ( $this->role->name == $roleName ) ) {
                return true;
            }
        }
        return false;
    }
}
