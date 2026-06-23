<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/wp-load.php';

$garden_key = 'garden:e7ae08ddd1d0e1b4fa18977b08abb22b';
$user_id = 4; // Khách hàng 4
$user = get_user_by('id', $user_id) ?: null;
$pots = function_exists('aitrongcay_portal_pots') ? aitrongcay_portal_pots($garden_key, $user) : [];

echo "<pre>";
print_r($pots);
echo "</pre>";
