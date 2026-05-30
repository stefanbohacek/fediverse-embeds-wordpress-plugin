<?php

/**
 * Plugin Name: Fediverse Embeds
 * Plugin URI: https://stefanbohacek.com/project/wordpress-plugin-for-fediverse-embeds/
 * Description: Embed posts from the fediverse.
 * Version: 1.6.7
 * Author: stefanbohacek
 * License: GPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: fediverse-embeds
 *
 * @package ftf-fediverse-embeds
 */


defined("ABSPATH") || exit;

define("FTF_FEDIVERSE_EMBEDS_VERSION", "1.6.7");

if (!defined("FTF_SHOW_ADVANCED_SETTINGS")) {
    define("FTF_SHOW_ADVANCED_SETTINGS", true);
}

/* External dependencies */

require_once __DIR__ . "/vendor/autoload.php";

/* Polyfills */

require_once __DIR__ . "/includes/polyfills.php";

/* Internal helper classes */

use FTF_Fediverse_Embeds\Embed_Posts;
use FTF_Fediverse_Embeds\Enqueue_Assets;
use FTF_Fediverse_Embeds\Media_Proxy;
use FTF_Fediverse_Embeds\Post_Manager;
use FTF_Fediverse_Embeds\Settings;
use FTF_Fediverse_Embeds\Site_Info;
use FTF_Fediverse_Embeds\Database;

add_action("plugins_loaded", function () {
    new Embed_Posts();
    new Enqueue_Assets();
    new Media_Proxy();
    new Settings();
    if (is_admin()) {
        new Post_Manager();
    }
    new Site_Info();
});

register_activation_hook(__FILE__, function () {
    $db = new Database();
    $db->create_database();
});

add_action("admin_init", function () {
    $stored_version = get_option("ftf_fediverse_embeds_version");
    if ($stored_version !== FTF_FEDIVERSE_EMBEDS_VERSION) {
        $db = new Database();
        $db->create_database();
        update_option("ftf_fediverse_embeds_version", FTF_FEDIVERSE_EMBEDS_VERSION);
    }
});
