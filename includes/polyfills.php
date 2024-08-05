<?php
/* Helper functions to support PHP < 8 */

if (!function_exists("str_contains")) {
  function str_contains($haystack, $needle)
  {
    return (strpos($haystack, $needle) !== false);
  }
}

if (!function_exists("str_ends_with")) {
  function str_ends_with(string $haystack, string $needle): bool
  {
    return strlen($needle) === 0 || substr($haystack, -strlen($needle)) === $needle;
  }
}
