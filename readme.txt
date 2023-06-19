=== Fediverse Embeds ===
Contributors: stefanbohacek
Tags: fediverse, mastodon, calckey, post, toot, embed
Requires at least: 5.0
Tested up to: 6.2.2
Stable tag: 1.0.0
Requires PHP: 8.0
License: MIT
License URI: https://opensource.org/licenses/MIT

Embed fediverse posts easily.

== Description ==

[Learn more](https://stefanbohacek.com/project/fediverse-embeds-wordpress-plugin/) | [View source](https://github.com/stefanbohacek/fediverse-embeds-wordpress-plugin)

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