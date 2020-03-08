# Numwal

*A Mounaiban mini-project.*

## About

### What's This?
Do you manage fleets of computing devices or virtual machines? Do you need to number them to make those systems quicker and easier to identify visually? Did you resort to using numbered lock screen or background wallpapers, precision-crafted in an image editor, to achieve that?

**Numwal** (shorthand for *Numbered Wallpapers*) generates numbered and blank wallpapers for use with desktop PCs, smartphones, tablets, VMs and potentially more kinds of information technology systems that have a use for a raster video display.

Numwal enables a platform-agnostic way of automating the creation of numbered and blank wallpapers, which might make creating and managing such wallpapers easier for you.

### Usage

#### Installation
*(Please skip this section if you have already set up the application)*

The installation procedure is basically:

1. Ensure PHP, ImageMagick, Imagick and Composer are set up on your system.

2. Download Numwal either by cloning or getting the ZIP archive (zipball?), and placing the app in a designated directory.

3. Initiate the initial setup by running Composer to download the Fat Free Framework (F3).

4. Set up the HTTP application server:

	a. If you are using a fully-featured web server like Apache, IIS, Nginx or Unit, refer to the instructions for your software, as they vary greatly.

	b. If you are just trying this thing out, start PHP's embedded HTTP server.

Quick start (for evaluation purposes only):

Assuming that the necessary dependencies have been already correctly installed:

1. `git clone git://mounaiban.github.com/numwal` OR extract the archive's contents into a directory named `numwal`, to download and/or install Numwal.

2. `composer upgrade` inside the `numwal-www` directory to fetch the latest copy of F3.

3. `php -S localhost:9999 -t .` while still inside the `numwal-www` directory to start to application.

Change the server address and port, and/or directory paths and names as needed.

If you intend on using Numwal as part of a regular operation, you are likely to be better off using the app with a fully-featured HTTP server.

**TODO:** Example installation instructions for setting Numwal up on the *Apache*, *Nginx* and *Unit* servers will be available soon.

#### Requesting Wallpapers
Use an HTTP client (such as a standard web browser or `curl`) to issue a GET on the following paths on the server:

* `/wallpaper` - Gets a list of numbered wallpaper styles with example links. Navigate to one of these links to download a sample wallpaper. Change the number at the end to get a wallpaper of a different number.

* `/blankpic` - Gets a list of preset sizes in which blank pictures may be obtained. A link to a W3C document which contains a [list of X11 colours](https://www.w3.org/TR/css-color-3/#svg-color), is also shown for your convenience.

Try it in your web browser:

*(assuming that Numwal is hosted on the local machine: hostname `localhost`, IPv4 `127.0.0.1`, IPv6 `::1`, port 9999)*

* `localhost:9999/wallpaper/default/9`

* `127.0.0.1:9999/blankpic/1440x3200/orange`

* `[::1]:9999/blankpic/dci4k/indigo`

or with the `curl` command (where available):

* `curl -o numwal-wp-demo.png http://localhost:9999/wallpaper/default/3`

* `curl -o numwal-blank-demo.png http://127.0.0.1:9999/blankpic/1440x3200/orange`

* `curl -o numwal-blank-demo.png http://[::1]:9999/blankpic/dci4k/indigo`

Web browsers are good for requesting wallpapers on an ad-hoc basis, while command-line HTTP clients are useful when used as part of a script.

#### Wallpaper Styles
Styles are placed in subdirectories under the `styles` directory. Styles can be added or modified.

**TODO**: Instructions for creating and customising styles are being written up and will be available soon.

### Copyright and Licensing 
Copyright 2020 Mounaiban.

Numwal is free software, licensed under the terms of the **Apache Public Licence 2.0**.

Some material bundled with the official distribution are licensed under separate terms:

#### Babuchas en una tienda de Marrakech, Marruecos
Copyright &copy;2014 I. Barrios and J. Ligero.

[Wikimedia Commons page](https://commons.wikimedia.org/wiki/File:Babuchas_02_--_2014_--_Marrakech,_Marruecos.jpg)

This file is licensed under the **Creative Commons Attribution-Share Alike 3.0 Unported** license.

This is the background picture used in the *babuchas* wallpaper styles.

#### Comic Neue
Copyright &copy;2014, 2016 Craig Rozynski. This font software is licensed under the **SIL Open Font License, Version 1.1**.

[Website](http://comicneue.com) | [GitHub repo](https://github.com/crozynski/comicneue)

This font family is used in the numbers in the *babuchas* wallpaper styles.

#### OpenDyslexic 
Copyright &copy;2019 Abbie Gonzalez. This font software is licensed under the **SIL Open Font License, Version 1.1**.

[Website](https://opendyslexic.org) | [GitHub repo](https://github.com/antijingoist/opendyslexic)

The SIL-OFL re-release of this font family is used for the numbers in the *default* wallpaper style.

#### Third-Party Versions and Unofficial Distributions
If you obtained this software from third-party sources, it may contain additional copyrighted material licensed under other terms and conditions. Please check with the creator or maintainer of the distribution to confirm these licensing terms and conditions as necessary. If you are a maintainer of a third-party distribution, it would be helpful have this copyright notice revised to be accurate for your distribution.

### Middleware
This application is built with the [Fat-Free Framework](https://fatfreeframework.com).

Wallpapers are generated using [ImageMagick](https://imagemagick.org) via the [Imagick PHP extension](https://pecl.php.net/package/imagick). 

### Philosophy, Project Background and Stuff
Numwal was a little exercise in realising Roy Fielding's Representational State Transfer (REST-fulness) in applications as well an exploration of PHP software development, convention-based architectures and ease of deployment.

It was originally created for use in a network environment that included both desktop and mobile devices, running different operating systems. This made a client-based automated wallpaper generation process more cumbersome than desired.

As such, this project aims to make Numwal as straightforward to deploy as possible, over wide range of PHP web application stacks, on any capable web server from Apache to Unit, any operating system from Alpine Linux to Windows, on any kind of host from Containers to Virtual Machines.


## Wishlists

### High-Priority Wishlist 

#### Documentation
* **Installation instructions** for the Apache HTTP Server, Nginx, Unit and for Docker, Alpine Linux, CentOS, Fedora, Debian, and Ubuntu.

#### Security
* **Initial security auditing**. Although Numwal is intended to be used only in closed intranets under the close supervision of administrators, it is a priority to ensure that this application does not contribute to any weakness on any network.

	* File system-scanning routines are used in important parts of this application, especially to support custom wallpaper styles. Image and font files from unknown sources are likely to be encountered.

#### Code Quality
* **Unit and Integration Testing**. Automated tests have not been written yet. These are essential for code maintainability.

#### Usability
* **Example Dockerfiles**. These would demonstrate the feasibility of using Numwal in a container.

### Long-term Wishlist ###

#### Documentation
* **Specifications for *style.json***. The CSS-like (but not quite), JSON-based format is yet to be documented.

* **Installation instructions for more systems**, especially FreeBSD, Windows and on Microsoft Internet Information Services (IIS) so that those running these won't get left out!

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

