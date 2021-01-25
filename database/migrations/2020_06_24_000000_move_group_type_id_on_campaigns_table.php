<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class moveGroupTypeIdOnCampaignsTable extends Migration
{
    /**
     * Run the migration to recreate group_type_id column in correct position.
     *
     * @return void
     */
    public function up()
    {
        $tableName = 'campaigns';
        $columnName = 'group_type_id';

        // Drop foreign index and the column.
        Schema::table($tableName, function ($table) use ($columnName) {
            $table->dropForeign([$columnName]);
            $table->dropColumn($columnName);
        });

        // Recreate the column in the correct position.
        Schema::table($tableName, function (Blueprint $table) use (
            $columnName
        ) {
            $table
                ->unsignedInteger($columnName)
                ->after('secondary_causes')
                ->nullable()
                ->index()
                ->comment('The group type id the group is for.');
            $table
                ->foreign($columnName)
                ->references('id')
                ->on('group_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
