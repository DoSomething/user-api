<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateReactionReportbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reaction_reportback', function (Blueprint $table) {
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
                ->integer('reportback_id')
                ->unsigned()
                ->index();
            $table
                ->foreign('reportback_id')
                ->references('id')
                ->on('reportbacks')
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
        Schema::drop('reaction_reportback');
    }
}
