<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddContentFieldsToPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('postable_id');
            $table->dropColumn('postable_type');
            $table
                ->string('url')
                ->after('northstar_id')
                ->comment('The url of the content');
            $table
                ->string('caption')
                ->after('url')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->morphs('postable');
            $table->dropColumn('url');
            $table->dropColumn('caption');
        });
    }
}
