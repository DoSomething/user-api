<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddOauthFieldsToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Update columns on user table
        Schema::table('users', function (Blueprint $table) {
            $table->string('northstar_id')->nullable();
            $table->unique('northstar_id');

            $table->string('access_token', 1024)->nullable();
            $table->integer('access_token_expiration')->nullable();
            $table->string('refresh_token', 1024)->nullable();

            $table->string('role')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('northstar_id');

            $table->dropColumn('access_token');
            $table->dropColumn('access_token_expiration');
            $table->dropColumn('refresh_token');
            $table->dropColumn('role');
        });
    }
}
