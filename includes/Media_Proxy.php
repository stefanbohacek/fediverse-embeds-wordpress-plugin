<?php

namespace FTF_Fediverse_Embeds;
// require_once __DIR__ . "/../vendor/autoload.php";
if (!class_exists("simple_html_dom_node")) {
    require_once __DIR__ . "/../vendor/simplehtmldom/simplehtmldom/simple_html_dom.php";
}

use FTF_Fediverse_Embeds\Helpers;

class Media_Proxy
{
    protected $archival_mode;

    function __construct()
    {
        // $this->archival_mode = get_option("ftf_fediverse_embeds_archival_mode") === "on" ? true : false;
        $this->archival_mode = true;
        add_action("rest_api_init", array($this, "register_media_proxy_endpoint"));
        add_action("wp_ajax_nopriv_ftf_media_proxy", array($this, "media_proxy"), 1000);
    }

    public function register_media_proxy_endpoint(/* $_REQUEST */)
    {
        register_rest_route("ftf", "media-proxy", array(
            "methods" => \WP_REST_Server::READABLE,
            "permission_callback" => "__return_true",
            "callback" => array($this, "proxy_media"),
        ));
    }

    public function proxy_media(\WP_REST_Request $request)
    {
        $url = $request["url"];
        $folder_name = "media";

        if ($this->archival_mode) {
            $url = base64_decode($url);
            $dir = plugin_dir_path(__FILE__) . "../$folder_name";
            $file_name = basename($url);
            $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);

            $file_name = md5($url) . "." . $file_extension;
            $file_path = "$dir/$file_name";

            $file_name_hashed = Helpers::generate_random_string(12) . time();
            $file_path_hashed = "$dir/$file_name_hashed";

            if (!is_dir($dir)) {
                mkdir($dir);
            }

            if (!file_exists("$dir/index.html")) {
                file_put_contents("$dir/index.html", "");
            }
        }

        if (empty($url) || !Helpers::is_safe_url($url)) {
            status_header(403);
            exit();
        }

        if ($this->archival_mode && file_exists($file_path)) {
            $image_info = getimagesize($file_path);
            header("Content-type: {$image_info['mime']}");
            echo file_get_contents($file_path);
        } else {
            if (!empty($url)) {
                global $wp_version;
                $parse = parse_url($url);
                $domain = $parse["host"];

                $allowed_domains = array(
                    "cdn.masto.host",
                    "pool.jortage.com",
                    "social-cdn.vivaldi.net",
                    "cdn.hosted.spacebear.ee",
                    "m.f-h.co"
                );

                $allowed_suffixes = array(
                    ".files.fedi.monster",
                    ".digitaloceanspaces.com"
                );

                $can_download_media = in_array($domain, $allowed_domains);

                if (!$can_download_media) {
                    foreach ($allowed_suffixes as $suffix) {
                        if (str_ends_with($domain, $suffix)) {
                            $can_download_media = true;
                            break;
                        }
                    }
                }

                if (!$can_download_media) {
                    // Converting files.domain.social and media.domain.social to domain.social

                    $stripped_domain = str_replace(array(
                        "cdn.",
                        "files.",
                        "media.",
                        "pool.",
                        "s3.",
                    ), "", $domain);

                    if (Helpers::is_safe_host($stripped_domain)) {
                        $remote_response = wp_remote_get("https://$stripped_domain/.well-known/nodeinfo", array(
                            "user-agent" => "FTF: Fediverse Embeds; WordPress/" . $wp_version . "; " . get_bloginfo("url"),
                        ));
                        // Check if this is a fediverse server.
                        if (!is_wp_error($remote_response) && $remote_response["response"] && $remote_response["response"]["code"] && $remote_response["response"]["code"] === 200) {
                            $can_download_media = true;
                        }
                    }
                }

                if (!$can_download_media) {
                    status_header(403);
                    exit();
                }

                $remote_response = wp_remote_get($url, array(
                    "user-agent" => "FTF: Fediverse Embeds; WordPress/" . $wp_version . "; " . get_bloginfo("url"),
                ));

                $content_type = wp_remote_retrieve_header($remote_response, "content-type");
                $mime_types_safe = array(
                    "image/apng",
                    "image/avif",
                    "image/bmp",
                    "image/gif",
                    "image/vnd.microsoft.icon",
                    "image/jpeg",
                    "image/png",
                    "image/svg+xml",
                    "image/tiff",
                    "image/webp",
                    "video/x-msvideo",
                    "video/mp4",
                    "video/mpeg",
                    "video/ogg",
                    "video/mp2t",
                    "video/webm",
                    "video/3gpp",
                    "audio/3gpp",
                    "video/3gpp2",
                    "audio/3gpp2",
                    "audio/aac",
                    "audio/midi, audio/x-midi",
                    "audio/mpeg",
                    "audio/ogg",
                    "audio/wav",
                    "audio/webm",
                );

                if (!in_array($content_type, $mime_types_safe)) {
                    status_header(403);
                    exit();
                }

                if ($this->archival_mode) {
                    // file_put_contents($file_path, $remote_response["body"]);
                    file_put_contents($file_path_hashed, $remote_response["body"]);
                    $validate = wp_check_filetype_and_ext($file_path, $file_name);

                    if (!in_array($validate["type"], $mime_types_safe)) {
                        unlink($file_path_hashed);
                    } else {
                        rename($file_path_hashed, $file_path);
                    }
                }

                header("Content-Type: " . $remote_response["headers"]["content-type"]);
                echo $remote_response["body"];
            }
        }
        exit();
    }
}
