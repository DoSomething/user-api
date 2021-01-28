<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class IncreaseTokenFieldLength extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('access_token', 2048)->change();
            $table->string('refresh_token', 2048)->change();
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->string('access_token', 2048)->change();
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
            $table->string('access_token', 1024)->change();
            $table->string('refresh_token', 1024)->change();
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->string('access_token', 1024)->change();
        });
    }
}
