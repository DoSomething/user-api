<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRockTheVoteReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rock_the_vote_reports', function (Blueprint $table) {
            $table->increments('id');
            $table->string('status')->index();
            $table->dateTime('since')->nullable();
            $table->dateTime('before')->nullable();
            $table->integer('row_count')->nullable();
            $table->integer('curent_index')->nullable();
            $table->unsignedInteger('retry_report_id')->nullable();
            $table->string('user_id')->nullable();

            $table->dateTime('dispatched_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'dispatched_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rock_the_vote_reports');
    }
}
