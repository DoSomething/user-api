<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class DropReportbacksItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('reportback_logs');

        Schema::drop('reportback_items');

        Schema::drop('reportbacks');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
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
            $table->unique(
                ['northstar_id', 'campaign_id', 'campaign_run_id'],
                'northstar_id_campaign_run',
            );
            $table->unique(
                ['drupal_id', 'campaign_id', 'campaign_run_id'],
                'drupal_id_campaign_run',
            );
        });

        Schema::create('reportback_items', function (Blueprint $table) {
            $table->increments('id');
            $table
                ->integer('reportback_id')
                ->unsigned()
                ->index('reportback_items_reportback_id_foreign');
            $table->string('file_url');
            $table->string('edited_file_url')->nullable();
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
            $table->string('reviewer')->nullable();
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

            $table
                ->foreign('reportback_id')
                ->references('id')
                ->on('reportbacks')
                ->onUpdate('RESTRICT')
                ->onDelete('CASCADE');
        });

        Schema::create('reportback_logs', function (Blueprint $table) {
            $table->increments('id');
            $table
                ->integer('reportback_id')
                ->unsigned()
                ->index('reportback_logs_reportback_id_foreign');
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

            $table
                ->foreign('reportback_id')
                ->references('id')
                ->on('reportbacks')
                ->onUpdate('RESTRICT')
                ->onDelete('CASCADE');
        });
    }
}
