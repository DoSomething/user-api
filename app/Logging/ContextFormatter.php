<?php

namespace Northstar\Logging;

use Illuminate\Log\Logger;

class ContextFormatter
{
    /**
     * Customize the given logger instance.
     *
     * @param  Logger  $logger
     * @return void
     */
    public function __invoke(Logger $logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->pushProcessor(function ($record) {
                $record['extra']['user_id'] = auth()->id();
                $record['extra']['client_id'] = client_id();
                $record['extra']['request_id'] = request()->header('X-Request-Id');

                return $record;
            });
        }
    }
}
