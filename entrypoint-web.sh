#!/bin/bash

service apache2 start

php /var/www/html/artisan queue:work --daemon &

tail -f /dev/null
