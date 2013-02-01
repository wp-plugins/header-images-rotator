=== ntp-header-images ===
Contributors: nord_tramper
Donate link: http://nord-tramper.ru
Tags: images, header, rotator
Requires at least: 2.7
Tested up to: 3.5
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A samll and lightweight plugin for showing random images with links in any place you want.

== Description ==

This plugin allows you to show random images with links to your posts in any place of your website. You can adjust count and size of images, add/delete images using admin page.
Also you can import all images from posts with preview automaticaly (no duplicate if you already have some image+post pairs added).
You can see the example in the header of [http://lifelongjourney.ru](http://lifelongjourney.ru)

== Installation ==

1. Upload ntp-header0images.zip to the `/wp-content/plugins/` directory and unzip it.
2. Activate the plugin through the 'Plugins' menu in WordPress
1. Place `<?php	get_header_imageset2(); ?>` in your templates where you want to show images

== Frequently asked questions ==

= Where pligin store images? =
In the separate table in mySQL
= Do your plugin drop table when uninstall? =
Yes. Table and all options will droped.

== Screenshots ==

1.How it looks like 

== Changelog ==
Version 1.1
-Add title to images. Old images will have empty title, but you can change it manually via settings page.



== Upgrade notice ==
1.Deactivate plugin
2.Update files
3.Activate plugin
== Arbitrary section 1 ==