# Numwal Evaluation Dockerfile (on PHP Development Server, without TLS)
FROM php:7-cli-alpine

# Prepare Numwal directory
RUN mkdir /usr/src/numwal-www

# Install Composer, and install the Fat Free Framework
# NOTE: This will throw a security warning and an error regarding 
# a lack of Git on the image. The app will work regardless.
COPY numwal-www/composer.json /usr/src/numwal-www/
WORKDIR /usr/src/numwal-www
RUN apk add composer && composer upgrade

# Install Imagick and dependencies using php-extension-installer
# as advised by the PHP docker image maintainers*
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin
RUN install-php-extensions imagick

# Install Numwal application files
COPY numwal-www /usr/src/numwal-www

# Runtime settings
EXPOSE 9010/tcp
CMD ["php", "-S", "0.0.0.0:9010", "-t", "/usr/src/numwal-www"]


# TODO: Find a means of running Composer and the PHP development 
# server as a non-root user.

# NOTE: Images created from this Dockerfile may fail on the very
# first run. If this happens, try restarting the container.

# PROTIP: Michele Locati's php-extension-installer script is
# the surest way of creating Imagick-enabled PHP images known yet.
# See the GitHub repository for more info:
# https://github.com/mlocati/docker-php-extension-installer

# * PHP Docker Official Images. How to Install More PHP extensions.
#   2020-03-10. https://hub.docker.com/_/php

