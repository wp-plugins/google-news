=== Google News ===
Contributors: Olav Kolbu
Donate link: http://www.kolbu.com/
Tags: widget, plugin, cnn, news, cnn news, rss, feed
Requires at least: 2.3.3
Tested up to: 2.5
Stable tag: trunk

Adds a sidebar widget to display the first N news items from an
admin-specified Google News RSS feed.

== Description ==

Google aggregates news from over 4500 news sources, updated
continously. The results can be retrieved as a number of 
RSS feeds, where you can create your own specific feed by
specifying one of more than 40 regions/languages, and an
optional topic ranging from Domestic to Most Popular to
Entertainment. Currently there are nine topics, including,
of course, All. In addition to this, any feed can be filtered 
through a search query so that only news items matching your 
query will be shown. Note that not all combinations of 
region/language and topic has been enabled by Google but
it should degrade gracefully.

Short version: 

Enable plugin, use widget then configure using the fairly 
self explanatory config box on the widgets page.

Long version:
This WordPress widget allows the WP admin to select the 
relevant options from easy to remember drop down lists 
on order to build a personalized feed and have that 
displayed in a widget. An optional widget title can be 
set, otherwise the feed title is used.

Each feed item comes with a long and a short description,
and you can also choose which one to display. Note that
the long descriptions are originally a complete and utter
mess of links, colour/font/style settings and tables(!)
in the original Google RSS feeds, so a bit of work has
gone into cleaning this up. YMMV. If you select the short
descriptions, the long description, completely stripped of
html-tags, is displayed as the link tooltip.

Lastly, there is the same Standard, Text Only and With 
Images choice as Google gives. Standard and With Images
appear to be identical in an RSS feed.

The feed is fetched for every view, so users are guaranteed
up to date information. No local storage of feed is done.
Clicking on a news item will of course take you via Google to
the news site with the relevant article, as per Google Terms of Use.

**[Download now!](http://downloads.wordpress.org/plugin/google-news.zip)**

[Support](http://www.kolbu.com/2008/04/07/google-news-plugin/)


== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Unzip into the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. It now shows up under available widgets, and you can select it and change the available options.

The screenshots section has a picture of the widget dialog box. 

== Screenshots ==

1. Widget in action under the Prosumer theme. Note the mouseover showing additional text from the news item.
2. The admin options page of the widget.

