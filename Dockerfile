FROM php:7.2-fpm-alpine

ARG ALPINE_MIRROR
ARG TIMEZONE=Asia/Shanghai

COPY src /home/www-data/dbvcs

RUN set -e; \
# Switch to a mirror if given
    if [ -n "$ALPINE_MIRROR" ]; then \
        sed -i 's!http://dl-cdn.alpinelinux.org!'"$ALPINE_MIRROR"'!g' /etc/apk/repositories; \
    fi; \
# Install build dependency packages
    apk update; \
    apk add --virtual .phpize-deps-configure $PHPIZE_DEPS tzdata; \
# Setup timezone
    if [ -n "$TIMEZONE" ]; then \
        cp "/usr/share/zoneinfo/$TIMEZONE" /etc/localtime; \
        echo "$TIMEZONE" > /etc/timezone; \
    fi; \
# PECL Extensions
    curl -OsSL http://pecl.php.net/channel.xml; \
    sed -i 's!https://pecl.php.net!'http://pecl.php.net'!g' channel.xml; \
    pear channel-update channel.xml; \
    rm channel.xml; \
    pecl install yaf; \
    docker-php-ext-enable yaf; \
# PHP Extensions
    docker-php-ext-install mysqli pdo_mysql; \
    docker-php-source delete; \
# Install run dependency packages
    runDeps="$( \
        scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
        | tr ',' '\n' \
        | sort -u \
        | awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
    )"; \
    apk add --virtual .php-rundeps $runDeps; \
# Cleanup
    rm -rf /tmp/pear; \
    rm -rf /usr/local/include; \
    rm -rf /var/cache/apk/*; \
# System configurations
    { \
        echo 'yaf.environ = develop'; \
        echo 'yaf.cache_config = Off'; \
        echo 'yaf.use_spl_autoload = On'; \
        echo 'yaf.use_namespace = On'; \
    } | tee -a /usr/local/etc/php/conf.d/docker-php-ext-yaf.ini; \
    { \
        echo '[global]'; \
        echo 'error_log = /proc/self/fd/2'; \
        echo; \
        echo '[www]'; \
        echo 'pm.status_path = /status'; \
        echo 'ping.path = /ping'; \
        echo; \
        echo 'clear_env = no'; \
        echo ; \
        echo '; Ensure worker stdout and stderr are sent to the main error log.'; \
        echo 'catch_workers_output = yes'; \
        echo; \
        echo 'request_slowlog_timeout = 5s'; \
        echo 'slowlog = /proc/self/fd/2'; \
        # php.ini configuration only for php-fpm
        echo 'php_admin_value[post_max_size] = 20M'; \
        echo 'php_admin_value[upload_max_filesize] = 20M'; \
        echo 'php_admin_value[error_reporting] = E_ALL & ~E_NOTICE'; \
        # echo 'php_admin_flag[display_errors] = Off'; \
        # echo 'php_admin_flag[log_errors] = On'; \
        echo 'php_admin_flag[display_errors] = On'; \
        echo 'php_admin_flag[log_errors] = Off'; \
        echo 'php_admin_value[date.timezone] = Asia/Shanghai'; \
    } | tee /usr/local/etc/php-fpm.d/docker.conf;
