<?php

namespace Northstar\Database;

use Carbon\Carbon;
use Illuminate\Queue\Failed\DatabaseFailedJobProvider;

class MongoFailedJobProvider extends DatabaseFailedJobProvider
{
    /**
     * Log a failed job into storage.
     *
     * @param  string $connection
     * @param  string $queue
     * @param  string $payload
     *
     * @return void
     */
    public function log($connection, $queue, $payload, $exception)
    {
        $failed_at = Carbon::now()->getTimestamp();

        $this->getTable()->insert(compact('connection', 'queue', 'payload', 'failed_at', 'exception'));
    }

    /**
     * Get a list of all of the failed jobs.
     *
     * @return array
     */
    public function all()
    {
        $all = $this->getTable()->orderBy('_id', 'desc')->get()->all();

        $all = array_map(function ($job) {
            return $this->processDatabaseJob($job);
        }, $all);

        return $all;
    }

    /**
     * Get a single failed job.
     *
     * @param  mixed $id
     * @return array
     */
    public function find($id)
    {
        $job = $this->getTable()->find($id);

        return $this->processDatabaseJob($job);
    }

    /**
     * Delete a single failed job from storage.
     *
     * @param  mixed $id
     * @return bool
     */
    public function forget($id)
    {
        return $this->getTable()->where('_id', $id)->delete() > 0;
    }

    /**
     * Cast the return array into an object.
     *
     * @return array|null
     */
    public function processDatabaseJob($job)
    {
        if (! $job) {
            return null;
        }

        // We need to cast this to an object (because jenssegers/mongodb
        // returns query results as an array, and the framework expects an
        // object. We also need to rename '_id' column  to 'id' while
        // maintaining key ordering (since some commands rely on that).
        return (object) [
            'id' => (string) $job['_id'],
            'connection' => $job['connection'],
            'queue' => $job['queue'],
            'payload' => $job['payload'],
            'failed_at' => $job['failed_at'],
            'exception' => $job['exception'],
        ];
    }
}
