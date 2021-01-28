<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddUniqueIndexToReportbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reportbacks', function (Blueprint $table) {
            $table->unique(
                ['northstar_id', 'campaign_id', 'campaign_run_id'],
                'northstar_id_campaign_run',
            );

            $table->unique(
                ['drupal_id', 'campaign_id', 'campaign_run_id'],
                'drupal_id_campaign_run',
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reportbacks', function (Blueprint $table) {
            $table->dropUnique('northstar_id_campaign_run');
            $table->dropUnique('drupal_id_campaign_run');
        });
    }
}
