<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class DropEventIdFromSignups extends Migration
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
            $table->dropIndex('signups_event_id_index');
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
        Schema::table('signups', function (Blueprint $table) {
            $table
                ->integer('event_id')
                ->unsigned()
                ->index()
                ->comment('The event ID');
            $table
                ->foreign('event_id')
                ->references('id')
                ->on('events');
        });
    }
}
