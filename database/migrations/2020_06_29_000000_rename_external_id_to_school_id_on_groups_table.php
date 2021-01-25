<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class renameExternalIdToSchoolIdOnGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropIndex(['external_id']);
            $table->dropColumn(['external_id']);
            $table
                ->string('school_id')
                ->nullable()
                ->index()
                ->after('state');
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
            $table->dropIndex(['school_id']);
            $table->dropColumn(['school_id']);
            $table
                ->string('external_id')
                ->nullable()
                ->index()
                ->after('name');
        });
    }
}
