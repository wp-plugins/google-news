=== Google News ===
Contributors: Olav Kolbu
Donate link: http://www.kolbu.com/
Tags: widget, plugin, cnn, news, cnn news, rss, feed
Requires at least: 2.3.3
Tested up to: 2.5
Stable tag: trunk

Displays N first news items from a selectable Google News 
RSS feed, inline or as a widget.

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

This plugin works both as a widget and as inline content
replacement. It can be used as both simultaneously, but
all instances will show the same content.

For widget use, simply use the widget as any other. For inline
use, insert the string **&lt;!--google-news--&gt;** (i.e.
<!--google-news-->) in your content and it will be replaced
with the news.

Short version: 

Enable plugin, configure using either the Google News option
on the Dashboard Settings page or the configuration box on
the widget page. Both access and update the same settings, 
you do not need to use one or the other. 

Long version:

This WordPress plugin can be used as both a widget and 
inside normal blog content. Due to this dual personality,
there are two ways of updating the plugin configuration. 
Note that these ways are identical and you do not specifically
have to use the confiuration box on the widgets page for
the widget or vice versa.


The available options are as follows. 

**Title:** Optional, which when set will be used in the
widget title or as a header above the news items when 
inline. If the title is empty, then a default title
of "Google News : &lt;region&gt; : &lt;feed type&gt;" is used. Note
that as per Google Terms of Service it is a requirement
to state that the news come from Google.

**News region:** A dropdown list of 40 choices, determining
the region/language of the feed. 

**News type:** Another dropdown list, determining what type of
news you are after. Sci/Tech, Business, Health etc.

**Output type:** Some Google feeds come with just text, 
some pictures or pictures on nearly every news item. Chose
which one you want here.

**News item length:** Short or long. The short version is really just 
the news item title as a one liner but probably the one most 
WP admins will use. The long version is a 3-4 line teaser that 
has been severely stripped of useless markup that Google insists 
on passing along, including tables, links, colour/font/style
settings etc. I've tried to clean it up so it won't mess up your 
theme. For the short version, the long text without html tags is 
available as a mouse rollover/tooltip.

**Max items to show:** As the title says, if the feed has
sufficient entries to fulfil the request. 

**Optional query filter:** One of the most important parts of
the Google News RSS Feed is the ability to filter the news
with your very own search query. Get relevant, up to date
news on the exact topic you want. Note that if you add a
search query, then the short item length will include an 
"all N news articles" link curtesy Google. If you choose
to add a query, then you most likely want to set a title
as well. To explain to the viewer what kind of news you have
selected for them to see.

The feed is fetched for every view, so users are guaranteed
up to date information. No local storage of feed is done.
Clicking on a news item will of course take you via Google to
the news site with the relevant article, as per Google Terms of Use.

If you want to change the look&feel, the inline table is 
wrapped in a div with the id "google-news-inline" and the
widget is wrapped in an li with id "google-news". Let me 
know if you need more to properly skin it.

MINOR NOTE: If you upgrade from 1.0 then you will get the default
"U.S. All" feed until you visit the configuration page for the
first time. Once there, most of your old settings will automatically 
be imported and used. Review and save and you should be good to go.

**[Download now!](http://downloads.wordpress.org/plugin/google-news.zip)**

[Support](http://www.kolbu.com/2008/04/07/google-news-plugin/)


== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Unzip into the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. It now shows up both under available widgets and has its own configuration page under Dashboard Settings.

The screenshots section has a picture of the configuration page. 

== Screenshots ==

1. Widget in action under the Prosumer theme. Note the mouseover showing additional text from the news item.
2. The admin options page of the widget.
3. Inline example under the Prosumer theme, replacing &lt;!--google-news--&gt; in content.

== Changelog ==

1. 1.0 Initial release
1. 1.1 Removed dependency on PHP 5.1++ functionality.
    Fixed UTF8-related bugs. 
    - Not a public release.
1. 2.0 Rewritten from scratch. Now uses a class to avoid polluting the 
    name space. Hopefully adhering to best practices plugin writing.
    Can now be used both as a widget and as inline content replacement.

