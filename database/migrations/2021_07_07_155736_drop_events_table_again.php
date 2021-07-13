<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropEventsTableAgain extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('events');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->increments('id');
            $table->morphs('eventable');
            $table->text('content');

            $table
                ->string('user')
                ->nullable()
                ->index()
                ->comment('Northstar ID of the user who did the action');

            $table->timestamps();
        });
    }
}
