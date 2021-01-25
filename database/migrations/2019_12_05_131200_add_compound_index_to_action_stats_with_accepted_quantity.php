<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompoundIndexToActionStatsWithAcceptedQuantity extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('action_stats', function (Blueprint $table) {
            $table->index(['action_id', DB::raw('accepted_quantity desc')]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('action_stats', function (Blueprint $table) {
            $table->dropIndex(['action_id', DB::raw('accepted_quantity desc')]);
        });
    }
}
