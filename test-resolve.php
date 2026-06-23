<?php
require_once __DIR__ . '/wp-load.php';
$pot = [
    'pot_name' => 'Khoang 1',
    'status' => 'Đang theo dõi',
    'status_summary' => 'Khoang vừa được kích hoạt, đang bắt đầu theo dõi Ngày 1.',
    'ai_note' => 'Khu vườn bắt đầu ghi nhận dữ liệu sinh trưởng của khoang mới.',
];
$resolved = aitrongcay_resolve_onboarding_plant_for_pot($pot);
print_r($resolved);
