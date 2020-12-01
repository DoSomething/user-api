<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SwapE164ForMobileField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $collection) {
            $collection->dropIndex('mobile_1');
        });

        $this->renameField('users', 'mobile', '_old_mobile');
        $this->renameField('users', 'e164', 'mobile');

        Schema::table('users', function (Blueprint $collection) {
            $collection->index('mobile', null, null, [
                'sparse' => true,
                'unique' => true,
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $collection) {
            $collection->dropIndex('mobile_1');
        });

        $this->renameField('users', 'mobile', 'e164');
        $this->renameField('users', '_old_mobile', 'mobile');

        Schema::table('users', function (Blueprint $collection) {
            $collection->index('mobile', null, null, [
                'sparse' => true,
                'unique' => true,
            ]);
        });
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
