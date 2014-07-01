---
title: Installation
slug: installation
date: '2014-06-02'
---

To install, you can download prebuilt phar archive or require anorgan/daze in composer. Preferred way of installing is phar:

``` bash
$ mkdir my-cool-site && cd my-cool-site
$ wget http://anorgan.github.io/daze/daze.phar
```

To start, initialize your Daze site:

``` bash
$ php daze.phar init
```

Daze holds all its data in `.daze` folder (unless your configuration is different), which makes it easy to git ignore it and commit all the files for "production". You can then keep `.daze/entries` in a separate branch for versioning purposes.