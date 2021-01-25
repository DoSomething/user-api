<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class addCityStateExternalIdToGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropUnique(['group_type_id', 'name']);
            $table
                ->string('external_id')
                ->nullable()
                ->index()
                ->after('name');
            $table
                ->string('city')
                ->nullable()
                ->after('external_id');
            $table
                ->string('state', 10)
                ->nullable()
                ->after('city');
            $table->index(['group_type_id', 'state']);
            $table->unique(['group_type_id', 'name', 'city', 'state']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropIndex(['group_type_id', 'state']);
            $table->dropUnique(['group_type_id', 'name', 'city', 'state']);
            $table->dropColumn(['external_id', 'city', 'state']);
            $table->unique(['group_type_id', 'name']);
        });
    }
}
