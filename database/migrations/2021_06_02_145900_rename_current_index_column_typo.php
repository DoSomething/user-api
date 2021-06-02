<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameCurrentIndexColumnTypo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rock_the_vote_reports', function (Blueprint $table) {
            $table->renameColumn('curent_index', 'current_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rock_the_vote_reports', function (Blueprint $table) {
            $table->renameColumn('current_index', 'curent_index');
        });
    }
}
