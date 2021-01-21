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
        Schema::connection('mongodb')->table('tokens', function ($collection) {
            $collection->index('key');
        });

        Schema::connection('mongodb')->table('api_keys', function (
            $collection
        ) {
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
        Schema::connection('mongodb')->table('tokens', function ($collection) {
            $collection->dropIndex('key_1');
        });

        Schema::connection('mongodb')->table('api_keys', function (
            $collection
        ) {
            $collection->dropIndex('api_key_1');
        });
    }
}
