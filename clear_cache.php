<?php
require_once __DIR__ . '/wp-load.php';

// Clear OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache cleared.\n";
}

// Clear WordPress Object Cache
if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
    echo "WP Object Cache flushed.\n";
}

// Clear common caching plugins
if (function_exists('w3tc_flush_all')) {
    w3tc_flush_all();
    echo "W3 Total Cache flushed.\n";
}
if (function_exists('wp_cache_clear_cache')) {
    wp_cache_clear_cache();
    echo "WP Super Cache flushed.\n";
}
if (class_exists('LiteSpeed_Cache_API')) {
    do_action('litespeed_purge_all');
    echo "LiteSpeed Cache flushed.\n";
}

echo "All caches cleared successfully!";
