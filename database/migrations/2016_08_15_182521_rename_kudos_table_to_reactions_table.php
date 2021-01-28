<?php

use Illuminate\Database\Migrations\Migration;

class RenameKudosTableToReactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('kudos', 'reactions');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('reactions', 'kudos');
    }
}
