<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateReactionReportbackItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reaction_reportback_item', function (Blueprint $table) {
            $table
                ->integer('reaction_id')
                ->unsigned()
                ->index();
            $table
                ->foreign('reaction_id')
                ->references('id')
                ->on('reactions')
                ->onDelete('cascade');
            $table
                ->integer('reportback_item_id')
                ->unsigned()
                ->index();
            $table
                ->foreign('reportback_item_id')
                ->references('id')
                ->on('reportback_items')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('reaction_reportback_item');
    }
}
