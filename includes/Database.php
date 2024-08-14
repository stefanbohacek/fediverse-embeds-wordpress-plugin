<?php

namespace FTF_Fediverse_Embeds;

use FTF_Fediverse_Embeds\Helpers;

class Database
{
    protected $table_name;

    function __construct()
    {
        $this->table_name = 'ftf_fediverse_embeds';
        // add_action('admin_menu', array($this, 'add_settings_page'));
        // add_filter('plugin_action_links_ftf_fediverse_embeds/index.php', array($this, 'settings_page_link'));
    }

    function create_database()
    {
        global $wpdb;
        $version = get_option('ftf_fediverse_embeds_version', '1.0');

        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . $this->table_name;

        $sql = "CREATE TABLE $table_name (
            `instance` VARCHAR(500),
            `post_id` BIGINT,
            `post_data` VARCHAR(12000),
            `last_updated` BIGINT
       ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    function save_post($instance, $post_id, $post_data)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $this->table_name;
        $post = $this->get_post($instance, $post_id);

        if ($post) {
            // Helpers::log_this('debug:updating_post', array(
            //     'status' => 'updating',
            //     'post' => $post,
            // ));
            $result = $wpdb->update(
                $table_name,
                array(
                    'instance' => $instance,
                    'post_id' => $post_id,
                    'post_data' => $post_data,
                    'last_updated' => time()
                ),
                array(
                    'instance' => $instance,
                    'post_id' => $post_id
                ),
                array('%s', '%d', '%s', '%d')
            );
        } else {
            // Helpers::log_this('debug:inserting_post', array(
            //     'status' => 'inserting',
            //     'post' => $post,
            // ));

            $result = $wpdb->insert(
                $table_name,
                array(
                    'instance' => $instance,
                    'post_id' => $post_id,
                    'post_data' => $post_data,
                    'last_updated' => time()
                ),
                array('%s', '%d', '%s', '%d')
            );
        }
    }

    function get_post($instance, $post_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $this->table_name;

        $post_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE instance = %s AND post_id = %d", $instance, $post_id), ARRAY_A);

        // Helpers::log_this('debug:loading post from DB', array(
        //     'post_data' => $post_data,
        // ));

        return $post_data;
    }

    function get_post_count()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $this->table_name;
        $post_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

        return $post_count;
    }
}
