FROM php:apache

COPY speedtest-files/backend /var/www/html/backend
COPY speedtest-files/favicon.ico /var/www/html/favicon.ico
COPY speedtest-files/index.html /var/www/html/index.html
COPY speedtest-files/speedtest.js /var/www/html/speedtest.js
COPY speedtest-files/speedtest_worker.js /var/www/html/speedtest_worker.js

RUN chmod 644 /var/www/html/backend/*.php 2>/dev/null || true && \
    chmod 644 /var/www/html/backend/*.phar 2>/dev/null || true && \
    chmod 644 /var/www/html/backend/*.mmdb 2>/dev/null || true

