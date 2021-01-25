<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->increments('id');
            $table
                ->string('northstar_id')
                ->index()
                ->comment('The users Northstar ID');
            $table
                ->string('event_type')
                ->index()
                ->comment('the type of event');
            $table
                ->string('submission_type')
                ->index()
                ->comment('Whether the event was User event or an Admin event');
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
        Schema::drop('events');
    }
}
