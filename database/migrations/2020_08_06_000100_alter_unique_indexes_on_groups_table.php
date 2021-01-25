<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUniqueIndexesOnGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('groups', function (Blueprint $table) {
            // We'll stop populating the state field in imports soon, once we drop this field.
            $table->dropUnique(['group_type_id', 'name', 'city', 'state']);
            // Instead we want to make sure location is unique along with group type + name + city.
            $table->unique(['group_type_id', 'name', 'city', 'location']);
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
            $table->dropUnique(['group_type_id', 'name', 'city', 'state']);
            $table->unique(['group_type_id', 'name', 'city', 'location']);
        });
    }
}
