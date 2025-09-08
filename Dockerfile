FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx \
    supervisor \
    libzip-dev \
    procps \
    net-tools \
    libgmp-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Disable default PHP-FPM service to avoid port conflicts
RUN mv /usr/local/etc/php-fpm.d/zz-docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf.disabled

# Install PHP extensions
RUN docker-php-ext-configure zip \
    && docker-php-ext-install zip pdo_mysql mbstring exif pcntl bcmath gd gmp

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy custom PHP configuration
COPY docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini

# Create required directories
RUN mkdir -p /etc/nginx/sites-available /etc/supervisor/conf.d /var/log/supervisor

# Set up Nginx config
RUN echo 'server { \
    listen 0.0.0.0:8080; \
    index index.php index.html; \
    root /var/www/public; \
    client_max_body_size 100M; \
    client_body_timeout 300s; \
    client_header_timeout 300s; \
    keepalive_timeout 300s; \
    send_timeout 300s; \
    fastcgi_read_timeout 300; \
    proxy_connect_timeout 300; \
    proxy_send_timeout 300; \
    proxy_read_timeout 300; \
    proxy_buffers 16 16k; \
    proxy_buffer_size 16k; \
    location ~ \.php$ { \
        try_files $uri =404; \
        fastcgi_split_path_info ^(.+\.php)(/.+)$; \
        fastcgi_pass 127.0.0.1:9000; \
        fastcgi_index index.php; \
        include fastcgi_params; \
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; \
        fastcgi_param PATH_INFO $fastcgi_path_info; \
        fastcgi_connect_timeout 300; \
        fastcgi_send_timeout 300; \
        fastcgi_read_timeout 300; \
        fastcgi_buffer_size 128k; \
        fastcgi_buffers 4 256k; \
        fastcgi_busy_buffers_size 256k; \
        fastcgi_temp_file_write_size 256k; \
    } \
    location / { \
        try_files $uri $uri/ /index.php?$query_string; \
        gzip_static on; \
    } \
}' > /etc/nginx/sites-available/default

# Remove existing default site config if it exists
RUN rm -f /etc/nginx/sites-enabled/default && \
    ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/

# Set up Supervisor config with necessary settings
RUN echo '[unix_http_server]\n\
file=/var/run/supervisor.sock\n\
chmod=0700\n\
\n\
[supervisord]\n\
nodaemon=true\n\
logfile=/var/log/supervisor/supervisord.log\n\
pidfile=/var/run/supervisord.pid\n\
\n\
[rpcinterface:supervisor]\n\
supervisor.rpcinterface_factory = supervisor.rpcinterface:make_main_rpcinterface\n\
\n\
[supervisorctl]\n\
serverurl=unix:///var/run/supervisor.sock\n\
\n\
[program:php-fpm]\n\
command=/usr/local/sbin/php-fpm -F\n\
autostart=true\n\
autorestart=true\n\
priority=5\n\
\n\
[program:nginx]\n\
command=/usr/sbin/nginx -g "daemon off;"\n\
autostart=true\n\
autorestart=true\n\
priority=10\n\ 
\n\
[program:laravel-worker]\n\
process_name=%(program_name)s_%(process_num)02d\n\
command=php /var/www/artisan queue:work --sleep=3 --tries=3 --max-time=3600\n\
autostart=true\n\
autorestart=true\n\
stopasgroup=true\n\
killasgroup=true\n\
user=www-data\n\
numprocs=2\n\
redirect_stderr=true\n\
stdout_logfile=/var/www/storage/logs/worker.log\n\
stopwaitsecs=3600' > /etc/supervisor/conf.d/supervisord.conf

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY . /var/www

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage

# Install app dependencies
RUN composer install --no-dev --optimize-autoloader

# Expose port 80
EXPOSE 80

# Copy entrypoint script
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Start Supervisor & Run Migrations
CMD ["/entrypoint.sh"]