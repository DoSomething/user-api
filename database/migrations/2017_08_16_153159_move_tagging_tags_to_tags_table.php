<?php

use App\Models\Tag;
use Illuminate\Database\Migrations\Migration;

class MoveTaggingTagsToTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tags = DB::table('tagging_tags')->get();

        foreach ($tags as $tag) {
            // Create new tag
            Tag::create(['tag_name' => $tag->name, 'tag_slug' => $tag->slug]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Truncate the Tags table
        DB::table('tags')->truncate();
    }
}
