<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClubsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clubs', function (Blueprint $table) {
            $table->increments('id');
            $table
                ->string('name', 255)
                ->index()
                ->comment('The name of the club.');
            $table
                ->string('leader_id')
                ->unique()
                ->comment('The Northstar ID of the club leader.');
            $table
                ->string('school_id', 255)
                ->nullable()
                ->comment('The school ID the club is associated with.');
            // This will store a maximum of 6 characters XX-XXX (2-letter country code, hyphen, 2-3 letter region code).
            $table
                ->string('location', 6)
                ->nullable()
                ->comment('The ISO 3166-2 region code for the clubs location.');
            $table
                ->string('city')
                ->nullable()
                ->comment('The city where the club is located.');
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
        Schema::drop('clubs');
    }
}
