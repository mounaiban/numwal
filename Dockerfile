# Numwal Evaluation Dockerfile (on PHP Development Server, without TLS)

# Stage 1: Base (Install Imagick on Docker's PHP official image)
# This stage is sufficient for development. Bind mount the Numwal
# repository root and you're good to go!

# Install Imagick and dependencies using php-extension-installer
# as advised by the PHP docker image maintainers*
FROM php:7-fpm-alpine as base
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin
RUN install-php-extensions imagick && \
	apk add composer 

# Stage 2A: Intranet Deployment - Numwal and Composer Dependency Installation
# NOTE: This will throw a security warning, and an error regarding 
# a lack of Git. The app will work regardless.
FROM base as intranet-init
RUN mkdir /usr/share/numwal && \
	mkdir /usr/share/numwal/www
COPY www/ /usr/share/numwal/www/
COPY composer.json /usr/share/numwal/
WORKDIR /usr/share/numwal
RUN composer install 

# Stage 2B: Intranet Deployment - Nginx Installation without TLS
FROM base as intranet-without-tls
# Portion on linking logs to stdout/stderr pinched from the Nginx Plus Admin Guide 
# https://docs.nginx.com/nginx/admin-guide/installing-nginx/installing-nginx-docker/
# Create nginx.pid to work around a 'no such file or directory' error.
# Copy fully-prepared Numwal directory from intranet-init stage.
RUN apk add nginx && \ 
	mkdir /run/nginx/ && \
	touch /run/nginx/nginx.pid && \
	ln -sf /dev/stdout /var/log/nginx/access.log && \
 	ln -sf /dev/stderr /var/log/nginx/error.log && \
	mkdir /usr/share/numwal
COPY --from=intranet-init /usr/share/numwal/ /usr/share/numwal/
COPY conf/numwal-nginx-without-tls.conf /etc/nginx/conf.d/numwal-nginx.conf
# Stage 2B Container runtime settings
STOPSIGNAL SIGTERM
EXPOSE 9080/tcp
CMD ["nginx", "-g", "daemon off;"]


# Stage 3: Intranet Deployment - Nginx Installation with TLS
FROM intranet-without-tls as intranet
COPY tls/numwal-private.pem /etc/ssl/private
COPY tls/numwal.pem /etc/ssl/certs
COPY conf/numwal-nginx.conf /etc/nginx/conf.d/numwal-nginx.conf
# Stage 3 Container runtime settings
STOPSIGNAL SIGTERM
EXPOSE 9443/tcp, 9080/tcp
CMD ["nginx", "-g", "daemon off;"]


# NOTE: The intranet images built from this Dockerfile use Nginx and
# php-fpm to host Numwal. Both are needed to be running to use Numwal,
# but only Nginx manages to start. To use the application, please
# start containers with the `php-fpm -D` command.

# PROTIP: To maximise cache use, try to place steps that produce the
# least frequent changes first. For example, an HTTP server that is 
# normally updated quarterly should be installed before an application
# which changes daily.

# PROTIP: The php-extension-installer script by Michele Locati is
# the surest way of creating Imagick-enabled PHP images known yet.
# See the GitHub repository for more info:
# https://github.com/mlocati/docker-php-extension-installer

# TODO: Find a rootless means of running Composer and the 
# development server

# TODO: Use environment variables to make it easier to switch between
# alternate Nginx configuration files and TLS keys from different files
# in different directories

# * PHP Docker Official Images. How to Install More PHP extensions.
#   2020-03-10. https://hub.docker.com/_/php

