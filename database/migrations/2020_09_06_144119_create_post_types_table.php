<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePostTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'post_types', function ( Blueprint $table ) {
            $table->id();
            $table->string( 'name' );
            $table->string( 'display_name' );
            $table->string( 'plural_name' );
            $table->unsignedBigInteger( 'language_id' )->nullable();
            $table->unsignedBigInteger( 'translated_id' )->nullable();

            $table->unique( [ 'name', 'language_id' ] );

            $table->foreign( 'language_id' )->references( 'id' )->on( 'languages' )->onDelete( 'SET NULL' );
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
        Schema::table( 'post_types', function ( Blueprint $table ) {
            $table->dropForeign( [ 'language_id' ] );
        } );
        Schema::dropIfExists( 'post_types' );
        DB::statement( 'SET FOREIGN_KEY_CHECKS=1;' );
    }
}
