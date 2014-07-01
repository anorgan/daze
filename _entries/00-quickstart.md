---
title: Quickstart
slug: quickstart
date: '2014-06-00'
draft: true
---
``` bash
$ mkdir my-cool-site && cd my-cool-site
my-cool-site $ wget http://anorgan.github.io/daze/daze.phar
my-cool-site $ php daze.phar init
Your new site title [My Cool Site]:
# You can now edit daze.yml, to change the title and other configuration

# Create new entry
my-cool-site $ php daze.phar create:entry
Title of entry: Hello, Daze!
Select type: 
  [md  ] Markdown (default)
  [html] Pure, raw HTML
> md
New entry has been created at /path/to/entries/hello-daze.md

# After editing hello-daze.md
my-cool-site $ php daze.phar build
Writing entry Hello, Daze!
```