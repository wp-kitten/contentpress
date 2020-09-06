<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateMenuItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'menu_items', function ( Blueprint $table ) {
            $table->id();

            //#! The order in the menu
            $table->unsignedBigInteger( 'menu_order' );

            //#! the id of the item: post id, page id, category id, etc
            $table->unsignedBigInteger( 'ref_item_id' )->nullable();

            //#! The id of the menu this item is part of
            $table->unsignedBigInteger( 'menu_id' );

            //#! self reference FK - to use as parent (like categories)
            $table->unsignedBigInteger( 'menu_item_id' )->nullable();

            //#! The item type the item is part of
            $table->unsignedBigInteger( 'menu_item_type_id' );

            $table->foreign( 'menu_id' )->references( 'id' )->on( 'menus' )->onDelete( 'cascade' );
            $table->foreign( 'menu_item_id' )->references( 'id' )->on( 'menu_items' )->onDelete( 'cascade' );
            $table->foreign( 'menu_item_type_id' )->references( 'id' )->on( 'menu_item_types' )->onDelete( 'cascade' );
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
        Schema::table( 'menu_items', function ( Blueprint $table ) {
            $table->dropForeign( [ 'menu_id' ] );
            $table->dropForeign( [ 'menu_item_id' ] );
            $table->dropForeign( [ 'menu_item_type_id' ] );
        } );
        Schema::dropIfExists( 'menu_items' );
        DB::statement( 'SET FOREIGN_KEY_CHECKS=1;' );
    }
}
