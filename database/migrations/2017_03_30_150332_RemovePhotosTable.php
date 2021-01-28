<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class RemovePhotosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('photos');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('photos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('file_url')->comment('The url of the file on s3.');
            $table
                ->string('edited_file_url')
                ->nullable()
                ->comment('The url of the edited file on s3.');
            $table->string('caption')->nullable();
            $table->timestamps();
        });
    }
}
