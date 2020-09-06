<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateMediaFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'media_files', function ( Blueprint $table ) {
            $table->id();

            $table->string( 'slug' );
            $table->longText( 'path' );

            $table->string( 'title' )->nullable();
            $table->longText( 'alt' )->nullable();
            $table->longText( 'caption' )->nullable();

            $table->unsignedBigInteger( 'language_id' )->nullable();

            $table->timestamps();

            $table->unique( [ 'slug', 'language_id' ] );

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
        Schema::table( 'media_files', function ( Blueprint $table ) {
            $table->dropForeign( [ 'language_id' ] );
        } );
        Schema::dropIfExists( 'media_files' );
        DB::statement( 'SET FOREIGN_KEY_CHECKS=1;' );
    }
}
