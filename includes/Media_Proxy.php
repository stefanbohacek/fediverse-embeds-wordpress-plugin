<?php
namespace FTF_Fediverse_Embeds;
// require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../vendor/simplehtmldom/simplehtmldom/simple_html_dom.php';

use FTF_Fediverse_Embeds\Helpers;

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
        /*
            At minimum, we need to make sure that the media file is being requested from a fediverse server,
            otherwise you could load arbitrary files from anywhere. Some fediverse servers may use a different subdomain
            for hosting their files, or a CDN.

            The current solution relies heavily on keeping track of allowed domains and converting URLs.

            This needs to be more robust.
        */
    
        $url = $request['url'];
        $folder_name = 'media';

        if ($this->archival_mode){
            $url = base64_decode($url);
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
            if (!empty($url)){
                global $wp_version;
                $parse = parse_url($url);
                $domain = $parse['host'];
    
                $allowed_domains = array(
                    'cdn.masto.host',
                    'pool.jortage.com',
                    'social-cdn.vivaldi.net'
                );
    
                $can_download_media = false;
    
                if (str_ends_with($domain, '.files.fedi.monster')){
                    $can_download_media = true;
    
                } elseif (in_array($domain, $allowed_domains)){
                    $can_download_media = true;
    
                } else {
                    // Converting files.domain.social and media.domain.social to domain.social
    
                    $domain = str_replace(array(
                        'cdn.',
                        'files.',
                        'media.',
                        'pool.',
                        's3.',
                    ), '', $domain);
    
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
                        $can_download_media = true;
                    }     
                }
    
                $remote_response = wp_remote_get($url, array(
                    'user-agent' => 'FTF: Fediverse Embeds; WordPress/' . $wp_version . '; ' . get_bloginfo('url'),                
                ));
    
                if ($can_download_media){
                    if ($this->archival_mode){
                        file_put_contents($file_path, $remote_response['body']);
                    }
                }
    
                header('Content-Type: ' . $remote_response['headers']['content-type']);
                echo $remote_response['body'];
            }
        }
        exit();
    }    
}
