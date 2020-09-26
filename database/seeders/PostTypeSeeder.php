<?php
namespace Database\Seeders;


use App\Models\Language;
use App\Models\PostType;
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
            'display_name' => 'Post',
            'plural_name' => 'Posts',
            'language_id' => Language::where( 'code', 'en' )->first()->id,
        ] );
        PostType::create( [
            'name' => 'page',
            'display_name' => 'Page',
            'plural_name' => 'Pages',
            'language_id' => Language::where( 'code', 'en' )->first()->id,
        ] );
    }
}
