<?php
if (! defined('ABSPATH')) { exit; }

function aitrongcay_add_notification(int $user_id, string $title, string $message, string $link = ''): void {
    if ($user_id <= 0) return;
    
    $notifications = get_user_meta($user_id, 'aitrongcay_notifications', true);
    if (!is_array($notifications)) $notifications = [];
    
    $new_notification = [
        'id' => uniqid('noti_'),
        'title' => $title,
        'message' => $message,
        'link' => $link,
        'time' => time(),
        'read' => false
    ];
    
    array_unshift($notifications, $new_notification);
    
    if (count($notifications) > 50) {
        $notifications = array_slice($notifications, 0, 50);
    }
    
    update_user_meta($user_id, 'aitrongcay_notifications', $notifications);
}

function aitrongcay_get_notifications(int $user_id): array {
    if ($user_id <= 0) return [];
    $notifications = get_user_meta($user_id, 'aitrongcay_notifications', true);
    return is_array($notifications) ? $notifications : [];
}

function aitrongcay_ajax_get_notifications(): void {
    if (!is_user_logged_in()) {
        wp_send_json_error('Not logged in');
    }
    $user_id = get_current_user_id();
    $notifications = aitrongcay_get_notifications($user_id);
    
    $unread_count = 0;
    foreach ($notifications as $n) {
        if (empty($n['read'])) {
            $unread_count++;
        }
    }
    
    wp_send_json_success([
        'unread_count' => $unread_count,
        'notifications' => $notifications
    ]);
}
add_action('wp_ajax_aitrongcay_get_notifications', 'aitrongcay_ajax_get_notifications');

function aitrongcay_ajax_mark_notifications_read(): void {
    if (!is_user_logged_in()) {
        wp_send_json_error('Not logged in');
    }
    $user_id = get_current_user_id();
    $notifications = aitrongcay_get_notifications($user_id);
    
    $updated = false;
    foreach ($notifications as &$n) {
        if (empty($n['read'])) {
            $n['read'] = true;
            $updated = true;
        }
    }
    
    if ($updated) {
        update_user_meta($user_id, 'aitrongcay_notifications', $notifications);
    }
    
    wp_send_json_success('Marked as read');
}
add_action('wp_ajax_aitrongcay_mark_notifications_read', 'aitrongcay_ajax_mark_notifications_read');
