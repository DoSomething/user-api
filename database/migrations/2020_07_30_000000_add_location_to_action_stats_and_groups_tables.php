<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocationToActionStatsAndGroupsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('action_stats', function (Blueprint $table) {
            $table
                ->string('location', 6)
                ->nullable()
                ->after('school_id');
            $table->index(['action_id', 'location']);
        });

        Schema::table('groups', function (Blueprint $table) {
            // We'll deprecate the state column once all GraphQL queries are changed.
            $table
                ->string('location', 6)
                ->nullable()
                ->after('state');
            $table->index(['group_type_id', 'location']);
        });

        DB::statement(
            "UPDATE groups SET location = CONCAT('US-', state) WHERE state IS NOT NULL",
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('action_stats', function (Blueprint $table) {
            $table->dropIndex(['action_id', 'location']);
            $table->dropColumn('location');
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->dropIndex(['group_type_id', 'location']);
            $table->dropColumn('location');
        });
    }
}
