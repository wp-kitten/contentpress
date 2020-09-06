<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'posts', function ( Blueprint $table ) {
            $table->id();

            $table->string( 'title' )->nullable();
            $table->string( 'slug' );
            $table->longText( 'content' )->nullable();
            $table->string( 'excerpt' )->nullable();
            $table->unsignedBigInteger( 'translated_post_id' )->nullable();

            $table->integer('is_sticky')->default('0');
            $table->integer('is_featured')->default('0');

            $table->unsignedBigInteger( 'user_id' )->nullable();
            $table->unsignedBigInteger( 'language_id' )->nullable();
            $table->unsignedBigInteger( 'post_type_id' )->nullable();
            $table->unsignedBigInteger( 'post_status_id' )->nullable();

            $table->timestamps();

            $table->unique( [ 'slug', 'post_type_id', 'language_id' ] );

            $table->foreign( 'user_id' )->references( 'id' )->on( 'users' )->onDelete( 'SET NULL' );
            $table->foreign( 'language_id' )->references( 'id' )->on( 'languages' )->onDelete( 'SET NULL' );
            $table->foreign( 'post_type_id' )->references( 'id' )->on( 'post_types' )->onDelete( 'cascade' );
            $table->foreign( 'post_status_id' )->references( 'id' )->on( 'post_statuses' )->onDelete( 'SET NULL' );
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
        Schema::table( 'posts', function ( Blueprint $table ) {
            $table->dropForeign( [ 'user_id' ] );
            $table->dropForeign( [ 'language_id' ] );
            $table->dropForeign( [ 'post_type_id' ] );
            $table->dropForeign( [ 'post_status_id' ] );
        } );
        Schema::dropIfExists( 'posts' );
        DB::statement( 'SET FOREIGN_KEY_CHECKS=1;' );
    }
}
