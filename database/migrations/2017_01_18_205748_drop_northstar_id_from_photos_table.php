<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class DropNorthstarIdFromPhotosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->dropIndex('photos_northstar_id_index');
            $table->dropColumn('northstar_id');

            $table->dropColumn('source');

            $table->dropColumn('remote_addr');
        });

        Schema::table('photos', function (Blueprint $table) {
            $table
                ->string('source')
                ->nullable()
                ->after('status')
                ->comment('Source which reportback file was submitted from.');

            $table
                ->ipAddress('remote_addr')
                ->nullable()
                ->after('source')
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
            $table
                ->string('northstar_id')
                ->index()
                ->after('id')
                ->comment('The users Northstar ID');

            $table->dropColumn('source');
            $table->dropColumn('remote_addr');
        });

        Schema::table('photos', function (Blueprint $table) {
            $table
                ->string('source')
                ->nullable()
                ->after('updated_at')
                ->comment('Source which reportback file was submitted from.');

            $table
                ->ipAddress('remote_addr')
                ->nullable()
                ->after('source')
                ->comment(
                    'The IP address of the user that submitted the file.',
                );
        });
    }
}
