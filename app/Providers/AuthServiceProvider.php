<?php

namespace App\Providers;

use App\Models\Capability;
use App\Models\Role;
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
        //#! Accessible through auth()->user()->can('__capability__');
        try {
            //#! Helper capability to identify an administrator
            Gate::define( 'super_admin', function ( $user ) {
                return $user->isInRole( [ ROLE::ROLE_SUPER_ADMIN ] );
            } );
            Gate::define( 'administrator', function ( $user ) {
                //#! include the super admin if applicable
                $roles = [ ROLE::ROLE_ADMIN ];
                if ( $user->can( 'super_admin' ) ) {
                    $roles[] = Role::ROLE_SUPER_ADMIN;
                }
                return $user->isInRole( $roles );
            } );
            //#! Helper capability to identify a contributor
            Gate::define( 'contributor', function ( $user ) {
                return $user->isInRole( [ ROLE::ROLE_ADMIN ] );
            } );
            //#! Helper capability to identify a member
            Gate::define( 'member', function ( $user ) {
                return $user->isInRole( [ ROLE::ROLE_ADMIN ] );
            } );

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
