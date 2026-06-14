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
            $url = base64_decode(str_replace(" ", "+", $url), true);
            if ($url === false) {
                status_header(400);
                exit();
            }
            $upload_dir = wp_upload_dir();
            $dir = $upload_dir["basedir"] . "/fediverse-embeds/" . $folder_name;
            $file_name = basename(parse_url($url, PHP_URL_PATH));
            $raw_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_extensions = ["jpg", "jpeg", "png", "gif", "webp", "avif", "bmp", "ico", "tiff", "mp4", "webm", "ogg", "mpeg", "avi", "mp2t", "3gp", "aac", "mp3", "wav", "midi"];
            $file_extension = in_array($raw_extension, $allowed_extensions, true) ? $raw_extension : "bin";

            $file_name = md5($url) . "." . $file_extension;
            $file_path = "$dir/$file_name";

            $file_name_hashed = Helpers::generate_random_string(12) . time();
            $file_path_hashed = "$dir/$file_name_hashed";
        }

        if (empty($url) || !Helpers::is_safe_url($url)) {
            status_header(403);
            exit();
        }

        if ($this->archival_mode) {
            if (!is_dir($dir)) {
                wp_mkdir_p($dir);
            }

            if (!file_exists("$dir/index.html")) {
                file_put_contents("$dir/index.html", "");
            }
        }

        if ($this->archival_mode && file_exists($file_path)) {
            $cached_mime = mime_content_type($file_path);
            if ($cached_mime === false) {
                status_header(500);
                exit();
            }
            header("Cache-Control: public, max-age=31536000");
            header("Content-Type: " . $cached_mime);
            echo file_get_contents($file_path);
        } else {
            if (!empty($url)) {
                global $wp_version;
                $parse = parse_url($url);
                $domain = $parse["host"] ?? "";

                $default_allowed_domains = Helpers::get_default_allowed_domains();
                $default_allowed_suffixes = Helpers::get_default_allowed_suffixes();

                $saved_domains = get_option("ftf_fediverse_embeds_allowed_domains");
                $saved_suffixes = get_option("ftf_fediverse_embeds_allowed_suffixes");

                $allowed_domains = ($saved_domains === false)
                    ? $default_allowed_domains
                    : array_values(array_filter(array_map(function ($entry) {
                        $entry = trim($entry);
                        $entry = preg_replace("#^https?://#i", "", $entry);
                        $entry = explode("/", $entry)[0];
                        return strtolower($entry);
                    }, explode("\n", $saved_domains))));

                $allowed_suffixes = ($saved_suffixes === false)
                    ? $default_allowed_suffixes
                    : array_values(array_filter(array_map(function ($entry) {
                        $entry = strtolower(trim($entry));
                        if ($entry !== "" && $entry[0] !== ".") {
                            $entry = "." . $entry;
                        }
                        return $entry;
                    }, explode("\n", $saved_suffixes))));

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
                    // Converting e.g. files.domain.social to domain.social, only strip one leading prefix.
                    $stripped_domain = preg_replace("/^(cdn|files|media|pool|s3)\./i", "", $domain);

                    if ($stripped_domain !== $domain && Helpers::is_safe_host($stripped_domain)) {
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
                    if (!empty($domain) && current_user_can("manage_options")) {
                        Media_Proxy::log_blocked_domain($domain);
                    }
                    status_header(403);
                    exit();
                }

                $remote_response = wp_remote_get($url, array(
                    "user-agent" => "FTF: Fediverse Embeds; WordPress/" . $wp_version . "; " . get_bloginfo("url"),
                ));

                if (is_wp_error($remote_response)) {
                    status_header(502);
                    exit();
                }

                $content_type = wp_remote_retrieve_header($remote_response, "content-type");
                $mime_types_safe = array(
                    "image/apng",
                    "image/avif",
                    "image/bmp",
                    "image/gif",
                    "image/vnd.microsoft.icon",
                    "image/jpeg",
                    "image/png",

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
                    "audio/midi",
                    "audio/x-midi",
                    "audio/mpeg",
                    "audio/ogg",
                    "audio/wav",
                    "audio/webm",
                );

                $content_type_base = strtolower(trim(explode(";", $content_type)[0]));
                if (!in_array($content_type_base, $mime_types_safe)) {
                    status_header(403);
                    exit();
                }

                if ($this->archival_mode) {
                    file_put_contents($file_path_hashed, $remote_response["body"]);
                    $validate = wp_check_filetype_and_ext($file_path_hashed, $file_name);

                    if (!in_array($validate["type"], $mime_types_safe)) {
                        unlink($file_path_hashed);
                        status_header(403);
                        exit();
                    }

                    rename($file_path_hashed, $file_path);
                }

                header("Cache-Control: public, max-age=31536000");
                header("Content-Type: " . $content_type_base);
                echo $remote_response["body"];
            }
        }
        exit();
    }

    public static function log_blocked_domain(string $domain)
    {
        $blocked = get_option("ftf_fediverse_embeds_blocked_domains", array());

        if (!is_array($blocked)) {
            $blocked = array();
        }

        $found = false;
        foreach ($blocked as &$entry) {
            if ($entry["domain"] === $domain) {
                $entry["last_seen"] = time();
                $found = true;
                break;
            }
        }
        unset($entry);

        if (!$found) {
            $blocked[] = array("domain" => $domain, "last_seen" => time());
        }

        usort($blocked, function ($a, $b) {
            return $b["last_seen"] - $a["last_seen"];
        });

        if (count($blocked) > 50) {
            $blocked = array_slice($blocked, 0, 50);
        }

        update_option("ftf_fediverse_embeds_blocked_domains", $blocked);
    }
}
