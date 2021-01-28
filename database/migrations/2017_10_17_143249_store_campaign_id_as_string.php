<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class StoreCampaignIdAsString extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('signups', function (Blueprint $table) {
            $table->string('campaign_id')->change();
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->string('campaign_id')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('signups', function (Blueprint $table) {
            $table->integer('campaign_id')->change();
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->integer('campaign_id')->change();
        });
    }
}
