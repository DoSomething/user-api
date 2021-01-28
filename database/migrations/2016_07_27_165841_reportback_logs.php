<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class ReportbackLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reportback_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('reportback_id')->unsigned();
            $table
                ->foreign('reportback_id')
                ->references('id')
                ->on('reportbacks')
                ->onDelete('cascade');
            $table
                ->string('northstar_id')
                ->comment('The rogue users.id that updated.');
            $table
                ->integer('drupal_id')
                ->comment('The phoenix users.uid that updated.');
            $table
                ->string('op')
                ->nullable()
                ->comment('Operation performed on the reportback.');
            $table->integer('quantity');
            $table->text('why_participated')->nullable();
            $table
                ->string('files')
                ->nullable()
                ->comment(
                    'Comma separated list of file fids attached to reportback.',
                );
            $table
                ->integer('num_files')
                ->comment('The number of files attached to reportback.');
            $table
                ->ipAddress('remote_addr')
                ->nullable()
                ->comment(
                    'The IP address of the user that submitted the file.',
                );
            $table
                ->text('reason')
                ->nullable()
                ->comment(
                    'The reason the reoportback item was flagged/promoted',
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
        Schema::drop('reportback_logs');
    }
}
