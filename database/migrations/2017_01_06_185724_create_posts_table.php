<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table
                ->integer('event_id')
                ->unsigned()
                ->index()
                ->comment('The event this post corresponds to.');
            $table
                ->foreign('event_id')
                ->references('id')
                ->on('events')
                ->onDelete('cascade');

            $table
                ->integer('signup_id')
                ->unsigned()
                ->index()
                ->comment('The signup this Post references.');
            $table
                ->foreign('signup_id')
                ->references('id')
                ->on('signups')
                ->onDelete('cascade');

            $table->morphs('postable');
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
        Schema::drop('posts');
    }
}
