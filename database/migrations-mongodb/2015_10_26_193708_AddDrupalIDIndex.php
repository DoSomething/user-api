<?php

use Illuminate\Database\Migrations\Migration;

class AddDrupalIDIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mongodb')->table('users', function ($collection) {
            $collection->index('drupal_id', null, null, ['sparse' => true]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mongodb')->table('users', function ($collection) {
            $collection->dropIndex('drupal_id_1');
        });
    }
}
