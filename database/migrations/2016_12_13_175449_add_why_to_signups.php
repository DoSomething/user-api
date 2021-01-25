<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddWhyToSignups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('signups', function (Blueprint $table) {
            $table
                ->integer('quantity_pending')
                ->nullable()
                ->comment('An unapproved quantity value.')
                ->change();

            $table
                ->text('why_participated')
                ->nullable()
                ->comment('An unapproved quantity value.')
                ->after('quantity_pending');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('signups', function (Blueprint $table) {
            $table
                ->text('quantity_pending')
                ->nullable()
                ->comment('An unapproved quantity value.')
                ->change();

            $table->dropColumn('why_participated');
        });
    }
}
