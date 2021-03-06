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

class PostTagged extends Notification implements ShouldQueue
{
    use Queueable;

    /*
     * The admin who tagged this post.
     *
     * @var App\Models\User;
     */
    public $admin;

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
        return (new SlackMessage())
            ->from('DoSomething.org')
            ->image(url('apple-touch-icon-precomposed.png'))
            ->content(
                "{$this->admin->display_name} just tagged this post as '{$this->tag->tag_name}':",
            )
            ->attachment(function ($attachment) {
                $attachment
                    ->color(Arr::random(['#fcd116', '#23b7fb', '#4e2b63']))
                    ->image($this->post->getMediaUrl())
                    ->title(
                        "{$this->post->user->display_name}'s submission for {$this->post->campaign->internal_title}",
                        url("/admin/posts/{$this->post->id}"),
                    )
                    ->fields([
                        'Caption' => Str::limit($this->post->text, 140),
                        'Why Participated' => Str::limit(
                            $this->post->signup->why_participated,
                        ),
                    ]);
            });
    }
}
