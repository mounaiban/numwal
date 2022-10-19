# Numwal Evaluation Dockerfile (on PHP Built-in Web Server, no TLS)

# Stage 1: PHP+Imagick Base Image
#
# The Base Image contains a copy of PHP, with an Alpine Linux
# userspace, and Imagick installed using php-extension-installer.
#
# Please run the correct command below for your system *before*
# building the image, to update to the latest version of
# php-extension-installer:
#
# docker pull mlocati/php-extension-installer
# podman pull docker.io/mlocati/php-extension-installer
#
# See: https://github.com/mlocati/docker-php-extension-installer#copying-the-script-from-a-docker-image
#
FROM php:7-fpm-alpine as base
COPY --from=docker.io/mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin
RUN /usr/bin/install-php-extensions imagick && \
    /usr/bin/install-php-extensions @composer

# Stage 2: Numwal-ready Environment
#
# The Numwal-ready Environment image contains all the dependencies
# required to run Numwal, but not the Numwal application files.
# It is used as a development image.
#
# NOTE: A security alert will appear warning against running Composer
# as root. The app will still work.
# TODO: Find a rootless means of running Composer
#
FROM base as numwal-env
RUN mkdir /usr/share/numwal && \
    mkdir /usr/share/numwal/www
COPY composer.json /usr/share/numwal/
WORKDIR /usr/share/numwal
RUN composer install

# Stage 3: Simple Intranet Demo Deployment
#
# This image deploys ready-to-run instances of Numwal using the PHP
# built-in web server without TLS. It is by no means production-
# grade, but it's good enough for a concept demonstration.
#
FROM numwal-env as numwal-intranet-demo
COPY www/ /usr/share/numwal/www/
STOPSIGNAL SIGTERM
EXPOSE 9080/tcp
WORKDIR /usr/share/numwal/www
# TODO: The tmp/cache directory is needed by F3 when using memcached,
# find out how to change this directory. The cache directory seems
# to be changeable only when using filesystem caching.
RUN mkdir /usr/share/numwal/www/tmp
RUN mkdir /usr/share/numwal/www/tmp/cache
RUN chmod 777 /usr/share/numwal/www/tmp/cache
CMD ["php", "-S", "0.0.0.0:80", "index.php"]
