<?php

use App\Language;
use App\PostType;
use Illuminate\Database\Seeder;

class PostTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PostType::create( [
            'name' => 'post',
            'display_name' => __( 'a.Post' ),
            'plural_name' => __( 'a.Posts' ),
            'language_id' => Language::where( 'code', 'en' )->first()->id,
        ] );
        PostType::create( [
            'name' => 'page',
            'display_name' => __( 'a.Page' ),
            'plural_name' => __( 'a.Pages' ),
            'language_id' => Language::where( 'code', 'en' )->first()->id,
        ] );
    }
}
