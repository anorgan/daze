---
title: Building
slug: building
date: '2014-06-04'
---
To create your site, you have to take all your entries and transform them to static pages. To do that, issue the `build` command:

``` bash
$ php daze.phar build
```

After it is done, you will be able to surf the site. To do that, I recommend setting up Apache/nginx or running PHP standalone server:

``` bash
$ php -S localhost:8888 -t .
```

To watch for changes... bla... bla... inotify
