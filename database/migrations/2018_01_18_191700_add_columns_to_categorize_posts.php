<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToCategorizePosts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table
                ->string('type')
                ->index()
                ->after('northstar_id')
                ->default('photo')
                ->comment(
                    'Describes the type of post submitted e.g. photo, call, voter-reg',
                );
            $table
                ->string('action_bucket')
                ->index()
                ->after('type')
                ->comment(
                    'Describes the bucket the action is tied to. A campaign could ask for multiple types of actions throught the life of the campaign.',
                );
            $table
                ->text('details')
                ->after('remote_addr')
                ->comment('A JSON field to store extra details about a post.');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('action_bucket');
            $table->dropColumn('details');
        });
    }
}
