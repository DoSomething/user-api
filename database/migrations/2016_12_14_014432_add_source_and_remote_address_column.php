<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddSourceAndRemoteAddressColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('photos', function (Blueprint $table) {
            $table
                ->string('source')
                ->nullable()
                ->comment('Source which reportback file was submitted from.');
            $table
                ->ipAddress('remote_addr')
                ->nullable()
                ->comment(
                    'The IP address of the user that submitted the file.',
                );
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
            $table->dropColumn('source');
            $table->dropColumn('remote_addr');
        });
    }
}
