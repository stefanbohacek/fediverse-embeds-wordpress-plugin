<?php
namespace FTF_Fediverse_Embeds;
// require_once __DIR__ . '/../vendor/autoload.php';
if (!class_exists('simple_html_dom_node')){
    require_once __DIR__ . '/../vendor/simplehtmldom/simplehtmldom/simple_html_dom.php';
}

class Helpers {
    public static function log_this($title, $data = false){
        if (func_num_args() === 0){
            return false;
        }
        elseif (func_num_args() === 1){
            $data = $title;
            $title = "LOG";
        }
    
        $border_char = "/";
        $border_length_bottom = 100;
        $border_length_top = $border_length_bottom - strlen($title) - 2;
    
        $border_length_top_left = floor($border_length_top/2);
        $border_length_top_right = ceil($border_length_top/2);
    
        $border_top_left = str_pad("", $border_length_top_left, $border_char);
        $border_top_right = str_pad("", $border_length_top_right, $border_char);  
    
        error_log("\n\n");
        error_log("$border_top_left $title $border_top_right");
    
        if (is_array($data) || is_object($data)){
            error_log(print_r($data, true));
        }
        else{
            error_log("");
            error_log($data);
            error_log("");
        }
        error_log(str_pad("", $border_length_bottom, $border_char) . "\n");
    }

    public static function get_directory_size($directory){
        $size = 0;

        if (is_dir($directory)) {
            foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory)) as $file){
                $size+=$file->getSize();
            }
        }

        return self::format_bytes($size);
    }

    public static function format_bytes($size, $precision = 2){
        $size_formatted = '0KB';

        if (is_numeric($size) && $size > 0){
            $base = log($size, 1024);
            $suffixes = array('', 'KB', 'MB', 'GB', 'TB');   
            $size_formatted = round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
        }

        return $size_formatted;
    }
}
