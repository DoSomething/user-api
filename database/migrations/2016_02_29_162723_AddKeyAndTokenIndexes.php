<?php

use Illuminate\Database\Migrations\Migration;

class AddKeyAndTokenIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tokens', function ($collection) {
            $collection->index('key');
        });

        Schema::table('api_keys', function ($collection) {
            $collection->index('api_key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tokens', function ($collection) {
            $collection->dropIndex('key_1');
        });

        Schema::table('api_keys', function ($collection) {
            $collection->dropIndex('api_key_1');
        });
    }
}
