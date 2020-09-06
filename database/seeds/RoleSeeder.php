<?php

use App\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::create( [
            'name' => ROLE::ROLE_SUPER_ADMIN,
        ] );
        Role::create( [
            'name' => ROLE::ROLE_ADMIN,
        ] );
        Role::create( [
            'name' => Role::ROLE_CONTRIBUTOR,
        ] );
        Role::create( [
            'name' => Role::ROLE_MEMBER,
        ] );
    }
}
