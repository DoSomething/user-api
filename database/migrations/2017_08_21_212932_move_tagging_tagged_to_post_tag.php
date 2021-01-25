<?php

use App\Models\Post;
use Illuminate\Database\Migrations\Migration;

class MoveTaggingTaggedToPostTag extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tags = DB::table('tagging_tagged')->get();

        foreach ($tags as $tag) {
            $post = Post::find($tag->taggable_id);
            if ($post) {
                $post->tag($tag->tag_name);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('post_tag')->truncate();
    }
}
