<?php

namespace App\Jobs;

use App\Services\CustomerIo;
use App\Services\Gambit;
use App\Services\Rogue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class DeleteUserFromOtherServices implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The user's ID.
     *
     * @var string
     */
    protected $id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // For sanity, we'll rate limit these to 1/s (so we don't risk overloading
        // Customer.io or any of our internal APIs with a deluge of requests).
        Redis::throttle('delete-apis')
            ->allow(1)
            ->every(1)
            ->then(function () {
                app(CustomerIo::class)->deleteUser($this->id);
                app(Gambit::class)->deleteUser($this->id);
                app(Rogue::class)->deleteUser($this->id);
            });
    }
}
