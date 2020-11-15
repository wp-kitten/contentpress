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
    }
}
