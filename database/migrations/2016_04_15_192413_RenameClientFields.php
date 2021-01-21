<?php

use Illuminate\Database\Migrations\Migration;
use Jenssegers\Mongodb\Schema\Blueprint;

class RenameClientFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // @NOTE: Renamed app_id & api_key to client_id & client_secret!

        // Add an index for querying by client_id & client_secret
        Schema::connection('mongodb')->table('clients', function (
            Blueprint $collection
        ) {
            $collection->index(['client_id', 'client_secret']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove index for client_id & client_secret
        Schema::connection('mongodb')->table('clients', function (
            Blueprint $collection
        ) {
            $collection->dropIndex(['client_id', 'client_secret']);
        });

        // @NOTE: Renamed client_id & client_secret to app_id & api_key!
    }
}
