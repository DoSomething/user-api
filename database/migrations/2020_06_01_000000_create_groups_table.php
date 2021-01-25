<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->increments('id');
            $table
                ->unsignedInteger('group_type_id')
                ->index()
                ->comment('The group type id the group is for.');
            $table
                ->foreign('group_type_id')
                ->references('id')
                ->on('group_types');
            $table->string('name');
            $table->unsignedInteger('goal')->nullable();
            $table->unique(['group_type_id', 'name']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('groups');
    }
}
