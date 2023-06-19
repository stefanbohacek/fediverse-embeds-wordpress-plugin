<?php
namespace FTF_Fediverse_Embeds;

$dir = plugin_dir_path(__FILE__);

if (!class_exists('simple_html_dom_node')){
    require_once $dir . 'simple_html_dom.php';
}

class Site_Info {
    function __construct(){
        add_action('wp_ajax_ftf_get_site_info', array($this, 'get_site_info'), 1000);
        add_action('wp_ajax_nopriv_ftf_get_site_info', array($this, 'get_site_info'), 1000);
    }

    function get_site_info(){
        $site_url = sanitize_text_field($_POST[ 'url' ]);
        $cache_key = 'site_data:' . $site_url;
        $site_data = wp_cache_get($cache_key, 'ftf_fediverse_embeds_post');

        $image = '';
        $title = '';
        $description = '';

        if ($site_data === false){
            $site_html = file_get_html($site_url);

            if ($site_html){
                $meta_image = $site_html->find('meta[name="twitter:image"]');

                if (!empty($meta_image)){
                    $image = $meta_image[0]->content;
                } else {
                    $meta_image = $site_html->find('meta[property="og:image"]');
                    $image = $meta_image[0]->content;
                }

                $meta_title = $site_html->find('meta[name="title"]');

                if (!empty($meta_title)){
                    $title = $meta_title[0]->content;
                } else {
                    $meta_title = $site_html->find('meta[name="twitter:title"]');

                    if (!empty($meta_title)){
                        $title = $meta_title[0]->content;
                    } else {
                        $meta_title = $site_html->find('meta[property="og:title"]');
                        $title = $meta_title[0]->content;
                    }
                }

                $meta_description = $site_html->find('meta[name="description"]');

                if (!empty($meta_description)){
                    $description = $meta_description[0]->content;
                } else {
                    $meta_description = $site_html->find('meta[name="twitter:title"]');

                    if (!empty($meta_description)){
                        $description = $meta_description[0]->content;
                    } else {
                        $meta_description = $site_html->find('meta[property="og:title"]');
                        $description = $meta_description[0]->content;
                    }

                }

                $description = $meta_description[0]->content;
            }

            $site_data = array(
                'url' => $site_url,
                'image' => $image,
                'title' => $title,
                'description' => $description
            );

            // Helpers::log_this('debug:get_site_info', array(
            //     'site_data' => $site_data,
            // ));

            wp_cache_set($cache_key, $site_data, 'ftf_fediverse_embeds_post', (60 * MINUTE_IN_SECONDS));
        }
        // error_log(print_r($site_data, true));
        wp_send_json($site_data);
    }
}
