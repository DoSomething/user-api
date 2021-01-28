<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class RemoveEventIdFromPhotos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->dropForeign('photos_event_id_foreign');
            $table->dropIndex('photos_event_id_index');
            $table->dropColumn('event_id');

            $table->dropForeign('photos_signup_id_foreign');
            $table->dropIndex('photos_signup_id_index');
            $table->dropColumn('signup_id');

            $table->increments('id')->first();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('photos', function (Blueprint $table) {
            $table
                ->integer('event_id')
                ->unsigned()
                ->nullable()
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
                ->nullable()
                ->index()
                ->comment('The signup this Post references.');
            $table
                ->foreign('signup_id')
                ->references('id')
                ->on('signups')
                ->onDelete('cascade');

            $table->dropColumn('id');
        });
    }
}
