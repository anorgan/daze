---
title: 'Writing Entries'
slug: writing-entries
date: '2014-06-05'
---

Just like in Jekyll, entries are written with Front Matter - a YAML configuration at the start of the file, inside three dashes:

```
---
title: This is the configuration
slug: of-the-file
date: 2014-07-05
draft: true
---
Now we can write some *Markdown* or <strong>HTML</strong>.
```

To ease the creation, there is a `create:entry` command, which will ask you a couple of questions and create the file for you:


``` bash
$ php daze.phar create:entry
Title of entry: Some test entry
Select type: 
  [md  ] Markdown (default)
  [html] Pure, raw HTML
> html
New entry has been created at /path/to/my-cool-site/.daze/entries/some-test-entry.html
```
