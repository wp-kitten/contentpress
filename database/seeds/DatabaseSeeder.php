<?php

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
        $this->call( UserMetaSeeder::class );
        $this->call( RoleCapabilitiesSeeder::class );
    }
}
