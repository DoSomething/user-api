<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddPostableIdAndTypeToReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table
                ->integer('postable_id')
                ->comment(
                    'Postable Id the of the post that has been reviewed.',
                );
            $table
                ->string('postable_type')
                ->comment('Type of the post that has been reviewed.');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn('postable_id');
            $table->dropColumn('postable_type');
        });
    }
}
