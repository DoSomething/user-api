<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateReportbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reportbacks', function (Blueprint $table) {
            $table->increments('id')->comment('Primary key. Maps to rbid');
            $table
                ->string('northstar_id')
                ->index()
                ->comment('Users northstar id');
            $table
                ->integer('drupal_id')
                ->index()
                ->comment('The phoenix users.uid that reported back.');
            $table
                ->integer('campaign_id')
                ->index()
                ->comment(
                    'The campaign node id that the user has reported back for.',
                );
            $table
                ->integer('campaign_run_id')
                ->index()
                ->nullable()
                ->comment(
                    'The campaign run id that the user reported back for.',
                );
            $table
                ->integer('quantity')
                ->comment(
                    'The quantity of reportback_nouns reportback_verbed.',
                );
            $table
                ->text('why_participated')
                ->nullable()
                ->comment('Why the user participated.');
            $table
                ->integer('num_participants')
                ->nullable()
                ->comment('The number of participants, if applicable.');
            $table
                ->integer('flagged')
                ->nullable()
                ->comment('Whether the Reportback has been flagged.');
            $table
                ->string('flagged_reason')
                ->nullable()
                ->comment('Reason why reportback was flagged.');
            $table
                ->integer('promoted')
                ->nullable()
                ->comment('Whether the Reportback has been promoted.');
            $table
                ->string('promoted_reason')
                ->nullable()
                ->comment('Reason why reportback was promoted.');
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
        Schema::drop('reportbacks');
    }
}
