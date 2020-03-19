# Numwal

*A Mounaiban mini-project.*

## About

### What's This?
Do you manage fleets of computing devices or virtual machines? Do you need to number them to make those systems quicker and easier to identify visually? Did you resort to using numbered lock screen or background wallpapers, precision-crafted in an image editor, to achieve that?

**Numwal** (shorthand for *Numbered Wallpapers*) generates numbered and blank wallpapers for use with desktop PCs, smartphones, tablets, VMs and potentially more kinds of information technology systems that have a use for a raster video display.

Numwal enables a platform-agnostic way of automating the creation of numbered and blank wallpapers by running as a wallpaper server. This might make generating such wallpapers easier for you. Wallpapers are just an HTTP GET request away, readily downloadable with a `curl` command in a script or a web browser, or any other way that works for you.

## Usage

### Installation
*(Please skip this section if you have already set up the application)*

As with any client-server application, Numwal may run on the local system, virtual machine or in a container.

The installation procedure is basically:

1. Ensure PHP, ImageMagick, Imagick and Composer are set up on your target system.

2. Download Numwal either by cloning or getting the ZIP archive (zipball?), and placing the app in a designated directory.

3. Initiate the initial setup by running Composer to download the Fat Free Framework.

4. Set up the HTTP application server.

5. Start the HTTP application server.

**NOTE:** Installation procedures vary between servers and operating systems; each software platform is different enough to need its own guide. Detailed and specific installation instructions will be posted on the wiki of this repository.

#### Quick Start: Demonstration Intranet Deployment with Containers
If you have container support enabled (e.g. Docker, Podman) on your system, you can get a self-contained evaluation container up and running pretty quickly using the Dockerfile included in this repo. The container created in this demonstration will have all the necessary software dependencies required to host a Numwal server instance in an intranet.

1. `git clone https://mounaiban.github.com/numwal` OR extract the archive's contents into a directory named `numwal`, to download and/or install Numwal. This `numwal` directory may be substituted for another as needed, and will be called the _repo root_.

2. Choose the type of container image you want to build. The included multi-stage Dockerfile offers three usable image types: `base`, `intranet-without-tls` and `intranet`.

3. If you are using the `intranet` container, place your TLS/SSL keys into the `tls/` directory. Skip this step if you are not using TLS. *Handle authoritative TLS keys with care; do not allow them to leak, and revoke leaked keys*.

4. Build the container. While in the same directory as the `Dockerfile`, pick the most appropriate command only from below to initiate the build process:

	* **With TLS:** `docker build -t numwal .`

	* **No TLS:** `docker build -t numwal-without-tls --target=intranet-without-tls .`

	* **With TLS, using Podman:** `podman build -t numwal .`

	* **No TLS, using Podman:** `podman build -t numwal-without-tls --target=intranet-without-tls .`

	* *Basic command structure:* `docker build -t $IMAGE_NAME --target=$IMAGE_TYPE .`

	* *PROTIP:* Remember the dot at the end of the command.

	* The `base` container type is intended for development use and is therefore beyond the scope of this demo.

5. Start the container! This has to be done in two steps when starting for the first time.

	i. Start the container. Choose the right command, depending on whether you enabled TLS.
	   
    * **With TLS:** `docker start --name numwal -d -p 9443:9443 -p 9080:9080 numwal`

    * **No TLS:** `docker start --name numwal -d -p 9080:9080 --name numwal numwal-without-tls`

    * **Alternate command with TLS:** `podman start --name numwal -d -p 9443:9443 -p 9080:9080 --name numwal localhost/numwal`


    * **Alternate command, no TLS:** `podman start --name numwal -d -p 9080:9080 localhost/numwal-without-tls`

	* *Basic command structure:* `docker start --name $CONTAINER_NAME -d -p $HOST_PORT:$CONTAINER_PORT $IMAGE_NAME`

	* On some systems, you may need to explicitly specify that your image is on the local machine with a `localhost/` prefix before your image name.

	ii. Start `php-fpm` on the container: `docker exec numwal php-fpm -D`.

    * Alternatively, `podman exec numwal php-fpm -D`

6. Stop the container by running: `docker stop numwal`.

**NOTE:** The current Dockerfile produces images and containers that depend on both Nginx and `php-fpm`, but only Nginx automatically starts. Help with solving this issue would be much appreciated. If you followed the above instructions, you should be able to generate wallpapers from `localhost` right away!

#### A Word on Running Numwal on the Internet
Serving Numwal on the public internet is regarded as an advanced subject beyond the scope of this guide. Publicly-accessible servers might not be difficult to set up at all and the instructions herein might even suffice, but the security risks and availability challenges make deployment of public internet services worthy of a discussion of its own that is not possible reasonably summarise in this document.

### Requesting Wallpapers
Use an HTTP client (such as a standard web browser or `curl`) to issue GET requests to the server to download wallpapers.
These examples assume that you have an instance of of Numwal running on your system (on the host or in a container), accessible from `localhost` on port `8080`.

Try these in your web browser:

* <http://localhost:9080/wallpaper/default/9>

* <http://127.0.0.1:9080/blankpic/1440x3200/orange> (IPv4, HxW)

* <http://[::1]:9080/blankpic/dci4k/indigo> (IPv6, preset size)

If you have enabled TLS on the web server hosting Numwal, you can request the wallpapers over an encrypted HTTPS connection to avoid meddlers on your network tampering with your wallpapers:

* <https://localhost:9443/wallpaper/default/9>

* <https://127.0.0.1:9443/blankpic/1440x3200/orange>

* <https://[::1]:9443/blankpic/dci4k/indigo>

Yes, you can also download wallpapers with the `curl` command!

* `curl -o numwal-wp-demo.png http://localhost:9080/wallpaper/default/3`

* `curl -o numwal-blank-demo.png http://127.0.0.1:9080/blankpic/1440x3200/orange`

* `curl -o numwal-blank-demo.png http://[::1]:9080/blankpic/dci4k/indigo`

Web browsers are good for requesting wallpapers on an ad-hoc basis, while command-line HTTP clients are useful when used as part of a script.

*PROTIP*: You will get security warnings if you use self-signed certificates on the wallpaper server. These can be safely ignored, if you know as a matter of fact that the server is legitimate. When using `curl`, either the `-k` or `--insecure` option must be used.

#### Wallpaper Styles
A list of available wallpaper styles with example links will be returned if no style or number is specified. Try requesting <http://localhost:9080/wallpaper/>.

Explore the contents of the `www/styles/` directory, where the style defintions reside. Styles can be added or modified, and are defined using a *CSS-like, but not quite*, format.

**TODO**: Instructions for creating and customising styles are being written up and will be available soon. Meanwhile, you can figure out for yourself by looking at the existing definitions and `www/wallpaper.php`.

#### Blank Wallpaper Preset Sizes
A list of preset sizes will be returned if no size or colour is specified <http://localhost:9080/blankpic/>.

A link to a W3C document which contains a [list of X11 colours](https://www.w3.org/TR/css-color-3/#svg-color) is also shown for your convenience.

#### List of Available Features
If you are lost, navigate to the default URL at <http://localhost:9080/>. A list of available features and relevant links will be returned. There aren't many features at the moment, and most of them have been covered by this document 😉.

## Copyright and Licensing 
Copyright 2020 Mounaiban.

Numwal is free software, licensed under the terms of the **Apache Public Licence 2.0**.

Some material bundled with the official distribution are licensed under separate terms:

### Babuchas en una tienda de Marrakech, Marruecos
Copyright &copy;2014 I. Barrios and J. Ligero.

[Wikimedia Commons page](https://commons.wikimedia.org/wiki/File:Babuchas_02_--_2014_--_Marrakech,_Marruecos.jpg)

This file is licensed under the **Creative Commons Attribution-Share Alike 3.0 Unported** license.

This is the background picture used in the *babuchas* wallpaper styles.

### Comic Neue
Copyright &copy;2014, 2016 Craig Rozynski. This font software is licensed under the **SIL Open Font License, Version 1.1**.

[Website](http://comicneue.com) | [GitHub repo](https://github.com/crozynski/comicneue)

This font family is used in the numbers in the *babuchas* wallpaper styles.

### OpenDyslexic 
Copyright &copy;2019 Abbie Gonzalez. This font software is licensed under the **SIL Open Font License, Version 1.1**.

[Website](https://opendyslexic.org) | [GitHub repo](https://github.com/antijingoist/opendyslexic)

The SIL-OFL re-release of this font family is used for the numbers in the *default* wallpaper style.

### Third-Party Versions and Unofficial Distributions
If you obtained this software from third-party sources, it may contain additional copyrighted material licensed under other terms and conditions. Please check with the creator or maintainer of the distribution to confirm these licensing terms and conditions as necessary. If you are a maintainer of a third-party distribution, it would be helpful have this copyright notice revised to be accurate for your distribution.

## Middleware
This application is built with the [Fat-Free Framework](https://fatfreeframework.com).

Wallpapers are generated using [ImageMagick](https://imagemagick.org) via the [Imagick PHP extension](https://pecl.php.net/package/imagick). 

## Project Background 
Numwal was a little exercise in realising Roy Fielding's Representational State Transfer (REST-fulness) in applications as well an exploration of other software development topics, especially PHP software development, convention-based architectures and ease of deployment. The degree of success of this exercise is entirely left to your own judgement.

It was originally created to aid device preparation and deployment in a particular network environment that once used numbered wallpapers on its desktop and mobile devices. Device and operating system differences made client-based wallpaper generation more cumbersome than desired.

As such, the project aimed to create an alternative, server-based approach to the problem.

Alas, this application was never deployed (and wasn't even ever made known to the administrator it was intended to help). Nevertheless, it has been uploaded here as a public learning journal for PHP programming, server app development, source code management and software deployment, for your learning pleasure, a specimen for your analyses, and as an outlet for your critique.

As with all Mounaiban projects, please feel free to use this project as a teaching and learning aid. Anyone who is going to use it as such is encouraged to scrutinise it as deeply as possible for flaws, and let them be known as lessons in improving the state of software technology.


## Wishlists

### Specific Issues
* **Error handling** throughout the application. Fat Free Framwork seems to trap errors at the framework level, foiling the usual approach with catch-try blocks. Error handling is important to these features:

	* Effective font fallbacks in `wallpaper.php`.

	* Handling invalid style definitions, missing fonts and picture files in `wallpaper.php` and giving meaningful diagnostic feedback.

	* RGB Hex codes when specifying colours to `blankpic`, like `/blankpic/6400x4800/FF0022/`

* **Wallpaper format selection in `wallpaper.php`**: currently, the routines are only prepared to handle JPEG and PNG source images, two out of potentially hundreds supported by ImageMagick. These features will be nice:

	* An option to explicitly choose the output image format in the style definition.

	* A system to classify sources as lossy and lossless, and then automatically select PNG or JPEG as appropriate.

### Not-So-Specific Features

#### Code Quality
* **Unit and Integration Testing**. Automated tests have not been written yet. These are essential for code maintainability.

#### Documentation
* **Specifications for *style.json***. The CSS-like (but not quite), JSON-based format is yet to be documented.

* **Installation instructions for more systems**, especially FreeBSD, Windows and on Microsoft Internet Information Services (IIS) so that those running these won't get left out.

	- A long term goal is to cover as many servers and operating systems as possible, from Apache to Unit, Alpine Linux to Windows, on any kind of host from Containers to Virtual Machines! 

#### Security
There are no specific goals for maintaining security, due to the ever-evolving nature of cybersecurity itself, but here are some perceived weak points:

* **File System-Scanning** routines, especially in `wallpaper.php` and `blankpic.php`.

* **Imagick and ImageMagick**, in encountering files of unknown origin, especially when untrusted users are allowed to create and customise wallpaper styles.

* **Placement of scripts on the server's file system**

* **Web Server Configuration** 

#### Usability
* **Accurate Error Messages, Error and Exception Handling**. The error handling in this app is really flimsy at the moment. It would be nice to be able to handle errors such as attempts to use styles that have not been installed.

* **Alphanumeric Number Support**. Some deployments may make use of alphanumeric 'numbers' for their systems, but Numwal currently only supports numeric wallpapers.

* **Blank Picture Generation** miscellaneous fixes:

	* Allow preset sizes with names that begin with numbers. This is currently not supported, due to the way WxH sizes are interpreted.

	* Enable support of RGB hex codes, allowing far more colour choices than what's currently supported.

* **Language Support**. The codebase's architecture currently makes translation and multi-language support more effortful than it needs to be.

* **Non-Composer Version** which eliminates the need to have and run Composer during setup, on systems where installed software must be kept to a minimum.

* **Performance**

	* Wallpaper generation was found to take up to several seconds on various test machines to complete. Listing wallpaper styles takes even longer. These processes involve filesystem access which could be optimised.

* **Thumbnail Generation for Wallpapers**. Many users are expected to find previewing of wallpaper styles really useful.

