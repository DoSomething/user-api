<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMetadataToActions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('actions', function (Blueprint $table) {
            $table->string('action_type')->after('post_type');
            $table->string('time_commitment')->after('action_type');
            $table->boolean('online')->after('anonymous');
            $table->boolean('quiz')->after('online');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('actions', function (Blueprint $table) {
            $table->dropColumn('action_type');
            $table->dropColumn('time_commitment');
            $table->dropColumn('online');
            $table->dropColumn('quiz');
        });
    }
}
