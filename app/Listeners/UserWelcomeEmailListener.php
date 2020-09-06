<?php

namespace App\Listeners;

use App\Events\UserRegisteredEvent;

class UserWelcomeEmailListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param UserRegisteredEvent $event
     * @return void
     */
    public function handle( UserRegisteredEvent $event )
    {
        $user = $event->user;

        //! Send welcome email
        mail( $user->email, __( 'a.Welcome to :app_name', [ 'app_name' => env( 'APP_NAME' ) ] ), __( 'a.Thank you for registering an account on our website!' ) );
    }
}
