<?php
namespace Database\Seeders;


use App\Models\CommentStatuses;
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
            'display_name' => 'Pending moderation',
        ] );
        CommentStatuses::create( [
            'name' => 'spam',
            'display_name' => 'Spam',
        ] );
        CommentStatuses::create( [
            'name' => 'approve',
            'display_name' => 'Approve',
        ] );
    }
}
