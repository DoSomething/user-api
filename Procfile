web: composer warmup && bootstrap/qgtunnel vendor/bin/heroku-php-nginx -C nginx.conf public/

release: php artisan migrate --force

queue: bootstrap/qgtunnel php artisan queue:work --tries=3 --sleep=5 --queue=$SQS_DEFAULT_QUEUE,$SQS_LOW_PRIORITY_QUEUE
