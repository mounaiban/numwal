# Numwal Evaluation Dockerfile (on PHP Development Server, without TLS)
FROM php:7-cli-alpine

# Prepare Numwal directory
RUN mkdir /usr/share/numwal && mkdir /usr/share/numwal/www

# Install Numwal application files, Composer and the Fat Free Framework
# NOTE: This will throw a security warning and an error regarding 
# a lack of Git. The app will work regardless.
COPY www/ /usr/share/numwal/www/
COPY composer.json /usr/share/numwal/
WORKDIR /usr/share/numwal
RUN apk add composer && composer upgrade

# Install Imagick and dependencies using php-extension-installer
# as advised by the PHP docker image maintainers*
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin
RUN install-php-extensions imagick

# Runtime settings
EXPOSE 9080/tcp
CMD ["php", "-S", "0.0.0.0:9080", "-t", "/usr/share/numwal/www/"]


# TODO: Find a rootless means of running Composer and the 
# development server

# NOTE: Containers based on this image may fail on the very first run.
# If this happens, try restarting the container.

# PROTIP: To maximise cache use, try to place steps that produce the
# least frequent changes first. For example, an HTTP server that is 
# normally updated quarterly should be installed before an application
# which changes daily.

# PROTIP: The php-extension-installer script by Michele Locati is
# the surest way of creating Imagick-enabled PHP images known yet.
# See the GitHub repository for more info:
# https://github.com/mlocati/docker-php-extension-installer

# * PHP Docker Official Images. How to Install More PHP extensions.
#   2020-03-10. https://hub.docker.com/_/php

