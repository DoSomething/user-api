<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReferrerUserIdToSignupsAndPostsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('signups', function (Blueprint $table) {
            $table
                ->string('referrer_user_id')
                ->nullable()
                ->index()
                ->after('source_details');
        });
        Schema::table('posts', function (Blueprint $table) {
            $table
                ->string('referrer_user_id')
                ->nullable()
                ->index()
                ->after('details');
            $table->index(['referrer_user_id', 'type']);
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
            $table->dropColumn('referrer_user_id');
        });
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('referrer_user_id');
            $table->dropIndex(['referrer_user_id', 'type']);
        });
    }
}
