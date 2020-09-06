<?php

use App\CommentStatuses;
use App\Role;
use App\Settings;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //#! Settings > General
        Settings::create( [
            'name' => 'default_language',
            'value' => 'en',
        ] );
        Settings::create( [
            'name' => 'default_post_status',
            'value' => 'draft',
        ] );
        Settings::create( [
            'name' => 'user_registration_open',
            'value' => 0,
        ] );
        Settings::create( [
            'name' => 'registration_verify_email',
            'value' => 0,
        ] );
        Settings::create( [
            'name' => 'allow_user_reset_password',
            'value' => 1,
        ] );
        Settings::create( [
            'name' => 'default_user_role',
            'value' => Role::where( 'name', Role::ROLE_MEMBER )->first()->id,
        ] );
        Settings::create( [
            'name' => 'default_comment_status',
            'value' => CommentStatuses::where( 'name', 'pending' )->first()->id,
        ] );
        Settings::create( [
            'name' => 'anyone_can_comment',
            'value' => false,
        ] );
        Settings::create( [
            'name' => 'site_title',
            'value' => 'ContentPress',
        ] );
        Settings::create( [
            'name' => 'site_description',
            'value' => 'WordPress-like application using the Laravel Framework.',
        ] );
        Settings::create( [
            'name' => 'blog_title',
            'value' => 'Blog',
        ] );
        Settings::create( [
            'name' => 'date_format',
            'value' => 'M j, Y',
        ] );
        Settings::create( [
            'name' => 'time_format',
            'value' => 'G:i',
        ] );

        //#! Settings > Reading
        Settings::create( [
            'name' => 'posts_per_page',
            'value' => 10,
        ] );
        Settings::create( [
            'name' => 'comments_per_page',
            'value' => 10,
        ] );
        //#! What the frontpage displays: the blog page (blog) or a specific page (page)
        Settings::create( [
            'name' => 'show_on_front',
            'value' => 'blog', // blog or page
        ] );
    }
}
