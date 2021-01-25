<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSignupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('signups', function (Blueprint $table) {
            $table->increments('id')->comment('Primary key.');
            $table
                ->integer('event_id')
                ->unsigned()
                ->index()
                ->comment('The event ID');
            $table
                ->foreign('event_id')
                ->references('id')
                ->on('events');
            $table
                ->string('northstar_id')
                ->index()
                ->comment('The users Northstar ID');
            $table
                ->integer('campaign_id')
                ->index()
                ->comment('The campaign node id the event happened on');
            $table
                ->integer('campaign_run_id')
                ->index()
                ->comment('The campaign run node id the event happened on.');
            $table
                ->integer('quantity')
                ->nullable()
                ->comment('Overall approved quantity the user submits.');
            $table
                ->text('quantity_pending')
                ->nullable()
                ->comment('An unapproved quantity value.');
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
        Schema::table('signups', function (Blueprint $table) {
            Schema::drop('signups');
        });
    }
}
