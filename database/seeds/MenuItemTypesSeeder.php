<?php

use App\MenuItemType;
use App\PostType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MenuItemTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //#! Include all post types
        $postTypes = PostType::all();
        if ( $postTypes ) {
            foreach ( $postTypes as $postType ) {
                MenuItemType::create( [
                    'name' => $postType->name,
                    'slug' => Str::slug( $postType->name ),
                ] );
            }
        }

        //#! Category
        MenuItemType::create( [
            'name' => 'category',
            'slug' => 'category',
        ] );

        //#! Custom
        MenuItemType::create( [
            'name' => 'custom',
            'slug' => 'custom',
        ] );
    }
}
