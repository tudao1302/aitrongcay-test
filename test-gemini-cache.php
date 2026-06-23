<?php
require_once 'd:\laragon\www\aitrongcay\wp-load.php';

$garden_key = 'garden%3Aa5bb69bedc403485a0f61f7ea22dd505';
$garden_key = urldecode($garden_key);
$configs = get_option('aitrongcay_rack_cfg_' . $garden_key, []);

print_r($configs);
