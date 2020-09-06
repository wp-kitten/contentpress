<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'menus', function ( Blueprint $table ) {
            $table->id();

            $table->string( 'name' );
            $table->string( 'slug' );
            $table->unsignedBigInteger( 'language_id' );

            $table->timestamps();

            $table->unique( [ 'slug', 'language_id' ] );

            $table->foreign( 'language_id' )->references( 'id' )->on( 'languages' )->onDelete( 'cascade' );
        } );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement( 'SET FOREIGN_KEY_CHECKS=0;' );
        Schema::table( 'menus', function ( Blueprint $table ) {
            $table->dropForeign( [ 'language_id' ] );
        } );
        Schema::dropIfExists( 'menus' );
        DB::statement( 'SET FOREIGN_KEY_CHECKS=1;' );
    }
}
