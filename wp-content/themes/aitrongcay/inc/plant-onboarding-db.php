<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

function aitrongcay_onboarding_tables(): array
{
    global $wpdb;

    return [
        'plants' => $wpdb->prefix . 'aitr_plants',
        'supplies' => $wpdb->prefix . 'aitr_supplies',
        'plant_supplies' => $wpdb->prefix . 'aitr_plant_supplies',
        'plant_sop_steps' => $wpdb->prefix . 'aitr_plant_sop_steps',
        'plant_public_content' => $wpdb->prefix . 'aitr_plant_public_content',
        'plant_environment_profiles' => $wpdb->prefix . 'aitr_plant_environment_profiles',
        'plant_growth_stages' => $wpdb->prefix . 'aitr_plant_growth_stages',
        'plant_nutrition_profiles' => $wpdb->prefix . 'aitr_plant_nutrition_profiles',
        'plant_checklists' => $wpdb->prefix . 'aitr_plant_checklists',
        'plant_health_issues' => $wpdb->prefix . 'aitr_plant_health_issues',
        'plant_alert_rules' => $wpdb->prefix . 'aitr_plant_alert_rules',
        'plant_workflows' => $wpdb->prefix . 'aitr_plant_workflows',
        'plant_protocol_topics' => $wpdb->prefix . 'aitr_plant_protocol_topics',
        'plant_robot_tasks' => $wpdb->prefix . 'aitr_plant_robot_tasks',
        'plant_soil_logs' => $wpdb->prefix . 'aitr_plant_soil_logs',
    ];
}

function aitrongcay_install_onboarding_tables(): void
{
    global $wpdb;

    $tables = aitrongcay_onboarding_tables();
    $charset_collate = $wpdb->get_charset_collate();

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $sql_plants = "CREATE TABLE {$tables['plants']} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        slug VARCHAR(190) NOT NULL,
        plant_code VARCHAR(80) DEFAULT '' NOT NULL,
        public_name VARCHAR(255) NOT NULL,
        internal_name VARCHAR(255) DEFAULT '' NOT NULL,
        scientific_name VARCHAR(190) DEFAULT '' NOT NULL,
        category VARCHAR(120) DEFAULT '' NOT NULL,
        variety_name VARCHAR(190) DEFAULT '' NOT NULL,
        default_cycle_days INT DEFAULT 0 NOT NULL,
        germination_days INT DEFAULT 0 NOT NULL,
        harvest_start_day INT DEFAULT 0 NOT NULL,
        mature_height_cm INT DEFAULT 0 NOT NULL,
        difficulty_level VARCHAR(40) DEFAULT '' NOT NULL,
        short_description TEXT NULL,
        nutrition_components TEXT NULL,
        special_nutrition_components TEXT NULL,
        status VARCHAR(40) DEFAULT 'draft' NOT NULL,
        cover_image_url TEXT NULL,
        cover_image_id BIGINT UNSIGNED DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        UNIQUE KEY slug (slug),
        KEY plant_code (plant_code),
        KEY status (status)
    ) $charset_collate;";

    $sql_supplies = "CREATE TABLE {$tables['supplies']} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        code VARCHAR(120) DEFAULT '' NOT NULL,
        name VARCHAR(255) NOT NULL,
        type VARCHAR(120) DEFAULT '' NOT NULL,
        spec TEXT NULL,
        unit VARCHAR(80) DEFAULT '' NOT NULL,
        cost_price DECIMAL(15,2) DEFAULT 0 NOT NULL,
        sale_price DECIMAL(15,2) DEFAULT 0 NOT NULL,
        image_url TEXT NULL,
        image_id BIGINT UNSIGNED DEFAULT NULL,
        description TEXT NULL,
        optional_metrics_json LONGTEXT NULL,
        supplier_name VARCHAR(255) DEFAULT '' NOT NULL,
        stock_quantity INT DEFAULT 0 NOT NULL,
        status VARCHAR(40) DEFAULT 'active' NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY type (type),
        KEY status (status)
    ) $charset_collate;";

    $sql_plant_supplies = "CREATE TABLE {$tables['plant_supplies']} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        plant_id BIGINT UNSIGNED NOT NULL,
        supply_id BIGINT UNSIGNED NOT NULL,
        usage_role VARCHAR(60) DEFAULT 'required' NOT NULL,
        quantity_per_tray VARCHAR(120) DEFAULT '' NOT NULL,
        notes TEXT NULL,
        sort_order INT DEFAULT 0 NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        UNIQUE KEY plant_supply_unique (plant_id, supply_id),
        KEY plant_id (plant_id),
        KEY supply_id (supply_id)
    ) $charset_collate;";

    $sql_plant_sop_steps = "CREATE TABLE {$tables['plant_sop_steps']} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        plant_id BIGINT UNSIGNED NOT NULL,
        step_title VARCHAR(255) DEFAULT '' NOT NULL,
        day_from INT DEFAULT 0 NOT NULL,
        day_to INT DEFAULT 0 NOT NULL,
        light_level VARCHAR(60) DEFAULT '' NOT NULL,
        watering_rule TEXT NULL,
        operator_tasks TEXT NULL,
        expected_state TEXT NULL,
        alert_conditions TEXT NULL,
        notes TEXT NULL,
        sort_order INT DEFAULT 0 NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY plant_id (plant_id),
        KEY sort_order (sort_order)
    ) $charset_collate;";

    $sql_plant_public_content = "CREATE TABLE {$tables['plant_public_content']} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        plant_id BIGINT UNSIGNED NOT NULL,
        headline VARCHAR(255) DEFAULT '' NOT NULL,
        short_description TEXT NULL,
        value_message TEXT NULL,
        transparent_data TEXT NULL,
        ai_agent_guidance LONGTEXT NULL,
        public_body LONGTEXT NULL,
        notes TEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        UNIQUE KEY plant_unique (plant_id)
    ) $charset_collate;";

    $sql_plant_environment_profiles = "CREATE TABLE {$tables['plant_environment_profiles']} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        plant_id BIGINT UNSIGNED NOT NULL,
        stage_code VARCHAR(50) DEFAULT 'general' NOT NULL,
        day_from INT DEFAULT 0 NOT NULL,
        day_to INT DEFAULT 0 NOT NULL,
        temp_air_min DECIMAL(6,2) DEFAULT 0 NOT NULL,
        temp_air_target DECIMAL(6,2) DEFAULT 0 NOT NULL,
        temp_air_max DECIMAL(6,2) DEFAULT 0 NOT NULL,
        humidity_min DECIMAL(6,2) DEFAULT 0 NOT NULL,
        humidity_target DECIMAL(6,2) DEFAULT 0 NOT NULL,
        humidity_max DECIMAL(6,2) DEFAULT 0 NOT NULL,
        ec_min DECIMAL(6,2) DEFAULT 0 NOT NULL,
        ec_target DECIMAL(6,2) DEFAULT 0 NOT NULL,
        ec_max DECIMAL(6,2) DEFAULT 0 NOT NULL,
        ph_min DECIMAL(6,2) DEFAULT 0 NOT NULL,
        ph_target DECIMAL(6,2) DEFAULT 0 NOT NULL,
        ph_max DECIMAL(6,2) DEFAULT 0 NOT NULL,
        dli_min DECIMAL(8,2) DEFAULT 0 NOT NULL,
        dli_target DECIMAL(8,2) DEFAULT 0 NOT NULL,
        dli_max DECIMAL(8,2) DEFAULT 0 NOT NULL,
        airflow_note TEXT NULL,
        source_note TEXT NULL,
        priority_order INT DEFAULT 0 NOT NULL,
        is_active TINYINT(1) DEFAULT 1 NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY plant_id (plant_id),
        KEY stage_code (stage_code)
    ) $charset_collate;";

    $sql_plant_growth_stages = "CREATE TABLE {$tables['plant_growth_stages']} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        plant_id BIGINT UNSIGNED NOT NULL,
        stage_index INT DEFAULT 1 NOT NULL,
        stage_name VARCHAR(255) DEFAULT '' NOT NULL,
        stage_code VARCHAR(80) DEFAULT '' NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY plant_id (plant_id),
        KEY stage_index (stage_index)
    ) $charset_collate;";

    $sql_plant_nutrition_profiles = "CREATE TABLE {$tables['plant_nutrition_profiles']} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        plant_id BIGINT UNSIGNED NOT NULL,
        stage_code VARCHAR(50) DEFAULT 'general' NOT NULL,
        day_from INT DEFAULT 0 NOT NULL,
        day_to INT DEFAULT 0 NOT NULL,
        ec_target DECIMAL(6,2) DEFAULT 0 NOT NULL,
        ph_target DECIMAL(6,2) DEFAULT 0 NOT NULL,
        water_ml_per_tray_per_day DECIMAL(10,2) DEFAULT 0 NOT NULL,
        stock_a_ml DECIMAL(10,2) DEFAULT 0 NOT NULL,
        stock_b_ml DECIMAL(10,2) DEFAULT 0 NOT NULL,
        mixing_note TEXT NULL,
        warning_note TEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY plant_id (plant_id),
        KEY stage_code (stage_code)
    ) $charset_collate;";

    $sql_plant_checklists = "CREATE TABLE {$tables['plant_checklists']} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        plant_id BIGINT UNSIGNED NOT NULL,
        stage_code VARCHAR(50) DEFAULT 'general' NOT NULL,
        checklist_type VARCHAR(40) DEFAULT 'daily' NOT NULL,
        item_order INT DEFAULT 0 NOT NULL,
        item_text TEXT NULL,
        expected_result TEXT NULL,
        severity_if_missed VARCHAR(40) DEFAULT '' NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY plant_id (plant_id),
        KEY checklist_type (checklist_type)
    ) $charset_collate;";

    $sql_plant_health_issues = "CREATE TABLE {$tables['plant_health_issues']} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        plant_id BIGINT UNSIGNED NOT NULL,
        issue_code VARCHAR(80) DEFAULT '' NOT NULL,
        issue_group VARCHAR(60) DEFAULT 'environment' NOT NULL,
        symptom_title VARCHAR(255) DEFAULT '' NOT NULL,
        symptom_detail TEXT NULL,
        likely_causes TEXT NULL,
        inspection_steps TEXT NULL,
        recommended_actions TEXT NULL,
        severity_level VARCHAR(40) DEFAULT '' NOT NULL,
        when_to_escalate TEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY plant_id (plant_id),
        KEY issue_group (issue_group)
    ) $charset_collate;";

    $sql_plant_alert_rules = "CREATE TABLE {$tables['plant_alert_rules']} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        plant_id BIGINT UNSIGNED NOT NULL,
        rule_code VARCHAR(80) DEFAULT '' NOT NULL,
        metric_code VARCHAR(80) DEFAULT '' NOT NULL,
        severity_level VARCHAR(40) DEFAULT '' NOT NULL,
        alert_title VARCHAR(255) DEFAULT '' NOT NULL,
        alert_message LONGTEXT NULL,
        recommended_action LONGTEXT NULL,
        sort_order INT DEFAULT 0 NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY plant_id (plant_id)
    ) $charset_collate;";

    $sql_plant_workflows = "CREATE TABLE {$tables['plant_workflows']} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        plant_id BIGINT UNSIGNED NOT NULL,
        workflow_code VARCHAR(80) DEFAULT '' NOT NULL,
        workflow_name VARCHAR(190) DEFAULT '' NOT NULL,
        trigger_type VARCHAR(40) DEFAULT 'manual' NOT NULL,
        workflow_payload_json LONGTEXT NULL,
        status VARCHAR(30) DEFAULT 'draft' NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY plant_id (plant_id)
    ) $charset_collate;";

    $sql_plant_protocol_topics = "CREATE TABLE {$tables['plant_protocol_topics']} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        plant_id BIGINT UNSIGNED NOT NULL,
        topic_code VARCHAR(80) DEFAULT '' NOT NULL,
        topic_direction VARCHAR(30) DEFAULT 'publish' NOT NULL,
        topic_name VARCHAR(255) DEFAULT '' NOT NULL,
        payload_schema_json LONGTEXT NULL,
        sample_payload_json LONGTEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY plant_id (plant_id)
    ) $charset_collate;";

    $sql_plant_robot_tasks = "CREATE TABLE {$tables['plant_robot_tasks']} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        plant_id BIGINT UNSIGNED NOT NULL,
        task_code VARCHAR(80) DEFAULT '' NOT NULL,
        task_name VARCHAR(190) DEFAULT '' NOT NULL,
        task_group VARCHAR(60) DEFAULT 'general' NOT NULL,
        task_instruction LONGTEXT NULL,
        success_criteria TEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY plant_id (plant_id)
    ) $charset_collate;";

    $sql_plant_soil_logs = "CREATE TABLE {$tables['plant_soil_logs']} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        garden_key VARCHAR(64) NOT NULL,
        pot_code VARCHAR(64) NOT NULL,
        supply_code VARCHAR(64) NOT NULL,
        supply_name VARCHAR(255) DEFAULT '' NOT NULL,
        amount_label VARCHAR(64) DEFAULT '' NOT NULL,
        notes TEXT NULL,
        created_by_user_id BIGINT UNSIGNED NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY garden_pot (garden_key, pot_code),
        KEY supply_code (supply_code)
    ) $charset_collate;";

    dbDelta($sql_plants);
    dbDelta($sql_supplies);
    dbDelta($sql_plant_supplies);
    dbDelta($sql_plant_sop_steps);
    dbDelta($sql_plant_public_content);
    dbDelta($sql_plant_environment_profiles);
    dbDelta($sql_plant_growth_stages);
    dbDelta($sql_plant_nutrition_profiles);
    dbDelta($sql_plant_checklists);
    dbDelta($sql_plant_health_issues);
    dbDelta($sql_plant_alert_rules);
    dbDelta($sql_plant_workflows);
    dbDelta($sql_plant_protocol_topics);
    dbDelta($sql_plant_robot_tasks);
    dbDelta($sql_plant_soil_logs);

    update_option('aitrongcay_onboarding_db_version', '2026-06-05-soil-logs', false);
}
add_action('after_switch_theme', 'aitrongcay_install_onboarding_tables');

function aitrongcay_maybe_install_onboarding_tables(): void
{
    if (get_option('aitrongcay_onboarding_db_version') === '2026-06-05-soil-logs') {
        return;
    }

    aitrongcay_install_onboarding_tables();
}
add_action('init', 'aitrongcay_maybe_install_onboarding_tables', 5);

function aitrongcay_column_exists(string $table, string $column): bool
{
    global $wpdb;

    $table = trim($table);
    $column = trim($column);
    if ($table === '' || $column === '') {
        return false;
    }

    $sql = $wpdb->prepare(
        'SHOW COLUMNS FROM `' . str_replace('`', '``', $table) . '` LIKE %s',
        $column
    );

    return (bool) $wpdb->get_var($sql);
}

function aitrongcay_run_onboarding_v2_schema_cleanup(): void
{
    if (get_option('aitrongcay_onboarding_v2_schema_cleanup_done_v2') === 'yes') {
        return;
    }

    global $wpdb;
    $tables = aitrongcay_onboarding_tables();

    $drop_map = [
        'plants' => [
            'cultivation_method',
            'seed_type',
            'seed_name',
            'seed_code',
            'supplier_name',
            'unit',
            'input_price',
            'germination_rate',
            'seed_batch',
            'expiry_note',
            'quality_notes',
            'sop_json',
            'ai_rules_json',
            'costing_json',
            'public_content_json',
        ],
        'plant_costing' => ['__drop_table__'],
        'plant_qa_pairs' => ['__drop_table__'],
        'plant_ai_schemas' => ['__drop_table__'],
        'plant_decision_nodes' => ['__drop_table__'],
        'plant_environment_profiles' => ['stage_name'],
        'plant_nutrition_profiles' => ['recipe_name'],
    ];

    foreach ($drop_map as $table_key => $columns) {
        if (empty($tables[$table_key])) {
            continue;
        }

        $table = $tables[$table_key];
        foreach ($columns as $column) {
            if ($column === '__drop_table__') {
                $wpdb->query('DROP TABLE IF EXISTS `' . str_replace('`', '``', $table) . '`');
                continue;
            }
            if (! aitrongcay_column_exists($table, $column)) {
                continue;
            }
            $wpdb->query('ALTER TABLE `' . str_replace('`', '``', $table) . '` DROP COLUMN `' . str_replace('`', '``', $column) . '`');
        }
    }

    update_option('aitrongcay_onboarding_v2_schema_cleanup_done_v2', 'yes', false);
}
add_action('init', 'aitrongcay_run_onboarding_v2_schema_cleanup', 6);

function aitrongcay_parse_money_input(string $value): float
{
    $normalized = preg_replace('/[^0-9,\.]/', '', $value);
    if (! is_string($normalized) || $normalized === '') {
        return 0.0;
    }

    $normalized = str_replace('.', '', $normalized);
    $normalized = str_replace(',', '.', $normalized);
    return (float) $normalized;
}

function aitrongcay_handle_media_upload_field(string $field_name): array
{
    if (empty($_FILES[$field_name]) || ! is_array($_FILES[$field_name])) {
        return ['attachment_id' => 0, 'url' => ''];
    }

    $error = (int) ($_FILES[$field_name]['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error !== UPLOAD_ERR_OK) {
        return ['attachment_id' => 0, 'url' => ''];
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $attachment_id = media_handle_upload($field_name, 0);
    if (is_wp_error($attachment_id) || ! $attachment_id) {
        return ['attachment_id' => 0, 'url' => ''];
    }

    return [
        'attachment_id' => (int) $attachment_id,
        'url' => (string) wp_get_attachment_url($attachment_id),
    ];
}

function aitrongcay_ajax_upload_media_image(): void
{
    check_ajax_referer('aitrongcay_upload_media_image', 'nonce');

    if (! is_user_logged_in() || ! function_exists('aitrongcay_can_manage_onboarding_catalog') || ! aitrongcay_can_manage_onboarding_catalog(wp_get_current_user())) {
        wp_send_json_error(['message' => 'Bạn không có quyền upload ảnh ở khu vực này.'], 403);
    }

    $field_name = sanitize_key((string) ($_POST['field_name'] ?? 'image_file'));
    $uploaded = aitrongcay_handle_media_upload_field($field_name);

    if ((int) ($uploaded['attachment_id'] ?? 0) <= 0 || (string) ($uploaded['url'] ?? '') === '') {
        wp_send_json_error(['message' => 'Không thể upload ảnh'], 400);
    }

    wp_send_json_success([
        'attachment_id' => (int) $uploaded['attachment_id'],
        'url' => (string) $uploaded['url'],
    ]);
}
add_action('wp_ajax_aitrongcay_upload_media_image', 'aitrongcay_ajax_upload_media_image');
add_action('wp_ajax_nopriv_aitrongcay_upload_media_image', 'aitrongcay_ajax_upload_media_image');

function aitrongcay_supplies_latest(int $limit = 12, string $search = ''): array
{
    global $wpdb;
    $tables = aitrongcay_onboarding_tables();
    $limit = max(1, min(100, $limit));
    $search = trim($search);

    if ($search !== '') {
        $like = '%' . $wpdb->esc_like($search) . '%';
        $sql = $wpdb->prepare(
            "SELECT * FROM {$tables['supplies']} WHERE name LIKE %s OR code LIKE %s OR type LIKE %s OR spec LIKE %s ORDER BY updated_at DESC, id DESC LIMIT %d",
            $like,
            $like,
            $like,
            $like,
            $limit
        );
    } else {
        $sql = $wpdb->prepare("SELECT * FROM {$tables['supplies']} ORDER BY updated_at DESC, id DESC LIMIT %d", $limit);
    }

    $rows = $wpdb->get_results($sql, ARRAY_A);
    return is_array($rows) ? $rows : [];
}

function aitrongcay_supplies_for_linking(int $limit = 100): array
{
    global $wpdb;
    $tables = aitrongcay_onboarding_tables();
    $limit = max(1, min(300, $limit));
    $sql = $wpdb->prepare(
        "SELECT id, name, type, spec FROM {$tables['supplies']} ORDER BY updated_at DESC, id DESC LIMIT %d",
        $limit
    );
    $rows = $wpdb->get_results($sql, ARRAY_A);
    return is_array($rows) ? $rows : [];
}

function aitrongcay_search_supplies_for_linking(string $search, int $limit = 20): array
{
    global $wpdb;
    $tables = aitrongcay_onboarding_tables();
    $search = trim($search);
    $limit = max(1, min(50, $limit));

    if ($search === '') {
        return [];
    }

    $like = '%' . $wpdb->esc_like($search) . '%';
    $sql = $wpdb->prepare(
        "SELECT id, name, type, spec FROM {$tables['supplies']} WHERE status IN ('active','draft') AND (name LIKE %s OR type LIKE %s OR spec LIKE %s OR code LIKE %s) ORDER BY updated_at DESC, id DESC LIMIT %d",
        $like,
        $like,
        $like,
        $like,
        $limit
    );
    $rows = $wpdb->get_results($sql, ARRAY_A);
    return is_array($rows) ? $rows : [];
}

function aitrongcay_ajax_search_supplies_for_linking(): void
{
    check_ajax_referer('aitrongcay_unlink_plant_supply', 'nonce');

    if (! is_user_logged_in() || ! function_exists('aitrongcay_can_manage_onboarding_catalog') || ! aitrongcay_can_manage_onboarding_catalog(wp_get_current_user())) {
        wp_send_json_error(['message' => 'Bạn không có quyền tìm vật tư ở khu vực này.'], 403);
    }

    $query = sanitize_text_field((string) ($_POST['query'] ?? ''));
    $rows = aitrongcay_search_supplies_for_linking($query, 20);
    wp_send_json_success(['items' => $rows]);
}
add_action('wp_ajax_aitrongcay_search_supplies_for_linking', 'aitrongcay_ajax_search_supplies_for_linking');
add_action('wp_ajax_nopriv_aitrongcay_search_supplies_for_linking', 'aitrongcay_ajax_search_supplies_for_linking');

function aitrongcay_supply_find(int $id): ?array
{
    global $wpdb;
    $tables = aitrongcay_onboarding_tables();
    if ($id <= 0) {
        return null;
    }

    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tables['supplies']} WHERE id = %d LIMIT 1", $id), ARRAY_A);
    return is_array($row) ? $row : null;
}

function aitrongcay_supply_type_options(): array
{
    return [
        'Hạt giống',
        'Dung dịch dinh dưỡng',
        'Thiết bị đi kèm',
        'Khoang',
        'Dụng cụ hỗ trợ',
    ];
}

function aitrongcay_supply_status_options(): array
{
    return [
        'active' => 'Đang dùng',
        'draft' => 'Nháp',
        'inactive' => 'Ngưng dùng',
    ];
}

function aitrongcay_plant_status_options(): array
{
    return [
        'draft' => 'Nháp',
        'testing' => 'Thử nghiệm',
        'active' => 'Đang vận hành',
        'public' => 'Public',
    ];
}

function aitrongcay_plants_latest(int $limit = 20, string $search = ''): array
{
    global $wpdb;
    $tables = aitrongcay_onboarding_tables();
    $limit = max(1, min(100, $limit));
    $search = trim($search);

    if ($search !== '') {
        $like = '%' . $wpdb->esc_like($search) . '%';
        $sql = $wpdb->prepare(
            "SELECT * FROM {$tables['plants']} WHERE public_name LIKE %s OR internal_name LIKE %s OR slug LIKE %s OR category LIKE %s ORDER BY updated_at DESC, id DESC LIMIT %d",
            $like,
            $like,
            $like,
            $like,
            $limit
        );
    } else {
        $sql = $wpdb->prepare("SELECT * FROM {$tables['plants']} ORDER BY updated_at DESC, id DESC LIMIT %d", $limit);
    }

    $rows = $wpdb->get_results($sql, ARRAY_A);
    return is_array($rows) ? $rows : [];
}

function aitrongcay_plant_find(int $id): ?array
{
    global $wpdb;
    $tables = aitrongcay_onboarding_tables();
    if ($id <= 0) {
        return null;
    }
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tables['plants']} WHERE id = %d LIMIT 1", $id), ARRAY_A);
    return is_array($row) ? $row : null;
}

function aitrongcay_plant_supply_ids(int $plant_id): array
{
    global $wpdb;
    $tables = aitrongcay_onboarding_tables();
    if ($plant_id <= 0) {
        return [];
    }
    $sql = $wpdb->prepare("SELECT supply_id FROM {$tables['plant_supplies']} WHERE plant_id = %d ORDER BY sort_order ASC, id ASC", $plant_id);
    $ids = $wpdb->get_col($sql);
    return array_map('intval', is_array($ids) ? $ids : []);
}

function aitrongcay_plant_supplies(int $plant_id): array
{
    global $wpdb;
    $tables = aitrongcay_onboarding_tables();
    if ($plant_id <= 0) {
        return [];
    }
    $sql = $wpdb->prepare(
        "SELECT ps.*, s.name, s.type, s.spec, s.unit, s.cost_price, s.sale_price, s.image_url, s.status AS supply_status
         FROM {$tables['plant_supplies']} ps
         INNER JOIN {$tables['supplies']} s ON s.id = ps.supply_id
         WHERE ps.plant_id = %d
         ORDER BY ps.sort_order ASC, ps.id ASC",
        $plant_id
    );
    $rows = $wpdb->get_results($sql, ARRAY_A);
    return is_array($rows) ? $rows : [];
}

function aitrongcay_plant_supply_usage_roles(): array
{
    return [
        'required' => 'Bắt buộc',
        'optional' => 'Tùy chọn',
        'alternative' => 'Thay thế',
    ];
}

function aitrongcay_plant_sop_steps(int $plant_id): array
{
    global $wpdb;
    $tables = aitrongcay_onboarding_tables();
    if ($plant_id <= 0) {
        return [];
    }
    $sql = $wpdb->prepare("SELECT * FROM {$tables['plant_sop_steps']} WHERE plant_id = %d ORDER BY sort_order ASC, id ASC", $plant_id);
    $rows = $wpdb->get_results($sql, ARRAY_A);
    return is_array($rows) ? $rows : [];
}

function aitrongcay_plant_public_content(int $plant_id): ?array
{
    global $wpdb;
    $tables = aitrongcay_onboarding_tables();
    if ($plant_id <= 0) {
        return null;
    }
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tables['plant_public_content']} WHERE plant_id = %d LIMIT 1", $plant_id), ARRAY_A);
    return is_array($row) ? $row : null;
}

function aitrongcay_plant_environment_profile(int $plant_id): ?array
{
    global $wpdb;
    $tables = aitrongcay_onboarding_tables();
    if ($plant_id <= 0) {
        return null;
    }
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tables['plant_environment_profiles']} WHERE plant_id = %d ORDER BY priority_order ASC, id ASC LIMIT 1", $plant_id), ARRAY_A);
    return is_array($row) ? $row : null;
}

function aitrongcay_plant_onboarding_clean_multiline_text(string $text, array $rules = []): string
{
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    $text = trim($text);
    if ($text === '') {
        return '';
    }

    $drop_exact = array_values(array_filter(array_map('trim', $rules['drop_exact'] ?? []), static function ($value) {
        return $value !== '';
    }));
    $strip_prefixes = array_values(array_filter(array_map('trim', $rules['strip_prefixes'] ?? []), static function ($value) {
        return $value !== '';
    }));

    $lines = preg_split('/\n/', $text);
    $cleaned = [];
    foreach ($lines as $line) {
        $line = trim((string) $line);
        if ($line === '') {
            continue;
        }
        if (in_array($line, $drop_exact, true)) {
            continue;
        }
        foreach ($strip_prefixes as $prefix) {
            if (stripos($line, $prefix) === 0) {
                $line = trim(substr($line, strlen($prefix)));
            }
        }
        if ($line === '' || in_array($line, $drop_exact, true)) {
            continue;
        }
        $cleaned[] = $line;
    }

    return trim(implode("\n", $cleaned));
}

function aitrongcay_plant_nutrition_profile(int $plant_id): ?array
{
    global $wpdb;
    $tables = aitrongcay_onboarding_tables();
    if ($plant_id <= 0) {
        return null;
    }
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tables['plant_nutrition_profiles']} WHERE plant_id = %d ORDER BY id ASC LIMIT 1", $plant_id), ARRAY_A);
    return is_array($row) ? $row : null;
}

function aitrongcay_plant_growth_stages(int $plant_id): array
{
    global $wpdb;
    $tables = aitrongcay_onboarding_tables();
    if ($plant_id <= 0) {
        return [];
    }
    $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tables['plant_growth_stages']} WHERE plant_id = %d ORDER BY stage_index ASC, id ASC", $plant_id), ARRAY_A);
    return is_array($rows) ? $rows : [];
}

function aitrongcay_plant_checklists(int $plant_id): array
{
    global $wpdb;
    $tables = aitrongcay_onboarding_tables();
    if ($plant_id <= 0) {
        return [];
    }
    $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tables['plant_checklists']} WHERE plant_id = %d ORDER BY checklist_type ASC, item_order ASC, id ASC", $plant_id), ARRAY_A);
    return is_array($rows) ? $rows : [];
}

function aitrongcay_plant_health_issues(int $plant_id): array
{
    global $wpdb;
    $tables = aitrongcay_onboarding_tables();
    if ($plant_id <= 0) {
        return [];
    }
    $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tables['plant_health_issues']} WHERE plant_id = %d ORDER BY id ASC", $plant_id), ARRAY_A);
    return is_array($rows) ? $rows : [];
}

function aitrongcay_plant_longtext_pack(int $plant_id, string $table_key): string
{
    global $wpdb;
    $tables = aitrongcay_onboarding_tables();
    if ($plant_id <= 0 || empty($tables[$table_key])) {
        return '';
    }
    $table = $tables[$table_key];
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE plant_id = %d ORDER BY id ASC LIMIT 1", $plant_id), ARRAY_A);
    if (! is_array($row)) {
        return '';
    }
    foreach (['schema_content_longtext', 'result_action_text', 'alert_message', 'workflow_payload_json', 'payload_schema_json', 'task_instruction', 'question_text'] as $field) {
        if (isset($row[$field]) && trim((string) $row[$field]) !== '') {
            $extra = [];
            if ($field === 'question_text' && isset($row['answer_text']) && trim((string) $row['answer_text']) !== '') {
                $extra[] = trim((string) $row['question_text']);
                $extra[] = trim((string) $row['answer_text']);
                return implode("\n", $extra);
            }
            return trim((string) $row[$field]);
        }
    }
    return '';
}

function aitrongcay_normalize_assoc_for_compare(array $row, array $ignore_keys = ['id', 'created_at', 'updated_at']): array
{
    foreach ($ignore_keys as $ignore_key) {
        unset($row[$ignore_key]);
    }
    ksort($row);
    foreach ($row as $key => $value) {
        if (is_string($value)) {
            $row[$key] = trim($value);
        } elseif (is_bool($value)) {
            $row[$key] = $value ? '1' : '0';
        } elseif ($value === null) {
            $row[$key] = '';
        } else {
            $row[$key] = (string) $value;
        }
    }
    return $row;
}

function aitrongcay_rows_equal(array $left, array $right, array $ignore_keys = ['id', 'created_at', 'updated_at']): bool
{
    return aitrongcay_normalize_assoc_for_compare($left, $ignore_keys) === aitrongcay_normalize_assoc_for_compare($right, $ignore_keys);
}

function aitrongcay_replace_single_longtext_pack(int $plant_id, string $table_key, array $data): void
{
    global $wpdb;
    $tables = aitrongcay_onboarding_tables();
    if ($plant_id <= 0 || empty($tables[$table_key])) {
        return;
    }
    $table = $tables[$table_key];
    $has_content = false;
    foreach ($data as $key => $value) {
        if ($key === 'plant_id') {
            continue;
        }
        if (trim((string) $value) !== '') {
            $has_content = true;
            break;
        }
    }
    $existing = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE plant_id = %d ORDER BY id ASC LIMIT 1", $plant_id), ARRAY_A);
    if (! $has_content) {
        if (is_array($existing) && isset($existing['id'])) {
            $wpdb->delete($table, ['id' => (int) $existing['id']], ['%d']);
        }
        return;
    }
    if (is_array($existing) && isset($existing['id'])) {
        if (! aitrongcay_rows_equal($existing, $data)) {
            $wpdb->update($table, $data, ['id' => (int) $existing['id']]);
        }
        return;
    }
    $wpdb->insert($table, $data);
}

function aitrongcay_supply_redirect_url(string $saved = '', array $extra = []): string
{
    $args = $extra;
    if ($saved !== '') {
        $args['saved'] = $saved;
    }
    return add_query_arg($args, home_url('/portal/vat-tu-thiet-bi-moi/'));
}

function aitrongcay_require_onboarding_catalog_manager(string $fallback_url = ''): void
{
    if (! is_user_logged_in()) {
        wp_safe_redirect(wp_login_url($fallback_url !== '' ? $fallback_url : home_url('/portal/kho-nong-cu-2/')));
        exit;
    }

    if (function_exists('aitrongcay_can_manage_onboarding_catalog') && aitrongcay_can_manage_onboarding_catalog(wp_get_current_user())) {
        return;
    }

    $redirect_url = $fallback_url !== '' ? $fallback_url : home_url('/portal/kho-nong-cu-2/');
    wp_safe_redirect(add_query_arg('catalog_access', 'denied', $redirect_url));
    exit;
}

function aitrongcay_handle_supply_create(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        wp_safe_redirect(home_url('/portal/vat-tu-thiet-bi-moi/'));
        exit;
    }

    aitrongcay_require_onboarding_catalog_manager(home_url('/portal/vat-tu-thiet-bi-moi/'));

    check_admin_referer('aitrongcay_supply_create', 'aitrongcay_supply_nonce');

    global $wpdb;
    $tables = aitrongcay_onboarding_tables();

    $supply_id = absint($_POST['supply_id'] ?? 0);
    $name = sanitize_text_field((string) ($_POST['supply_name'] ?? ''));
    $type = sanitize_text_field((string) ($_POST['supply_type'] ?? ''));
    $code = sanitize_text_field((string) ($_POST['supply_code'] ?? ''));
    $spec = sanitize_text_field((string) ($_POST['supply_spec'] ?? ''));
    $unit = sanitize_text_field((string) ($_POST['supply_unit'] ?? ''));
    $cost_price = aitrongcay_parse_money_input((string) ($_POST['supply_cost_price'] ?? ''));
    $sale_price = aitrongcay_parse_money_input((string) ($_POST['supply_sale_price'] ?? ''));
    $supplier_name = sanitize_text_field((string) ($_POST['supply_supplier_name'] ?? ''));
    $status = sanitize_key((string) ($_POST['supply_status'] ?? 'active'));
    $description = sanitize_textarea_field((string) ($_POST['supply_description'] ?? ''));
    $optional_metrics = sanitize_textarea_field((string) ($_POST['supply_optional_metrics'] ?? ''));
    $image_url = esc_url_raw((string) ($_POST['supply_image_url'] ?? ''));
    $existing_image_id = absint($_POST['supply_existing_image_id'] ?? 0);
    $uploaded_image = aitrongcay_handle_media_upload_field('supply_image_file');

    if ($name === '') {
        wp_safe_redirect(aitrongcay_supply_redirect_url('missing-name'));
        exit;
    }

    if (! array_key_exists($status, aitrongcay_supply_status_options())) {
        $status = 'active';
    }

    if ((int) $uploaded_image['attachment_id'] > 0) {
        $existing_image_id = (int) $uploaded_image['attachment_id'];
        $image_url = (string) $uploaded_image['url'];
    }

    $data = [
        'code' => $code,
        'name' => $name,
        'type' => $type,
        'spec' => $spec,
        'unit' => $unit,
        'cost_price' => $cost_price,
        'sale_price' => $sale_price,
        'image_url' => $image_url,
        'image_id' => $existing_image_id > 0 ? $existing_image_id : null,
        'description' => $description,
        'optional_metrics_json' => wp_json_encode([
            'raw_text' => $optional_metrics,
        ], JSON_UNESCAPED_UNICODE),
        'supplier_name' => $supplier_name,
        'status' => $status,
    ];

    $format = ['%s','%s','%s','%s','%s','%f','%f','%s','%d','%s','%s','%s','%s'];

    if ($supply_id > 0) {
        $wpdb->update($tables['supplies'], $data, ['id' => $supply_id], $format, ['%d']);
        wp_safe_redirect(aitrongcay_supply_redirect_url('updated', ['edit' => $supply_id]));
        exit;
    }

    $wpdb->insert($tables['supplies'], $data, $format);
    $supply_id = (int) $wpdb->insert_id;

    wp_safe_redirect(aitrongcay_supply_redirect_url('1', ['edit' => $supply_id]));
    exit;
}
add_action('admin_post_aitrongcay_supply_create', 'aitrongcay_handle_supply_create');
add_action('admin_post_nopriv_aitrongcay_supply_create', 'aitrongcay_handle_supply_create');

function aitrongcay_handle_supply_delete(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        wp_safe_redirect(home_url('/portal/vat-tu-thiet-bi-moi/'));
        exit;
    }

    aitrongcay_require_onboarding_catalog_manager(home_url('/portal/vat-tu-thiet-bi-moi/'));

    check_admin_referer('aitrongcay_supply_delete', 'aitrongcay_supply_delete_nonce');
    global $wpdb;
    $tables = aitrongcay_onboarding_tables();
    $supply_id = absint($_POST['supply_id'] ?? 0);
    if ($supply_id > 0) {
        $wpdb->delete($tables['supplies'], ['id' => $supply_id], ['%d']);
    }
    wp_safe_redirect(aitrongcay_supply_redirect_url('deleted'));
    exit;
}
add_action('admin_post_aitrongcay_supply_delete', 'aitrongcay_handle_supply_delete');
add_action('admin_post_nopriv_aitrongcay_supply_delete', 'aitrongcay_handle_supply_delete');

function aitrongcay_handle_supply_duplicate(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        wp_safe_redirect(home_url('/portal/vat-tu-thiet-bi-moi/'));
        exit;
    }

    aitrongcay_require_onboarding_catalog_manager(home_url('/portal/vat-tu-thiet-bi-moi/'));

    check_admin_referer('aitrongcay_supply_duplicate', 'aitrongcay_supply_duplicate_nonce');
    global $wpdb;
    $tables = aitrongcay_onboarding_tables();
    $supply_id = absint($_POST['supply_id'] ?? 0);
    $supply = aitrongcay_supply_find($supply_id);
    if (! is_array($supply)) {
        wp_safe_redirect(aitrongcay_supply_redirect_url('duplicate-missing'));
        exit;
    }

    unset($supply['id'], $supply['created_at'], $supply['updated_at']);
    $supply['name'] = (string) $supply['name'] . ' (copy)';
    if ((string) ($supply['code'] ?? '') !== '') {
        $supply['code'] = (string) $supply['code'] . '-COPY';
    }

    $wpdb->insert($tables['supplies'], $supply);
    wp_safe_redirect(aitrongcay_supply_redirect_url('duplicated'));
    exit;
}
add_action('admin_post_aitrongcay_supply_duplicate', 'aitrongcay_handle_supply_duplicate');
add_action('admin_post_nopriv_aitrongcay_supply_duplicate', 'aitrongcay_handle_supply_duplicate');

function aitrongcay_plant_redirect_url(string $saved = '', array $extra = []): string
{
    $args = $extra;
    if ($saved !== '') {
        $args['saved'] = $saved;
    }
    return add_query_arg($args, home_url('/portal/onboarding-cay-moi/'));
}

function aitrongcay_handle_plant_save(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        wp_safe_redirect(home_url('/portal/onboarding-cay-moi/'));
        exit;
    }

    aitrongcay_require_onboarding_catalog_manager(home_url('/portal/onboarding-cay-moi/'));

    check_admin_referer('aitrongcay_plant_save', 'aitrongcay_plant_nonce');

    global $wpdb;
    $tables = aitrongcay_onboarding_tables();

    $plant_id = absint($_POST['plant_id'] ?? 0);
    $public_name = sanitize_text_field((string) ($_POST['public_name'] ?? ''));
    $internal_name = sanitize_text_field((string) ($_POST['internal_name'] ?? ''));
    $slug = sanitize_title((string) ($_POST['slug'] ?? ''));
    $plant_code = sanitize_text_field((string) ($_POST['plant_code'] ?? ''));
    $scientific_name = sanitize_text_field((string) ($_POST['scientific_name'] ?? ''));
    $category_values = isset($_POST['categories']) && is_array($_POST['categories'])
        ? array_values(array_unique(array_filter(array_map(static function ($value) {
            return sanitize_text_field((string) $value);
        }, $_POST['categories']))))
        : [];
    $category = implode(', ', $category_values);
    $short_description = sanitize_textarea_field((string) ($_POST['short_description'] ?? ''));
    $nutrition_components = sanitize_textarea_field((string) ($_POST['nutrition_components'] ?? ''));
    $special_nutrition_components = sanitize_textarea_field((string) ($_POST['special_nutrition_components'] ?? ''));
    $status = sanitize_key((string) ($_POST['plant_status'] ?? 'draft'));
    $variety_name = sanitize_text_field((string) ($_POST['variety_name'] ?? ''));
    $default_cycle_days = absint($_POST['default_cycle_days'] ?? 0);
    $germination_days = absint($_POST['germination_days'] ?? 0);
    $harvest_start_day = absint($_POST['harvest_start_day'] ?? 0);
    $mature_height_cm = absint($_POST['mature_height_cm'] ?? ($_POST['harvest_end_day'] ?? 0));
    $difficulty_level = sanitize_text_field((string) ($_POST['difficulty_level'] ?? ''));
    $cover_image_url = trim((string) ($_POST['cover_image_url'] ?? ''));
    if ($cover_image_url === '0' || strtolower($cover_image_url) === 'false' || strtolower($cover_image_url) === 'null') {
        $cover_image_url = '';
    }
    $cover_image_url = $cover_image_url !== '' ? esc_url_raw($cover_image_url) : '';
    $existing_cover_image_id = absint($_POST['plant_existing_image_id'] ?? 0);
    $uploaded_cover = aitrongcay_handle_media_upload_field('plant_image_file');
    $selected_supply_ids = isset($_POST['selected_supply_ids']) && is_array($_POST['selected_supply_ids'])
        ? array_values(array_unique(array_filter(array_map('absint', $_POST['selected_supply_ids']))))
        : [];
    $supply_roles = isset($_POST['supply_role']) && is_array($_POST['supply_role']) ? $_POST['supply_role'] : [];
    $supply_quantities = isset($_POST['supply_quantity']) && is_array($_POST['supply_quantity']) ? $_POST['supply_quantity'] : [];

    if ($public_name === '') {
        wp_safe_redirect(aitrongcay_plant_redirect_url('missing-name'));
        exit;
    }
    if ($slug === '') {
        $slug = sanitize_title($public_name);
    }
    if (! array_key_exists($status, aitrongcay_plant_status_options())) {
        $status = 'draft';
    }

    if ((int) $uploaded_cover['attachment_id'] > 0) {
        $existing_cover_image_id = (int) $uploaded_cover['attachment_id'];
        $cover_image_url = (string) $uploaded_cover['url'];
    }

    if ($existing_cover_image_id > 0 && $cover_image_url === '') {
        $cover_image_url = (string) wp_get_attachment_url($existing_cover_image_id);
    }
    if ($cover_image_url !== '' && $existing_cover_image_id <= 0 && function_exists('attachment_url_to_postid')) {
        $existing_cover_image_id = (int) attachment_url_to_postid($cover_image_url);
    }
    if ($cover_image_url !== '') {
        $cover_image_url = wp_make_link_relative($cover_image_url);
    }

    $data = [
        'slug' => $slug,
        'plant_code' => $plant_code,
        'public_name' => $public_name,
        'internal_name' => $internal_name,
        'scientific_name' => $scientific_name,
        'category' => $category,
        'variety_name' => $variety_name,
        'default_cycle_days' => $default_cycle_days,
        'germination_days' => $germination_days,
        'harvest_start_day' => $harvest_start_day,
        'mature_height_cm' => $mature_height_cm,
        'difficulty_level' => $difficulty_level,
        'short_description' => $short_description,
        'nutrition_components' => $nutrition_components,
        'special_nutrition_components' => $special_nutrition_components,
        'status' => $status,
        'cover_image_url' => $cover_image_url,
        'cover_image_id' => $existing_cover_image_id > 0 ? $existing_cover_image_id : null,
    ];

    $format = ['%s','%s','%s','%s','%s','%s','%s','%d','%d','%d','%d','%s','%s','%s','%s','%s','%s','%d'];

    if ($plant_id > 0) {
        $existing_plant = aitrongcay_plant_find($plant_id);
        if (! is_array($existing_plant) || ! aitrongcay_rows_equal($existing_plant, $data)) {
            $wpdb->update($tables['plants'], $data, ['id' => $plant_id], $format, ['%d']);
        }
    } else {
        $wpdb->insert($tables['plants'], $data, $format);
        $plant_id = (int) $wpdb->insert_id;
    }

    if ($plant_id > 0) {
        $new_supply_rows = [];
        $sort_order = 0;
        $role_options = aitrongcay_plant_supply_usage_roles();
        foreach ($selected_supply_ids as $supply_id) {
            $role = sanitize_key((string) ($supply_roles[$supply_id] ?? 'required'));
            if (! isset($role_options[$role])) {
                $role = 'required';
            }
            $quantity = sanitize_text_field((string) ($supply_quantities[$supply_id] ?? ''));
            $new_supply_rows[] = [
                'plant_id' => $plant_id,
                'supply_id' => $supply_id,
                'usage_role' => $role,
                'quantity_per_tray' => $quantity,
                'notes' => '',
                'sort_order' => $sort_order++,
            ];
        }
        $existing_supply_rows = array_map(static function ($row) {
            return [
                'plant_id' => (int) ($row['plant_id'] ?? 0),
                'supply_id' => (int) ($row['supply_id'] ?? 0),
                'usage_role' => (string) ($row['usage_role'] ?? ''),
                'quantity_per_tray' => (string) ($row['quantity_per_tray'] ?? ''),
                'notes' => (string) ($row['notes'] ?? ''),
                'sort_order' => (int) ($row['sort_order'] ?? 0),
            ];
        }, aitrongcay_plant_supplies($plant_id));
        if (wp_json_encode($existing_supply_rows) !== wp_json_encode($new_supply_rows)) {
            $wpdb->delete($tables['plant_supplies'], ['plant_id' => $plant_id], ['%d']);
            foreach ($new_supply_rows as $new_supply_row) {
                $wpdb->insert($tables['plant_supplies'], $new_supply_row, ['%d','%d','%s','%s','%s','%d']);
            }
        }

        $sop_single_text = aitrongcay_plant_onboarding_clean_multiline_text(
            sanitize_textarea_field((string) ($_POST['sop_single_text'] ?? '')),
            [
                'drop_exact' => ['SOP tổng quát'],
                'strip_prefixes' => ['Việc cần làm:'],
            ]
        );
        $existing_sop_steps = aitrongcay_plant_sop_steps($plant_id);
        $new_sop_steps = $sop_single_text !== '' ? [[
            'plant_id' => $plant_id,
            'step_title' => 'SOP tổng quát',
            'day_from' => 0,
            'day_to' => 0,
            'light_level' => '',
            'watering_rule' => '',
            'operator_tasks' => $sop_single_text,
            'expected_state' => '',
            'alert_conditions' => '',
            'notes' => '',
            'sort_order' => 0,
        ]] : [];
        $normalized_existing_sop = array_map(static function ($row) {
            return [
                'plant_id' => (int) ($row['plant_id'] ?? 0),
                'step_title' => (string) ($row['step_title'] ?? ''),
                'day_from' => (int) ($row['day_from'] ?? 0),
                'day_to' => (int) ($row['day_to'] ?? 0),
                'light_level' => (string) ($row['light_level'] ?? ''),
                'watering_rule' => (string) ($row['watering_rule'] ?? ''),
                'operator_tasks' => (string) ($row['operator_tasks'] ?? ''),
                'expected_state' => (string) ($row['expected_state'] ?? ''),
                'alert_conditions' => (string) ($row['alert_conditions'] ?? ''),
                'notes' => (string) ($row['notes'] ?? ''),
                'sort_order' => (int) ($row['sort_order'] ?? 0),
            ];
        }, $existing_sop_steps);
        if (wp_json_encode($normalized_existing_sop) !== wp_json_encode($new_sop_steps)) {
            $wpdb->delete($tables['plant_sop_steps'], ['plant_id' => $plant_id], ['%d']);
            if ($new_sop_steps !== []) {
                $wpdb->insert($tables['plant_sop_steps'], $new_sop_steps[0], ['%d','%s','%d','%d','%s','%s','%s','%s','%s','%s','%d']);
            }
        }

        $public_data = [
            'plant_id' => $plant_id,
            'headline' => sanitize_text_field((string) ($_POST['public_headline'] ?? '')),
            'short_description' => sanitize_textarea_field((string) ($_POST['public_short_description'] ?? '')),
            'value_message' => sanitize_textarea_field((string) ($_POST['public_value_message'] ?? '')),
            'transparent_data' => sanitize_textarea_field((string) ($_POST['public_transparent_data'] ?? '')),
            'ai_agent_guidance' => sanitize_textarea_field((string) ($_POST['ai_agent_guidance'] ?? '')),
            'public_body' => sanitize_textarea_field((string) ($_POST['public_body'] ?? '')),
            'notes' => sanitize_textarea_field((string) ($_POST['public_notes'] ?? '')),
        ];
        $existing_public = aitrongcay_plant_public_content($plant_id);
        if (is_array($existing_public) && isset($existing_public['id'])) {
            if (! aitrongcay_rows_equal($existing_public, $public_data)) {
                $wpdb->update(
                    $tables['plant_public_content'],
                    $public_data,
                    ['id' => (int) $existing_public['id']],
                    ['%d','%s','%s','%s','%s','%s','%s','%s'],
                    ['%d']
                );
            }
        } else {
            $wpdb->insert(
                $tables['plant_public_content'],
                $public_data,
                ['%d','%s','%s','%s','%s','%s','%s','%s']
            );
        }

        $environment_data = [
            'plant_id' => $plant_id,
            'stage_code' => sanitize_key((string) ($_POST['env_stage_code'] ?? 'general')) ?: 'general',
            'day_from' => absint($_POST['env_day_from'] ?? 0),
            'day_to' => absint($_POST['env_day_to'] ?? 0),
            'temp_air_min' => (float) ($_POST['env_temp_air_min'] ?? 0),
            'temp_air_target' => (float) ($_POST['env_temp_air_target'] ?? 0),
            'temp_air_max' => (float) ($_POST['env_temp_air_max'] ?? 0),
            'humidity_min' => (float) ($_POST['env_humidity_min'] ?? 0),
            'humidity_target' => (float) ($_POST['env_humidity_target'] ?? 0),
            'humidity_max' => (float) ($_POST['env_humidity_max'] ?? 0),
            'ec_min' => (float) ($_POST['env_ec_min'] ?? 0),
            'ec_target' => (float) ($_POST['env_ec_target'] ?? 0),
            'ec_max' => (float) ($_POST['env_ec_max'] ?? 0),
            'ph_min' => (float) ($_POST['env_ph_min'] ?? 0),
            'ph_target' => (float) ($_POST['env_ph_target'] ?? 0),
            'ph_max' => (float) ($_POST['env_ph_max'] ?? 0),
            'dli_min' => (float) ($_POST['env_dli_min'] ?? 0),
            'dli_target' => (float) ($_POST['env_dli_target'] ?? 0),
            'dli_max' => (float) ($_POST['env_dli_max'] ?? 0),
            'airflow_note' => sanitize_textarea_field((string) ($_POST['env_airflow_note'] ?? '')),
            'source_note' => aitrongcay_plant_onboarding_clean_multiline_text(
                sanitize_textarea_field((string) ($_POST['env_source_note'] ?? '')),
                [
                    'drop_exact' => ['Tổng quát'],
                ]
            ),
            'priority_order' => 0,
            'is_active' => 1,
        ];
        $existing_environment = aitrongcay_plant_environment_profile($plant_id);
        if (is_array($existing_environment) && isset($existing_environment['id'])) {
            if (! aitrongcay_rows_equal($existing_environment, $environment_data)) {
                $wpdb->update($tables['plant_environment_profiles'], $environment_data, ['id' => (int) $existing_environment['id']]);
            }
        } else {
            $wpdb->insert($tables['plant_environment_profiles'], $environment_data);
        }

        $growth_stage_names = isset($_POST['growth_stage_name']) && is_array($_POST['growth_stage_name']) ? $_POST['growth_stage_name'] : [];
        $growth_stage_payload = wp_unslash((string) ($_POST['growth_stage_payload'] ?? ''));
        if ($growth_stage_payload !== '') {
            $decoded_growth_stage_payload = json_decode($growth_stage_payload, true);
            if (is_array($decoded_growth_stage_payload)) {
                $growth_stage_names = $decoded_growth_stage_payload;
            }
        }
        $growth_stage_index = 1;
        $new_growth_stage_rows = [];
        foreach ($growth_stage_names as $stage_name_raw) {
            $stage_name = sanitize_text_field((string) $stage_name_raw);
            if ($stage_name === '') {
                continue;
            }
            $new_growth_stage_rows[] = [
                'plant_id' => $plant_id,
                'stage_index' => $growth_stage_index,
                'stage_name' => $stage_name,
                'stage_code' => sanitize_title($stage_name),
            ];
            $growth_stage_index++;
        }
        $existing_growth_stage_rows = array_map(static function ($row) {
            return [
                'plant_id' => (int) ($row['plant_id'] ?? 0),
                'stage_index' => (int) ($row['stage_index'] ?? 0),
                'stage_name' => (string) ($row['stage_name'] ?? ''),
                'stage_code' => (string) ($row['stage_code'] ?? ''),
            ];
        }, aitrongcay_plant_growth_stages($plant_id));
        if (wp_json_encode($existing_growth_stage_rows) !== wp_json_encode($new_growth_stage_rows)) {
            $wpdb->delete($tables['plant_growth_stages'], ['plant_id' => $plant_id], ['%d']);
            foreach ($new_growth_stage_rows as $new_growth_stage_row) {
                $wpdb->insert($tables['plant_growth_stages'], $new_growth_stage_row, ['%d','%d','%s','%s']);
            }
        }

        $nutrition_data = [
            'plant_id' => $plant_id,
            'stage_code' => sanitize_key((string) ($_POST['nutrition_stage_code'] ?? 'general')) ?: 'general',
            'day_from' => absint($_POST['nutrition_day_from'] ?? 0),
            'day_to' => absint($_POST['nutrition_day_to'] ?? 0),
            'ec_target' => (float) ($_POST['nutrition_ec_target'] ?? 0),
            'ph_target' => (float) ($_POST['nutrition_ph_target'] ?? 0),
            'water_ml_per_tray_per_day' => (float) ($_POST['nutrition_water_ml_per_tray_per_day'] ?? 0),
            'stock_a_ml' => (float) ($_POST['nutrition_stock_a_ml'] ?? 0),
            'stock_b_ml' => (float) ($_POST['nutrition_stock_b_ml'] ?? 0),
            'mixing_note' => aitrongcay_plant_onboarding_clean_multiline_text(
                sanitize_textarea_field((string) ($_POST['nutrition_mixing_note'] ?? '')),
                [
                    'drop_exact' => ['Công thức cơ bản'],
                ]
            ),
            'warning_note' => sanitize_textarea_field((string) ($_POST['nutrition_warning_note'] ?? '')),
        ];
        $existing_nutrition = aitrongcay_plant_nutrition_profile($plant_id);
        if (is_array($existing_nutrition) && isset($existing_nutrition['id'])) {
            if (! aitrongcay_rows_equal($existing_nutrition, $nutrition_data)) {
                $wpdb->update($tables['plant_nutrition_profiles'], $nutrition_data, ['id' => (int) $existing_nutrition['id']]);
            }
        } else {
            $wpdb->insert($tables['plant_nutrition_profiles'], $nutrition_data);
        }

        $checklist_text = sanitize_textarea_field((string) ($_POST['checklist_daily_text'] ?? ''));
        $checklist_lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $checklist_text))));
        $new_checklist_rows = [];
        foreach ($checklist_lines as $index => $line) {
            $new_checklist_rows[] = [
                'plant_id' => $plant_id,
                'stage_code' => 'general',
                'checklist_type' => 'daily',
                'item_order' => $index,
                'item_text' => $line,
                'expected_result' => '',
                'severity_if_missed' => 'medium',
            ];
        }
        $existing_checklist_rows = array_map(static function ($row) {
            return [
                'plant_id' => (int) ($row['plant_id'] ?? 0),
                'stage_code' => (string) ($row['stage_code'] ?? ''),
                'checklist_type' => (string) ($row['checklist_type'] ?? ''),
                'item_order' => (int) ($row['item_order'] ?? 0),
                'item_text' => (string) ($row['item_text'] ?? ''),
                'expected_result' => (string) ($row['expected_result'] ?? ''),
                'severity_if_missed' => (string) ($row['severity_if_missed'] ?? ''),
            ];
        }, aitrongcay_plant_checklists($plant_id));
        if (wp_json_encode($existing_checklist_rows) !== wp_json_encode($new_checklist_rows)) {
            $wpdb->delete($tables['plant_checklists'], ['plant_id' => $plant_id], ['%d']);
            foreach ($new_checklist_rows as $new_checklist_row) {
                $wpdb->insert($tables['plant_checklists'], $new_checklist_row, ['%d','%s','%s','%d','%s','%s','%s']);
            }
        }

        $health_blocks = array_values(array_filter(array_map('trim', preg_split('/\n\s*\n/', (string) ($_POST['health_issues_text'] ?? '')))));
        $new_health_issue_rows = [];
        foreach ($health_blocks as $index => $block) {
            $lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $block))));
            $title = $lines[0] ?? ('Vấn đề ' . ($index + 1));
            $detail = implode("\n", array_slice($lines, 1));
            $new_health_issue_rows[] = [
                'plant_id' => $plant_id,
                'issue_code' => sanitize_title($title),
                'issue_group' => 'environment',
                'symptom_title' => $title,
                'symptom_detail' => $detail,
                'likely_causes' => '',
                'inspection_steps' => '',
                'recommended_actions' => '',
                'severity_level' => 'medium',
                'when_to_escalate' => '',
            ];
        }
        $existing_health_issue_rows = array_map(static function ($row) {
            return [
                'plant_id' => (int) ($row['plant_id'] ?? 0),
                'issue_code' => (string) ($row['issue_code'] ?? ''),
                'issue_group' => (string) ($row['issue_group'] ?? ''),
                'symptom_title' => (string) ($row['symptom_title'] ?? ''),
                'symptom_detail' => (string) ($row['symptom_detail'] ?? ''),
                'likely_causes' => (string) ($row['likely_causes'] ?? ''),
                'inspection_steps' => (string) ($row['inspection_steps'] ?? ''),
                'recommended_actions' => (string) ($row['recommended_actions'] ?? ''),
                'severity_level' => (string) ($row['severity_level'] ?? ''),
                'when_to_escalate' => (string) ($row['when_to_escalate'] ?? ''),
            ];
        }, aitrongcay_plant_health_issues($plant_id));
        if (wp_json_encode($existing_health_issue_rows) !== wp_json_encode($new_health_issue_rows)) {
            $wpdb->delete($tables['plant_health_issues'], ['plant_id' => $plant_id], ['%d']);
            foreach ($new_health_issue_rows as $new_health_issue_row) {
                $wpdb->insert($tables['plant_health_issues'], $new_health_issue_row);
            }
        }

        aitrongcay_replace_single_longtext_pack($plant_id, 'plant_alert_rules', [
            'plant_id' => $plant_id,
            'rule_code' => 'general-alert',
            'metric_code' => 'general',
            'severity_level' => 'medium',
            'alert_title' => 'Phase 3 alert pack',
            'alert_message' => sanitize_textarea_field((string) ($_POST['phase3_alert_rules_text'] ?? '')),
            'recommended_action' => '',
            'sort_order' => 0,
        ]);

        aitrongcay_replace_single_longtext_pack($plant_id, 'plant_workflows', [
            'plant_id' => $plant_id,
            'workflow_code' => 'main-workflow',
            'workflow_name' => 'Phase 3 workflow pack',
            'trigger_type' => 'manual',
            'workflow_payload_json' => sanitize_textarea_field((string) ($_POST['phase3_workflows_text'] ?? '')),
            'status' => 'draft',
        ]);

        aitrongcay_replace_single_longtext_pack($plant_id, 'plant_protocol_topics', [
            'plant_id' => $plant_id,
            'topic_code' => 'main-topic',
            'topic_direction' => 'publish',
            'topic_name' => 'phase3/topic',
            'payload_schema_json' => sanitize_textarea_field((string) ($_POST['phase3_protocol_mqtt_text'] ?? '')),
            'sample_payload_json' => '',
        ]);

        aitrongcay_replace_single_longtext_pack($plant_id, 'plant_robot_tasks', [
            'plant_id' => $plant_id,
            'task_code' => 'main-task',
            'task_name' => 'Phase 3 robot pack',
            'task_group' => 'general',
            'task_instruction' => sanitize_textarea_field((string) ($_POST['phase3_robot_tasks_text'] ?? '')),
            'success_criteria' => '',
        ]);
    }

    wp_safe_redirect(aitrongcay_plant_redirect_url($plant_id > 0 && isset($_POST['plant_id']) && absint($_POST['plant_id']) > 0 ? 'updated' : '1', ['edit' => $plant_id]));
    exit;
}
add_action('admin_post_aitrongcay_plant_save', 'aitrongcay_handle_plant_save');
add_action('admin_post_nopriv_aitrongcay_plant_save', 'aitrongcay_handle_plant_save');

function aitrongcay_handle_plant_delete(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        wp_safe_redirect(home_url('/portal/onboarding-cay-moi/'));
        exit;
    }

    aitrongcay_require_onboarding_catalog_manager(home_url('/portal/onboarding-cay-moi/'));

    check_admin_referer('aitrongcay_plant_delete', 'aitrongcay_plant_delete_nonce');
    global $wpdb;
    $tables = aitrongcay_onboarding_tables();
    $plant_id = absint($_POST['plant_id'] ?? 0);
    if ($plant_id > 0) {
        $wpdb->delete($tables['plants'], ['id' => $plant_id], ['%d']);
        $wpdb->delete($tables['plant_supplies'], ['plant_id' => $plant_id], ['%d']);
    }
    wp_safe_redirect(aitrongcay_plant_redirect_url('deleted'));
    exit;
}
add_action('admin_post_aitrongcay_plant_delete', 'aitrongcay_handle_plant_delete');
add_action('admin_post_nopriv_aitrongcay_plant_delete', 'aitrongcay_handle_plant_delete');

function aitrongcay_handle_plant_duplicate(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        wp_safe_redirect(home_url('/portal/onboarding-cay-moi/'));
        exit;
    }

    aitrongcay_require_onboarding_catalog_manager(home_url('/portal/onboarding-cay-moi/'));

    check_admin_referer('aitrongcay_plant_duplicate', 'aitrongcay_plant_duplicate_nonce');
    global $wpdb;
    $tables = aitrongcay_onboarding_tables();
    $plant_id = absint($_POST['plant_id'] ?? 0);
    $plant = aitrongcay_plant_find($plant_id);
    if (! is_array($plant)) {
        wp_safe_redirect(aitrongcay_plant_redirect_url('duplicate-missing'));
        exit;
    }
    unset($plant['id'], $plant['created_at'], $plant['updated_at']);
    $plant['public_name'] = (string) $plant['public_name'] . ' (copy)';
    $plant['slug'] = sanitize_title((string) $plant['slug'] . '-copy-' . wp_generate_password(4, false, false));
    $wpdb->insert($tables['plants'], $plant);
    wp_safe_redirect(aitrongcay_plant_redirect_url('duplicated'));
    exit;
}
add_action('admin_post_aitrongcay_plant_duplicate', 'aitrongcay_handle_plant_duplicate');
add_action('admin_post_nopriv_aitrongcay_plant_duplicate', 'aitrongcay_handle_plant_duplicate');

function aitrongcay_ajax_unlink_plant_supply(): void
{
    check_ajax_referer('aitrongcay_unlink_plant_supply', 'nonce');

    global $wpdb;
    $tables = aitrongcay_onboarding_tables();
    $plant_id = absint($_POST['plant_id'] ?? 0);
    $supply_id = absint($_POST['supply_id'] ?? 0);

    if ($plant_id <= 0 || $supply_id <= 0) {
        wp_send_json_error(['message' => 'Thiếu plant_id hoặc supply_id'], 400);
    }

    $deleted = $wpdb->delete($tables['plant_supplies'], ['plant_id' => $plant_id, 'supply_id' => $supply_id], ['%d', '%d']);
    if ($deleted === false) {
        wp_send_json_error(['message' => 'Không thể bỏ liên kết'], 500);
    }

    wp_send_json_success(['plant_id' => $plant_id, 'supply_id' => $supply_id]);
}
add_action('wp_ajax_aitrongcay_unlink_plant_supply', 'aitrongcay_ajax_unlink_plant_supply');
add_action('wp_ajax_nopriv_aitrongcay_unlink_plant_supply', 'aitrongcay_ajax_unlink_plant_supply');

function aitrongcay_ajax_link_plant_supply(): void
{
    check_ajax_referer('aitrongcay_unlink_plant_supply', 'nonce');

    global $wpdb;
    $tables = aitrongcay_onboarding_tables();
    $plant_id = absint($_POST['plant_id'] ?? 0);
    $supply_id = absint($_POST['supply_id'] ?? 0);
    $usage_role = sanitize_key((string) ($_POST['usage_role'] ?? 'required'));
    $quantity_per_tray = sanitize_text_field((string) ($_POST['quantity_per_tray'] ?? ''));
    $roles = aitrongcay_plant_supply_usage_roles();

    if ($plant_id <= 0 || $supply_id <= 0) {
        wp_send_json_error(['message' => 'Thiếu plant_id hoặc supply_id'], 400);
    }
    if (! isset($roles[$usage_role])) {
        $usage_role = 'required';
    }

    $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$tables['plant_supplies']} WHERE plant_id = %d AND supply_id = %d LIMIT 1", $plant_id, $supply_id));

    if ($existing) {
        $updated = $wpdb->update(
            $tables['plant_supplies'],
            [
                'usage_role' => $usage_role,
                'quantity_per_tray' => $quantity_per_tray,
            ],
            ['id' => (int) $existing],
            ['%s', '%s'],
            ['%d']
        );
        if ($updated === false) {
            wp_send_json_error(['message' => 'Không thể cập nhật liên kết'], 500);
        }
    } else {
        $max_sort = (int) $wpdb->get_var($wpdb->prepare("SELECT COALESCE(MAX(sort_order), -1) FROM {$tables['plant_supplies']} WHERE plant_id = %d", $plant_id));
        $inserted = $wpdb->insert(
            $tables['plant_supplies'],
            [
                'plant_id' => $plant_id,
                'supply_id' => $supply_id,
                'usage_role' => $usage_role,
                'quantity_per_tray' => $quantity_per_tray,
                'notes' => '',
                'sort_order' => $max_sort + 1,
            ],
            ['%d', '%d', '%s', '%s', '%s', '%d']
        );
        if ($inserted === false) {
            wp_send_json_error(['message' => 'Không thể tạo liên kết'], 500);
        }
    }

    $linked_rows = aitrongcay_plant_supplies($plant_id);
    $current = null;
    foreach ($linked_rows as $row) {
        if ((int) ($row['supply_id'] ?? 0) === $supply_id) {
            $current = $row;
            break;
        }
    }

    wp_send_json_success([
        'plant_id' => $plant_id,
        'supply_id' => $supply_id,
        'linked' => $current,
        'roleLabel' => $roles[$usage_role] ?? $usage_role,
    ]);
}
add_action('wp_ajax_aitrongcay_link_plant_supply', 'aitrongcay_ajax_link_plant_supply');
add_action('wp_ajax_nopriv_aitrongcay_link_plant_supply', 'aitrongcay_ajax_link_plant_supply');

// ── Soil Health Logs ─────────────────────────────────────────────────────────

function aitrongcay_soil_get_logs(string $garden_key): array
{
    global $wpdb;
    $tables = aitrongcay_onboarding_tables();
    if (empty($garden_key)) {
        return [];
    }
    
    $sql = $wpdb->prepare("SELECT * FROM {$tables['plant_soil_logs']} WHERE garden_key = %s ORDER BY created_at DESC LIMIT 100", $garden_key);
    $rows = $wpdb->get_results($sql, ARRAY_A);
    return is_array($rows) ? $rows : [];
}

function aitrongcay_soil_add_log(string $garden_key, string $pot_code, string $supply_code, string $supply_name, string $amount_label, string $notes, int $user_id): bool
{
    global $wpdb;
    $tables = aitrongcay_onboarding_tables();
    
    if (empty($garden_key) || empty($pot_code) || empty($supply_code)) {
        return false;
    }
    
    $inserted = $wpdb->insert(
        $tables['plant_soil_logs'],
        [
            'garden_key' => $garden_key,
            'pot_code' => $pot_code,
            'supply_code' => $supply_code,
            'supply_name' => $supply_name,
            'amount_label' => $amount_label,
            'notes' => $notes,
            'created_by_user_id' => $user_id > 0 ? $user_id : null,
            'created_at' => current_time('mysql', 1)
        ],
        ['%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s']
    );
    
    return $inserted !== false;
}

function aitrongcay_ajax_soil_add_log(): void
{
    if (! is_user_logged_in()) {
        wp_send_json_error(['message' => 'Bạn cần đăng nhập.'], 401);
    }
    
    $garden_key = trim((string) ($_POST['garden_key'] ?? ''));
    $pot_code = trim((string) ($_POST['pot_code'] ?? ''));
    $supply_code = trim((string) ($_POST['supply_code'] ?? ''));
    $supply_name = trim((string) ($_POST['supply_name'] ?? ''));
    $amount_label = trim((string) ($_POST['amount_label'] ?? ''));
    $notes = trim((string) ($_POST['notes'] ?? ''));
    $user_id = get_current_user_id();
    
    if (empty($garden_key) || empty($pot_code) || empty($supply_code)) {
        wp_send_json_error(['message' => 'Vui lòng điền đủ thông tin (Khoang trồng, Chế phẩm).'], 400);
    }
    
    $success = aitrongcay_soil_add_log($garden_key, $pot_code, $supply_code, $supply_name, $amount_label, $notes, $user_id);
    
    if ($success) {
        wp_send_json_success(['message' => 'Đã lưu nhật ký vi sinh thành công!']);
    } else {
        wp_send_json_error(['message' => 'Lỗi lưu database.'], 500);
    }
}
add_action('wp_ajax_aitrongcay_soil_add_log', 'aitrongcay_ajax_soil_add_log');
