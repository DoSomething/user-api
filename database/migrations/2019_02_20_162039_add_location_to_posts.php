<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocationToPosts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            // This will store a maximum of 6 characters XX-XXX (2-letter country code, hyphen, 2-3 letter region code).
            $table
                ->string('location', 6)
                ->nullable()
                ->after('details')
                ->comment(
                    'The ISO 3166-2 region code this was submitted from.',
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
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('location');
        });
    }
}
