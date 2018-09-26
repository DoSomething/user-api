<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVotingMakeAPlanFieldsToUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function($table) {
            $table->string('voting_plan_status');
            $table->string('voting_plan_method_of_transport');
            $table->string('voting_plan_time_of_day');
            $table->string('voting_plan_attending_with');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function($table) {
            $table->dropColumn('voting_plan_status');
            $table->dropColumn('voting_plan_method_of_transport');
            $table->dropColumn('voting_plan_time_of_day');
            $table->dropColumn('voting_plan_attending_with');
        });
    }
}
