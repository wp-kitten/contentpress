<?php

namespace App\Listeners;

use App\Events\UserRegisteredEvent;
use App\Models\Language;
use App\Models\Role;
use App\Models\Settings;
use App\Models\UserMeta;

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

        //#! Update email_verified_at column if registration_verify_email option is disabled
        $settings = new Settings();
        if ( !$settings->getSetting( 'registration_verify_email', false ) ) {
            //#! Update user email_verified_at column
            $user->email_verified_at = now();
            $user->update();
        }
    }
}
