<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class DropEventsTable extends Migration
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
            $table->integer('signup_id')->index();
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
            $table->integer('quantity')->nullable();
            $table->integer('quantity_pending')->nullable();
            $table->text('why_participated')->nullable();
            $table->text('caption')->nullable();
            $table->text('status')->nullable();
            $table->text('source')->nullable();
            $table->ipAddress('remote_addr')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }
}
