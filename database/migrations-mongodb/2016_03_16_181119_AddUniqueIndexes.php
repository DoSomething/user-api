<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddUniqueIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mongodb')->table('users', function (
            Blueprint $collection
        ) {
            $collection->dropIndex('email_1');
            $collection->index('email', null, null, [
                'sparse' => true,
                'unique' => true,
            ]);

            $collection->dropIndex('mobile_1');
            $collection->index('mobile', null, null, [
                'sparse' => true,
                'unique' => true,
            ]);
        });

        Schema::connection('mongodb')->table('tokens', function (
            Blueprint $collection
        ) {
            $collection->dropIndex('key_1');
            $collection->unique('key');
        });

        Schema::connection('mongodb')->table('api_keys', function (
            Blueprint $collection
        ) {
            $collection->dropIndex('api_key_1');
            $collection->unique('api_key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mongodb')->table('users', function (
            Blueprint $collection
        ) {
            $collection->dropUnique('email_1');
            $collection->index('email');

            $collection->dropUnique('mobile_1');
            $collection->index('mobile');
        });

        Schema::connection('mongodb')->table('tokens', function (
            Blueprint $collection
        ) {
            $collection->dropUnique('key_1');
            $collection->index('key');
        });

        Schema::connection('mongodb')->table('api_keys', function (
            Blueprint $collection
        ) {
            $collection->dropUnique('api_key_1');
            $collection->index('api_key');
        });
    }
}
