<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAnonymousColumnToActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('actions', function (Blueprint $table) {
            $table
                ->boolean('anonymous')
                ->after('scholarship_entry')
                ->comment(
                    'Whether or not the post PII should be anonymized via the API.',
                );
            $table->dropColumn('active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('actions', function (Blueprint $table) {
            $table->dropColumn('anonymous');
            $table
                ->boolean('active')
                ->comment('Whether or not the action is active.');
        });
    }
}
