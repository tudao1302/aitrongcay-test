<?php
require 'wp-load.php';

// Force WP-Cron execution
wp_cron();

$out = "Running timelapse capture directly...\n";
$start = microtime(true);
aitrongcay_do_timelapse_capture();
$end = microtime(true);

$out .= "Done in " . ($end - $start) . " seconds.\n";

$upload_info = wp_upload_dir();
$base_dir = $upload_info['basedir'] . '/timelapse';
$out .= "Checking directories in $base_dir ...\n";

ob_start();
system('dir "' . str_replace('/', '\\', $base_dir) . '" /s /b');
$out .= ob_get_clean();

file_put_contents(ABSPATH . '/test-timelapse-out.txt', $out);
echo "Log written.";
