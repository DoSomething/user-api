<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddColumnsToPostTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table
                ->string('status')
                ->nullable()
                ->index()
                ->default('pending')
                ->after('postable_type');
            $table
                ->string('source')
                ->nullable()
                ->comment('Source which reportback file was submitted from.')
                ->after('status');
            $table
                ->ipAddress('remote_addr')
                ->nullable()
                ->comment('The IP address of the user that submitted the file.')
                ->after('source');
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
            $table->dropColumn('status');
            $table->dropColumn('source');
            $table->dropColumn('remote_addr');
        });
    }
}
