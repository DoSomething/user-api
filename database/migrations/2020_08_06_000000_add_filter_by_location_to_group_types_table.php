<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFilterByLocationToGroupTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('group_types', function (Blueprint $table) {
            $table
                ->boolean('filter_by_location')
                ->default(false)
                ->after('name')
                ->comment(
                    'Whether or not group finders for this group type should filter by location.',
                );
        });

        DB::statement(
            'UPDATE group_types SET filter_by_location = filter_by_state',
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('group_types', function (Blueprint $table) {
            $table->dropColumn('filter_by_location');
        });
    }
}
