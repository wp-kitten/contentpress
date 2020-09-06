<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCategoryMetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_metas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger( 'category_id' );
            $table->unsignedBigInteger( 'language_id' )->nullable();
            $table->string( 'meta_name' );
            $table->longText( 'meta_value' )->nullable();

            $table->unique( [ 'meta_name', 'category_id', 'language_id' ] );
            $table->foreign( 'category_id' )->references( 'id' )->on( 'categories' )->onDelete( 'cascade' );
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
        Schema::table( 'category_metas', function ( Blueprint $table ) {
            $table->dropForeign( [ 'category_id' ] );
            $table->dropForeign( [ 'language_id' ] );
        } );
        Schema::dropIfExists( 'category_metas' );
        DB::statement( 'SET FOREIGN_KEY_CHECKS=1;' );
    }
}
