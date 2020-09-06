<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCategoryPostTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'category_post', function ( Blueprint $table ) {
            $table->id();
            $table->unsignedBigInteger( 'post_id' );
            $table->unsignedBigInteger( 'category_id' );

            $table->unique( [ 'post_id', 'category_id' ] );

            $table->foreign( 'post_id' )->references( 'id' )->on( 'posts' )->onDelete( 'cascade' );
            $table->foreign( 'category_id' )->references( 'id' )->on( 'categories' )->onDelete( 'cascade' );
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
        Schema::table( 'category_post', function ( Blueprint $table ) {
            $table->dropForeign( [ 'post_id' ] );
            $table->dropForeign( [ 'category_id' ] );
        } );
        Schema::dropIfExists( 'category_post' );
        DB::statement( 'SET FOREIGN_KEY_CHECKS=1;' );
    }
}
