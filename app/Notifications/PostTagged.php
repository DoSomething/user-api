<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

const USER_AND_REVIEWER_QUERY = '
    query UserAndReviewerQuery($userId: String!, $adminId: String!) {
        user(id: $userId) {
            displayName
        }
        admin: user(id: $adminId) {
            displayName
        }
    }
';

class PostTagged extends Notification implements ShouldQueue
{
    use Queueable;

    /*
     * The admin who tagged this post.
     *
     * @var string;
     */
    public $adminId;

    /*
     * Post Instance
     *
     * @var App\Models\Post;
     */
    public $post;

    /*
     * Tag Instance
     *
     * @var App\Models\Tag;
     */
    public $tag;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Post $post, Tag $tag)
    {
        $this->admin = auth()->user();

        $this->post = $post;

        $this->tag = $tag;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack'];
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\SlackMessage
     */
    public function toSlack($notifiable)
    {
        // TEMPORARY: If we'd previously serialized this job with a
        // string 'adminId', turn this into a full admin user model:
        if ($this->adminId) {
            $this->admin = User::find($this->adminId);
        }

        $userName = $this->post->user->display_name;
        $adminName = $this->admin->display_name;

        return (new SlackMessage())
            ->from('Rogue', ':tonguecat:')
            ->content(
                $adminName .
                    ' just tagged this post as "' .
                    $this->tag->tag_name .
                    '":',
            )
            ->attachment(function ($attachment) use ($userName) {
                $permalink = url("/admin/posts/{$this->post->id}");
                $image = $this->post->getMediaUrl();

                $attachment
                    ->title(
                        $userName .
                            '\'s submission for "' .
                            $this->post->campaign->internal_title .
                            '"',
                        $permalink,
                    )
                    ->fields([
                        'Caption' => Str::limit($this->post->text, 140),
                        'Why Participated' => Str::limit(
                            $this->post->signup->why_participated,
                        ),
                    ])
                    ->color(Arr::random(['#fcd116', '#23b7fb', '#4e2b63']));

                // Do not send images with local URL to Slack (ie: http://rogue.test/images/filename)
                if ($image && config('app.env') !== 'local') {
                    $attachment->image($image);
                }
            });
    }
}
