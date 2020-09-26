<?php
namespace Database\Seeders;


use App\Models\CommentStatuses;
use App\Models\Role;
use App\Models\Settings;
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
        $settings = [
            //#! Settings > General
            'default_language' => 'en',
            'default_post_status' => 'draft',
            'user_registration_open' => 0,
            'registration_verify_email' => 0,
            'allow_user_reset_password' => 1,
            'default_user_role' => Role::where( 'name', Role::ROLE_MEMBER )->first()->id,
            'default_comment_status' => CommentStatuses::where( 'name', 'pending' )->first()->id,
            'anyone_can_comment' => false,
            'site_title' => 'ContentPress',
            'site_description' => 'WordPress-like application using the Laravel Framework.',
            'blog_title' => 'Blog',
            'date_format' => 'M j, Y',
            'time_format' => 'G:i',
            //#! Settings > Reading
            'posts_per_page' => 10,
            'comments_per_page' => 10,
            //#! What the frontpage displays: the blog page (blog) or a specific page (page)
            'show_on_front' => 'blog',
        ];

        foreach ( $settings as $name => $value ) {
            Settings::create( [
                'name' => $name,
                'value' => $value,
            ] );
        }
    }
}
