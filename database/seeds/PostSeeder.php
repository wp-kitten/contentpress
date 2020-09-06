<?php

use App\Post;
use App\PostStatus;
use App\PostType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        $postContent = '<div class="row clearfix"><div class="column full"><p>Hello! This is a sample paragraph to get you started! You can use the integrated page builder and customize this page the way you want!</p></div></div>';
//
//        $title = "Dummy Post #1";
//        Post::create( [
//            'title' => $title,
//            'slug' => Str::slug( $title ),
//            'content' => $postContent,
//            'excerpt' => '',
//            'user_id' => 1,
//            'language_id' => 1,
//            'post_status_id' => PostStatus::where( 'name', 'publish' )->first()->id,
//            'post_type_id' => PostType::where( 'name', 'post' )->first()->id,
//        ] );
    }
}
