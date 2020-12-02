<?php

use Illuminate\Database\Migrations\Migration;

class RenameMobileStatusField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->renameField('users', 'mobilecommons_status', 'sms_status');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->renameField('users', 'sms_status', 'mobilecommons_status');
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

        // Rename 'mobile_status' to 'mobilecommons_status'.
        $connection
            ->collection($collection)
            ->whereRaw([$old => ['$exists' => true]])
            ->update(['$rename' => [$old => $new]]);
    }
}
