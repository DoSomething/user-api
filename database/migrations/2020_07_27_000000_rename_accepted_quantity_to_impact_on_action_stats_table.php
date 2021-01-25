<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class renameAcceptedQuantityToImpactOnActionStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('action_stats', function (Blueprint $table) {
            $table->dropIndex(['action_id', DB::raw('accepted_quantity desc')]);
            $table->renameColumn('accepted_quantity', 'impact');
            $table->index(['action_id', DB::raw('impact desc')]);
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
            $table->dropIndex(['action_id', DB::raw('impact desc')]);
            $table->renameColumn('impact', 'accepted_quantity');
            $table->index(['action_id', DB::raw('accepted_quantity desc')]);
        });
    }
}
