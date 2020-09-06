<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateMenuItemMetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu_item_metas', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger( 'menu_item_id' );
            $table->string( 'meta_name' );
            $table->longText( 'meta_value' )->nullable();

            $table->unique( [ 'menu_item_id', 'meta_name' ] );

            $table->foreign( 'menu_item_id' )->references( 'id' )->on( 'menu_items' )->onDelete( 'cascade' );

            $table->timestamps();
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
        Schema::table( 'menu_item_metas', function ( Blueprint $table ) {
            $table->dropForeign( [ 'menu_item_id' ] );
        } );
        Schema::dropIfExists( 'menu_item_metas' );
        DB::statement( 'SET FOREIGN_KEY_CHECKS=1;' );
    }
}
