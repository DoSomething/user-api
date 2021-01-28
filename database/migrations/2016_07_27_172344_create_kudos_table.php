<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateKudosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kudos', function (Blueprint $table) {
            $table->increments('id');
            $table
                ->string('northstar_id')
                ->index()
                ->comment('Users northstar id');
            $table
                ->integer('drupal_id')
                ->index()
                ->comment('The phoenix users.uid that applied the kudos.');
            $table
                ->integer('file_id')
                ->index()
                ->comment(
                    'The reportback_file.fid that this kudos applies to.',
                );
            $table
                ->integer('taxonomy_id')
                ->index()
                ->comment('The taxonomy_term.tid that this kudos belongs to.');
            $table->timestamps();

            $table->unique(
                ['file_id', 'drupal_id', 'northstar_id', 'taxonomy_id'],
                'file_drupal_ns_taxonomy_unique',
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
        Schema::drop('kudos');
    }
}
