<?php

use App\PostStatus;
use Illuminate\Database\Seeder;

class PostStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PostStatus::create( [
            'name' => 'autosave',
            'display_name' => 'Autosave',
        ] );
        PostStatus::create( [
            'name' => 'draft',
            'display_name' => 'Draft',
        ] );
        PostStatus::create( [
            'name' => 'publish',
            'display_name' => 'Publish',
        ] );
        PostStatus::create( [
            'name' => 'private',
            'display_name' => 'Private',
        ] );
    }
}
