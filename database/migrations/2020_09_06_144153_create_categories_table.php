<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'categories', function ( Blueprint $table ) {
            $table->id();

            $table->string( 'name' );
            $table->string( 'slug' );
            $table->string( 'description' )->nullable();

            $table->unsignedBigInteger( 'category_id' )->nullable();
            $table->unsignedBigInteger( 'post_type_id' )->default('1');
            $table->unsignedBigInteger( 'language_id' )->nullable();
            $table->unsignedBigInteger( 'translated_category_id' )->nullable();
            $table->timestamps();

            $table->unique( [ 'slug', 'language_id', 'post_type_id' ] );

            $table->foreign( 'category_id' )->references( 'id' )->on( 'categories' )->onDelete('cascade');
            $table->foreign( 'language_id' )->references( 'id' )->on( 'languages' )->onDelete( 'cascade' );
            $table->foreign( 'post_type_id' )->references( 'id' )->on( 'post_types' )->onDelete( 'cascade' );
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
        Schema::table( 'categories', function ( Blueprint $table ) {
            $table->dropForeign( [ 'category_id' ] );
            $table->dropForeign( [ 'language_id' ] );
            $table->dropForeign( [ 'post_type_id' ] );
        } );
        Schema::dropIfExists( 'categories' );
        DB::statement( 'SET FOREIGN_KEY_CHECKS=1;' );
    }
}
