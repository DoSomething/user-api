<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class ChangePostableIdAndTypeToReactionableInReactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reactions', function (Blueprint $table) {
            $table->renameColumn('postable_id', 'reactionable_id');
            $table->renameColumn('postable_type', 'reactionable_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reactions', function ($table) {
            $table->renameColumn('reactionable_id', 'postable_id');
            $table->renameColumn('reactionable_type', 'postable_type');
        });
    }
}
