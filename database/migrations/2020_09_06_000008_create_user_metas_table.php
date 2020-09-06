<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateUserMetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_metas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger( 'user_id' )->nullable();
            $table->unsignedBigInteger( 'language_id' )->nullable();
            $table->string( 'meta_name' );
            $table->longText( 'meta_value' )->nullable();

            $table->unique( [ 'user_id', 'meta_name', 'language_id' ] );

            $table->foreign( 'user_id' )->references( 'id' )->on( 'users' )->onDelete( 'SET NULL' );
            $table->foreign( 'language_id' )->references( 'id' )->on( 'languages' )->onDelete( 'SET NULL' );
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
        Schema::table( 'user_metas', function ( Blueprint $table ) {
            $table->dropForeign( [ 'user_id' ] );
            $table->dropForeign( [ 'language_id' ] );
        } );
        Schema::dropIfExists( 'user_metas' );
        DB::statement( 'SET FOREIGN_KEY_CHECKS=1;' );
    }
}
