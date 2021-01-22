<?php

use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mongodb')->create('users', function ($collection) {
            /* Database-generated ID */
            //$collection->index('doc_id');

            $collection->text('mobile');

            /* Email address - forced to lowercase */
            $collection->sparse('email');

            /* Mobile phone number */
            $collection->sparse('mobile');

            /* Password */
            $collection->text('password');

            /* Drupal UID */
            $collection->text('drupal_id');

            /* Mailing address */
            $collection->text('addr_street1');
            $collection->text('addr_street2');
            $collection->text('addr_city');
            $collection->text('addr_state');
            $collection->text('addr_zip');

            /* Country */
            $collection->text('country');

            /* Date of birth */
            $collection->text('birthdate');

            /* First name */
            $collection->text('first_name');

            /* Last name */
            $collection->text('last_name');

            /* List of campaign actions */
            $collection->text('campaigns');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mongodb')->drop('users');
    }
}
