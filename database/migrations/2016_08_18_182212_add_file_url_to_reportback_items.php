<?php

use Illuminate\Database\Migrations\Migration;

class AddFileUrlToReportbackItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reportback_items', function ($table) {
            $table->string('file_url')->after('file_id');
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
            $table->dropColumn('file_url');
        });
    }
}
