<?php

use Illuminate\Database\Migrations\Migration;

class ChangeGoodPhotoToGoodSubmission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $goodPhoto = DB::table('tags')
            ->where(['tag_name' => 'Good Photo'])
            ->update([
                'tag_name' => 'Good Submission',
                'tag_slug' => 'good-submission',
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $goodPhoto = DB::table('tags')
            ->where(['tag_name' => 'Good Submission'])
            ->update([
                'tag_name' => 'Good Photo',
                'tag_slug' => 'good-photo',
            ]);
    }
}
