![Preview of multiple posts embedded with the Fediverse Embeds plugin.](./images/screenshots/main.png)

# Fediverse Embeds

Embed fediverse posts on your WordPress site. You can [download it from the WordPress plugin directory](https://wordpress.org/plugins/fediverse-embeds/), and read about the motivation behind this project [on my blog](https://stefanbohacek.com/project/wordpress-plugin-for-fediverse-embeds/).

Feedback and help will be very appreciated!

- see [open issues](https://github.com/stefanbohacek/fediverse-embeds-wordpress-plugin/issues?q=is%3Aissue+is%3Aopen+sort%3Aupdated-desc) or [create a new one](https://github.com/stefanbohacek/fediverse-embeds-wordpress-plugin/issues/new)
- [contact](https://stefanbohacek.com/contact/)

A few examples of the plugin in use:

- [alttexthalloffame.org](https://alttexthalloffame.org/)
- [stefanbohacek.com](https://stefanbohacek.com/blog/a-netizens-guide-to-mastodon-fediverse/)
- [botwiki.org](https://botwiki.org/blog/what-kind-of-bots-are-posting-in-the-fediverse/)

## Features

- **Show or hide post metrics**

![Two side-by-side screenshots comparing the same post with and without the number of likes/favorites, boosts/re-shares, and replies.](./images/screenshots/post-metrics.png)

- **Automatic dark and light theme**

![A diagonally split screenshot of an embedded post, showing a light and dark version.](./images/screenshots/dark-light-theme.png)

- **Labels for bots 🤖 and server admins 👑 (optional)**
- **Labels for updated 📝 and deleted posts 🗑️** (see notes on [how deleted posts are handled](https://github.com/stefanbohacek/fediverse-embeds-wordpress-plugin/issues/1))

## How to use

<!-- 1. [Install the plugin.](https://wordpress.org/plugins/ftf_fediverse_embeds) -->
1. Download and install the plugin.
2. Add the embed code to your post.

## Supported platforms

| **Platform**  | **Supported**   |
|---------------|-----------------|
| **Mastodon**  | ✔️ [How to embed](https://fedi.tips/how-to-embed-mastodon-posts-on-a-website/) |
| **Pleroma**   | ❌              |
| **Akkoma**    | ❌              |
| **Friendica** | ❌              |
| **Misskey**   | ❌ [#17](https://github.com/stefanbohacek/fediverse-embeds-wordpress-plugin/issues/17)          |
| **Calckey**   | ❌ [#16](https://github.com/stefanbohacek/fediverse-embeds-wordpress-plugin/issues/16)          |
| **Peertube**  | ❌ [#2](https://github.com/stefanbohacek/fediverse-embeds-wordpress-plugin/issues/2)           |
| **Pixelfed**  | ❌ [#14](https://github.com/stefanbohacek/fediverse-embeds-wordpress-plugin/issues/14)          |

## Privacy notice

When embedding a post from a fediverse server, the content of the post needs to be fetched from that server, which is then stored in your site's database. Based on the way you configure the plugin, additional requests will be made periodically to refresh the cached data.

When making requests to a third party server in general, the server will receive and may record the IP address of the server hosting your website.

## Development

```sh
# install dependencies
npm install
# build front-end scripts and styles
npm run dev
# when adding new PHP classes inside `includes`
composer dumpautoload -o 
```

## FAQ

### Images or posts are not loading

#### Embed code

Please make sure to copy the full embed code, including the `iframe` and the `script` tag.

#### API Endpoints

Please make sure that you're not blocking `/wp-json/ftf/*` endpoints, either via a plugin like [Disable WP REST API](I:\OneDrive\Projects\WordPress\Plugins\fediverse-embeds\git\README.md), or through server configuration or your firewall.

Note that individual servers have an option to prevent this plugin from loading data, see more details [below](#how-do-i-prevent-this-plugin-from-embedding-my-posts).
### How can I run my own code after all embeds are processed?

The plugin fires a custom `ftf_fediverse_embeds_posts_processed` event that passes the list of processed embeds. Here's an example of how it can be used:

```js
document.addEventListener('ftf_fediverse_embeds_posts_processed', (event) => {
    console.log(event.detail);

    event.detail.forEach(embed => {
        // do something with each embed
    });
});
```

Or with jQuery:

```js
$(document).on('ftf_fediverse_embeds_processed', () => {
    $('.fediverse-post').each(() => {
        const $post = $(this);
        // Now you can do something with each post.
    });
});
```

### How do I prevent this plugin from embedding my posts?

The plugin will use the following user agent when making requests to your fediverse servers:

```
FTF: Fediverse Embeds; WordPress/VERSION; https://WEBSITE.COM
```

`VERSION` is the version of the WordPress website making the request, and `WEBSITE.COM` is its URL, this is the default user agent used by WordPress.

The admin of your instance can then block any requests where the user agent contains `FTF: Fediverse Embeds`.
