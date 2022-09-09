# Numwal Evaluation Dockerfile (on PHP Built-in Web Server, no TLS)

# Stage 1: PHP+Imagick Base Image
#
# The Base Image is sufficient for development. Bind mount the Numwal
# repository root (the same directory as this Dockerfile), and use
# "docker exec" or "podman exec" commands to start PHP.
#
# This Dockerfile uses php-extension-installer. Please run the correct
# command below for your system *before* building the image, to update
# the script to the latest version:
# docker pull mlocati/php-extension-installer
# podman pull docker.io/mlocati/php-extension-installer
#
# See: https://github.com/mlocati/docker-php-extension-installer#copying-the-script-from-a-docker-image
FROM php:7-alpine as base
COPY --from=docker.io/mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin
RUN /usr/bin/install-php-extensions imagick && \
    /usr/bin/install-php-extensions @composer

# Stage 2: Simple Intranet Demo Deployment
#
# This image deploys instances of Numwal using the PHP built-in web
# server without TLS. It is by no means production-grade, but it's
# good enough for a concept demonstration.
#
# NOTE: A security alert will appear warning against running Composer
# as root. The app will still work.
# TODO: Find a rootless means of running Composer
FROM base as intranet-demo
RUN mkdir /usr/share/numwal && \
	mkdir /usr/share/numwal/www
COPY www/ /usr/share/numwal/www/
COPY composer.json /usr/share/numwal/
WORKDIR /usr/share/numwal
RUN composer install
STOPSIGNAL SIGTERM
EXPOSE 9080/tcp
WORKDIR /usr/share/numwal/www
CMD ["php", "-S", "0.0.0.0:80", "index.php"]
