<?php

namespace App\Listeners;

use App\Events\UserRegisteredEvent;
use App\Language;
use App\Role;
use App\UserMeta;

class UserRegisteredListener
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

        //#! Set the default user role
        if ( empty( $user->role_id ) ) {
            $user->role_id = Role::where( 'name', Role::ROLE_MEMBER )->first()->id;
            $user->update();
        }

        //#! Add user's default meta data
        $languageID = Language::where( 'code', 'en' )->first()->id;

        if ( !$meta = UserMeta::where( 'meta_name', '_website_url' )->where( 'user_id', $user->id )->first() ) {
            UserMeta::create( [
                'meta_name' => '_website_url',
                'meta_value' => '',
                'user_id' => $user->id,
                'language_id' => $languageID,
            ] );
        }
        if ( !$meta = UserMeta::where( 'meta_name', '_user_bio' )->where( 'user_id', $user->id )->first() ) {
            UserMeta::create( [
                'meta_name' => '_user_bio',
                'meta_value' => '',
                'user_id' => $user->id,
                'language_id' => $languageID,
            ] );
        }
        if ( !$meta = UserMeta::where( 'meta_name', '_profile_image' )->where( 'user_id', $user->id )->first() ) {
            UserMeta::create( [
                'meta_name' => '_profile_image',
                'meta_value' => '',
                'user_id' => $user->id,
                'language_id' => $languageID,
            ] );
        }
    }
}
