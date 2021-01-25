<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActionStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('action_stats', function (Blueprint $table) {
            $table->increments('id');
            $table
                ->unsignedInteger('action_id')
                ->index()
                ->comment('The action id the stat is for.');
            $table
                ->foreign('action_id')
                ->references('id')
                ->on('actions');
            $table
                ->string('school_id', 255)
                ->index()
                ->comment('The school id the stat is for.');
            $table
                ->unsignedInteger('accepted_quantity')
                ->comment(
                    'The sum of accepted post quantity for the school and action.',
                );
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
        Schema::drop('action_stats');
    }
}
