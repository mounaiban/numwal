# Numwal

*A Mounaiban mini-project.*

## About

**Numwal** (shorthand for *Numbered Wallpapers*) generates numbered and blank
wallpapers for use with fleets of PCs, smartphones, tablets, VMs and other
systems with a display for quick identification. It is a server-based attempt at
a device- or system-independent method of generating wallpapers.

Wallpapers are just an HTTP GET request away, readily downloadable with a `curl`
command in a script or a web browser, or any other way that works for you.

## Usage
> Please note that some links in this document only work when viewed
> from a running instance of Numwal, and not on GitHub. These links
> are indicated by the 🔹 symbol.

### Deployment/Installation

To deploy or install a(nother) Numwal demo instance, please see
SETUP.md, or <https://github.com/mounaiban/numwal/blob/master/SETUP.md>
for detailed instructions.

### Requesting Wallpapers

Use an HTTP client (like standard web browsers or `curl`) to issue GET
requests to the server to download wallpapers. For now, all wallpapers
are in PNG format.

These examples assume that you have an instance of of Numwal running
at `localhost` on port `9080`:

Try these in your web browser:

* <http://localhost:9080/wallpaper/default/9>

* <http://127.0.0.1:9080/blankpic/1440x3200/orange> (IPv4, HxW)

* <http://[::1]:9080/blankpic/dci4k/indigo> (IPv6, preset size)

Wallpapers may also be downloaded with the `curl` command, useful when
wallpapers have to be requested from a script:

* `curl -o numwal-wp-demo.png http://localhost:9080/wallpaper/default/3`

* `curl -o numwal-blank-demo.png http://127.0.0.1:9080/blankpic/1440x3200/orange`

* `curl -o numwal-blank-demo.png http://[::1]:9080/blankpic/dci4k/indigo`

#### Numbered Wallpapers

The URI syntax is as follows (when the server is at `localhost`):

`localhost/wallpaper/$STYLE/$NUMBER`

A [list of available wallpaper styles🔹](/wallpaper) with example links will be returned if no style or number is specified.

Style definitions are in `www/styles/`. Styles can be added or
modified, and are defined using a JSON-based *CSS-like but not quite*
format.

**TODO**: Instructions for creating and customising styles will be
released later. Meanwhile, check `www/wallpaper.php` for supported
options and the `www/styles` to get a feel for the style definition
formats.

#### Blank Wallpapers

The URI syntax is as follows (when the server is at `localhost`):

`localhost/blankpic/$SIZE/$COLOUR`

A list of [supported values for `$SIZE`🔹](/blankpic) will be returned
if no size or colour is specified.

See the [list of X11 colours](https://www.w3.org/TR/css-color-3/#svg-color) for supported values for `$COLOUR`.

### List of Available Features
If you are lost, go to any non-existent URL or [the index🔹](/).
A list of available features will be returned. There aren't many
features at the moment, and most of them have been covered by this
document 😉.

## Copyright and Licensing
Copyright 2020-2022 Mounaiban.

Numwal is free software, licensed under the terms of the
**Apache Public Licence 2.0**.

Some material bundled with the official distribution are licensed
under separate terms:

### Babuchas en una tienda de Marrakech, Marruecos
Copyright &copy;2014 I. Barrios and J. Ligero.

[Wikimedia Commons page](https://commons.wikimedia.org/wiki/File:Babuchas_02_--_2014_--_Marrakech,_Marruecos.jpg)

This file is licensed under the
**Creative Commons Attribution-Share Alike 3.0 Unported** license.

This is the background picture used in the *babuchas* wallpaper
styles.

### Comic Neue
Copyright &copy;2014, 2016 Craig Rozynski. This font software is
licensed under the **SIL Open Font License, Version 1.1**.

[Website](http://comicneue.com) | [GitHub repo](https://github.com/crozynski/comicneue)

This font family is used in the numbers in the *babuchas* wallpaper
styles.

### OpenDyslexic
Copyright &copy;2019 Abbie Gonzalez. This font software is licensed
under the **SIL Open Font License, Version 1.1**.

[Website](https://opendyslexic.org) | [GitHub repo](https://github.com/antijingoist/opendyslexic)

The SIL-OFL re-release of this font family is used for the numbers in
the *default* wallpaper style.

### Third-Party Versions and Unofficial Distributions
If you obtained this software from third-party sources with additional
styles, it may contain additional copyrighted material such as
pictures and fonts. Please check with the copyright holders of the
materials for licensing terms and conditions.

## Middleware
This application is built with the [Fat-Free Framework](https://fatfreeframework.com).

Wallpapers are generated using [ImageMagick](https://imagemagick.org) via the [Imagick PHP extension](https://pecl.php.net/package/imagick).

## Project Background

Numwal's original mission had two objectives: to help a certain admin tell
identical-looking devices of a fleet apart, attempt to realise Roy Fielding's
[Represenational State Transfer (REST)](https://www.ics.uci.edu/~fielding/pubs/dissertation/rest_arch_style.htm)
pattern while doing it.

The degree of success of this exercise is entirely left to your own judgement.
See also: [*Splitting Hairs With REST: Does a standard JSON REST API violate HATEOAS?*](https://stackoverflow.com/questions/9055197) on StackOverflow.

Alas, this application was never deployed, nor ever known to the administrator
it was intended to help. Nevertheless, it has been uploaded here as a public
learning journal for Docker, Git and PHP and web API development. You are
invited to use it in your classes and tutorials on anything about PHP,
networking and virtualisation/container technology, information security and
anything in between.
