# Installing or Deploying Numwal

> This guide is for deployment from the command line only. You may
> disregard it if you are using front-end tools like Docker Desktop.

Numwal may be deployed in a Docker container, or installed on a host
system. Containers are the recommended method where available (and
most modern operating systems have some form of Docker support).
They are easier to set up than a virtual machine, and provide enough
virtualisation for a web server application like Numwal.

## Quick Demo Container Deployment

To deploy Numwal in the most basic configuration, using the PHP
built-in web server without TLS, run the following commands in the
same directory as `Dockerfile`:

### Docker

```sh
docker pull mlocati/php-extension-installer
docker build -t numwal:0.6-simple .  # remember the dot
docker run --rm --name numwal -dp 9080:80 numwal:0.6-simple
```

### Podman

```sh
podman pull docker.io/mlocati/php-extension-installer
podman build -t numwal:0.6-simple .   # remember the dot
podman run --rm --name numwal -dp 9080:80 localhost/numwal:0.6-simple
```

> TODO: There will be a security warning about running Composer as
> the root user. While this does not affect normal operation, finding
> a rootless means of running Composer is still part of the plan.

### Generating Wallpapers

After a successful deployment, you can start requesting wallpapers.
If you followed the guide as-is, Numwal should be accessible at
localhost on port 9080: try going to <http://localhost:9080/wallpaper>
in your web browser. You should see some example wallpaper URIs.

### Stopping the Containers

```sh
docker stop numwal
```
> Replace `docker` with `podman` as appropriate

### Restarting the Containers

Due to the `--rm` flag in the `run` commands above, the containers are
removed once they stop. Repeat the `run` commands above to restart the
containers:

#### Docker
```sh
docker run --rm --name numwal -dp 9080:80 numwal:0.6-simple
```

#### Podman
```sh
podman run --rm --name numwal -dp 9080:80 localhost/numwal:0.6-simple
```

## Development Container Deployment

The development container contains the necessary dependencies for
running Numwal (PHP, Imagick, Composer, Fat Free Framework, etc...)
but without a copy of the application.

The actual application directory is bind mounted into the development
container, effectively sharing it with the container. This allows
working on the code on the host system and avoids frequent rebuilds.

To deploy the development container, run the following commands from
the same directory as `Dockerfile`:

### Docker

```sh
docker pull mlocati/php-extension-installer
docker build -t numwal-dev --target=numwal-env .
docker run --rm --name numwal-devc -dp 9080:80 \
    -v "$PWD/www":/usr/share/numwal/www \
    -w /usr/share/numwal/www \
    numwal-dev \
    php -S 0.0.0.0:80 index.php
```

### Podman

```sh
podman pull docker.io/mlocati/php-extension-installer
podman build -t numwal-dev --target=numwal-env .
podman run --rm --name numwal-devc -dp 9080:80 \
    -v "$PWD/www":/usr/share/numwal/www:Z \
    -w /usr/share/numwal/www \
    localhost/numwal-dev \
    php -S 0.0.0.0:80 index.php
```

Remember: the target for the Development Container is `numwal-env`.

If you followed the guide as-is, the app should be accessible from
localhost on port 9080 at <http://localhost:9080>. Most changes to the
source files should take effect immediately.

### Fat-Free Framework Debug Mode

The Fat-Free Framework which Numwal runs on has a Debug mode that can
be toggled from the `NUMWAL_DEBUG` environment variable in the
container using the `-e` option in Docker or Podman. Here's a Podman
example:

```sh
podman run --rm --name numwal-devc -dp 9080:80 \
    -v "$PWD/www":/usr/share/numwal/www:Z \
    -w /usr/share/numwal/www \
    -e NUMWAL_DEBUG=2 \
    localhost/numwal-dev \
    php -S 0.0.0.0:80 index.php
```

## Demo Enterprise Deployment

A sample enterprise deployment Docker Compose project file,
(`compose.yml`) is also available. This project deploys Numwal in a
triple container setup, running on php-fpm and Nginx, with Memcached.

To deploy, just run `docker-compose up -d`. To shut down and remove
all containers, run `docker-compose down`.

### Environment Variables

The following settings may be altered without editing any project or
configuration files, by exporting these environment variables on
the host:

* `NUMWAL_CACHE_CLEAR`: set to any integer 1 or higher to order the
  underlying Fat Free Framework to clear caches.

* `NUMWAL_CACHE_SIZE`: sets the amount of memory used by Memcached.
  Remember to specify the order of magnitude (M, G, maybe T to
  overkill).

* `NUMWAL_MEMCACHED_HOST`: sets the hostname of the Memcached server.

* `NUMWAL_NGINX_CONFIG`: set to `with-tls` to enable HTTPS support.
  Remember to have the keys ready, preferably on a secure server,
  and to modify `numwal-with-tls.config.template` to suit your
  environment.

  * Alternatively, if self-signed keys are in use, leave the
    private key in `tls/private/` directory and the public key in
    the `tls/keys/` directory.

### Setting up Numwal with TLS for HTTPS Support

#### About the `tls/` Directory (Security Alert!)

The `tls/` directory is for use with self-signed test certificates
only. A production environment should keep TLS keys on a secure
server in order to avoid copying private keys into images and
containers.

**Copying private keys increases the risk of a leak, constituting a
serious incident.**

### Default Ports and Addresses

By default, Numwal listens on port 9080, or 9443 for HTTPS.

### PROTIPS for New Hackers

#### Podman `docker-compose` Support

If you are using Podman (usually on a Fedora host), additional setup
may be needed to enable `docker-compose` support. Ensure that the
`podman-docker` scripts and `docker-compose` are installed, and
enable the Podman socket by running:

```sh
systemctl --user enable podman.socket  # not always needed
systemctl --user start podman.socket
export DOCKER_HOST=unix:///run/user/$UID/podman/podman.sock
```

The above commands allow `docker-compose` to be used without `sudo`.

#### Choosing Registries for Docker Images

If prompted to choose between multiple available registries,
`docker.io/` offers the best availability and the widest selection of
images, at time of writing.

#### Rebuild to Apply Changes

Remember to rebuild the images with `docker-compose build` to apply
changes to the codebase or configuration files to the deployment.

## References
Get Started. Docker Docs. <https://docs.docker.com/get-started/>

Rozek, B. Rootless Docker-Compose with Podman. 2022-02-29 <https://brandonrozek.com/blog/rootless-docker-compose-podman/>
