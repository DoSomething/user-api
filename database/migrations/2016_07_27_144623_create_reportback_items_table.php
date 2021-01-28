<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateReportbackItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reportback_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('reportback_id')->unsigned();
            $table
                ->foreign('reportback_id')
                ->references('id')
                ->on('reportbacks')
                ->onDelete('cascade');
            $table
                ->integer('file_id')
                ->index()
                ->unsigned();
            $table->string('caption')->nullable();
            $table
                ->string('status')
                ->index()
                ->nullable()
                ->default('pending');
            $table
                ->integer('reviewed')
                ->unsigned()
                ->nullable();
            $table
                ->integer('reviewer')
                ->unsigned()
                ->nullable();
            $table
                ->string('review_source')
                ->nullable()
                ->comment('Source URL which review was submitted from.');
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
        Schema::drop('reportback_items');
    }
}
