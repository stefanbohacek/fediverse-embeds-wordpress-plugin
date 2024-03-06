=== Fediverse Embeds ===
Contributors: fourtonfish
Tags: fediverse, mastodon, calckey, post, toot, embed
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.12
Requires PHP: 8.0
License: MIT
License URI: https://opensource.org/licenses/MIT

Embed fediverse posts easily.

== Description ==

This plugin lets you optimize and customize embedded posts from fediverse platform that support this feature.

Please visit the GitHub repo for instructions on [how to embed posts](https://github.com/stefanbohacek/fediverse-embeds-wordpress-plugin#supported-platforms) and for [general FAQ and troubleshooting tips](https://github.com/stefanbohacek/fediverse-embeds-wordpress-plugin#faq).

[Learn more](https://stefanbohacek.com/project/wordpress-plugin-for-fediverse-embeds/) | [View source](https://github.com/stefanbohacek/fediverse-embeds-wordpress-plugin)

== Privacy notice ==

When embedding a post from a fediverse server, the content of the post needs to be fetched from that server, which is then stored in your site's database. Based on the way you configure the plugin, additional requests will be made periodically to refresh the cached data.

When making requests to a third party server in general, the server will receive and may record the IP address of the server hosting your website. Please consult the privacy details and terms of use of each server you are embedding content from. (Example for mastodon.social: [About Mastodon](https://mastodon.social/privacy-policy), [Privacy policy](https://mastodon.social/privacy-policy))

== Changelog ==

= 1.0.12 =
* Added "defer" strategy to the main plugin JS file.

= 1.0.11 =
* Updated image alt text field.

= 1.0.10 =
* Minor code cleanup.

= 1.0.9 =
* Minor code cleanup.

= 1.0.8 =
* Minor code cleanup.

= 1.0.7 =
* Temporarily skip Pixelfed embeds.

= 1.0.6 =
* Display attached images in full height.

= 1.0.5 =
* Include the plugin version for easier debugging.

= 1.0.4 =
* Minor styling cleanup.

= 1.0.3 =
* Fixed fatal error when the simple_html_dom class already exists.

= 1.0.2 =
* Updated WordPress compatibility version.

= 1.0.1 =
* Keep the blockquote as parent element when rendering embedded post.

= 1.0 =
* Initial release.
