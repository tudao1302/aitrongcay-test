<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', 'aitrongcay_support_chat_init_db');
function aitrongcay_support_chat_init_db() {
    if (get_option('aitr_support_chat_db_version', '0') !== '1.0') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'aitr_support_chat';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            sender_type varchar(20) NOT NULL,
            message text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            is_read tinyint(1) DEFAULT 0 NOT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        update_option('aitr_support_chat_db_version', '1.0');
    }
}

// 2. Xử lý AJAX lấy tin nhắn của user
add_action('wp_ajax_aitrongcay_get_support_messages', 'aitrongcay_get_support_messages_ajax');
add_action('wp_ajax_nopriv_aitrongcay_get_support_messages', 'aitrongcay_get_support_messages_ajax');
function aitrongcay_get_support_messages_ajax() {
    $user_id = get_current_user_id();
    if (!$user_id) {
        // Cho phép guest dùng session/cookie, hoặc bắt buộc login?
        // Đề bài yêu cầu "cạnh bên dưới chỗ đăng nhập", thường người ta dùng chat này cả lúc chưa đăng nhập.
        // Nhưng để dễ quản lý, mình gán session_id cho guest.
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['aitr_guest_id'])) {
            $_SESSION['aitr_guest_id'] = 'guest_' . time() . '_' . rand(1000, 9999);
        }
        $user_id_str = $_SESSION['aitr_guest_id'];
    } else {
        $user_id_str = (string) $user_id;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'aitr_support_chat';

    // Lấy tin nhắn
    // Với guest thì lưu user_id dạng số âm (hash) hoặc sao đó?
    // Thay đổi cấu trúc table: user_id có thể là chuỗi (guest_id) hoặc đổi sang varchar?
    // À, DB đã tạo user_id bigint. 
    // Thôi bắt buộc đăng nhập mới chat được cho nhanh, hoặc dùng bigint fake.
    // Nếu guest thì user_id = CRC32(session_id) dạng số âm.
    if (!is_numeric($user_id_str)) {
        $numeric_user_id = -1 * abs((int) crc32($user_id_str));
    } else {
        $numeric_user_id = (int) $user_id_str;
    }

    $messages = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table_name} WHERE user_id = %d ORDER BY created_at ASC LIMIT 100", $numeric_user_id), ARRAY_A);
    
    // Đánh dấu đã đọc tin nhắn của admin
    $wpdb->query($wpdb->prepare("UPDATE {$table_name} SET is_read = 1 WHERE user_id = %d AND sender_type = 'admin'", $numeric_user_id));

    wp_send_json_success(['messages' => $messages, 'user_id' => $numeric_user_id]);
}

// 3. Xử lý AJAX gửi tin nhắn
add_action('wp_ajax_aitrongcay_send_support_message', 'aitrongcay_send_support_message_ajax');
add_action('wp_ajax_nopriv_aitrongcay_send_support_message', 'aitrongcay_send_support_message_ajax');
function aitrongcay_send_support_message_ajax() {
    $user_id = get_current_user_id();
    if (!$user_id) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['aitr_guest_id'])) {
            $_SESSION['aitr_guest_id'] = 'guest_' . time() . '_' . rand(1000, 9999);
        }
        $user_id_str = $_SESSION['aitr_guest_id'];
    } else {
        $user_id_str = (string) $user_id;
    }

    if (!is_numeric($user_id_str)) {
        $numeric_user_id = -1 * abs((int) crc32($user_id_str));
    } else {
        $numeric_user_id = (int) $user_id_str;
    }

    $message = trim((string) ($_POST['message'] ?? ''));
    if (empty($message)) {
        wp_send_json_error(['message' => 'Nội dung không được để trống']);
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'aitr_support_chat';
    
    $wpdb->insert(
        $table_name,
        [
            'user_id' => $numeric_user_id,
            'sender_type' => 'customer',
            'message' => wp_kses_post($message),
            'created_at' => current_time('mysql'),
            'is_read' => 0
        ]
    );

    wp_send_json_success(['message' => 'Đã gửi']);
}

// 4. Admin Menu để xem tin nhắn
add_action('admin_menu', 'aitrongcay_support_chat_admin_menu');
function aitrongcay_support_chat_admin_menu() {
    add_menu_page(
        'Hỗ trợ khách hàng',
        'Hỗ trợ (Chat)',
        'manage_options',
        'aitr-support-chat',
        'aitrongcay_support_chat_admin_page',
        'dashicons-format-chat',
        30
    );
}

function aitrongcay_support_chat_admin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'aitr_support_chat';
    
    // Danh sách người chat
    $users_sql = "SELECT user_id, MAX(created_at) as last_msg_time, SUM(CASE WHEN sender_type = 'customer' AND is_read = 0 THEN 1 ELSE 0 END) as unread_count 
                  FROM {$table_name} 
                  GROUP BY user_id 
                  ORDER BY last_msg_time DESC";
    $chat_users = $wpdb->get_results($users_sql);

    $active_user = isset($_GET['user_chat']) ? (int) $_GET['user_chat'] : 0;
    if (!$active_user && !empty($chat_users)) {
        $active_user = (int) $chat_users[0]->user_id;
    }

    echo '<div class="wrap" style="display:flex;gap:20px;height:calc(100vh - 100px);margin-top:20px;">';
    
    // Sidebar
    echo '<div style="width:300px;background:#fff;border:1px solid #ccd0d4;overflow-y:auto; display:flex; flex-direction:column;">';
    echo '<h2 style="padding:15px;margin:0;border-bottom:1px solid #ccd0d4;">Khách hàng</h2>';
    echo '<ul id="aitr-admin-user-list" style="margin:0;padding:0;list-style:none;">';
    foreach ($chat_users as $cu) {
        $is_active = $cu->user_id == $active_user;
        $bg = $is_active ? '#f0f0f1' : 'transparent';
        $uid = (int)$cu->user_id;
        $user_obj = $uid > 0 ? get_user_by('id', $uid) : false;
        $name = $user_obj ? $user_obj->display_name : ($uid > 0 ? "User #{$uid}" : "Guest #{$uid}");
        $url = add_query_arg(['user_chat' => $uid], menu_page_url('aitr-support-chat', false));
        $unread = $cu->unread_count > 0 ? "<span class='aitr-unread-badge' style='background:red;color:#fff;border-radius:10px;padding:2px 6px;font-size:11px;float:right;'>{$cu->unread_count}</span>" : '';
        echo "<li style='border-bottom:1px solid #eee;'><a href='{$url}' style='display:block;padding:15px;text-decoration:none;color:#3c434a;background:{$bg};'>{$name} {$unread}</a></li>";
    }
    echo '</ul></div>';

    // Chat Box
    echo '<div style="flex:1;background:#fff;border:1px solid #ccd0d4;display:flex;flex-direction:column;">';
    if ($active_user) {
        $active_user_obj = $active_user > 0 ? get_user_by('id', $active_user) : false;
        $u_name = $active_user_obj ? $active_user_obj->display_name : ($active_user > 0 ? "User #{$active_user}" : "Guest #{$active_user}");
        echo "<h2 style='padding:15px;margin:0;border-bottom:1px solid #ccd0d4;'>Đang chat với: {$u_name}</h2>";
        
        echo '<div id="aitr-admin-chat-box" style="flex:1;padding:20px;overflow-y:auto;display:flex;flex-direction:column;gap:10px;background:#f9f9f9;">';
        echo '</div>';

        // Khung nhập
        echo '<div style="padding:15px;border-top:1px solid #ccd0d4;background:#fff;">';
        echo '<form id="aitr-admin-chat-form" style="display:flex;gap:10px;">';
        echo "<input type='hidden' id='aitr-reply-to' value='{$active_user}'>";
        echo '<textarea id="aitr-admin-message" rows="2" style="flex:1;padding:10px;border-radius:5px;" placeholder="Nhập tin nhắn..." required></textarea>';
        echo '<button type="submit" class="button button-primary" style="align-self:flex-end;">Gửi</button>';
        echo '</form>';
        echo '</div>';
        
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            let chatBox = document.getElementById('aitr-admin-chat-box');
            let form = document.getElementById('aitr-admin-chat-form');
            let input = document.getElementById('aitr-admin-message');
            let userId = document.getElementById('aitr-reply-to').value;
            
            function loadMessages() {
                fetch(ajaxurl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=aitrongcay_admin_get_messages&user_id=' + userId
                })
                .then(res => res.json())
                .then(res => {
                    if (res.success && res.data.messages) {
                        chatBox.innerHTML = '';
                        res.data.messages.forEach(m => {
                            let isAdmin = m.sender_type === 'admin';
                            let align = isAdmin ? 'align-self:flex-end;background:#e3f2fd;border:1px solid #bbdefb;' : 'align-self:flex-start;background:#fff;border:1px solid #ddd;';
                            let msgHTML = `<div style="max-width:70%;padding:10px 15px;border-radius:15px;${align}">
                                <div style="font-size:13px;line-height:1.5;">${m.message.replace(/\\n/g, '<br>')}</div>
                                <div style="font-size:10px;color:#888;margin-top:5px;text-align:right;">${m.created_at}</div>
                            </div>`;
                            chatBox.innerHTML += msgHTML;
                        });
                        chatBox.scrollTop = chatBox.scrollHeight;
                        
                        // Hide unread badge for this user
                        let activeLi = document.querySelector(`a[href*="user_chat=${userId}"]`);
                        if (activeLi) {
                            let badge = activeLi.querySelector('.aitr-unread-badge');
                            if (badge) badge.style.display = 'none';
                        }
                    }
                });
            }

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                let message = input.value.trim();
                if (!message) return;
                
                input.value = '';
                
                // Temp append
                let tempHTML = `<div style="max-width:70%;padding:10px 15px;border-radius:15px;align-self:flex-end;background:#e3f2fd;border:1px solid #bbdefb;opacity:0.7;">
                                <div style="font-size:13px;line-height:1.5;">${message.replace(/\\n/g, '<br>')}</div>
                            </div>`;
                chatBox.innerHTML += tempHTML;
                chatBox.scrollTop = chatBox.scrollHeight;

                fetch(ajaxurl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=aitrongcay_admin_send_message&user_id=' + userId + '&message=' + encodeURIComponent(message)
                }).then(() => loadMessages());
            });
            
            // Allow Enter to send
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    form.dispatchEvent(new Event('submit'));
                }
            });

            loadMessages();
            setInterval(loadMessages, 5000);
        });
        </script>
        <?php
    } else {
        echo '<div style="padding:40px;text-align:center;color:#888;">Chọn một khách hàng để bắt đầu chat</div>';
    }
    echo '</div>'; // End Chat Box

    echo '</div>';
}

add_action('wp_ajax_aitrongcay_admin_get_messages', 'aitrongcay_admin_get_messages_ajax');
function aitrongcay_admin_get_messages_ajax() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error();
    }
    $user_id = (int) $_POST['user_id'];
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'aitr_support_chat';
    
    $messages = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table_name} WHERE user_id = %d ORDER BY created_at ASC", $user_id), ARRAY_A);
    // Đánh dấu đã đọc
    $wpdb->query($wpdb->prepare("UPDATE {$table_name} SET is_read = 1 WHERE user_id = %d AND sender_type = 'customer'", $user_id));
    
    wp_send_json_success(['messages' => $messages]);
}

add_action('wp_ajax_aitrongcay_admin_send_message', 'aitrongcay_admin_send_message_ajax');
function aitrongcay_admin_send_message_ajax() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error();
    }
    
    $user_id = (int) $_POST['user_id'];
    $message = trim((string) wp_unslash($_POST['message']));
    
    if (empty($message)) {
        wp_send_json_error();
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'aitr_support_chat';
    
    $wpdb->insert($table_name, [
        'user_id' => $user_id,
        'sender_type' => 'admin',
        'message' => wp_kses_post($message),
        'created_at' => current_time('mysql'),
        'is_read' => 0
    ]);
    
    wp_send_json_success();
}
