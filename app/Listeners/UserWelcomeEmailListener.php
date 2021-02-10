<?php

namespace App\Listeners;

use App\Events\UserRegisteredEvent;
use App\Models\Options;

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

        sleep( 1 );

        //#! Make sure we only send 1 email since it looks like it's being sent twice..
        //#! This option can be later on deleted, let's say upon user login
        $optionsClass = new Options();
        $optName = "notified-welcome-{$user->id}";
        $opt = $optionsClass->getOption( $optName );
        if ( empty( $opt ) ) {
            //! Send welcome email
            $optionsClass->addOption( $optName, 1 );
            mail( $user->email, __( 'a.Welcome to :app_name', [ 'app_name' => env( 'APP_NAME' ) ] ), __( 'a.Thank you for registering an account on our website!' ) );
        }

    }
}
