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
docker build -t numwal:0.5-simple .  # remember the dot
docker run --rm --name numwal -dp 9080:80 numwal:0.5-simple
```

### Podman

```sh
podman pull docker.io/mlocati/php-extension-installer
podman build -t numwal:0.5-simple .   # remember the dot
podman run --rm --name numwal -dp 9080:80 localhost/numwal:0.5-simple
```

> TODO: There will be a security warning about running Composer as
> the root user. This does not affect operation of the demo instance.
> A rootless means of running Composer will be implemented figured out

### Generating Wallpapers

After a successful deployment, you can start requesting wallpapers.
If you followed the guide as-is, Numwal should be accessible at
localhost on port 9080: try going to <http://[::1]:9080/wallpaper>
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
docker run --rm --name numwal -dp 9080:80 numwal:0.5-simple
```

#### Podman
```sh
podman run --rm --name numwal -dp 9080:80 localhost/numwal:0.5-simple
```

## References
Get Started. Docker Docs. <https://docs.docker.com/get-started/>

