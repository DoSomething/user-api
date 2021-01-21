<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToSmsStatusAndTopicsFields extends Migration
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
            $collection->index(
                ['sms_status', 'sms_subscription_topics'],
                null,
                null,
                ['sparse' => true],
            );
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
            $collection->dropIndex('sms_status_1_sms_subscription_topics_1');
        });
    }
}
