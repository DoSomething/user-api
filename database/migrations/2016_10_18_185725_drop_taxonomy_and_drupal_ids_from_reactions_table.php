<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class DropTaxonomyAndDrupalIdsFromReactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reactions', function ($table) {
            $table->dropColumn('taxonomy_id');
            $table->dropColumn('drupal_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reactions', function (Blueprint $table) {
            $table->integer('taxonomy_id');
            $table->integer('drupal_id');
        });
    }
}
