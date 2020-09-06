<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePostTagTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'post_tag', function ( Blueprint $table ) {
            $table->id();
            $table->unsignedBigInteger( 'post_id' );
            $table->unsignedBigInteger( 'tag_id' );

            $table->unique( [ 'post_id', 'tag_id' ] );

            $table->foreign( 'post_id' )->references( 'id' )->on( 'posts' )->onDelete( 'cascade' );
            $table->foreign( 'tag_id' )->references( 'id' )->on( 'tags' )->onDelete( 'cascade' );
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
        Schema::table( 'post_tag', function ( Blueprint $table ) {
            $table->dropForeign( [ 'post_id' ] );
            $table->dropForeign( [ 'tag_id' ] );
        } );
        Schema::dropIfExists( 'post_tag' );
        DB::statement( 'SET FOREIGN_KEY_CHECKS=1;' );
    }
}
