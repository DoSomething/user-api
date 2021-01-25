<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddLogColumnsToEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            $table
                ->integer('quantity')
                ->nullable()
                ->after('submission_type');
            $table
                ->integer('quantity_pending')
                ->nullable()
                ->after('quantity');
            $table
                ->text('why_participated')
                ->nullable()
                ->after('quantity_pending');
            $table
                ->text('caption')
                ->nullable()
                ->after('why_participated');
            $table
                ->text('status')
                ->nullable()
                ->after('caption');
            $table
                ->text('source')
                ->nullable()
                ->after('status');
            $table
                ->ipAddress('remote_addr')
                ->nullable()
                ->after('source');
            $table
                ->text('reason')
                ->nullable()
                ->after('remote_addr');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('quantity');
            $table->dropColumn('quantity_pending');
            $table->dropColumn('why_participated');
            $table->dropColumn('caption');
            $table->dropColumn('status');
            $table->dropColumn('source');
            $table->dropColumn('remote_addr');
            $table->dropColumn('reason');
        });
    }
}
