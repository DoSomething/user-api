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
        Schema::table('users', function ($collection) {
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
        Schema::table('users', function ($collection) {
            $collection->dropIndex('drupal_id_1');
        });
    }
}
