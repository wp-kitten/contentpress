<?php
namespace Database\Seeders;


use App\Models\CommentStatuses;
use App\Helpers\StatsHelper;
use App\Helpers\ThemesManager;
use App\Models\Options;
use App\Models\Post;
use App\Models\PostComments;
use App\Models\PostType;
use App\Models\User;
use Illuminate\Database\Seeder;

class OptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Options::create( [
            'name' => 'enabled_languages',
            'value' => maybe_serialize( [ 'en' ] ),
        ] );

        //#! Set the current theme
        $defaultThemeName = env( 'DEFAULT_THEME_NAME', '' );
        if ( !empty( $defaultThemeName ) ) {
            ThemesManager::getInstance()->saveActiveTheme( $defaultThemeName );
        }

        //#! Number of posts
        //#! Number of approved comments
        //#! Number of pending comments
        //#! Number of spam comments
        //#! Number of users

        $aid = CommentStatuses::where( 'name', 'approve' )->first()->id;
        $pid = CommentStatuses::where( 'name', 'pending' )->first()->id;
        $sid = CommentStatuses::where( 'name', 'spam' )->first()->id;

        $optData = [
            StatsHelper::KEY_POSTS => [
                CURRENT_YEAR => [
                    CURRENT_MONTH_NUM => Post::count(),
                ],
            ],
            StatsHelper::KEY_COMMENTS => [
                CURRENT_YEAR => [
                    CURRENT_MONTH_NUM => PostComments::where( 'comment_status_id', $aid )->count(),
                ],
            ],
            StatsHelper::KEY_COMMENTS_PENDING => [
                CURRENT_YEAR => [
                    CURRENT_MONTH_NUM => PostComments::where( 'comment_status_id', $pid )->count(),
                ],
            ],
            StatsHelper::KEY_SPAM_COMMENTS => [
                CURRENT_YEAR => [
                    CURRENT_MONTH_NUM => PostComments::where( 'comment_status_id', $sid )->count(),
                ],
            ],
            StatsHelper::KEY_USERS => [
                CURRENT_YEAR => [
                    CURRENT_MONTH_NUM => User::count(),
                ],
            ],
        ];

        //#! Statistics
        Options::create( [
            'name' => 'site_stats',
            'value' => maybe_serialize( $optData ),
        ] );

        //#! Post types
        $postTypes = PostType::all();
        if ( $postTypes ) {
            foreach ( $postTypes as $postType ) {
                if ( $postType->name == 'post' ) {
                    Options::create( [
                        'name' => "post_type_{$postType->name}_allow_categories",
                        'value' => 1,
                    ] );
                    Options::create( [
                        'name' => "post_type_{$postType->name}_allow_tags",
                        'value' => 1,
                    ] );
                    Options::create( [
                        'name' => "post_type_{$postType->name}_allow_comments",
                        'value' => 1,
                    ] );
                }
                elseif ( $postType->name == 'page' ) {
                    Options::create( [
                        'name' => "post_type_{$postType->name}_allow_categories",
                        'value' => 0,
                    ] );
                    Options::create( [
                        'name' => "post_type_{$postType->name}_allow_tags",
                        'value' => 0,
                    ] );
                    Options::create( [
                        'name' => "post_type_{$postType->name}_allow_comments",
                        'value' => 1,
                    ] );
                }
            }
        }
    }
}
