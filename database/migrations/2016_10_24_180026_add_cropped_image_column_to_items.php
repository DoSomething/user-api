<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddCroppedImageColumnToItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reportback_items', function (Blueprint $table) {
            $table
                ->string('edited_file_url')
                ->nullable()
                ->after('file_url');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reportback_items', function (Blueprint $table) {
            $table->dropColumn('edited_file_url');
        });
    }
}
