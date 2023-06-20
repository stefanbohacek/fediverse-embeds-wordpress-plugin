![Preview of multiple posts embedded with the Fediverse Embeds plugin.](./images/screenshots/main.png)

# Fediverse Embeds

Embed fediverse posts on your WordPress site.

This plugin is ***under active development*** and will be available on wordpress.org/plugins once ready. In the meantime, you can download it from this repo and [install it manually](https://wordpress.com/support/plugins/install-a-plugin/#install-a-plugin-with-a-zip-file).

Feedback and help will be very appreciated!

- see [open issues](https://github.com/stefanbohacek/fediverse-embeds-wordpress-plugin/issues?q=is%3Aissue+is%3Aopen+sort%3Aupdated-desc) or [create a new one](https://github.com/stefanbohacek/fediverse-embeds-wordpress-plugin/issues/new)
- [contact](https://stefanbohacek.com/contact/)



A few examples of the plugin in use:

- [stefanbohacek.com](https://stefanbohacek.com/blog/a-netizens-guide-to-mastodon-fediverse/)
- [botwiki.org](https://botwiki.org/blog/what-kind-of-bots-are-posting-in-the-fediverse/)

## Features

- **Show or hide post metrics**

![Two side-by-side screenshots comparing the same post with and without the number of likes/favorites, boosts/re-shares, and replies.](./images/screenshots/post-metrics.png)

- **Automatic dark and light theme**

![A diagonally split screenshot of an embedded post, showing a light and dark version.](./images/screenshots/dark-light-theme.png)

- **Labels for bots 🤖 and server admins 👑 (optional)**
- **Labels for updated 📝 and deleted posts 🗑️** (see notes on [how deleted posts are handled](https://github.com/stefanbohacek/fediverse-embeds-wordpress-plugin/issues/1))

<!-- Learn more [on stefanbohacek.com](https://stefanbohacek.com/project/tweet-embeds-wordpress-plugin/). -->

## How to use

<!-- 1. [Install the plugin.](https://wordpress.org/plugins/ftf_fediverse_embeds) -->
1. Download and install the plugin.
2. Add the embed code to your post.

## Supported platforms

| **Platform**  | **Supported** | **How to embed** |
|---------------|---------------|------------------|
| **Mastodon**  | ✔️            | [How to embed](https://fedi.tips/how-to-embed-mastodon-posts-on-a-website/)  |
| **Pleroma**   | ❌            |                 |
| **Akkoma**    | ❌            |                 |
| **Friendica** | ❌            |                 |
| **Misskey**   | ❌            |                 |
| **Calckey**   | ❌            |                 |
| **Peertube**  | ❌            |                 |
| **Pixelfed**  | ❌            |                 |

- [Add support for embedding Peertube posts](https://github.com/stefanbohacek/fediverse-embeds-wordpress-plugin/issues/2)
- [Add support for embedding posts via oEmbed](https://github.com/stefanbohacek/fediverse-embeds-wordpress-plugin/issues/3)

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

### How do I prevent this plugin from embedding my posts?

The plugin will use the following user agent when making requests to your fediverse servers:

```
FTF: Fediverse Embeds; WordPress/VERSION; https://WEBSITE.COM
```

`VERSION` is the version of the WordPress website making the request, and `WEBSITE.COM` is its URL, this is the default user agent used by WordPress.

The admin of your instance can then block any requests where the user agent contains `FTF: Fediverse Embeds`.
