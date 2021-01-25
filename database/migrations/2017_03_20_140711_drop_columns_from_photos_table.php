<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class DropColumnsFromPhotosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('source');
            $table->dropColumn('remote_addr');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('photos', function (Blueprint $table) {
            $table
                ->string('status')
                ->nullable()
                ->index()
                ->default('pending')
                ->after('caption');
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
}
