<?php
/**
 * Plugin Name: Fediverse Embeds
 * Plugin URI: https://wordpress.org/plugins/fediverse-embeds/
 * Description: Embed posts from the fediverse.
 * Version: 1.0.7
 * Author: stefanbohacek
 * Text Domain: ftf_fediverse_embeds
 *
 * @package ftf-fediverse-embeds
 */


defined('ABSPATH') || exit;

require_once __DIR__ . '/vendor/autoload.php';

use FTF_Fediverse_Embeds\Embed_Posts;
use FTF_Fediverse_Embeds\Enqueue_Assets;
use FTF_Fediverse_Embeds\Media_Proxy;
use FTF_Fediverse_Embeds\Settings;
use FTF_Fediverse_Embeds\Site_Info;
use FTF_Fediverse_Embeds\Database;

$embed_posts_init = new Embed_Posts();
$enqueue_assets_init = new Enqueue_Assets();
$media_proxy_init = new Media_Proxy();
$settings_init = new Settings();
$site_info_init = new Site_Info();

register_activation_hook(__FILE__, function(){
    $db = new Database();
    $db->create_database();
});
