<?php

use App\CommentStatuses;
use App\Post;
use App\PostComments;
use App\User;
use Illuminate\Database\Seeder;

class PostCommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        PostComments::create([
//            'content' => '<p>Hi, this is a comment.</p><p>To get started with moderating, editing, and deleting comments, please visit the Comments screen under each post type in the dashboard.</p>',
//            'user_id' => User::where('id', '!=', null)->first()->id,
//            'post_id' => Post::where('id', '!=', null)->first()->id,
//            'comment_status_id' => CommentStatuses::where('name', 'pending')->first()->id,
//        ]);
    }
}
