# Numwal Full Config Demo Project Nginx Dockerfile
# For use with compose.yml only
# 
# NOTE: TLS keys are still copied into the container when the
# no-tls configuration is used
#
FROM nginx:1-alpine
ARG NUMWAL_NGINX_CONFIG=no-tls
COPY conf/numwal-${NUMWAL_NGINX_CONFIG}.conf.template /etc/nginx/templates/
COPY tls/keys/ /etc/ssl/keys/
COPY tls/private/ /etc/ssl/private/
RUN nginx
