=== WP DataDisplay ===
Contributors: alantygel
Tags: data, custom database
Requires at least: 3.8.1
Tested up to: 4.1.1
Stable tag: trunk
License: GPL3

This plugin creates a custom post type that displays data of several database tables based on a unique ID.

== Description ==
This plugins allows the batch creation of posts using database tables as content source. As inputs, the plugin uses one main table and several others. The plugin will create one post per row of the main table, according to a configurable template, using information of all tables, based on a common ID. It also offers a shorcode to create a search page.

== Installation ==

How to use:

1) Upload a table in your database called "main", with the appropriate prefix (see wpdd_config.php). This table needs one column called ID.
2) Upload other tables. They must also have a column called ID, which will be associated to the main table.
3) In the plugin settings, set up the datadisplay title and template, and click save changes.
4) Click on generate posts. For each row on main table, it will create one post (posttype datadisplay). Post title and content will follow the template settings.


== Frequently Asked Questions ==
== Changelog ==
== Upgrade Notice ==
== Screenshots ==

