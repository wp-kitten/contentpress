<?php

namespace App\Providers;

use App\Models\Capability;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //#! Register user capabilities
        //#! Accessible through auth()->user()->can('__capability__'); or auth()->user()->can(ROLE_NAME);
        try {
            /*
             * Add roles dynamically
             */
            $roles = Role::all();
            foreach ( $roles as $role ) {
                Gate::define( $role->name, function ( User $user ) use ( $role ) {
                    /*
                     * If administrator, the super admin automatically inherits their capabilities
                     */
                    if ( $role->name == Role::ROLE_ADMIN ) {
                        $roles = [ ROLE::ROLE_ADMIN ];
                        if ( $user->can( 'super_admin' ) ) {
                            $roles[] = Role::ROLE_SUPER_ADMIN;
                        }
                        return $user->isInRole( $roles );
                    }
                    return $user->isInRole( $role->name );
                } );
            }

            //#! Dynamically set capabilities per user
            $capabilities = Capability::all();
            foreach ( $capabilities as $capability ) {
                Gate::define( $capability->name, function ( $user ) use ( $capability ) {
                    $cap = $user->role->capabilities()->where( 'name', $capability->name )->first();
                    return ( $cap && $cap->name == $capability->name );
                } );
            }
        }
        catch ( \Exception $e ) {
        }
    }
}
