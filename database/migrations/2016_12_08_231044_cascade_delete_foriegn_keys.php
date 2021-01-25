<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CascadeDeleteForiegnKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('signups', function (Blueprint $table) {
            $table->dropForeign('signups_event_id_foreign');
            $table
                ->foreign('event_id')
                ->references('id')
                ->on('events')
                ->onDelete('cascade');
        });

        Schema::table('reviews', function ($table) {
            $table->dropForeign('reviews_event_id_foreign');
            $table
                ->foreign('event_id')
                ->references('id')
                ->on('events')
                ->onDelete('cascade');

            $table->dropForeign('reviews_signup_id_foreign');
            $table
                ->foreign('signup_id')
                ->references('id')
                ->on('signups')
                ->onDelete('cascade');
        });

        Schema::table('photos', function ($table) {
            $table->dropForeign('photos_event_id_foreign');
            $table
                ->foreign('event_id')
                ->references('id')
                ->on('events')
                ->onDelete('cascade');

            $table->dropForeign('photos_signup_id_foreign');
            $table
                ->foreign('signup_id')
                ->references('id')
                ->on('signups')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('signups', function (Blueprint $table) {
            $table->dropForeign('signups_event_id_foreign');
            $table
                ->foreign('event_id')
                ->references('id')
                ->on('events');
        });

        Schema::table('reviews', function ($table) {
            $table->dropForeign('reviews_event_id_foreign');
            $table
                ->foreign('event_id')
                ->references('id')
                ->on('events');

            $table->dropForeign('reviews_signup_id_foreign');
            $table
                ->foreign('signup_id')
                ->references('id')
                ->on('signups');
        });

        Schema::table('photos', function ($table) {
            $table->dropForeign('photos_event_id_foreign');
            $table
                ->foreign('event_id')
                ->references('id')
                ->on('events');

            $table->dropForeign('photos_signup_id_foreign');
            $table
                ->foreign('signup_id')
                ->references('id')
                ->on('signups');
        });
    }
}
