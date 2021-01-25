<?php

namespace App\Repositories;

use App\Models\Action;
use App\Models\Post;
use App\Models\Review;
use App\Models\Signup;
use App\Services\ImageStorage;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Facades\Image;

class PostRepository
{
    /**
     * Image storage service (either disk or S3).
     *
     * @var \App\Services\ImageStorage
     */
    protected $storage;

    /**
     * Create a PostRepository.
     *
     * @param ImageStorage $storage
     */
    public function __construct(ImageStorage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Find a post by post_id and return associated signup and tags.
     *
     * @param int $id
     * @return \App\Models\Post
     */
    public function find($id)
    {
        return Post::with('signup', 'tags')->findOrFail($id);
    }

    /**
     * Create a Post.
     *
     * @param  array $data
     * @param  int $signupId
     * @param  string $authenticatedUserRole
     *
     * @return \App\Models\Post|null
     */
    public function create(
        array $data,
        $signupId,
        $authenticatedUserRole = null
    ) {
        $signup = Signup::find($signupId);

        // Get the action_id either from the payload or the DB.
        if (isset($data['action_id'])) {
            $actionId = $data['action_id'];
            $action = Action::findOrFail($data['action_id']);
        } else {
            $action = Action::where([
                'campaign_id' => $signup->campaign_id,
                'post_type' => $data['type'],
                'name' => $data['action'],
            ])->first();

            if (!$action) {
                info('action_not_found', [
                    'campaign_id' => $signup->campaign_id,
                    'post_type' => $data['type'],
                    'name' => $data['action'],
                ]);

                throw ValidationException::withMessages(
                    array_fill_keys(
                        ['campaign_id', 'post_type', 'name'],
                        'An action with the given fields does not exist.',
                    ),
                );
            }

            $actionId = $action->id;
        }

        if (isset($data['file'])) {
            $fileUrl = $this->storage->put($signup->id, $data['file']);
        } else {
            $fileUrl = null;
        }

        // Create a post.
        $post = new Post([
            'signup_id' => $signup->id,
            'northstar_id' => $signup->northstar_id,
            'campaign_id' => $signup->campaign_id,
            'quantity' => isset($data['quantity']) ? $data['quantity'] : null,
            'hours_spent' => isset($data['hours_spent'])
                ? $data['hours_spent']
                : null,
            'type' => $action->post_type,
            'action' => $action->name,
            'action_id' => $actionId,
            'url' => $fileUrl,
            'text' => isset($data['text']) ? $data['text'] : null,
            'location' => isset($data['location']) ? $data['location'] : null,
            'postal_code' => isset($data['postal_code'])
                ? $data['postal_code']
                : null,
            'school_id' => isset($data['school_id'])
                ? $data['school_id']
                : null,
            'source' => token()->client(),
            'source_details' => isset($data['source_details'])
                ? $data['source_details']
                : null,
            'details' => isset($data['details']) ? $data['details'] : null,
            'referrer_user_id' => isset($data['referrer_user_id'])
                ? $data['referrer_user_id']
                : null,
            'group_id' => isset($data['group_id']) ? $data['group_id'] : null,
        ]);

        // If this is a share-social type post, auto-accept.
        $post->status = $post->type === 'share-social' ? 'accepted' : 'pending';

        $isAdminOrStaff = is_staff_user();

        $hasAdminScope = in_array('admin', token()->scopes());

        // Admin users may provide a source, status, and created_at when uploading a post.
        if ($isAdminOrStaff || $hasAdminScope) {
            // If the admin sets a custom status, set this status.
            if (isset($data['status'])) {
                $post->status = $data['status'];
            }

            $post->source = isset($data['source'])
                ? $data['source']
                : token()->client();

            // If there is a created_at property, fill this in (e.g. if created_at is sent when creating a record with the importer app).
            if (isset($data['created_at'])) {
                $post->created_at = strtotime($data['created_at']);
            }
        }

        $post->save();

        // If a 'why_participated' is provided, set it on the signup:
        if (isset($data['why_participated'])) {
            $signup->update(['why_participated' => $data['why_participated']]);
        }

        return $post;
    }

    /**
     * Update an existing Post.
     *
     * @param \App\Models\Post $post
     * @param array $data
     *
     * @return Post
     */
    public function update($post, $data)
    {
        $post->update($data);

        return $post;
    }

    /**
     * Delete a post and remove the file from s3.
     *
     * @param int $postId
     * @return $post;
     */
    public function destroy($postId)
    {
        $post = Post::findOrFail($postId);

        if ($post->url) {
            $this->storage->delete($post);

            // Set the url of the post to null.
            $post->url = null;
            $post->save();
        }

        // Soft delete the post.
        $post->delete();

        return $post->trashed();
    }

    /**
     * Creates a Review for the Post, then updates Post status and re-aggregates.
     *
     * @param array Post $post
     * @param string $status
     * @param string $comment (optional)
     *
     * @return Post
     */
    public function reviews(Post $post, $status, $comment = null, $admin = null)
    {
        // Create the Review.
        $review = Review::create([
            'signup_id' => $post->signup_id,
            'northstar_id' => $post->northstar_id,
            'admin_northstar_id' => $admin ? $admin : auth()->id(),
            'status' => $status,
            'old_status' => $post->status,
            'comment' => $comment,
            'post_id' => $post->id,
        ]);

        // Update the status on the Post.
        $post->status = $status;
        $post->save();

        // Update the "counter cache" on the Post Campaign:
        $post->campaign->refreshCounts();

        return $post;
    }

    /**
     * Updates a post's tags when added.
     *
     * @param object $post
     * @param string $tag
     *
     * @return
     */
    public function tag(Post $post, $tag)
    {
        // Check to see if the post already has this tag.
        // If so, no need to add again.
        if (!$post->tagNames()->contains($tag)) {
            $post->tag($tag);
        }

        // Return the post object including the tags that are related to it.
        return Post::with('signup', 'tags')->findOrFail($post->id);
    }

    /**
     * Updates a post's tags when deleted.
     *
     * @param object $post
     * @param string $tag
     *
     * @return
     */
    public function untag(Post $post, $tag)
    {
        // If the post already has the tag, delete. Otherwise, don't do anything.
        if ($post->tagNames()->contains($tag)) {
            $post->untag($tag);
        }

        // Return the post object including the tags that are related to it.
        return Post::with('signup', 'tags')->findOrFail($post->id);
    }
}
