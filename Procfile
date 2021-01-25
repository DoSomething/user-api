web: composer warmup && vendor/bin/heroku-php-nginx -C nginx.conf public/

release: php artisan migrate --force --path=database/migrations-mongodb --database=mongodb && php artisan migrate --force --path=database/migrations --database=mysql

queue: php artisan queue:work --tries=3 --sleep=5 --queue=$SQS_DEFAULT_QUEUE,$SQS_LOW_PRIORITY_QUEUE
