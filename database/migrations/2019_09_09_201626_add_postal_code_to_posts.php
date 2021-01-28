<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPostalCodeToPosts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            // This stores the postal code the post was submitted from, if in Australia, Canada (first 3 characters), France,
            // Germany, Italy, Spain, Switzerland, the United Kingdom (first 2-4 characters), or the United States.
            // See: https://docs.fastly.com/vcl/variables/client-geo-postal-code/
            $table
                ->string('postal_code', 10)
                ->nullable()
                ->after('location')
                ->comment('The postal code this was submitted from.');
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
            $table->dropColumn('postal_code');
        });
    }
}
