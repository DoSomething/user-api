<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->increments('id');
            $table
                ->string('internal_title')
                ->comment('Campaign title used internally.');
            $table
                ->dateTime('start_date')
                ->comment('The start date of the campaign.');
            $table
                ->dateTime('end_date')
                ->comment('The end date of the campaign.');
            $table->timestamps();
            $table->softDeletes();
        });

        // Set autoincrement to 9000 (the last node id in Ashes), in case we want to backfill legacy campaigns into the Rogue database.
        DB::update('ALTER TABLE campaigns AUTO_INCREMENT = 9000;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('campaigns');
    }
}
