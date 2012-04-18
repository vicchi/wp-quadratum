=== WP Quadratum ===
Contributors: vicchi
Donate Link: http://www.vicchi.org/codeage/donate/
Tags: wp-quadratum, maps, map, foursquare, checkins, checkin, widget
Requires at least: 3.3
Tested up to: 3.3.1
Stable tag: 1.0.1

A WordPress plugin to display your last Foursquare checkin as a map widget, fully authenticated via OAuth 2.0.

== Description ==

This plugin allows you to display your last Foursquare checkin as a map widget on the sidebar of your WordPress powered site.

Setting and options include:

1. Associate your WordPress powered site with your [Foursquare](https://foursquare.com/) account using [OAuth 2.0](http://oauth.net/2/), which keeps your personal information safe and secure.
1. Add your authentication credentials for the [Nokia Location APIs](http://www.developer.nokia.com/Develop/Maps/), either within the plugin's settings and options or via the [WP Nokia Auth](http://wordpress.org/extend/plugins/wp-nokia-auth/) plugin.
1. Choose the width and height of the widget and map on the sidebar.
1. Choose the zoom level of the map display.
1. Choose whether to show private checkins on the map.

The current version of this plugin allows you to associate a single Foursquare account with your WordPress site; associating multiple Foursquare accounts, one per user account is not currently supported.

== Installation ==

1. You can install WP Quadratum automatically from the WordPress admin panel. From the Dashboard, navigate to the *Plugins / Add New* page and search for *"WP Quadratum"* and click on the *"Install Now"* link.
1. Or you can install WP Quadratum manually. Download the plugin Zip archive and uncompress it. Copy or upload the `wp-quadratum` folder to the `wp-content/plugins` folder on your web server.
1. Activate the plugin. From the Dashboard, navigate to Plugins and click on the *"Activate"* link under the entry for WP Quadratum.
1. Configure your Foursquare credentials; from the Dashboard, navigate to the *Settings / WP Quadratum* page or click on the *"Settings"* link from the Plugins page on the Dashboard.
1. To display your Foursquare checkins, WP Quadratum needs to be authorised to access your Foursquare account information; this is a simple, safe and secure 3 step process. QP Quadratum never sees your account login information and cannot store any personally identifiable information.
1. Step 1. Register this WordPress site as a Foursquare application on the [Foursquare OAuth Consumer Registration](https://foursquare.com/oauth/register) page. If you're not currently logged into your Foursquare account, you'll need to login with the Foursquare account whose checkins you want WP Quadratum to display. The *Application Name* is a label you want to use to identify this connection to your Foursquare account. The *Application Web Site* is the URL of your Wordpress site. The *Callback URL* will be provided for you and will be along the lines of http://www.yoursite.com/wp-content/plugins/wp-quadratum/includes/wp-quadratum-callback.php (this is just an example, *don't use this URL*). Once you have successfully registered your site, you'll be provided with two keys, the *Client ID* and the *Client Secret*.
1. Step 2. Copy and paste the supplied *Client ID* and *Client Secret* into the respective WP Quadratum setting fields. Click on the *"Save Changes"* button to preserve them.
1. Step 3. You should now be authorised and ready to go; click on the *Connect to Foursquare* button.
1. If you have the WP Nokia Auth plugin installed and configured, your Nokia Location API credentials will be shown in read-only form.
1. If you don't have the WP Nokia Auth plugin installed and configured, you can enter your Nokia Location API credentials to give you, amongst other benefits, an increased per month transaction limit. Click on the *Save Changes* button to save your credentials.
1. Add and configure a WP Quadratum Widget. From the Dashboard, navigate to *Appearance / Widgets* and drag the WP Quadratum Widget to your desired widget area.
1. You can configure the widget's title, with widget's width and map height in px, the map zoom level and whether to show private checkins or not. Click on the *Save* button to preserve your changes.

== Frequently Asked Questions ==

= How do I get help or support for this plugin? =

In short, very easily. But before you read any further, take a look at [Asking For WordPress Plugin Help And Support Without Tears](http://www.vicchi.org/2012/03/31/asking-for-wordpress-plugin-help-and-support-without-tears/) before firing off a question. In order of preference, you can ask a question on the [WordPress support forum](http://wordpress.org/tags/wp-quadratum?forum_id=10); this is by far the best way so that other users can follow the conversation. You can ask me a question on Twitter; I'm [@vicchi](http://twitter.com/vicchi). Or you can drop me an email instead. I can't promise to answer your question but I do promise to answer and do my best to help.

= Is there a web site for this plugin? =

Absolutely. Go to the [WP Quadratum home page](http://www.vicchi.org/codeage/wp-quadratum/) for the latest information. There's also the official [WordPress plugin repository page](http://wordpress.org/extend/plugins/wp-quadratum/) and the [source for the plugin is on GitHub](http://vicchi.github.com/wp-quadratum/) as well.

= I have multiple authors on my site; can I have a widget for each author's Foursquare account? =

In the current version, no. In the current version, you can link a single Foursquare account with your WordPress site (multi-site or network sites may work, assuming each site is for a single user but I haven't tested this). The plugin is currently designed to support a WordPress site which is used for a personal blog (in other words, exactly the way my site is set up). Future versions of the plugin *may* support this if people ask for this feature (assuming anyone apart from myself actually *uses* it!).

= Nokia Maps? Really? =

Yes. Really. At the time of writing (April 2012) 196 countries, 75M Places, 2.4M map changes a day. That sort of really. All available through a set of developer friendly APIs.

= OK. Nokia Maps. I get it. But why register? =

The Nokia Location APIs work if you don't register. But they work even better and you can do even more if you do register. Take transactional limits. Unregistered users of the Location APIs get 1 million transactions over a lifetime. 1 million sounds a lot but it soon mounts up. Registered users get 2 million transactions. *Per month*. [Plus a whole lot more](http://www.developer.nokia.com/Develop/Maps/Quota/).

= Why are you so pro Nokia Maps? =

A disclaimer is in order. I work for Nokia's Location & Commerce group, that produces Nokia Maps. I see what goes into the map and what gets displayed. I'm very pro Nokia Maps for just this reason.

= What about other maps providers? Google, Mapquest, OpenStreetMap? =

Right now, Nokia Maps is the only supported map provider. I work for Nokia and I wanted to see Nokia Maps on my personal blog. In a future release, I'll probably add support for the [Mapstraction API](http://mapstraction.com/) so you can choose your own desired mapping provider.

= I want to amend/hack/augment this plugin; can I do the same? =

Totally; this plugin is licensed under the GNU General Public License v2 (GPLV2). See http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt for the full license terms.

== Screenshots ==

1. Clean installation
1. Foursquare credentials entered and saved
1. Successfully authenticated with Foursquare
1. Nokia Location API credentials provided via the WP Nokia Auth plugin
1. Configured widget
1. Sample widget in place on the sidebar

== Changelog ==

The current version is 1.0 (2012.04.12)

= 1.0 =
* First version of WP Quadratum released

== Upgrade Notice ==

= 1.0 =
* This is the first version of WP Quadratum
