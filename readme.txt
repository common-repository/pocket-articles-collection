=== Pocket Articles Collection ===
Contributors: Martin Hallonqvist
Tags: Pocket Articles
Requires at least: 3.0
Tested up to: 3.6
Stable tag: 1.0.0
License: GNU General Public License

Pocket Articles Collection plugin helps you collect and display your favourite Pocket articles in your WordPress blog.

== Description ==

Pocket Articles Collection plugin helps you collect, manage and display your favourite Pocket articles in your WordPress blog.


**Features and notes**

* **Admin interface**: An admin interface to add, edit and manage Pocket Articles. Details such as title and source URL of the Article, and attributes like tags and visibility, can be specified. The 'Pocket Articles' menu in the WP admin navigation leads to the Pocket Articles admin interface.
* **Backgrond downloading of new articles**: Once every hour, the Pocket articles plugin will connect to the appointed Pocket account and download all articles that's been added since the job was last run.
* **Shortcode**: Pocket Articles can be displayed in a WordPress page by placing a `[pocketarticlecoll]`shortcode. A few examples are provided below. For more examples and the full list of arguments, please refer the [plugin homepage](http://www.thestrongtype.com/resources/pocket-articles-collection-wordpress-plugin/) or 'other notes'. 
	* Placing `[pocketarticlecoll]` in the page displays all Pocket articles.
	* `[pocketarticlecoll title="Somebody"]` displays Pocket articles titleed by Somebody.
	* `[pocketarticlecoll tags="tag1,tag2,tag3"]` displays Pocket articles tagged tag1 or tag2 or tag3, one or more or all of these
	* `[pocketarticlecoll orderby="random" limit=1]` displays a random pocketarticle
* **The template function**: To code the random pocketarticle functionality directly into a template file, the template function `pocketarticlescollection_pocketarticle()` can be used. Please refer the plugin homepage or 'other notes' for details.
* Compatible with WordPress 3.0 multi-site functionality.
* The plugin suppports localization. Refer the plugin page or 'other notes' for the full list of available languages and the respective translators. 

For more information, visit the [plugin homepage](http://www.thestrongtype.com/resources/pocket-articles-collection-wordpress-plugin/), where yo can also provide feed back.

== Installation ==
1. Upload `pocketarticles-collection` directory to the `/wp-content/plugins/` directory
2. Activate the 'Pocket Articles Collection' plugin through the 'Plugins' menu in WordPress
3. Open the Settings page for "Pocket Articles"
4. Enter your "Consumer Key" and your "Access Token" for Pocket if you want to use your own.
   or
   Click on the button on the top of the settings page that reads "Click here if you want to authorize your app to your pocket account". The follow the instructions and log in with your account to Pocket and go thorugh with the athorization. The access token will then be filled in for you, using the "Pocket Articles Collection Plugin" Pocket application.
5. Wait for the background process to run (you will see each time it runs at the bottom of the settings page...) and automatically download your pocket articles for display.

== Screenshots ==
1. Settings section
2. Pocket article administration
3. Pocket articles listed
 
== Frequently Asked Questions ==

= How to change the random Pocket article text color? =

Styling such as text color, font size, background color, etc., of the random pocket article can be customized by editing the pocketarticles-collection.css file. Please also note that any updates to the plugin will overwrite your customized css file, so it's advisable to backup your customization before updating the plugin.

= How to change the link text from 'Next Pocket article »' to something else? =

Change the value of the variable `$pocketarticlescollection_next_pocketarticle` on line 34 of the pocketarticles-collection.php file.

= How to change the admin access level setting for the Pocket articles collection admin page? =

Change the value of the variable `$pocketarticlescollection_admin_userlevel` on line 37 of the pocketarticles-collection.php file. Refer [WordPress documentation](http://codex.wordpress.org/Roles_and_Capabilities) for more information about user roles and capabilities.

== The [pocketarticlecoll] shortcode ==
Pocket articles can be displayed in a page by placing the shortcode `[pocketarticlecoll]`. This will display all the public Pocket articles ordered by the pocketarticle id.

Different attributes can be specified to customize the way the Pocket articles are displayed. Here's the list of attributes:

* **id** *(integer)*
	* For example, `[pocketarticlecoll id=3]` displays a single pocketarticle, the id of which is 3. If there is no pocketarticle with the id 3, nothing is displayed.
	* This overrides all other attributes. That is, if id attribute is specified, any other attribute specified is ignored.
	
* **title** *(string)*
	* `[pocketarticlecoll title="Somebody"]` displays all Pocket articles titleed 'Somebody'.

* **sourceurl** *(string)*	
	* `[pocketarticlecoll sourceurl="Something"]` displays Pocket articles with the sourceurl 'Something'.

* **tags** *(string, comma separated)*	
	* `[pocketarticlecoll tags="tag1"]` displays all Pocket articles tagged 'tag1'.
	* `[pocketarticlecoll tags="tag1, tag2, tag3"]` displays Pocket articles tagged 'tag1' or 'tag2' or 'tag3', one or more or all of these.

* **orderby** *(string)*
	* When multiple Pocket articles are displayed, the Pocket articles or ordered based on this value. The value can be either of these:
		* 'pocketarticle_id' (default)
		* 'title'
		* 'sourceurl'
		* 'time_added'
		* 'random'
	
* **order** *(string)* 
	* The value can be either 'ASC' (default) or 'DESC', for ascending and descending order respectively.
	* For example, `[pocketarticlecoll orderby="time_added" order="DESC"]` will display all the Pocket articles in the order of date added, latest first and the earliest last.
	
* **paging** *(boolean)*
	* The values can be:
		* false (or 0) (default)
		* true (or 1) -- introduces paging. This is used in conjunction with `limit_per_page` (see below).
	* For example, `[pocketarticlecoll paging=true limit_per_page=30]` will introduce paging with maximum of 30 Pocket articles per page.
	* Note: if `orderby="random"` is used, paging is ignored.

* **page** *(integer)*
	* The page to start with on a paged article collection.

* **limit_per_page** *(integer)*
	* The maximum number of Pocket articles to be displayed in a page when paging is introduced, as described above.
	* The defualt value is 10. For example, `[pocketarticlecoll paging=true]` will introduce paging with maximum of 10 Pocket articles per page.

* **limit** *(integer)*
	* The maximum number of Pocket articles to be displayed in a single page ie., when paging is 'false'.
	* This can be used, for example, to display just a random pocketarticle. `[pocketarticlecoll orderby="random" limit=1]`

* **show_source** *(boolean)*
	* The values can be:
		* false (or 0) (default)
		* true (or 1) -- shows a link to the source of the article with only the url scheme and domain name (for example http://www.google.com)

* **show_date** *(boolean)*
	* The values can be:
		* false (or 0) (default)
		* true (or 1) -- shows the date when the article was downloaded to the wordpress installation by the plugin

== The pocketarticlescollection_pocketarticle() template function ==

The pocketarticlescollection_pocketarticle() template function can be used to display a random pocketarticle in places other than sidebar.

Usage: `<?php pocketarticlescollection_pocketarticle('arguments'); ?>`

The list of parameters (arguments) that can be passed on to this function:

* **show_title** *(boolean)*
	* To show/hide the title name
		* 1 - shows the title name (default)
		* 0 - hides the title name

* **show_sourceurl** *(boolean)*
	* To show/hide the sourceurl field
		* 1 - shows the sourceurl 
		* 0 - hides the sourceurl (default)

* **random** *(boolean)*
	* Refresh the pocketarticle in random or sequential order
		* 1 - random refresh (default)
		* 0 - sequential, with the latest pocketarticle first
		
* **tags** *(string)*
	* Comma separated list of tags. Only Pocket articles with one or more of these tags will be shown.
 
* **char_limit** *(integer)*
	* Pocket articles with number of characters more than this value will be filtered out. This is useful if you don't want to display long Pocket articles using this function. The default value is 500.

* **echo** *(boolean)*
	* Toggles the display of the random pocketarticle or return the pocketarticle as an HTML text string to be used in PHP. The default value is 1 (display the pocketarticle). Valid values:
		* 1 (true) - default
		* 0 (false) 

**Example usage:**

* `<?php pocketarticlescollection_pocketarticle(); ?>`

	* Uses the default values for the parameters. Shows title, hides sourceurl, shows the 'Next pocketarticle' link, no tags filtering, no character limit, displays the pocketarticle.

* `<?php pocketarticlescollection_pocketarticle('show_title=0&show_sourceurl=1&tags=fun,fav'); ?>`

	* Hides title, shows sourceurl, only Pocket articles tagged with 'fun' or 'fav' or both are shown. 'Next pocketarticle' link is shown (default) and no character limit (default).

== Localization ==

As of the current version, localization is available in the following languages (code / language / title):

* 'en' / English / [Martin Hallonqvist]
* 'sv_SE' / Swedish / [Martin Hallonqvist]

You can translate the plugin in your language if it's not done already. The localization template file (pocketarticles-collection.pot) can be found in the 'languages' folder of the plugin.

==Changelog==

* **2013-09-01: Version 0.9.0**
    * Initial release

* **2013-11-13: Version 1.0.0**
    * First verified version.
	* Changed default ordering of the articles on the administration page to descending ID (that is, the last one imported shown first)
	* Added two new shortcode tags (show_source and show_date)

== Upgrade Notice ==
