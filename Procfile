web: composer warmup && vendor/bin/heroku-php-nginx -C nginx.conf public/

release: php artisan migrate --force --database=mongodb

queue: php artisan queue:work --tries=3 --sleep=5 --queue=$SQS_DEFAULT_QUEUE,$SQS_LOW_PRIORITY_QUEUE
