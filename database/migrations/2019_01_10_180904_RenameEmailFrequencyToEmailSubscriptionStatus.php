<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameEmailFrequencyToEmailSubscriptionStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->renameField('users', 'email_frequency', 'email_subscription_status');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->renameField('users', 'email_subscription_status', 'email_frequency');
    }

     /**
     * Rename the given field on any documents in the collection.
     *
     * @param string $collection
     * @param string $old
     * @param string $new
     */
    public function renameField($collection, $old, $new)
    {
        /** @var \Jenssegers\Mongodb\Connection $connection */
        $connection = app('db')->connection('mongodb');

        $connection->collection($collection)
            ->whereRaw([$old => ['$exists' => true]])
            ->update(['$rename' => [$old => $new]]);
    }
}
