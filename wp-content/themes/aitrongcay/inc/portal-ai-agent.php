<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

function aitrongcay_ai_agent_option_name(): string
{
    return 'aitrongcay_ai_agent_config';
}

function aitrongcay_ai_agent_default_config(): array
{
    return [
        'enabled' => 0,
        'mode' => 'adapter-ready',
        'endpoint_url' => '',
        'bearer_token' => '',
        'model' => 'openclaw',
        'timeout_seconds' => 90,
    ];
}

function aitrongcay_ai_agent_config(): array
{
    $saved = get_option(aitrongcay_ai_agent_option_name(), []);
    if (! is_array($saved)) {
        $saved = [];
    }

    $config = array_merge(aitrongcay_ai_agent_default_config(), $saved);
    $config['enabled'] = empty($config['enabled']) ? 0 : 1;
    $config['mode'] = in_array((string) $config['mode'], ['adapter-ready', 'remote-http', 'openai-chat', 'gemini-chat'], true) ? (string) $config['mode'] : 'adapter-ready';
    $config['endpoint_url'] = esc_url_raw((string) $config['endpoint_url']);
    $config['bearer_token'] = trim((string) $config['bearer_token']);
    $config['model'] = trim((string) ($config['model'] ?? 'openclaw')) ?: 'openclaw';
    $config['timeout_seconds'] = max(5, min(90, (int) $config['timeout_seconds']));

    return $config;
}

function aitrongcay_ai_agent_is_remote_enabled(): bool
{
    $config = aitrongcay_ai_agent_config();
    if ((int) $config['enabled'] !== 1) {
        return false;
    }
    if ($config['mode'] === 'gemini-chat') {
        return true;
    }
    return in_array($config['mode'], ['remote-http', 'openai-chat'], true) && $config['endpoint_url'] !== '';
}

function aitrongcay_ai_agent_tables(): array
{
    global $wpdb;

    return [
        'threads' => $wpdb->prefix . 'aitr_ai_threads',
        'messages' => $wpdb->prefix . 'aitr_ai_messages',
        'sessions' => $wpdb->prefix . 'aitr_ai_sessions',
        'profiles' => $wpdb->prefix . 'aitr_ai_profiles',
        'memory_items' => $wpdb->prefix . 'aitr_ai_memory_items',
        'session_snapshots' => $wpdb->prefix . 'aitr_ai_session_snapshots',
    ];
}

function aitrongcay_install_ai_agent_tables(): void
{
    global $wpdb;

    $tables = aitrongcay_ai_agent_tables();
    $charset_collate = $wpdb->get_charset_collate();

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    dbDelta("CREATE TABLE {$tables['threads']} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        thread_key VARCHAR(190) NOT NULL,
        garden_key VARCHAR(190) NOT NULL,
        owner_user_id BIGINT UNSIGNED NOT NULL,
        viewer_user_id BIGINT UNSIGNED NOT NULL,
        title VARCHAR(255) DEFAULT '' NOT NULL,
        remote_thread_key VARCHAR(190) DEFAULT '' NOT NULL,
        last_user_message TEXT NULL,
        last_assistant_message LONGTEXT NULL,
        status VARCHAR(40) DEFAULT 'active' NOT NULL,
        last_error TEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY thread_key (thread_key),
        KEY garden_viewer (garden_key, viewer_user_id),
        KEY owner_user_id (owner_user_id)
    ) $charset_collate;");

    dbDelta("CREATE TABLE {$tables['messages']} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        thread_id BIGINT UNSIGNED NOT NULL,
        session_id BIGINT UNSIGNED NULL,
        role VARCHAR(20) NOT NULL,
        message_text LONGTEXT NULL,
        message_meta LONGTEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY thread_id (thread_id),
        KEY session_id (session_id),
        KEY role (role)
    ) $charset_collate;");

    dbDelta("CREATE TABLE {$tables['sessions']} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        session_key VARCHAR(190) NOT NULL,
        legacy_thread_id BIGINT UNSIGNED NULL,
        user_id BIGINT UNSIGNED NOT NULL,
        owner_user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
        garden_key VARCHAR(190) NOT NULL DEFAULT '',
        scope_type VARCHAR(40) NOT NULL DEFAULT 'garden',
        title VARCHAR(255) DEFAULT '' NOT NULL,
        status VARCHAR(40) DEFAULT 'active' NOT NULL,
        working_summary LONGTEXT NULL,
        remote_thread_key VARCHAR(190) DEFAULT '' NOT NULL,
        last_user_message TEXT NULL,
        last_assistant_message LONGTEXT NULL,
        last_error TEXT NULL,
        last_message_at DATETIME NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY session_key (session_key),
        UNIQUE KEY legacy_thread_id (legacy_thread_id),
        KEY user_id (user_id),
        KEY owner_user_id (owner_user_id),
        KEY garden_user (garden_key, user_id),
        KEY status_last_message (status, last_message_at)
    ) $charset_collate;");

    dbDelta("CREATE TABLE {$tables['profiles']} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT UNSIGNED NOT NULL,
        profile_summary LONGTEXT NULL,
        preferences_json LONGTEXT NULL,
        facts_json LONGTEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY user_id (user_id)
    ) $charset_collate;");

    dbDelta("CREATE TABLE {$tables['memory_items']} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT UNSIGNED NOT NULL,
        garden_key VARCHAR(190) NOT NULL DEFAULT '',
        session_id BIGINT UNSIGNED NULL,
        scope VARCHAR(40) NOT NULL DEFAULT 'user',
        kind VARCHAR(40) NOT NULL DEFAULT 'fact',
        content LONGTEXT NULL,
        salience_score DECIMAL(5,2) NOT NULL DEFAULT 0.50,
        confidence_score DECIMAL(5,2) NOT NULL DEFAULT 0.50,
        source_message_id BIGINT UNSIGNED NULL,
        is_pinned TINYINT(1) NOT NULL DEFAULT 0,
        expires_at DATETIME NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_scope_kind (user_id, scope, kind),
        KEY garden_key (garden_key),
        KEY session_id (session_id),
        KEY is_pinned (is_pinned),
        KEY expires_at (expires_at)
    ) $charset_collate;");

    dbDelta("CREATE TABLE {$tables['session_snapshots']} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        session_id BIGINT UNSIGNED NOT NULL,
        summary LONGTEXT NULL,
        open_loops LONGTEXT NULL,
        entities_json LONGTEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY session_id (session_id),
        KEY created_at (created_at)
    ) $charset_collate;");

    $legacy_threads = $wpdb->get_results("SELECT * FROM {$tables['threads']}", ARRAY_A);
    if (is_array($legacy_threads)) {
        foreach ($legacy_threads as $thread) {
            $legacy_thread_id = (int) ($thread['id'] ?? 0);
            if ($legacy_thread_id <= 0) {
                continue;
            }

            $existing_session_id = (int) $wpdb->get_var($wpdb->prepare("SELECT id FROM {$tables['sessions']} WHERE legacy_thread_id = %d LIMIT 1", $legacy_thread_id));
            if ($existing_session_id <= 0) {
                $wpdb->insert(
                    $tables['sessions'],
                    [
                        'session_key' => 'legacy:' . (string) ($thread['thread_key'] ?? ('thread-' . $legacy_thread_id)),
                        'legacy_thread_id' => $legacy_thread_id,
                        'user_id' => (int) ($thread['viewer_user_id'] ?? 0),
                        'owner_user_id' => (int) ($thread['owner_user_id'] ?? 0),
                        'garden_key' => (string) ($thread['garden_key'] ?? ''),
                        'scope_type' => ((string) ($thread['garden_key'] ?? '') !== '') ? 'garden' : 'global',
                        'title' => (string) (($thread['title'] ?? '') !== '' ? $thread['title'] : ('AI Agent • ' . (string) ($thread['garden_key'] ?? ''))),
                        'status' => (string) ($thread['status'] ?? 'active'),
                        'remote_thread_key' => (string) ($thread['remote_thread_key'] ?? ''),
                        'last_user_message' => (string) ($thread['last_user_message'] ?? ''),
                        'last_assistant_message' => (string) ($thread['last_assistant_message'] ?? ''),
                        'last_error' => (string) ($thread['last_error'] ?? ''),
                        'last_message_at' => (string) ($thread['updated_at'] ?? current_time('mysql')),
                        'created_at' => (string) ($thread['created_at'] ?? current_time('mysql')),
                        'updated_at' => (string) ($thread['updated_at'] ?? current_time('mysql')),
                    ],
                    ['%s', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
                );
                $existing_session_id = (int) $wpdb->insert_id;
            }

            if ($existing_session_id > 0) {
                $wpdb->query($wpdb->prepare("UPDATE {$tables['messages']} SET session_id = %d WHERE thread_id = %d AND session_id IS NULL", $existing_session_id, $legacy_thread_id));
            }
        }
    }

    update_option('aitrongcay_ai_agent_db_version', '2026-04-13-v2', false);
}
add_action('after_switch_theme', 'aitrongcay_install_ai_agent_tables');

function aitrongcay_maybe_install_ai_agent_tables(): void
{
    if (get_option('aitrongcay_ai_agent_db_version') === '2026-04-13-v2') {
        return;
    }
    aitrongcay_install_ai_agent_tables();
}
add_action('init', 'aitrongcay_maybe_install_ai_agent_tables', 6);

function aitrongcay_ai_generate_session_key(): string
{
    return 'sess:' . wp_generate_password(20, false, false);
}

function aitrongcay_ai_session_scope_type(string $garden_key, string $mode = ''): string
{
    $mode = sanitize_key($mode);
    if ($mode === 'onboarding') {
        return 'onboarding';
    }
    return $garden_key !== '' ? 'garden' : 'global';
}

function aitrongcay_ai_get_session_by_id(int $session_id, WP_User $viewer): array
{
    global $wpdb;
    if ($session_id <= 0) {
        return [];
    }
    $tables = aitrongcay_ai_agent_tables();
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tables['sessions']} WHERE id = %d AND user_id = %d LIMIT 1", $session_id, (int) $viewer->ID), ARRAY_A);
    return is_array($row) ? $row : [];
}

function aitrongcay_ai_list_sessions(WP_User $viewer, string $garden_key = '', int $limit = 30): array
{
    global $wpdb;
    $tables = aitrongcay_ai_agent_tables();
    $limit = max(1, min(100, $limit));
    if ($garden_key !== '') {
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$tables['sessions']} WHERE user_id = %d AND garden_key = %s AND status <> 'deleted' ORDER BY COALESCE(last_message_at, updated_at, created_at) DESC LIMIT %d",
            (int) $viewer->ID,
            $garden_key,
            $limit
        ), ARRAY_A);
    } else {
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$tables['sessions']} WHERE user_id = %d AND status <> 'deleted' ORDER BY COALESCE(last_message_at, updated_at, created_at) DESC LIMIT %d",
            (int) $viewer->ID,
            $limit
        ), ARRAY_A);
    }
    return is_array($rows) ? $rows : [];
}

function aitrongcay_ai_create_session(WP_User $viewer, string $garden_key = '', array $args = []): array
{
    global $wpdb;
    $tables = aitrongcay_ai_agent_tables();
    $owner = $garden_key !== '' && function_exists('aitrongcay_get_garden_owner_user') ? aitrongcay_get_garden_owner_user($garden_key) : null;
    $owner_user_id = (int) (($owner instanceof WP_User ? $owner->ID : $viewer->ID) ?: 0);
    $scope_type = aitrongcay_ai_session_scope_type($garden_key, (string) ($args['mode'] ?? ''));
    $default_title = $scope_type === 'onboarding' ? 'Khởi tạo khoang mới' : ($garden_key !== '' ? 'Trợ lý AI • ' . $garden_key : 'Trợ lý AI mới');
    $title = trim((string) ($args['title'] ?? '')) ?: $default_title;
    $session_key = aitrongcay_ai_generate_session_key();
    $wpdb->insert(
        $tables['sessions'],
        [
            'session_key' => $session_key,
            'user_id' => (int) $viewer->ID,
            'owner_user_id' => $owner_user_id,
            'garden_key' => $garden_key,
            'scope_type' => $scope_type,
            'title' => $title,
            'status' => 'active',
            'last_message_at' => current_time('mysql'),
        ],
        ['%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s']
    );
    return aitrongcay_ai_get_session_by_id((int) $wpdb->insert_id, $viewer);
}

function aitrongcay_ai_get_or_create_session(string $garden_key, WP_User $viewer, array $args = []): array
{
    global $wpdb;
    $tables = aitrongcay_ai_agent_tables();
    $requested_session_id = (int) ($args['session_id'] ?? 0);
    if ($requested_session_id > 0) {
        $session = aitrongcay_ai_get_session_by_id($requested_session_id, $viewer);
        if ($session) {
            return $session;
        }
    }
    $requested_session_key = trim((string) ($args['session_key'] ?? ''));
    if ($requested_session_key !== '') {
        $session = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tables['sessions']} WHERE session_key = %s AND user_id = %d LIMIT 1", $requested_session_key, (int) $viewer->ID), ARRAY_A);
        if (is_array($session)) {
            return $session;
        }
    }

    $legacy_thread = aitrongcay_ai_get_or_create_thread($garden_key, $viewer);
    $legacy_thread_id = (int) ($legacy_thread['id'] ?? 0);
    if ($legacy_thread_id > 0) {
        $session = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tables['sessions']} WHERE legacy_thread_id = %d LIMIT 1", $legacy_thread_id), ARRAY_A);
        if (is_array($session) && empty($args['force_new'])) {
            return $session;
        }
    }

    return aitrongcay_ai_create_session($viewer, $garden_key, $args);
}

function aitrongcay_ai_append_message_to_session(int $session_id, string $role, string $text, array $meta = []): int
{
    global $wpdb;
    if ($session_id <= 0) {
        return 0;
    }
    $tables = aitrongcay_ai_agent_tables();
    $session = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tables['sessions']} WHERE id = %d LIMIT 1", $session_id), ARRAY_A);
    if (! is_array($session)) {
        return 0;
    }
    $legacy_thread_id = (int) ($session['legacy_thread_id'] ?? 0);
    $wpdb->insert(
        $tables['messages'],
        [
            'thread_id' => max(0, $legacy_thread_id),
            'session_id' => $session_id,
            'role' => $role,
            'message_text' => $text,
            'message_meta' => $meta ? wp_json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
        ],
        ['%d', '%d', '%s', '%s', '%s']
    );
    return (int) $wpdb->insert_id;
}

function aitrongcay_ai_get_session_history(int $session_id, int $limit = 20): array
{
    global $wpdb;
    $tables = aitrongcay_ai_agent_tables();
    $limit = max(1, min(100, $limit));
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT id, role, message_text, created_at FROM {$tables['messages']} WHERE session_id = %d ORDER BY id DESC LIMIT %d",
        $session_id,
        $limit
    ), ARRAY_A);
    if (! is_array($rows) || $rows === []) {
        return [];
    }
    $rows = array_reverse($rows);
    return array_map(static function (array $row): array {
        return [
            'id' => (int) ($row['id'] ?? 0),
            'role' => (string) ($row['role'] ?? 'assistant'),
            'text' => (string) ($row['message_text'] ?? ''),
            'time' => ! empty($row['created_at']) ? mysql2date('c', (string) $row['created_at'], false) : wp_date('c'),
        ];
    }, $rows);
}

function aitrongcay_ai_update_session_state(int $session_id, array $patch): void
{
    global $wpdb;
    if ($session_id <= 0) {
        return;
    }
    $tables = aitrongcay_ai_agent_tables();
    $data = [];
    $format = [];
    foreach ([
        'title' => '%s',
        'status' => '%s',
        'working_summary' => '%s',
        'remote_thread_key' => '%s',
        'last_user_message' => '%s',
        'last_assistant_message' => '%s',
        'last_error' => '%s',
        'last_message_at' => '%s',
    ] as $key => $mask) {
        if (array_key_exists($key, $patch)) {
            $data[$key] = $patch[$key];
            $format[] = $mask;
        }
    }
    if ($data === []) {
        return;
    }
    $wpdb->update($tables['sessions'], $data, ['id' => $session_id], $format, ['%d']);
}

function aitrongcay_ai_build_session_summary(int $session_id): string
{
    $history = aitrongcay_ai_get_session_history($session_id, 6);
    if ($history === []) {
        return '';
    }
    $parts = [];
    foreach ($history as $item) {
        $role = (string) ($item['role'] ?? 'assistant');
        $text = trim((string) ($item['text'] ?? ''));
        if ($text === '') {
            continue;
        }
        $parts[] = ($role === 'user' ? 'Anh/chị' : 'Cindy') . ': ' . wp_trim_words($text, 18, '...');
    }
    return implode("\n", array_slice($parts, -4));
}

function aitrongcay_ai_maybe_promote_memory(WP_User $viewer, array $session, string $message, string $reply, int $source_message_id = 0): void
{
    global $wpdb;
    $tables = aitrongcay_ai_agent_tables();
    $text = trim($message);
    if ($text === '') {
        return;
    }
    $lower = function_exists('mb_strtolower') ? mb_strtolower($text) : strtolower($text);
    $kind = '';
    $scope = 'session';
    $salience = 0.55;
    if (preg_match('/\b(tôi thích|em thích|anh thích|ưu tiên|muốn gọi|xưng hô)\b/u', $lower)) {
        $kind = 'preference';
        $scope = 'user';
        $salience = 0.72;
    } elseif (preg_match('/\b(mục tiêu|kế hoạch|dự định|sẽ trồng|đang trồng)\b/u', $lower)) {
        $kind = 'goal';
        $scope = ((string) ($session['garden_key'] ?? '') !== '') ? 'garden' : 'user';
        $salience = 0.68;
    } elseif (preg_match('/\b(chốt|quyết định|thống nhất|ghi nhớ)\b/u', $lower)) {
        $kind = 'decision';
        $scope = 'session';
        $salience = 0.75;
    }
    if ($kind === '') {
        return;
    }
    $wpdb->insert(
        $tables['memory_items'],
        [
            'user_id' => (int) $viewer->ID,
            'garden_key' => (string) ($scope === 'garden' ? ($session['garden_key'] ?? '') : ''),
            'session_id' => (int) ($session['id'] ?? 0),
            'scope' => $scope,
            'kind' => $kind,
            'content' => wp_trim_words($text, 40, '...'),
            'salience_score' => $salience,
            'confidence_score' => 0.70,
            'source_message_id' => $source_message_id > 0 ? $source_message_id : null,
            'is_pinned' => 0,
        ],
        ['%d', '%s', '%d', '%s', '%s', '%s', '%f', '%f', '%d', '%d']
    );
}

function aitrongcay_ai_get_memory_context(WP_User $viewer, array $session, int $limit = 8): array
{
    global $wpdb;
    $tables = aitrongcay_ai_agent_tables();
    $limit = max(1, min(20, $limit));
    $garden_key = (string) ($session['garden_key'] ?? '');
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT scope, kind, content FROM {$tables['memory_items']} WHERE user_id = %d AND ((scope = 'user') OR (scope = 'session' AND session_id = %d) OR (scope = 'garden' AND garden_key = %s)) ORDER BY is_pinned DESC, salience_score DESC, updated_at DESC LIMIT %d",
        (int) $viewer->ID,
        (int) ($session['id'] ?? 0),
        $garden_key,
        $limit
    ), ARRAY_A);
    return is_array($rows) ? $rows : [];
}

function aitrongcay_ai_thread_key(string $garden_key, int $viewer_user_id): string
{
    return 'garden:' . md5($garden_key . '|' . $viewer_user_id);
}

function aitrongcay_ai_get_or_create_thread(string $garden_key, WP_User $viewer): array
{
    global $wpdb;

    $tables = aitrongcay_ai_agent_tables();
    $owner = function_exists('aitrongcay_get_garden_owner_user') ? aitrongcay_get_garden_owner_user($garden_key) : null;
    $owner_user_id = (int) (($owner instanceof WP_User ? $owner->ID : $viewer->ID) ?: 0);
    $thread_key = aitrongcay_ai_thread_key($garden_key, (int) $viewer->ID);

    $thread = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tables['threads']} WHERE thread_key = %s LIMIT 1", $thread_key), ARRAY_A);
    if (is_array($thread)) {
        return $thread;
    }

    $wpdb->insert(
        $tables['threads'],
        [
            'thread_key' => $thread_key,
            'garden_key' => $garden_key,
            'owner_user_id' => $owner_user_id,
            'viewer_user_id' => (int) $viewer->ID,
            'title' => 'AI Agent • ' . $garden_key,
            'status' => 'active',
        ],
        ['%s', '%s', '%d', '%d', '%s', '%s']
    );

    $thread = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tables['threads']} WHERE id = %d LIMIT 1", (int) $wpdb->insert_id), ARRAY_A);
    return is_array($thread) ? $thread : [];
}

function aitrongcay_ai_append_message(int $thread_id, string $role, string $text, array $meta = []): void
{
    global $wpdb;

    $tables = aitrongcay_ai_agent_tables();
    $wpdb->insert(
        $tables['messages'],
        [
            'thread_id' => $thread_id,
            'role' => $role,
            'message_text' => $text,
            'message_meta' => $meta ? wp_json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
        ],
        ['%d', '%s', '%s', '%s']
    );
}

function aitrongcay_ai_get_history(int $thread_id, int $limit = 20): array
{
    global $wpdb;

    $tables = aitrongcay_ai_agent_tables();
    $limit = max(1, min(50, $limit));
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT role, message_text, created_at FROM {$tables['messages']} WHERE thread_id = %d ORDER BY id DESC LIMIT %d",
        $thread_id,
        $limit
    ), ARRAY_A);

    if (! is_array($rows)) {
        return [];
    }

    $rows = array_reverse($rows);

    return array_map(static function (array $row): array {
        return [
            'role' => (string) ($row['role'] ?? 'assistant'),
            'text' => (string) ($row['message_text'] ?? ''),
            'time' => ! empty($row['created_at']) ? mysql2date('c', (string) $row['created_at'], false) : wp_date('c'),
        ];
    }, $rows);
}

function aitrongcay_ai_update_thread_state(int $thread_id, array $patch): void
{
    global $wpdb;

    $tables = aitrongcay_ai_agent_tables();
    $data = [];
    $format = [];

    foreach ([
        'remote_thread_key' => '%s',
        'last_user_message' => '%s',
        'last_assistant_message' => '%s',
        'status' => '%s',
        'last_error' => '%s',
    ] as $key => $mask) {
        if (array_key_exists($key, $patch)) {
            $data[$key] = $patch[$key];
            $format[] = $mask;
        }
    }

    if ($data === []) {
        return;
    }

    $wpdb->update($tables['threads'], $data, ['id' => $thread_id], $format, ['%d']);
}

function aitrongcay_ai_build_context_payload(string $message, WP_User $user, string $garden_key, array $thread, array $history): array
{
    $profile = function_exists('aitrongcay_portal_profile_for_garden_context')
        ? aitrongcay_portal_profile_for_garden_context($garden_key, $user)
        : aitrongcay_portal_profile_for_user($user);
    $pots = function_exists('aitrongcay_portal_pots') ? aitrongcay_portal_pots($garden_key, $user) : [];
    $tools = function_exists('aitrongcay_portal_tool_shelf') ? aitrongcay_portal_tool_shelf($garden_key, $user) : [];
    $pot_notes = function_exists('aitrongcay_get_garden_pot_notes') ? aitrongcay_get_garden_pot_notes($garden_key) : [];
    $memory_items = function_exists('aitrongcay_ai_get_memory_context') ? aitrongcay_ai_get_memory_context($user, $thread, 8) : [];

    return [
        'thread' => [
            'id' => (int) ($thread['id'] ?? 0),
            'session_key' => (string) ($thread['session_key'] ?? ''),
            'thread_key' => (string) ($thread['thread_key'] ?? ''),
            'remote_thread_key' => (string) ($thread['remote_thread_key'] ?? ''),
            'working_summary' => (string) ($thread['working_summary'] ?? ''),
        ],
        'viewer' => [
            'id' => (int) $user->ID,
            'display_name' => (string) ($user->display_name ?: $user->user_login),
            'email' => (string) $user->user_email,
        ],
        'garden' => [
            'key' => $garden_key,
            'profile' => $profile,
            'pots' => array_slice(is_array($pots) ? $pots : [], 0, 12),
            'tool_shelf' => array_slice(is_array($tools) ? $tools : [], 0, 20),
            'pot_notes' => is_array($pot_notes) ? $pot_notes : [],
        ],
        'history' => array_slice($history, -4),
        'memory' => $memory_items,
        'message' => $message,
        'source' => [
            'app' => 'aitrongcay-wordpress',
            'mode' => aitrongcay_ai_agent_is_remote_enabled() ? 'remote-http' : 'adapter-ready',
        ],
    ];
}

function aitrongcay_ai_build_openai_messages(array $payload): array
{
    $viewer_name = (string) ($payload['viewer']['display_name'] ?? 'người dùng');
    $garden_key = (string) ($payload['garden']['key'] ?? '');
    $profile = $payload['garden']['profile'] ?? [];
    $garden_name = trim((string) ($profile['garden_name'] ?? $profile['name'] ?? ''));
    $pots = array_slice(is_array($payload['garden']['pots'] ?? null) ? $payload['garden']['pots'] : [], 0, 8);
    $pot_labels = [];
    foreach ($pots as $pot) {
        if (! is_array($pot)) {
            continue;
        }
        $label = trim((string) ($pot['name'] ?? $pot['code'] ?? ''));
        $plant_name = trim((string) ($pot['plant_name'] ?? ''));
        if ($label === '') {
            continue;
        }
        $metrics = [];
        if (!empty($pot['temperature'])) $metrics[] = 'Nhiệt độ: ' . $pot['temperature'];
        if (!empty($pot['humidity'])) $metrics[] = 'Độ ẩm: ' . $pot['humidity'];
        if (!empty($pot['soil_moisture'])) $metrics[] = 'Độ ẩm đất: ' . $pot['soil_moisture'];
        if (!empty($pot['soil_ec'])) $metrics[] = 'EC đất: ' . $pot['soil_ec'];
        if (!empty($pot['ph'])) $metrics[] = 'pH: ' . $pot['ph'];
        if (!empty($pot['soil_temp'])) $metrics[] = 'Nhiệt độ đất: ' . $pot['soil_temp'];
        if (!empty($pot['status_summary'])) $metrics[] = 'Tình trạng: ' . $pot['status_summary'];

        $metrics_str = $metrics !== [] ? ' (' . implode(', ', $metrics) . ')' : '';
        $pot_labels[] = ($plant_name !== '' ? ($label . ' [' . $plant_name . ']') : $label) . $metrics_str;
    }

    $current_date = wp_date('l, d/m/Y');
    $current_month = (int) wp_date('n');
    $season = '';
    if (in_array($current_month, [2, 3, 4], true)) $season = 'Mùa xuân';
    elseif (in_array($current_month, [5, 6, 7], true)) $season = 'Mùa hè';
    elseif (in_array($current_month, [8, 9, 10], true)) $season = 'Mùa thu';
    else $season = 'Mùa đông';

    $system = [
        'Anh là Cindy, trợ lý AI của aitrongcay.com.',
        'Thời gian hệ thống hiện tại: ' . $current_date . ' (' . $season . '). Vị trí: Việt Nam.',
        'Bạn là chuyên gia nông nghiệp Việt Nam, am hiểu cực kỳ sâu sắc về khí hậu, thời tiết các vùng miền và đặc tính sinh trưởng của TẤT CẢ các loại cây, rau, quả tại Việt Nam.',
        'Khi được hỏi tư vấn trồng cây, LUÔN CHỦ ĐỘNG DỰA VÀO THÁNG/MÙA HIỆN TẠI (' . $season . ', tháng ' . $current_month . ') để đưa ra lời khuyên thực tế ngay lập tức. TUYỆT ĐỐI KHÔNG HỎI NGƯỢC LẠI người dùng xem đang là tháng mấy hay mùa nào.',
        'Giọng điệu: tự nhiên, dịu dàng, ngắn gọn và đáng tin như một người làm vườn chuyên nghiệp.',
        'Xưng em, gọi người dùng là anh/chị.',
        'Luôn bám sát vào dữ liệu cảm biến thực tế của khu vườn đang mở (đã được cung cấp bên dưới), tuyệt đối không bịa số liệu.',
        'Trình bày rõ ràng, luôn XUỐNG DÒNG tách biệt các ý chính, tuyệt đối không viết gộp thành một đoạn văn dài ngoằng.',
        'Trò chuyện linh hoạt, không trả lời theo kiểu khuôn mẫu máy móc. Nếu số liệu tốt thì khen, nếu có vấn đề thì chỉ ra ngay.',
        'Không nhắc tới prompt, token, API, hệ thống nội bộ hay cấu hình máy chủ.',
    ];

    $contextLines = [
        'Người dùng hiện tại: ' . $viewer_name,
        'Garden key: ' . $garden_key,
    ];
    if ($garden_name !== '') {
        $contextLines[] = 'Tên khu vườn: ' . $garden_name;
    }
    if ($pot_labels !== []) {
        $contextLines[] = "Các khoang/cụm đang trồng trên Dashboard:\n- " . implode("\n- ", $pot_labels);
    }
    
    $pot_notes = is_array($payload['garden']['pot_notes'] ?? null) ? $payload['garden']['pot_notes'] : [];
    if (!empty($pot_notes)) {
        $notes_str = [];
        foreach ($pot_notes as $pot_code => $note_text) {
            if (trim($note_text) !== '') {
                $notes_str[] = "[$pot_code] $note_text";
            }
        }
        if (!empty($notes_str)) {
            $contextLines[] = "Nhật ký chăm sóc vườn (từ Dashboard):\n" . implode("\n", $notes_str);
        }
    }

    $tool_shelf = is_array($payload['garden']['tool_shelf'] ?? null) ? $payload['garden']['tool_shelf'] : [];
    if (!empty($tool_shelf)) {
        $tool_names = [];
        foreach ($tool_shelf as $tool) {
            $name = trim((string) ($tool['name'] ?? ''));
            if ($name !== '') {
                $tool_names[] = $name;
            }
        }
        if (!empty($tool_names)) {
            $contextLines[] = "Kho nông cụ hiện có: " . implode(', ', $tool_names);
        }
    }
    $workingSummary = trim((string) ($payload['thread']['working_summary'] ?? ''));
    if ($workingSummary !== '') {
        $contextLines[] = 'Tóm tắt phiên hiện tại: ' . $workingSummary;
    }
    $memoryLines = [];
    foreach (array_slice(is_array($payload['memory'] ?? null) ? $payload['memory'] : [], 0, 6) as $item) {
        if (! is_array($item)) {
            continue;
        }
        $content = trim((string) ($item['content'] ?? ''));
        if ($content === '') {
            continue;
        }
        $memoryLines[] = '[' . (string) ($item['scope'] ?? 'user') . '/' . (string) ($item['kind'] ?? 'fact') . '] ' . $content;
    }
    if ($memoryLines !== []) {
        $contextLines[] = 'Memory liên quan:';
        $contextLines = array_merge($contextLines, $memoryLines);
    }

    $messages = [[
        'role' => 'system',
        'content' => implode("\n", array_merge($system, [''], $contextLines)),
    ]];

    foreach (array_slice(is_array($payload['history'] ?? null) ? $payload['history'] : [], -4) as $item) {
        if (! is_array($item)) {
            continue;
        }
        $role = (string) ($item['role'] ?? 'user');
        $text = trim((string) ($item['text'] ?? ''));
        if ($text === '' || ! in_array($role, ['system', 'user', 'assistant'], true)) {
            continue;
        }
        $messages[] = [
            'role' => $role,
            'content' => $text,
        ];
    }

    $latestMessage = trim((string) ($payload['message'] ?? ''));
    $lastMessage = $messages[count($messages) - 1] ?? null;
    if ($latestMessage !== '' && (! is_array($lastMessage) || (string) ($lastMessage['role'] ?? '') !== 'user' || trim((string) ($lastMessage['content'] ?? '')) !== $latestMessage)) {
        $messages[] = [
            'role' => 'user',
            'content' => $latestMessage,
        ];
    }

    return $messages;
}

function aitrongcay_ai_extract_openai_text(array $response_json): string
{
    $content = $response_json['choices'][0]['message']['content'] ?? '';
    if (is_string($content)) {
        return trim($content);
    }

    if (is_array($content)) {
        $chunks = [];
        foreach ($content as $part) {
            if (is_string($part)) {
                $chunks[] = $part;
                continue;
            }
            if (is_array($part) && isset($part['text']) && is_string($part['text'])) {
                $chunks[] = $part['text'];
            }
        }
        return trim(implode("\n", array_filter($chunks)));
    }

    return '';
}

function aitrongcay_ai_call_remote_agent(string $message, WP_User $user, string $garden_key, array $thread, array $history): array
{
    $config = aitrongcay_ai_agent_config();
    if (! aitrongcay_ai_agent_is_remote_enabled()) {
        return [
            'ok' => false,
            'message' => 'AI agent remote chưa được bật.',
            'mode' => 'adapter-ready',
        ];
    }

    $payload = aitrongcay_ai_build_context_payload($message, $user, $garden_key, $thread, $history);
    $headers = [
        'Content-Type' => 'application/json; charset=utf-8',
        'Accept' => 'application/json',
    ];
    if ($config['bearer_token'] !== '') {
        $headers['Authorization'] = 'Bearer ' . $config['bearer_token'];
    }

    $request_body = $payload;
    if ($config['mode'] === 'openai-chat') {
        $headers['x-openclaw-session-key'] = 'aitrongcay:' . sanitize_key((string) ($thread['thread_key'] ?? ('garden-' . $user->ID)));
        $headers['x-openclaw-message-channel'] = 'webchat';
        $request_body = [
            'model' => (string) $config['model'],
            'messages' => aitrongcay_ai_build_openai_messages($payload),
        ];
    } elseif ($config['mode'] === 'gemini-chat') {
        $api_key = aitrongcay_get_gemini_api_key();
        if ($api_key === '') {
            return ['ok' => false, 'message' => 'Chưa cấu hình Gemini API key (trong màn hình Cài đặt chung).', 'mode' => 'gemini-chat'];
        }
        // Gemini model name mapping - translate stored value to API-valid model name
        $model_map = [
            'gemini-1.5-flash'  => 'gemini-flash-latest',
            'gemini-1.5-pro'    => 'gemini-pro-latest',
            'gemini-2.0-flash'  => 'gemini-2.0-flash',
            'gemini-2.5-flash'  => 'gemini-2.5-flash',
            'openclaw'          => 'gemini-flash-latest',
        ];
        $raw_model = trim($config['model']);
        $endpoint_model = $model_map[$raw_model] ?? ($raw_model !== '' ? $raw_model : 'gemini-flash-latest');
        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/' . $endpoint_model . ':generateContent?key=' . rawurlencode($api_key);
        
        $messages = aitrongcay_ai_build_openai_messages($payload);
        $contents = [];
        $system_instruction_text = '';
        
        foreach ($messages as $msg) {
            if ($msg['role'] === 'system') {
                $system_instruction_text .= $msg['content'] . "\n\n";
                continue;
            }
            $target_role = $msg['role'] === 'assistant' ? 'model' : 'user';
            
            $text = $msg['content'];
            // Prepend system instruction to the very first user message
            if ($system_instruction_text !== '' && $target_role === 'user' && empty($contents)) {
                $text = "System Instruction:\n" . $system_instruction_text . "---\nUser Input:\n" . $text;
                $system_instruction_text = ''; // only prepend once
            }
            
            if (count($contents) > 0 && $contents[count($contents) - 1]['role'] === $target_role) {
                $contents[count($contents) - 1]['parts'][] = ['text' => "\n\n" . $text];
            } else {
                $contents[] = [
                    'role' => $target_role,
                    'parts' => [['text' => $text]],
                ];
            }
        }
        
        // If there were only system messages (rare)
        if ($system_instruction_text !== '' && empty($contents)) {
             $contents[] = [
                 'role' => 'user',
                 'parts' => [['text' => $system_instruction_text]]
             ];
        }
        
        $request_body = [
            'contents' => $contents,
        ];

        $response = wp_remote_post($endpoint, [
            'timeout' => (int) $config['timeout_seconds'],
            'headers' => ['Content-Type' => 'application/json'],
            'body' => wp_json_encode($request_body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
        
        if (is_wp_error($response)) {
            return [
                'ok' => false,
                'message' => $response->get_error_message(),
                'mode' => 'gemini-chat',
            ];
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $body = (string) wp_remote_retrieve_body($response);
        $json = json_decode($body, true);

        if ($code !== 200 || !is_array($json)) {
            return [
                'ok' => false,
                'message' => 'Gemini API trả về lỗi: ' . ($json['error']['message'] ?? $body),
                'mode' => 'gemini-chat',
                'http_code' => $code,
            ];
        }

        $reply = (string) ($json['candidates'][0]['content']['parts'][0]['text'] ?? '');
        if ($reply === '') {
            return [
                'ok' => false,
                'message' => 'Gemini không trả lời nội dung.',
                'mode' => 'gemini-chat',
            ];
        }

        return [
            'ok' => true,
            'reply' => trim($reply),
            'mode' => 'gemini-chat',
            'agentStatus' => 'Tư vấn trực tiếp từ Google Gemini API.',
            'sessionLabel' => 'garden-assistant-user-' . max(1, (int) $user->ID),
            'remoteThreadKey' => '',
            'latestPhoto' => [],
            'raw' => $json,
        ];
    }

    if ($config['mode'] !== 'gemini-chat') {
        $response = wp_remote_post($config['endpoint_url'], [
            'timeout' => (int) $config['timeout_seconds'],
            'headers' => $headers,
            'body' => wp_json_encode($request_body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        if (is_wp_error($response)) {
            return [
                'ok' => false,
                'message' => $response->get_error_message(),
                'mode' => 'remote-http',
            ];
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $body = (string) wp_remote_retrieve_body($response);
        $json = json_decode($body, true);

        if ($code < 200 || $code >= 300 || ! is_array($json)) {
            return [
                'ok' => false,
                'message' => 'AI agent remote trả về dữ liệu chưa hợp lệ.',
                'mode' => 'remote-http',
                'http_code' => $code,
                'raw_body' => $body,
            ];
        }

        $reply = $config['mode'] === 'openai-chat'
            ? aitrongcay_ai_extract_openai_text($json)
            : trim((string) ($json['reply'] ?? $json['message'] ?? ''));
        if ($reply === '') {
            return [
                'ok' => false,
                'message' => 'AI agent remote chưa trả lời nội dung.',
                'mode' => 'remote-http',
                'http_code' => $code,
            ];
        }

        return [
            'ok' => true,
            'reply' => $reply,
            'mode' => (string) $config['mode'],
            'agentStatus' => (string) ($json['agent_status'] ?? ($config['mode'] === 'openai-chat' ? 'Đã nối Cindy qua OpenClaw Chat API.' : 'Đã nối AI agent remote.')),
            'sessionLabel' => (string) ($json['session_label'] ?? ($config['mode'] === 'openai-chat' ? ('aitrongcay:' . sanitize_key((string) ($thread['thread_key'] ?? ('garden-' . $user->ID)))) : ('garden-assistant-user-' . max(1, (int) $user->ID)))),
            'remoteThreadKey' => (string) ($json['thread_key'] ?? $json['remote_thread_key'] ?? ''),
            'latestPhoto' => is_array($json['latest_photo'] ?? null) ? $json['latest_photo'] : [],
            'raw' => $json,
        ];
    }
    return ['ok' => false, 'mode' => 'adapter-ready'];
}

function aitrongcay_register_ai_agent_admin_page(): void
{
    add_submenu_page(
        'aitrongcay-unified-admin-beta',
        __('AI Agent khu vườn', 'aitrongcay'),
        __('AI Agent khu vườn', 'aitrongcay'),
        'manage_options',
        'aitrongcay-ai-agent',
        'aitrongcay_render_ai_agent_admin_page'
    );
}
add_action('admin_menu', 'aitrongcay_register_ai_agent_admin_page', 100);

function aitrongcay_render_ai_agent_admin_page(): void
{
    if (! current_user_can('manage_options')) {
        wp_die(esc_html__('Bạn không có quyền truy cập mục này.', 'aitrongcay'));
    }

    $config = aitrongcay_ai_agent_config();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        check_admin_referer('aitrongcay_ai_agent_save');
        $config = [
            'enabled' => empty($_POST['enabled']) ? 0 : 1,
            'mode' => sanitize_key((string) wp_unslash($_POST['mode'] ?? 'adapter-ready')),
            'endpoint_url' => esc_url_raw((string) wp_unslash($_POST['endpoint_url'] ?? '')),
            'bearer_token' => trim((string) wp_unslash($_POST['bearer_token'] ?? '')),
            'model' => sanitize_text_field((string) wp_unslash($_POST['model'] ?? 'openclaw')),
            'timeout_seconds' => max(5, min(90, (int) ($_POST['timeout_seconds'] ?? 90))),
        ];
        update_option(aitrongcay_ai_agent_option_name(), $config, false);
        $config = aitrongcay_ai_agent_config();
        echo '<div class="notice notice-success"><p>Đã lưu cấu hình AI agent.</p></div>';
    }

    ?>
    <div class="wrap">
        <h1>AI Agent khu vườn</h1>
        <p>Giữ nguyên giao diện portal hiện tại, nhưng cho phép nối Cindy ra agent service bên ngoài qua HTTP hoặc OpenClaw Chat API.</p>
        <form method="post">
            <?php wp_nonce_field('aitrongcay_ai_agent_save'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">Bật AI remote</th>
                    <td><label><input type="checkbox" name="enabled" value="1" <?php checked(! empty($config['enabled'])); ?>> Cho phép portal gọi AI agent bên ngoài</label></td>
                </tr>
                <tr>
                    <th scope="row">Chế độ</th>
                    <td>
                        <select name="mode">
                            <option value="adapter-ready" <?php selected($config['mode'], 'adapter-ready'); ?>>Adapter-ready (fallback nội bộ)</option>
                            <option value="remote-http" <?php selected($config['mode'], 'remote-http'); ?>>Remote HTTP</option>
                            <option value="gemini-chat" <?php selected($config['mode'], 'gemini-chat'); ?>>Google Gemini (Tích hợp sẵn)</option>
                            <option value="openai-chat" <?php selected($config['mode'], 'openai-chat'); ?>>OpenClaw Chat API</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Endpoint URL</th>
                    <td><input type="url" class="regular-text code" name="endpoint_url" value="<?php echo esc_attr((string) $config['endpoint_url']); ?>" placeholder="https://.../v1/chat/completions hoặc https://.../garden-agent"></td>
                </tr>
                <tr>
                    <th scope="row">Bearer token</th>
                    <td><input type="text" class="regular-text code" name="bearer_token" value="<?php echo esc_attr((string) $config['bearer_token']); ?>" placeholder="optional"></td>
                </tr>
                <tr>
                    <th scope="row">Model / Agent</th>
                    <td>
                        <input type="text" class="regular-text code" name="model" value="<?php echo esc_attr((string) ($config['model'] ?? 'openclaw')); ?>" placeholder="openclaw">
                        <p class="description">Dùng cho chế độ OpenClaw Chat API. Mặc định là <code>openclaw</code>.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Timeout (giây)</th>
                    <td><input type="number" min="5" max="90" step="1" name="timeout_seconds" value="<?php echo esc_attr((string) $config['timeout_seconds']); ?>"></td>
                </tr>
            </table>
            <?php submit_button('Lưu cấu hình'); ?>
        </form>
    </div>
    <?php
}
