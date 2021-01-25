<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropTaggingTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tagging_tags', function ($table) {
            $table->dropForeign('tagging_tags_tag_group_id_foreign');
            $table->dropColumn('tag_group_id');
        });

        Schema::drop('tagging_tags');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('tagging_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug', 255)->index();
            $table->string('name', 255);
            $table->boolean('suggest')->default(false);
            $table
                ->integer('count')
                ->unsigned()
                ->default(0); // count of how many times this tag was used

            $table
                ->integer('tag_group_id')
                ->unsigned()
                ->nullable();
            $table
                ->foreign('tag_group_id')
                ->references('id')
                ->on('tagging_tag_groups');
        });
    }
}
