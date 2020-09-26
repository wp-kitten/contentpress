<?php
namespace Database\Seeders;


use App\Models\Role;
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
            'display_name' => 'Super Admin',
        ] );
        Role::create( [
            'name' => ROLE::ROLE_ADMIN,
            'display_name' => 'Admin',
        ] );
        Role::create( [
            'name' => Role::ROLE_CONTRIBUTOR,
            'display_name' => 'Contributor',
        ] );
        Role::create( [
            'name' => Role::ROLE_MEMBER,
            'display_name' => 'Member',
        ] );
    }
}
