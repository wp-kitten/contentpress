<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateMediaFileMetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'media_file_metas', function ( Blueprint $table ) {
            $table->id();
            $table->unsignedBigInteger( 'media_file_id' )->nullable();
            $table->unsignedBigInteger( 'language_id' )->nullable();
            $table->string( 'meta_name' );
            $table->longText( 'meta_value' )->nullable();

            $table->unique( [ 'media_file_id', 'language_id', 'meta_name' ] );

            $table->foreign( 'media_file_id' )->references( 'id' )->on( 'media_files' )->onDelete( 'cascade' );
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
        Schema::table( 'media_file_metas', function ( Blueprint $table ) {
            $table->dropForeign( [ 'language_id' ] );
        } );
        Schema::dropIfExists( 'media_file_metas' );
        DB::statement( 'SET FOREIGN_KEY_CHECKS=1;' );
    }
}
