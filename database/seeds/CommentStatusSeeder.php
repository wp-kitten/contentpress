<?php

use App\CommentStatuses;
use Illuminate\Database\Seeder;

class CommentStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        CommentStatuses::create( [
            'name' => 'pending',
            'display_name' => __( 'a.Pending moderation' ),
        ] );
        CommentStatuses::create( [
            'name' => 'spam',
            'display_name' => __( 'a.Spam' ),
        ] );
        CommentStatuses::create( [
            'name' => 'approve',
            'display_name' => __( 'a.Approve' ),
        ] );
    }
}
