# Simple relative content

This plugin makes all attachments urls stored in database relative to the WP_SITEURL url. 

## Problem

When you use the WordPress editor to insert a media in the content field, it stores a reference to the absolute path. So if you change the location of your site (by switching from staging to production environment for example) all your paths will be incorrect.

## What does this plugin?

This plugin stores relative pathes in the db instead of an absolute path.

## Limitations

**Beware**: this plugin must be activated **BEFORE inserting a media** into the content field. It has no effect on the medias stored before the activation.

## How it works?

It's mostly based on the **WP_SITEURL** constant which should be defined in the wp_config.php file. If this constant isn't defined it uses the "siteurl" option value instead. If the plugin detects that you access the website from another location than the "siteurl" defined initially it can correct most of the wrong urls in the database.

    define('WP_SITEURL', 'http://www.example.com');

Localisation of the plugin into french.