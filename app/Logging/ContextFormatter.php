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
                // This header is set by Heroku's router on all incoming requests.
                $record['extra']['request_id'] = request()->header('X-Request-Id');

                // Without this check, logging or exceptions that occur before the application
                // is fully loaded will result in 'Class auth/session does not exist' errors).
                if (app()->isBooted()) {
                    $record['extra']['user_id'] = auth()->id();
                    $record['extra']['client_id'] = client_id();
                }

                return $record;
            });
        }
    }
}
