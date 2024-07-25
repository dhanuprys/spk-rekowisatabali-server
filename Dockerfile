FROM richarvey/nginx-php-fpm:3.1.6

WORKDIR /var/www/html

# Install alat-alat yang dibutuhkan
RUN apk add --update nodejs npm nano tmux libwebp-dev lib
RUN docker-php-ext-configure gd --with-webp --with-jpeg --with-freetype
RUN docker-php-ext-install -j$(nproc) gd

# Empty nginx conf
RUN rm -rf /etc/nginx && mkdir /etc/nginx

COPY ./.deployment/nginx /etc/nginx
COPY ./.deployment/php /usr/local/etc/php
COPY ./.deployment/supervisord.conf /etc/supervisord.conf
COPY . .

RUN composer install --optimize-autoloader --no-dev
RUN php artisan storage:link
RUN php artisan config:cache \
    && php artisan event:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan icons:cache \
    && php artisan optimize

RUN npm install && npm run build

EXPOSE 80
