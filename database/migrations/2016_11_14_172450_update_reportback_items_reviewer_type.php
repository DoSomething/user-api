<?php

use Illuminate\Database\Migrations\Migration;

class UpdateReportbackItemsReviewerType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reportback_items', function ($table) {
            $table->string('reviewer')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reportback_items', function ($table) {
            $table
                ->integer('reviewer')
                ->unsigned()
                ->change();
        });
    }
}
