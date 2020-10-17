<?php
namespace Database\Seeders;


use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //#! Super admin user
        User::create( [
            'name' => 'Super Admin',
            'email' => 'super-admin@local.host',
            'email_verified_at' => now(),
            'password' => bcrypt( 'super-admin' ), // password
            'remember_token' => Str::random( 40 ),
            'role_id' => Role::where( 'name', Role::ROLE_SUPER_ADMIN )->first()->id,
            'display_name' => 'Super Admin',
        ] );

        //#! Admin user
        User::create( [
            'name' => 'admin',
            'email' => 'admin@local.host',
            'email_verified_at' => now(),
            'password' => bcrypt( 'admin' ), // password
            'remember_token' => Str::random( 40 ),
            'role_id' => Role::where( 'name', Role::ROLE_ADMIN )->first()->id,
            'display_name' => 'Admin',
        ] );

        //#! Contributor user
        User::create( [
            'name' => 'contributor',
            'email' => 'contributor@local.host',
            'email_verified_at' => now(),
            'password' => bcrypt( 'contributor' ), // password
            'remember_token' => Str::random( 40 ),
            'role_id' => Role::where( 'name', Role::ROLE_CONTRIBUTOR )->first()->id,
            'display_name' => 'Contributor',
        ] );

        //#! Member user
        User::create( [
            'name' => 'member',
            'email' => 'member@local.host',
            'email_verified_at' => now(),
            'password' => bcrypt( 'member' ), // password
            'remember_token' => Str::random( 40 ),
            'role_id' => Role::where( 'name', Role::ROLE_MEMBER )->first()->id,
            'display_name' => 'Member',
        ] );
    }
}
