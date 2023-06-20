<?php
namespace FTF_Fediverse_Embeds;
use FTF_Fediverse_Embeds\Helpers;

$dir = plugin_dir_path(__FILE__);

if (!class_exists('simple_html_dom_node')){
    require_once $dir . 'simple_html_dom.php';
}

class Media_Proxy {
    protected $archival_mode;

    function __construct(){
        // $this->archival_mode = get_option('ftf_fediverse_embeds_archival_mode') === 'on' ? true : false; 
        $this->archival_mode = true;
        add_action('rest_api_init', array($this, 'register_media_proxy_endpoint'));
        add_action('wp_ajax_nopriv_ftf_media_proxy', array($this, 'media_proxy'), 1000);
    }

    public function register_media_proxy_endpoint(/* $_REQUEST */) {
        register_rest_route('ftf', 'media-proxy', array(
            'methods' => \WP_REST_Server::READABLE,
            'permission_callback' => '__return_true',
            'callback' => array($this, 'proxy_media'),
      ));
    }

    public function proxy_media(\WP_REST_Request $request){
        $url = $request['url'];
        $folder_name = 'media';

        if ($this->archival_mode){
            $dir = plugin_dir_path(__FILE__) . "../$folder_name";
            $file_name = basename($url);
            $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);

            $file_name = md5($url) . '.' . $file_extension;
            $file_path = "$dir/$file_name";
    
            if (!is_dir($dir)) {
                mkdir($dir);
            }
        }

        if ($this->archival_mode && file_exists($file_path)){
    
        //     Helpers::log_this('debug:proxy_media', array(
        //         'url' => $url,
        //         'file_name' => $file_name,
        //         'file_path' => $file_path,
        //         'file_exists' => 'true',
        //    ));
    
            $image_info = getimagesize($file_path);
            header("Content-type: {$image_info['mime']}");
            echo file_get_contents($file_path);

        } else {
            global $wp_version;
            $parse = parse_url($url);
            $domain = $parse['host'];

            // Helpers::log_this('debug:proxy_media', array(
            //     'url' => $url,
            //     'file_name' => $file_name,
            //     'domain' => $domain,
            //     // 'remote_response' => $remote_response,
            // ));

            $remote_response = wp_remote_get("https://$domain/.well-known/nodeinfo", array(
                'user-agent' => 'FTF: Fediverse Embeds; WordPress/' . $wp_version . '; ' . get_bloginfo('url'),                
            ));
            // Check if this is a fediverse server.

            if (!is_wp_error($remote_response) && $remote_response['response'] && $remote_response['response']['code'] && $remote_response['response']['code'] === 200){
                $remote_response = wp_remote_get($url, array(
                    'user-agent' => 'FTF: Fediverse Embeds; WordPress/' . $wp_version . '; ' . get_bloginfo('url'),                
                ));

                if ($this->archival_mode){
                    file_put_contents($file_path, $remote_response['body']);
                }
        
                header('Content-Type: ' . $remote_response['headers']['content-type']);
                echo $remote_response['body'];
            } else {
                echo '';
            }     
        }
        exit();
    }    
}
