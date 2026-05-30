<?php

namespace FTF_Fediverse_Embeds;
// require_once __DIR__ . "/../vendor/autoload.php";
if (!class_exists("simple_html_dom_node")) {
    require_once __DIR__ . "/../vendor/simplehtmldom/simplehtmldom/simple_html_dom.php";
}

class Helpers
{
    public static function log_this($title, $data = false)
    {
        if (func_num_args() === 0) {
            return false;
        } elseif (func_num_args() === 1) {
            $data = $title;
            $title = "LOG";
        }

        $border_char = "/";
        $border_length_bottom = 100;
        $border_length_top = $border_length_bottom - strlen($title) - 2;

        $border_length_top_left = floor($border_length_top / 2);
        $border_length_top_right = ceil($border_length_top / 2);

        $border_top_left = str_pad("", $border_length_top_left, $border_char);
        $border_top_right = str_pad("", $border_length_top_right, $border_char);

        error_log("\n\n");
        error_log("$border_top_left $title $border_top_right");

        if (is_array($data) || is_object($data)) {
            error_log(print_r($data, true));
        } else {
            error_log("");
            error_log($data);
            error_log("");
        }
        error_log(str_pad("", $border_length_bottom, $border_char) . "\n");
    }


    public static function get_embed_platform($content)
    {
        $platform = false;

        if (is_string($content)) {
            if (str_contains($content, "class=\"pixelfed__embed\"")) {
                // TODO handle Pixelfed embeds
                // $platform = "pixelfed";
            } elseif (
                str_contains($content, "class=\"mastodon-embed\"") ||
                // str_contains($content, "/embed.js") ||
                str_contains($content, "allow-scripts allow-same-origin allow-popups allow-popups-to-escape-sandbox allow-forms")
            ) {
                $platform = "mastodon";
            }
        }

        return $platform;
    }

    public static function should_embed_assets()
    {

        if (defined("FTF_FE_ALWAYS_ENQUEUE")) {
            return true;
        }

        global $wp_query;

        if (
            !empty($wp_query) &&
            property_exists($wp_query, "queried_object") &&
            !empty($wp_query->queried_object) &&
            property_exists($wp_query->queried_object, "post_content")
        ) {
            $content = $wp_query->queried_object->post_content;
            $platform = Helpers::get_embed_platform($content);
        }

        return !empty($platform);
    }

    public static function get_directory_size($directory)
    {
        $cache_key = "ftf_dir_size_" . md5($directory);
        $cached = get_transient($cache_key);

        if ($cached !== false) {
            return $cached;
        }

        $size = 0;

        if (is_dir($directory)) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory)) as $file) {
                $size += $file->getSize();
            }
        }

        $result = self::format_bytes($size);
        set_transient($cache_key, $result, 5 * MINUTE_IN_SECONDS);

        return $result;
    }

    public static function format_bytes($size, $precision = 2)
    {
        $size_formatted = "0KB";

        if (is_numeric($size) && $size > 0) {
            $base = log($size, 1024);
            $suffixes = array("", "KB", "MB", "GB", "TB");
            $size_formatted = round(pow(1024, $base - floor($base)), $precision) . " " . $suffixes[floor($base)];
        }

        return $size_formatted;
    }

    public static function is_safe_host(string $host): bool
    {
        $host = trim($host, "[]");

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return (bool) filter_var(
                $host,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            );
        }

        $cache_key = "ftf_safe_host_" . md5($host);
        $cached = get_transient($cache_key);

        if ($cached !== false) {
            return (bool) $cached;
        }

        $a_records    = dns_get_record($host, DNS_A)    ?: [];
        $aaaa_records = dns_get_record($host, DNS_AAAA) ?: [];
        $all_records  = array_merge($a_records, $aaaa_records);

        if (empty($all_records)) {
            set_transient($cache_key, 0, HOUR_IN_SECONDS);
            return false;
        }

        foreach ($all_records as $record) {
            $ip = $record["ip"] ?? $record["ipv6"] ?? null;
            if ($ip === null) {
                set_transient($cache_key, 0, HOUR_IN_SECONDS);
                return false;
            }
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                set_transient($cache_key, 0, HOUR_IN_SECONDS);
                return false;
            }
        }

        set_transient($cache_key, 1, HOUR_IN_SECONDS);
        return true;
    }

    public static function is_safe_url(string $url): bool
    {
        $parsed = parse_url($url);

        if (!in_array($parsed["scheme"] ?? "", ["http", "https"], true)) {
            return false;
        }

        $host = $parsed["host"] ?? "";
        if ($host === "") {
            return false;
        }

        return self::is_safe_host($host);
    }

    public static function get_default_allowed_domains(): array
    {
        return array(
            "mastodon.social",
            "files.mastodon.social",
            "cdn.masto.host",
            "pool.jortage.com",
            "social-cdn.vivaldi.net",
            "cdn.hosted.spacebear.ee",
            "m.f-h.co",
        );
    }

    public static function get_default_allowed_suffixes(): array
    {
        return array(
            ".files.fedi.monster",
            ".digitaloceanspaces.com",
            ".mastodon.nz",
        );
    }

    public static function generate_random_string($length = 10)
    {
        $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $characters_length = strlen($characters);
        $random_string = "";

        for ($i = 0; $i < $length; $i++) {
            $random_string .= $characters[random_int(0, $characters_length - 1)];
        }

        return $random_string;
    }
}
