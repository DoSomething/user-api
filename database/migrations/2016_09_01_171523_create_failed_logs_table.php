<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFailedLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('failed_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('drupal_id')->comment('The phoenix users.uid.');
            $table->integer('nid');
            $table->integer('quantity');
            $table->text('why_participated')->nullable();
            $table->string('file_url');
            $table->string('caption');
            $table->string('source');
            $table
                ->string('op')
                ->nullable()
                ->comment('Operation performed on the reportback.');
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
        Schema::drop('failed_logs');
    }
}
