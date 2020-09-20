<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'feeds', function ( Blueprint $table ) {
            $table->id();
            $table->longText( 'url' );
            $table->string( 'hash', 100 )->unique();
            $table->unsignedBigInteger( 'category_id' )->nullable();
            $table->unsignedBigInteger( 'user_id' );
            $table->timestamps();
            $table->softDeletes();

            $table->foreign( 'category_id' )->references( 'id' )->on( 'categories' )->cascadeOnDelete();
            $table->foreign( 'user_id' )->references( 'id' )->on( 'users' )->cascadeOnDelete();
        } );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists( 'feeds' );
    }
}
