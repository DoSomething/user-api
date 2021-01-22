<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddIndexToAuthCodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mongodb')->table('auth_codes', function (
            Blueprint $collection
        ) {
            $collection->unique('code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mongodb')->table('auth_codes', function (
            Blueprint $collection
        ) {
            $collection->dropUnique('code');
        });
    }
}
