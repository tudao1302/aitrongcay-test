<?php
require_once dirname(__FILE__) . '/wp-load.php';
update_option('timezone_string', 'Asia/Ho_Chi_Minh');
update_option('gmt_offset', 7);
echo "Timezone updated to Asia/Ho_Chi_Minh\n";
echo "wp_date: " . wp_date('Y-m-d H:i:s') . "\n";
echo "current_time mysql: " . current_time('mysql') . "\n";
