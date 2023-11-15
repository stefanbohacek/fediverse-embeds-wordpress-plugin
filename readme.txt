=== Fediverse Embeds ===
Contributors: stefanbohacek
Tags: fediverse, mastodon, calckey, post, toot, embed
Requires at least: 5.0
Tested up to: 6.3
Stable tag: 1.0.1
Requires PHP: 8.0
License: MIT
License URI: https://opensource.org/licenses/MIT

Embed fediverse posts easily.

== Description ==

[Learn more](https://stefanbohacek.com/project/wordpress-plugin-for-fediverse-embeds/) | [View source](https://github.com/stefanbohacek/fediverse-embeds-wordpress-plugin)

== FAQ and Troubleshooting ==

**I need to run a script after the embeds are processed**

You can use the `ftf_fediverse_embeds_processed` event. Using jQuery as an example, you could do the following:

`
$(document).on('ftf_fediverse_embeds_processed', () => {
    $('.fediverse-post').each(() => {
        const $post = $(this);
        // Now you can do something with each post.
    });
});
`

== Privacy notice ==

When embedding a post from a fediverse server, the content of the post needs to be fetched from that server, which is then stored in your site's database. Based on the way you configure the plugin, additional requests will be made periodically to refresh the cached data.

When making requests to a third party server in general, the server will receive and may record the IP address of the server hosting your website. Please consult the privacy details and terms of use of each server you are embedding content from. (Example for mastodon.social: [About Mastodon](https://mastodon.social/privacy-policy), [Privacy policy](https://mastodon.social/privacy-policy))

== Changelog ==

= 1.0.1 =
* Keep the blockquote as parent element when rendering embedded post.

= 1.0 =
* Initial release.
