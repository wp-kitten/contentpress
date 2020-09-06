<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePostCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_comments', function (Blueprint $table) {
            $table->id();
            $table->longText('content');
            $table->string('author_name')->nullable();
            $table->string('author_email')->nullable();
            $table->string('author_url')->nullable();
            $table->string('author_ip')->nullable();
            $table->string('user_agent')->nullable();

            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('comment_status_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('comment_id')->nullable();
            $table->timestamps();

            //#! the parent comment id (for threaded comments)
            $table->foreign( 'comment_id' )->references( 'id' )->on( 'post_comments' )->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
            $table->foreign('comment_status_id')->references('id')->on('comment_statuses')->onDelete('cascade');
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
        Schema::table( 'post_comments', function ( Blueprint $table ) {
            $table->dropForeign( [ 'comment_id' ] );
            $table->dropForeign( [ 'user_id' ] );
            $table->dropForeign( [ 'post_id' ] );
            $table->dropForeign( [ 'comment_status_id' ] );
        } );
        Schema::dropIfExists( 'post_comments' );
        DB::statement( 'SET FOREIGN_KEY_CHECKS=1;' );
    }
}
