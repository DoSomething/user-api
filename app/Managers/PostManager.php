<?php

namespace App\Managers;

use App\Jobs\CreateCustomerIoEvent;
use App\Jobs\SendPostToCustomerIo;
use App\Jobs\SendReviewedPostToCustomerIo;
use App\Models\Post;
use App\Models\User;
use App\Repositories\PostRepository;
use App\Services\Fastly;

class PostManager
{
    /**
     * The Fastly API client.
     *
     * @var App\Services\Fastly
     */
    protected $fastly;

    /*
     * The post repository.
     *
     * @var App\Repositories\PostRepository;
     */
    protected $repository;

    /**
     * Constructor.
     *
     * @param PostRepository $posts
     * @param Fastly $fastly
     */
    public function __construct(PostRepository $posts, Fastly $fastly)
    {
        $this->repository = $posts;
        $this->fastly = $fastly;
    }

    /**
     * Handles all business logic around creating posts.
     *
     * @param array $data
     * @param int $signupId
     * @param int $shouldSendToCustomerIo
     *
     * @return \App\Models\Post
     */
    public function create($data, $signupId, $shouldSendToCustomerIo = true)
    {
        $post = $this->repository->create($data, $signupId);

        if ($shouldSendToCustomerIo) {
            // Send post event(s) to Customer.io for messaging:
            SendPostToCustomerIo::dispatch($post);
        }

        if ($shouldSendToCustomerIo && $post->referrer_user_id) {
            optional(User::find($post->referrer_user_id), function (
                $referrerUser
            ) use ($post) {
                CreateCustomerIoEvent::dispatch(
                    $referrerUser,
                    'referral_post_created',
                    $post->getReferralPostEventPayload(),
                );
            });
        }

        // Log that a post was created.
        info('post_created', [
            'id' => $post->id,
            'signup_id' => $post->signup_id,
            'post_created_source' => $post->source,
        ]);

        return $post;
    }

    /**
     * Handles all business logic around updating posts.
     *
     * @param \App\Models\Post $post
     * @param array $data
     * @param bool $log
     * @return \App\Models\Post
     */
    public function update($post, $data, $log = true)
    {
        $post = $this->repository->update($post, $data);

        // Send post event(s) to Customer.io for messaging:
        SendPostToCustomerIo::dispatch($post);

        if ($post->referrer_user_id) {
            optional(User::find($post->referrer_user_id), function (
                $referrerUser
            ) use ($post) {
                CreateCustomerIoEvent::dispatch(
                    $referrerUser,
                    'referral_post_updated',
                    $post->getReferralPostEventPayload(),
                );
            });
        }

        if ($log) {
            // Log that a post was updated.
            info('post_updated', [
                'id' => $post->id,
                'signup_id' => $post->signup_id,
            ]);
        }

        return $post;
    }

    /**
     * Handles all business logic around reviewing posts.
     *
     * @param \App\Models\Post $post
     * @param array $data
     * @return \App\Models\Post
     */
    public function review($post, $data, $comment = null, $admin = null)
    {
        $post = $this->repository->reviews($post, $data, $comment, $admin);

        // sending review to customerIo is delayed to ensure users can optionally add tags to rejected or accepted posts
        // if they're added, we want them to be sent along in the payload!
        SendReviewedPostToCustomerIo::dispatch($post)->delay(
            now()->addMinutes(5),
        );

        // Log that a post was reviewed.
        info('post_reviewed', [
            'id' => $post->id,
            'admin_northstar_id' => $admin ? $admin : auth()->id(),
            'status' => $post->status,
        ]);

        return $post;
    }

    /**
     * Handle all business logic around deleting a post.
     *
     * @param int $postId
     * @return bool
     */
    public function destroy(Post $post)
    {
        $trashed = $this->repository->destroy($post->id);

        $this->fastly->purge($post);

        info('post_deleted', ['id' => $post->id]);

        return $trashed;
    }
}
