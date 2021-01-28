<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class UpdateReactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reactions', function (Blueprint $table) {
            $table->dropColumn('reactionable_id');
            $table->dropColumn('reactionable_type');
            $table
                ->integer('post_id')
                ->after('northstar_id')
                ->unsigned();
            $table
                ->foreign('post_id')
                ->references('id')
                ->on('posts')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reactions', function (Blueprint $table) {
            $table->morphs('reactionable');
            $table->dropForeign('reactions_post_id_foreign');
            $table->dropColumn('post_id');
        });
    }
}
