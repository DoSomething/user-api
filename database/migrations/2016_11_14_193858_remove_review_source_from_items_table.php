<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class RemoveReviewSourceFromItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reportback_items', function (Blueprint $table) {
            $table->dropColumn('review_source');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reportback_items', function (Blueprint $table) {
            $table
                ->string('review_source')
                ->nullable()
                ->comment('Source URL which review was submitted from.')
                ->after('reviewer');
        });
    }
}
