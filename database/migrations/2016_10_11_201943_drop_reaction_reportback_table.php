<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class DropReactionReportbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('reaction_reportback');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
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
}
