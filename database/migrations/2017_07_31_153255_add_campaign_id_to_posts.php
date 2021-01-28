<?php

use App\Models\Post;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCampaignIdToPosts extends Migration
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
                ->integer('campaign_id')
                ->after('signup_id')
                ->index()
                ->comment('The campaign node ID for this post.');
        });

        // Copy `campaign_id` from the signups table so we can make more
        // efficient queries against the posts table (without expensive join).
        foreach (Post::with('signup')->cursor() as $post) {
            $campaign_id = $post->signup->campaign_id;
            $post->update(['campaign_id' => $campaign_id]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('campaign_id');
        });
    }
}
