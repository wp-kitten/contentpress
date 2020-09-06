<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePostMetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'post_metas', function ( Blueprint $table ) {
            $table->id();
            $table->unsignedBigInteger( 'post_id' );
            $table->unsignedBigInteger( 'language_id' )->nullable();
            $table->string( 'meta_name' );
            $table->longText( 'meta_value' )->nullable();

            $table->unique( [ 'post_id', 'language_id', 'meta_name' ] );

            $table->foreign( 'post_id' )->references( 'id' )->on( 'posts' )->onDelete( 'cascade' );
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
        Schema::table( 'post_metas', function ( Blueprint $table ) {
            $table->dropForeign( [ 'post_id' ] );
            $table->dropForeign( [ 'language_id' ] );
        } );
        Schema::dropIfExists( 'post_metas' );
        DB::statement( 'SET FOREIGN_KEY_CHECKS=1;' );
    }
}
