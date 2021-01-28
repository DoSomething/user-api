<?php

use Illuminate\Database\Migrations\Migration;

class RenameClientTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // @NOTE: Renamed "api_keys" table to "clients"!
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // @NOTE: Renamed the "clients" table to "api_keys"!
    }
}
