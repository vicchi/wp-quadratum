=== WP Quadratum ===
Contributors: vicchi
Donate Link: http://www.vicchi.org/codeage/donate/
Tags: wp-quadratum, maps, map, foursquare, checkins, checkin, widget, swarm
Requires at least: 3.9.0
Tested up to: 4.1.0
Stable tag: 1.3.1.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Display your last Swarm checkin as a map widget in the sidebar or embedded in a post or page, fully authenticated via OAuth 2.0.

== Description ==

This plugin allows you to display your last Swarm checkin as a map widget on the sidebar or embedded via a shortcode in a post or page of your WordPress powered site.

Setting and options include:

1. Associate your WordPress powered site with your [Foursquare](https://foursquare.com/) account using [OAuth 2.0](http://oauth.net/2/), which keeps your personal information safe and secure.
1. Choose which map provider you want your checkin shown on; you can choose from:
	1. [HERE Maps](http://developer.here.com/javascript_api)
	1. [Google Maps v3](https://developers.google.com/maps/documentation/javascript/)
	1. [Bing Maps v7](http://msdn.microsoft.com/en-us/library/gg427610.aspx)
	1. [OpenStreetMap](http://www.openstreetmap.org) from [Leaflet](http://leafletjs.com/)
	1. [OpenStreetMap](http://www.openstreetmap.org) from [OpenLayers](http://openlayers.org)
	1. [OpenStreetMap]() from [MapQuest](http://developer.mapquest.com/web/products/open/sdk)
1. Add your maps API key(s) for your chosen map provider; HERE, Google, Bing and MapQuest maps all require API keys.
1. Choose the width and height of the widget and map on the sidebar. The width and height can be specified either as pixels (`px`) or as a percentage.
1. Choose the zoom level of the map display.

The <em>strapline</em> text containing the venue name, venue URL and timestamp of your last Swarm checkin can be customised via the plugin's filters. See the *Filter Support And Usage* section for more information.

The current version of this plugin allows you to associate a single Foursquare account with your WordPress site; associating multiple Foursquare accounts, one per user account is not currently supported.

== Installation ==

1. You can install WP Quadratum automatically from the WordPress admin panel. From the Dashboard, navigate to the *Plugins / Add New* page and search for *"WP Quadratum"* and click on the *"Install Now"* link.
1. Or you can install WP Quadratum manually. Download the plugin Zip archive and uncompress it. Copy or upload the `wp-quadratum` folder to the `wp-content/plugins` folder on your web server.
1. Activate the plugin. From the Dashboard, navigate to Plugins and click on the *"Activate"* link under the entry for WP Quadratum.
1. Configure your Foursquare credentials; from the Dashboard, navigate to the *Settings / WP Quadratum* page or click on the *"Settings"* link from the Plugins page on the Dashboard.
1. To display your Swarm checkins, WP Quadratum needs to be authorised to access your Foursquare account information; this is a simple, safe and secure 3 step process. WP Quadratum never sees your account login information and cannot store any personally identifiable information.
	1. Register your WordPress site as a Foursquare application on the [Foursquare App Registration](https://foursquare.com/developers/register) page. If you're not currently logged into your Foursquare account, you'll need to login with the Foursquare account whose checkins you want WP Quadratum to display. The *Your app name* field is a label you want to use to identify this connection to your Foursquare account. The *Download / welcome page url* is the URL of your Wordpress site. The *Redirect URI* will be provided for you and will be along the lines of `http://www.yoursite.com/wp-content/plugins/wp-quadratum/includes/wp-quadratum-callback.php` (this is just an example, *don't use this URL*). *Push API Notifications* should be set to *Disable pushes to this app*. All other fields can be left at their default values. Once you have successfully registered your site, you'll be provided with two keys, the *Client ID* and the *Client Secret*.
	1. Copy and paste the supplied *Client ID* and *Client Secret* into the respective WP Quadratum setting fields. Click on the *"Save Changes"* button to preserve them.
	1. You should now be authorised and ready to go; click on the *Connect to Foursquare* button.
1. Choose your mapping provider. From the *Maps* tab, select the map provider from the *Maps Provider* drop down.
1. If your chosen mapping provider requires an API key or keys, enter them as requested. If you don't have an API key, each maps provider tab has a link to the provider's site where you can sign up and obtain your API key. Click on the *Save Changes* button to save your credentials.
1. Add and configure a WP Quadratum Widget. From the Dashboard, navigate to *Appearance / Widgets* and drag the WP Quadratum Widget to your desired widget area.
1. You can configure the widget's title, with widget's width and map height in `px` or as a percentage and the map zoom level. Click on the *Save* button to preserve your changes.

== Frequently Asked Questions ==

= How do I get help or support for this plugin? =

In short, very easily. But before you read any further, take a look at [Asking For WordPress Plugin Help And Support Without Tears](http://www.vicchi.org/2012/03/31/asking-for-wordpress-plugin-help-and-support-without-tears/) before firing off a question. In order of preference, you can ask a question on the [WordPress support forum](http://wordpress.org/tags/wp-quadratum?forum_id=10); this is by far the best way so that other users can follow the conversation. You can ask me a question on Twitter; I'm [@vicchi](http://twitter.com/vicchi). Or you can drop me an email instead. I can't promise to answer your question but I do promise to answer and do my best to help.

= Is there a web site for this plugin? =

Absolutely. Go to the [WP Quadratum home page](http://www.vicchi.org/codeage/wp-quadratum/) for the latest information. There's also the official [WordPress plugin repository page](http://wordpress.org/extend/plugins/wp-quadratum/) and the [source for the plugin is on GitHub](http://vicchi.github.com/wp-quadratum/) as well.

= I have multiple authors on my site; can I have a widget for each author's Foursquare account? =

In the current version, no. In the current version, you can link a single Foursquare account with your WordPress site (multi-site or network sites may work, assuming each site is for a single user but I haven't tested this). The plugin is currently designed to support a WordPress site which is used for a personal blog (in other words, exactly the way my site is set up). Future versions of the plugin *may* support this if people ask for this feature (assuming anyone apart from myself actually *uses* it!).

= Can I change the format of the strapline that appears under the checkin map? =

Yes. The `wp_quadratum_strapline` filter is for just this purpose. The filter is passed the default strapline as well as the URL to the Foursquare venue checked in at, the name of the venue and the date and time of the checkin as a UNIX timestamp. See the *Filter Support And Usage* section for more information.

= I want to amend/hack/augment this plugin; can I do the same? =

Totally; this plugin is licensed under the GNU General Public License v2 (GPLV2). See http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt for the full license terms.

= Where does the name WP Quadratum come from? =
WP Quadratum is named after both the Latin words *quattor*, meaning **four** and *quadratum*, meaning **square**.

== Screenshots ==

1. Installed Plugins: Authentication prompt shown after activating plugin for the first time
1. Settings and Options: Foursquare Tab; No Client ID or Client Secret entered or saved
1. Settings and Options: Foursquare Tab; Client ID and Client Secret saved
1. Foursquare Authentication: Allow or deny plugin access to your Foursquare account
1. Settings and Options: Foursquare Tab; Successfully authenticated with Foursquare
1. Settings and Options: Maps Tab; HERE Maps configuration
1. Settings and Options: Maps Tab; Google Maps v3 configuration
1. Settings and Options: Maps Tab; Leaflet Maps configuration
1. Settings and Options: Maps Tab; Bing Maps v7 configuration
1. Settings and Options: Maps Tab; OpenLayers Maps configuration
1. Settings and Options: Maps Tab; MapQuest Open Maps configuration
1. Settings and Options: Shortcode Tab; [wp_quadratum] shortcode enabled
1. Settings and Options: Shortcode Tab; [wp_quadratum_locality] shortcode enabled, no Factual OAuth Key or Secret entered or saved
1. Settings and Options: Shortcode Tab; [wp_quadratum_locality] shortcode enabled, Factual OAuth Key and Secret saved
1. Settings and Options: Defaults Tab
1. Settings and Options: Colophon Tab
1. Appearance: Widgets; Sample widget settings
1. Sample Widget: Google v3, HERE and Leaflet maps
1. Sample Widget: Bing v7, OpenLayers and MapQuest Open maps

== Changelog ==

The current version is 1.3.1.4 (2015.02.05)

= 1.3.1.4 =
* Released 2015.02.05
* Fixed: Updated venue category icon handling to correctly display venue icons
* Changed: Refer to checkins as Swarm checkins and not Foursquare checkins
* Removed: Locally cached category icons

= 1.3.1.3 =
* Released: 2014.07.09
* Fixed: Updated category icon handling in line with Foursquare API changes
* Added: Local black and white cached category icons

= 1.3.1.2 =
* Released: 2014.07.07
* Fixed: Updated Foursquare `DATEVERIFIED` version parameter to prevent API calls verified prior to `20120609` being rejected.

= 1.3.1.1 =
* Released: 2013.11.28
* Fixed: Bug in checking for when the `[wp_quadratum_map]` and `[wpq_map]` shortcodes are enabled.
* Updated: Factual PHP driver to latest version.

= 1.3.1 =
* Released: 2013.10.23
* Added: Caching of last good response from the Foursquare API, allowing the plugin to operate if the API is temporarily down.
* Added: New locality shortcodes, `[wp_quadratum_locality]` (and `[wpq_locality]` as an alias) to allow the last checkin's venue name, address, region, postal code, coordinates, timezone and/or timezone offset to be embedded in posts or pages.
* Added: New checkin map shortcodes, `[wp_quadratum_map]` and `[wpq_map]` as aliases for the plugin's `[wp_quadratum]` shortcode.
* Added: Ability for the plugin's shortcodes to be made configurable, on and off.
* Added: Ability to backfill the response of the Foursquare API, via Factual's reverse geocoder, to cope with cases when a Foursquare venue doesn't have a complete set of metadata attributes to be used in conjunction with the locality shortcodes.
* Added: New filter, `wp_quadratum_locality`, to filter and amend the output of the `[wp_quadratum_locality]` shortcode.
* Fixed: Detect and trap an invalid or empty response from the Foursquare API, preventing numerous PHP warnings from polluting a post or page.
* Other: Fully compatible with WordPress v3.7 "Basie".

= 1.3.0 =
* Released: 2013.08.22
* Added: Support for HERE, Leaflet, MapQuest Open and Bing maps.
* Added: All maps API JS now loads in the page footer to speed up overall page loading times.
* Added: Support for a new filter, `wp_quadratum_checkin` giving full access to all the Foursquare checkin metadata that the Foursquare API returns.
* Added: Support for specifying the height and width of the map as a percentage as well as in px.
* Fixed: Update the admin 'Foursquare' tab to use the new app registration URL. Adjust the help text to reflect the new app registration layout on `foursquare.com/developers/register`.
* Fixed: Updated Mapstraction support to pull JS code from `mapstraction.com` rather than `github.com/mapstraction/mxn` to work around new GitHub content serving policies.
* Removed: Support for filtering out private checkins; the Foursquare API no longer supports this.
* Removed: Support for the CloudMade maps API; this has now been superseded by Leaflet maps.
* Removed: Support for the Nokia maps API; this has now been superseded by HERE maps.
* Removed: Support for authenticating Nokia maps via WP Nokia Auth; Nokia maps are now superseded by HERE maps.
* Removed: Support for the `Widget ID` field from the plugin's widget; the plugin now uses the WordPress assigned widget instance.
* Other: Transitioned to `WP_Mapstraction` from `WP_MXNHelper`.

= 1.2.0 =
* Released: 2012.11.06
* Added: Support for the `wp_quadratum_strapline` filter.
* Added: Enqueue non-minified versions of the plugin's CSS and JS files if `WP_DEBUG` or `WPQUADRATUM_DEBUG` are defined.
* Other: Updated to latest versions of `WP_PluginBase` and `WP_MXNHelper`.
* Other: Moved all submodule classes/libraries from the plugin's root directory to /includes.

= 1.1.0 =
* Released: 2012.07.01
* Added: Support for Nokia, CloudMade, Google and OpenLayers maps via Mapstraction
* Added: Split plugin settings and options into Foursquare, Maps, Defaults and Colophon tabs
* Added: `[wp_quadratum]` shortcode to allow a checkin map to be embedded in posts and pages.
* Fixed: Support for Internet Explorer compatibility for Nokia Maps.

= 1.0.2 =
Summary: Minor fixes to widget HTML structure
Fixed: Non W3C/HTML4 compliant widget code which caused the map not to be displayed when viewed with Internet Explorer

= 1.0.1 =
Summary: Minor fixes to PHP base class.
Fixed: An issue with an old version of WP_PluginBase, the PHP class which WP Quadratum extends.

= 1.0.0 =
* First version of WP Quadratum released

== Upgrade Notice ==

= 1.3.1.4 =
Updated venue category icon handling to correctly display venue icons and refer to checkins as Swarm checkins and not Foursquare checkins

= 1.3.1.3 =
Updated category icon handling in line with Foursquare API changes and added black and white cached icons

= 1.3.1.2 =
Updated Foursquare `DATEVERIFIED` version parameter to prevent API calls verified prior to `20120609` being rejected.

= 1.3.1.1 =
Fixed bug in checking for when the `[wp_quadratum_map]` and `[wpq_map]` shortcodes are enabled. Updated Factual PHP driver to latest version.

= 1.3.1 =
Cache last good Foursquare checkin response for when the Foursquare API is down. Add new locality shortcode. This release is fully compatible with WordPress 3.7 "Basie".

= 1.3.0 =
Fix issue where the map did not load due to new GitHub content serving policy. Add support for Leaflet, Bing and MapQuest Open maps. Add new `wp_quadratum_checkin` filter.

= 1.2 =
Adds support for the `wp_quadratum_strapline` filter plus internal housekeeping and library upgrades.

= 1.1 =
Adds support for multiple map providers, Internet Explorer map rendering issues and a new shortcode. This is the 4th. version of WP Quadratum.

= 1.0.2 =
This is the 3rd version of WP Quadratum; makes widget code W3C/HTML4 compliant, which was breaking widget display on Internet Explorer.

= 1.0.1 =
This is the 2nd version of WP Quadratum; fixing an issue with the PHP base class that the code extends.

= 1.0 =
* This is the first version of WP Quadratum

== Shortcode Support And Usage ==

WP Quadratum supports two shortcodes and three shortcode aliases; `[wp_quadratum_map]` to expand the shortcode in a post or page and replace it with the checkin map and `[wp_quadratum_locality]` to allow you to embed aspects of your last checkin in a post or page.

= [wp_quadratum_map] =

Adding this shortcode to the content of a post or page as content, expands the shortcode and replaces it with a checkin map.

The shortcode also supports multiple *attributes* which allow you to customise the way in which the shortcode is expanded into the checkin map:

* the `width` attribute
* the `width_units` attribute
* the `height` attribute
* the `height_units` attribute
* the `zoom` attribute

** The "width" Attribute **

The `width` attribute, in conjunction with the `height` attribute specifies the width of the map to be inserted into a post or page. If omitted, the map width defaults to a value of `300`.

** The "width_units" Attribute **

The `width_units` attribute, specifies how the value specified in the `width` attribute should be interpreted. Valid values for this attribute as `px` and `%`, denoting that the `width` attribute should be interpreted in pixels or as a percentage respectively. If omitted, this attribute defaults to a value of `px`.

** The "height" Attribute **

The `height` attribute, in conjunction with the `width` attribute specifies the height of the map to be inserted into a post or page. If omitted, the map height defaults to a value of `300`.

** The "height_units" Attribute **

The `height_units` attribute, specifies how the value specified in the `height` attribute should be interpreted. Valid values for this attribute as `px` and `%`, denoting that the `height` attribute should be interpreted in pixels or as a percentage respectively. If omitted, this attribute defaults to a value of `px`.

** The "zoom" Attribute **

The `zoom` attribute specifies the zoom level to be used when displaying the checkin map. If omitted, the zoom level defaults to a value of `16` which is roughly analogous to a neighbourhood zoom.

= [wp_quadratum] =

The `[wp_quadratum]` shortcode is an alias for the `[wp_quadratum_map]` shortcode and has the same functionality.

= [wpq_map] =

The `[wpq_map]` shortcode is an alias for the `[wp_quadratum_map]` shortcode and has the same functionality.

= [wp_quadratum_locality] =

Adding this shortcode to the content of a post or page, expands the shortcode and replaces it with information about your last Foursquare checkin. The information to be displayed is selected by the shortcode's `type` attribute, which allows you to select the venue name, address, region, postal code, coordinates, timezone or timezone offset.

By default, the `[wp_quadratum_locality]` shortcode and the `[wpq_locality]` alias are disabled. This is because not all Foursquare venues contain the full scope of locality elements that the shortcode supports (the minimum requirements for a Foursquare venue are name, category and coordinates). To backfill any missing venue elements, WP Quadratum uses a *reverse geocoding* service from [Factual](http://www.factual.com/) to supply the missing information.

To enable the `[wp_quadratum_locality]` shortcode, from the Dashboard navigate to *Settings / WP Quadratum* and click on the *Shortcodes* tab. Select the *Enable Locality Shortcode Usage* checkbox and the *Factual OAuth Settings* meta-box will appear. You'll then need to sign up for a [Factual API key](https://www.factual.com/api-keys/request) after which you'll be given an *OAuth Key* and *OAuth Secret*. Copy and paste these into the *Factual OAuth* text fields and click on *Save Shortcode Settings*. You'll now be able to use the `[wp_quadratum_locality]` shortcode or its alias.


**The "type" Attribute**

The `type` attribute specifies the element of your last Foursquare checkin that is to be displayed in a post or page and can take one of the following values:

* `venue` - the name of the last Foursquare venue you checked into.
* `address` - the street address of the venue; not including the region, locality or postal code.
* `region` - the region of the venue; the geographic context of the region will vary from country to country but is roughly analogous to the venue's city.
* `postcode` - the postal code of the venue, if the country or region supports postal codes.
* `coordinates` - the geographic coordinates of the venue, in the form latitude,longitude.
* `timezone` - the timezone name of the time of the checkin, such as `Europe/London`.
* `tzoffset` - the offset from GMT of the time of the checkin, in the form GMT[-+]hours, such as GMT-1 or GMT+2.
* `locality` - the locality of the venue; the geographic context of the locality will vary according to country, but is roughly analogous to the town or neighbourhood.

If the `type` attribute is not supplied, or if the value of this attribute is not one of the above values, `type="locality"` will be assumed. The shortcode's replacement value can be modified via the plugin's `wp_quadratum_locality` filter; see the *Filter Support and Usage* section for more information.

= [wpq_locality] =

The `[wpq_locality]` shortcode is an alias for the `[wp_quadratum_locality]` shortcode and has the same functionality.

== Filter Support And Usage ==

WP Quadratum supports three filters, which are described in more detail below. The plugin's filters allow you to:

* change the descriptive text that appears immediately below the map when displayed via the plugin's widget or shortcode.
* gain access to the checkin metadata that is returned from the Foursquare API
* change the output of the [wp_quadratum_locality]` shortcode

= wp_quadratum_checkin =

Allow a filter hook function to gain access to the checkin metadata that is returned from the Foursquare API and which is used to build the checkin map and strapline. It's important to note that the implementation of this filter isn't strictly a WordPress filter per se. The user defined hook function is passed only the checkin metadata. Any changes made to the metadata will not be reflected in the output of the plugin's or shortcode's map, nor will any return value from the hook function be honoured by the plugin. The filter will be called before the `wp_quadratum_strapline` filter, if used, allowing you to store the checkin contents and use them within the `wp_quadratum_strapline` filter hook.

The contents of the checkin data this filter can access are a `Checkin Response` object, which is documented on the [Foursquare Developer Site](https://developer.foursquare.com/docs/responses/checkin).

*Example:* Store the contents of the Foursquare checkin that the plugin will be to display the checkin map.

`$last_checkin = null;
add_filter('wp_quadratum_checkin', store_last_checkin, 10, 1);
function store_last_checkin($checkin) {
	$last_checkin = $checkin;
}`

= wp_quadratum_strapline =

Applied to the strapline that is displayed via the plugin's widget or shortcode. The strapline is the text that appears immediately below the checkin map.

*Example:* Change the date and time formatting in the strapline

`add_filter('wp_quadratum_strapline', 'format_strapline', 10, 2);
function format_strapline($content, $params) {
	// $params = array (
	//		'venue-url' => '4Sq venue url for checkin',
	//		'venue-name' => 'checkin venue name',
	//		'checked-in-at' => 'timestamp of checkin'
	//	);

	$strapline = '<h5>Last seen at <a href="' . $params['venue-url'] . '" target="_blank">' . $params['venue-name'] . '</a> on ' . date('l jS \of F Y h:i:s A', $params['checked-in-at']) . '</h5>';
	return $strapline;
}`

= wp_quadratum_locality =

Applied to the replacement content of the `[wp_quadratum_locality]` shortcode immediately before the shortcode is replaced. The filter's hook function is passed two arguments; the shortcode's value and corresponding `type` attribute.

*Example:* Wrap each invocation of the `[wp_quadratum_locality]` shortcode in a `div` whose class includes the attribute type.

`add_filter('wp_quadratum_locality', 'format_locality', 10, 2);
function format_locality($value, $type) {
	$class = 'wp-quadratum-locality-' . $type;
	return '<div class="' . $class . '">' . $value . '</div>';
}`
