<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class DropEventIdFromReviews extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign('reviews_event_id_foreign');
            $table->dropIndex('reviews_event_id_index');
            $table->dropColumn('event_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table
                ->integer('event_id')
                ->unsigned()
                ->index()
                ->comment('The event this post corresponds to.');
            $table
                ->foreign('event_id')
                ->references('id')
                ->on('events');
        });
    }
}
