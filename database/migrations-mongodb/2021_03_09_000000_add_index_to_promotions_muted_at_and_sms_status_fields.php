<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToPromotionsMutedAtAndSmsStatusFields extends Migration
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
                ['promotions_muted_at', 'sms_status'],
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
            $collection->dropIndex('promotions_muted_at_1_sms_status_1');
        });
    }
}
