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
    numwal-dev \
    php -S 0.0.0.0:80 index.php
```

### Podman

```sh
podman pull docker.io/mlocati/php-extension-installer
podman build -t numwal-dev --target=numwal-env .
podman run --rm --name numwal-devc -dp 9080:80 \
    -v "$PWD/www":/usr/share/numwal/www:Z \
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
    -e NUMWAL_DEBUG=2 \
    localhost/numwal-dev \
    php -S 0.0.0.0:80 index.php
```

## References
Get Started. Docker Docs. <https://docs.docker.com/get-started/>

