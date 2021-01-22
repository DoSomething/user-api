<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddMoreIndexesToRefreshTokens extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mongodb')->table('refresh_tokens', function (
            Blueprint $collection
        ) {
            $collection->index('user_id');
            $collection->index('client_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mongodb')->table('refresh_tokens', function (
            Blueprint $collection
        ) {
            $collection->dropIndex('user_id_1');
            $collection->dropIndex('client_id_1');
        });
    }
}
