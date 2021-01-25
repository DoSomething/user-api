<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeBucketAndDetailsColumnsNullable extends Migration
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
                ->text('details')
                ->nullable()
                ->change();
            $table->dropColumn('action_bucket');
            $table
                ->string('action')
                ->index()
                ->after('type')
                ->default('default')
                ->comment(
                    'Describes the bucket the action is tied to. A campaign could ask for multiple types of actions throught the life of the campaign.',
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
        Schema::table('posts', function (Blueprint $table) {
            $table
                ->text('details')
                ->nullable(false)
                ->change();
            $table
                ->string('action_bucket')
                ->index()
                ->after('type')
                ->comment(
                    'Describes the bucket the action is tied to. A campaign could ask for multiple types of actions throught the life of the campaign.',
                );
            $table->dropColumn('action');
        });
    }
}
