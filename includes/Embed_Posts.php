<?php

namespace FTF_Fediverse_Embeds;
// require_once __DIR__ . '/../vendor/autoload.php';
if (!class_exists('simple_html_dom_node')) {
    require_once __DIR__ . '/../vendor/simplehtmldom/simplehtmldom/simple_html_dom.php';
}

use FTF_Fediverse_Embeds\Database;
use FTF_Fediverse_Embeds\Helpers;

class Embed_Posts
{
    protected $db;
    protected $archival_mode;

    function __construct()
    {
        // $this->archival_mode = get_option('ftf_fediverse_embeds_archival_mode') === 'on' ? true : false; 
        $this->archival_mode = true;
        $this->db = new Database();

        add_action('render_block', array($this, 'process_embeds'), 10, 2);
        add_action('wp_ajax_ftf_get_post', array($this, 'get_post_ajax'), 1000);
        add_action('wp_ajax_nopriv_ftf_get_post', array($this, 'get_post_ajax'), 1000);
    }

    function process_embeds($block_content, $block)
    {
        $platform = null;
        $deleted_posts = get_option('ftf_fediverse_embeds_deleted_posts');

        if (str_contains($block_content, 'class="pixelfed__embed"')) {
            // TODO handle Pixelfed embeds
            // $platform = 'pixelfed';
        } elseif (str_contains($block_content, 'class="mastodon-embed"') || str_contains($block_content, '/embed.js')) {
            $platform = 'mastodon';
        }

        if ($platform) {
            $html = str_get_html($block_content);

            foreach ($html->find('iframe') as $iframe) {
                $url = $iframe->src;

                if ($platform === 'pixelfed') {
                    $url = str_replace('/p/', '/', $url);
                }

                $url_parts = explode('/', $url);

                $protocol = $url_parts[0];
                $instance = $url_parts[2];
                $username = $url_parts[3];
                $post_id = $url_parts[4];

                // Helpers::log_this('debug:post', array(
                //     'url' => $url,
                //     'protocol' => $protocol,
                //     'instance' => $instance,
                //     'username' => $username,
                //     'post_id' => $post_id,
                // ));

                $post = $this->get_post(array(
                    'instance' => $instance,
                    'post_id' => $post_id,
                ), true);

                // if ($live_post_data['status'] === 'deleted'){
                // if ($saved_post_data['__status'] = 'deleted'){

                try {
                    $post_content = false;
                    $account_display_name = false;
                    $account_username = false;
                    $post_url = false;
                    $post_date = false;

                    // Helpers::log_this('debug:post', array(
                    //     'post' => $post,
                    // ));

                    if (is_string($post['post_data'])) {
                        $post_data = json_decode($post['post_data'], true);
                    } else {
                        $post_data = $post['post_data'];
                    }

                    if (!empty($post_data)) {
                        if (!empty($post_data['account'])) {
                            if (isset($post_data['account']['display_name']) && !empty(trim($post_data['account']['display_name']))) {
                                $account_display_name = $post_data['account']['display_name'];
                            } else {
                                $account_display_name = "@" . $post_data['account']['username'];
                            }

                            $account_username = $post_data['account']['username'];
                        }

                        $post_url = !empty($post_data['url']) ? $post_data['url'] : "";
                        $post_content = !empty($post_data['content']) ? $post_data['content'] : "";
                        $post_date = !empty($post_data['created_at']) ? $post_data['created_at'] : "";
                    }

                    // Helpers::log_this(array(
                    //     "account_username" => $account_username,
                    //     "post_url" => $post_url,
                    //     "post_content" => $post_content,
                    //     "post_data" => $post_data,
                    // ));

                    $iframe_html = "";

                    if (($post_content || !empty($post_data['media_attachments'])) && $account_display_name && $account_username && $post_url && $post_date) {
                        $iframe_html = <<<HTML
                            <blockquote data-instance="$instance" data-post-id="$post_id" class="ftf-fediverse-post-embed">
                                $post_content
                                <p class="ftf-fediverse-post-embed-author">
                                    &mdash; $account_display_name (@$account_username@$instance) 
                                    <a class="ftf-fediverse-post-embed-link" href="$post_url">$post_date</a>
                                </p>
                            </blockquote>
                        HTML;
                    }
                    $iframe->outertext = $iframe_html;
                } catch (\Exception $e) {
                    $iframe_html  = "<blockquote";
                    $iframe_html .= "data-instance=\"$instance\"";
                    $iframe_html .= "data-post-id=\"$post_id\"";
                    $iframe_html .= "class=\"ftf-fediverse-post-embed-removed\"";
                    $iframe_html .= ">";
                    $iframe_html .= "<p>This post by $username@$instance was removed</p>";
                    $iframe_html .= "</blockquote>";
                    $iframe->outertext = $iframe_html;
                }

                // if ($post->media_attachments){
                //     $media_attachments = array_map(function($attachment) use ($instance){
                //         return array(
                //             'type' => $attachment->type,
                //             // 'preview_url' => $attachment->preview_url,
                //             'description' => $attachment->description,
                //             'instance' => $instance,
                //             'id' => $attachment->id,
                //             'extension' => pathinfo($attachment->preview_url)['extension']
                //         );
                //     }, $post->media_attachments);
                // }

                // Helpers::log_this('debug:post data', array(
                //     'post_url' => $post_url,
                //     'post_id' => $post_id,                
                //     'account_display_name' => $account_display_name,
                //     'account_username' => $account_username,
                //     'domain' => $instance,
                //     'post_date' => $post_date,
                //     'post_content' => $post_content,
                //     'media_attachments' => $media_attachments,
                //     'post' => $post,
                // ));
            }
            $block_content = $html->save();
        }

        return $block_content;
    }

    function get_post($post, $skip_cache = false)
    {
        $data_refresh_minutes = get_option('ftf_fediverse_embeds_data_refresh_minutes');
        $response = array();

        if (empty($data_refresh_minutes)) {
            $data_refresh_minutes = 60;
        }

        $cache_key = "post_data:" . $post['instance'] . ':' . $post['post_id'];
        $cached_post_data = wp_cache_get($cache_key, 'ftf_fediverse_embeds_post');

        // Helpers::log_this('get_post:cached', array(
        //     'cache_key' => $cache_key,
        //     'cached_post_data' => $cached_post_data,                
        // ));  

        if ($cached_post_data !== false) {
            $response = $cached_post_data;
        } else {
            $saved_post_data = $this->db->get_post($post['instance'], $post['post_id']);

            if ($saved_post_data) {
                $saved_post_data['post_data'] = json_decode($saved_post_data['post_data'], true);

                // Helpers::log_this('get_post:db', array(
                //     'saved_post_data' => $saved_post_data,                
                // ));  

                if (!$skip_cache) {
                    $time_now = time();
                    $time_passed = $time_now - $saved_post_data['last_updated'];
                    $time_passed_minutes = $time_passed / MINUTE_IN_SECONDS;
                    // Helpers::log_this('debug:refresh', array(
                    //     'time_now' => date('m/d/Y H:i:s', $time_now),
                    //     'last_updated' => date('m/d/Y H:i:s', $saved_post_data['last_updated']),
                    //     'time_passed_minutes' => $time_passed_minutes,
                    // ));

                    if ($time_passed_minutes > $data_refresh_minutes) {
                        // if ($time_passed_minutes > 0.1){
                        $live_post_data = $this->get_live_post_data($post);
                        if ($live_post_data) {

                            // Helpers::log_this('debug:refresh:live_post_data', array(
                            //     'live_post_data' => $live_post_data,
                            // ));

                            if (key_exists('status', $live_post_data)) {
                                if ($live_post_data['status'] === 'deleted') {
                                    $saved_post_data['__status'] = 'deleted';
                                    $response = $saved_post_data;
                                } else {
                                    $response = array(
                                        'instance' => $post['instance'],
                                        'post_id' => $post['post_id'],
                                        'post_data' => $live_post_data,
                                        'last_updated' => time(),
                                    );
                                }
                            } else {
                                if ($live_post_data['id'] && $live_post_data['created_at']) {
                                    $response = array(
                                        'instance' => $post['instance'],
                                        'post_id' => $post['post_id'],
                                        'post_data' => $live_post_data,
                                        'last_updated' => time(),
                                    );
                                }
                            }
                        }
                    } else {
                        $response = $saved_post_data;
                    }
                } else {
                    $response = $saved_post_data;
                }
            } else {
                $response = array(
                    'instance' => $post['instance'],
                    'post_id' => $post['post_id'],
                    'post_data' => $this->get_live_post_data($post),
                    'last_updated' => time(),
                );
            }
        }

        return $response;
    }

    function get_live_post_data($post)
    {
        $response = array();
        $post_instance = $post['instance'];
        $post_id = $post['post_id'];

        $req_url = "https://" . $post_instance . "/api/v1/statuses/" . $post_id;

        // Helpers::log_this('get_live_post_data', array(
        //     'post' => $post,
        //     'req_url' => $req_url,
        // ));

        try {
            global $wp_version;

            $remote_response = wp_remote_get($req_url, array(
                'user-agent' => 'FTF: Fediverse Embeds; WordPress/' . $wp_version . '; ' . get_bloginfo('url'),
            ));

            // Helpers::log_this('get_live_post_data', array(
            //     'remote_response' => $remote_response,
            // ));

            if (
                $remote_response &&
                !is_wp_error($remote_response) &&
                $remote_response['response'] &&
                $remote_response['response']['code']
            ) {
                if ($remote_response['response']['code'] === 200) {

                    $live_post_data = wp_remote_retrieve_body($remote_response);

                    try {
                        $live_post_data_json = json_decode($live_post_data, true);

                        // Helpers::log_this('debug:get_live_post_data', array(
                        //     'id' => $live_post_data_json->id,
                        //     'created_at' => $live_post_data_json->created_at,
                        // ));

                        if (!empty($live_post_data_json) && !empty($live_post_data_json['id']) && !empty($live_post_data_json['created_at'])) {
                            $response = $live_post_data_json;

                            $this->db->save_post($post_instance, $post_id, $live_post_data);
                        }
                    } catch (\Exception $e) {
                        //noop
                    }
                } elseif ($remote_response['response']['code'] === 404) {
                    // Post was deleted.
                    $response = array('status' => 'deleted');
                } else {
                    $response = array('status' => $remote_response['response']['code']);
                }
            } else {
                // Helpers::log_this('debug:get_post', array(
                //     'remote_response' => $remote_response,
                // ));
            }
        } catch (\Exception $e) {
            //noop
        }

        // Helpers::log_this('response', $response);

        return $response;
    }

    function get_post_ajax($post)
    {
        if (array_key_exists('post', $_POST)) {
            $allow_public_api_access = defined('FTF_FEDIVERSE_EMBEDS_PUBLIC_ACCESS') ? FTF_FEDIVERSE_EMBEDS_PUBLIC_ACCESS : false;

            $response = array();
            $post = sanitize_text_field($_POST['post']);
            $nonce = sanitize_text_field($_POST['nonce']);

            if ($allow_public_api_access || wp_verify_nonce($nonce, 'ftf-fediverse-embeds-nonce')) {
                if (!empty($post)) {
                    $post = json_decode(str_replace('\"', '"', $post), true);
                    $response = $this->get_post($post);

                    // Helpers::log_this('get_post_ajax', array(
                    //     'post' => $post,
                    //     'response' => $response,
                    // ));
                }
            } else {
                wp_send_json_error();
                return;
            }
        }

        wp_send_json($response);
    }
}
