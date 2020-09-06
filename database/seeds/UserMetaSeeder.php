<?php

use App\User;
use App\UserMeta;
use Illuminate\Database\Seeder;

class UserMetaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //TODO: MOVE THESE TO EVENT USER:CREATED

        //#! Admin
        UserMeta::create( [
            'meta_name' => '_website_url',
            'meta_value' => '',
            'user_id' => 1,
            'language_id' => 1,
        ] );
        UserMeta::create( [
            'meta_name' => '_user_bio',
            'meta_value' => '',
            'user_id' => 1,
            'language_id' => 1,
        ] );
        UserMeta::create( [
            'meta_name' => '_profile_image',
            'meta_value' => '',
            'user_id' => 1,
            'language_id' => 1,
        ] );
    }
}
