<?php
namespace Database\Seeders;


use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call( LanguageSeeder::class );
        $this->call( RoleSeeder::class );
        $this->call( CapabilitiesSeeder::class );
        $this->call( UserSeeder::class );
        $this->call( RoleCapabilitiesSeeder::class );

        $this->call( CommentStatusSeeder::class );
        $this->call( PostStatusSeeder::class );
        $this->call( PostTypeSeeder::class );
        $this->call( MenuItemTypesSeeder::class );

        $this->call( PostSeeder::class );
        $this->call( PostCommentSeeder::class );

        $this->call( OptionsSeeder::class );
        $this->call( SettingsSeeder::class );
    }
}
