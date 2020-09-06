<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddColumnRoleIdToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->nullable();
            $table->foreign( 'role_id' )->references( 'id' )->on( 'roles' )->onDelete( 'SET NULL' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement( 'SET FOREIGN_KEY_CHECKS=0;' );
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign( [ 'role_id' ] );
            $table->dropColumn('role_id');
        });
        DB::statement( 'SET FOREIGN_KEY_CHECKS=1;' );
    }
}
