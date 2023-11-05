<?php
namespace FTF_Fediverse_Embeds;
// require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../vendor/simplehtmldom/simplehtmldom/simple_html_dom.php';

class Enqueue_Assets {
    function __construct(){
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('script_loader_tag', array($this, 'add_type_attribute'), 10, 3);
    }

    function enqueue_scripts(){
        $include_bootstrap_styles = get_option('ftf_fediverse_embeds_include_bootstrap_styles', 'on');
        $show_metrics = get_option('ftf_fediverse_embeds_show_metrics', 'on');
        $show_post_labels = get_option('ftf_fediverse_embeds_show_post_labels', 'on');
        $deleted_posts = get_option('ftf_fediverse_embeds_deleted_posts', 'keep');

        $plugin_dir_url = plugin_dir_url(__FILE__);
        $plugin_dir_path = plugin_dir_path(__FILE__);

        $js_url = $plugin_dir_url . '../dist/js/scripts.js';
        $js_path = $plugin_dir_path . '../dist/js/scripts.js';

        wp_register_script('ftf-fediverse-embeds-frontend-js', $js_url, array(), filemtime($js_path), true);
        wp_localize_script('ftf-fediverse-embeds-frontend-js', 'ftf_fediverse_embeds', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'blog_url' => get_site_url(),
            'nonce' => wp_create_nonce('ftf-fediverse-embeds-nonce'),
            'config' => array(
                'show_metrics' => $show_metrics === 'on',
                'show_post_labels' => $show_post_labels === 'on',
                'deleted_posts' => $deleted_posts 
          )
      ));

        wp_enqueue_script('ftf-fediverse-embeds-frontend-js');

        if ($include_bootstrap_styles === 'on'){
            $css_url = $plugin_dir_url . '../dist/css/styles-bs.min.css';
            $css_path = $plugin_dir_path . '../dist/css/styles-bs.min.css';
        } else {
            $css_url = $plugin_dir_url . '../dist/css/styles.min.css';
            $css_path = $plugin_dir_path . '../dist/css/styles.min.css';
        }

        wp_enqueue_style('ftf-fediverse-embeds-frontend-styles', $css_url, array(), filemtime($css_path));
    }

    function add_type_attribute($tag, $handle, $src){
        if ('ftf-fediverse-embeds-frontend-js' !== $handle) {
            return $tag;
        }
        $tag = '<script type="module" src="' . esc_url($src) . '" defer="defer"></script>';
        return $tag;
    }
}
