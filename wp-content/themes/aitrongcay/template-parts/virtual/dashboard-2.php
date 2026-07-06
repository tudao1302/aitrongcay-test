<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
  exit;
}

$page = aitrongcay_current_virtual_page();
$slug = $page['slug'] ?? 'portal/dashboard-2';
$current_user = wp_get_current_user();
$is_logged_in = is_user_logged_in();

$garden_key = $is_logged_in ? aitrongcay_resolve_active_garden_key($current_user instanceof WP_User ? $current_user : null) : '';
$can_control_garden = $is_logged_in && function_exists('aitrongcay_user_can_control_garden') ? aitrongcay_user_can_control_garden($garden_key, (int) $current_user->ID) : false;
$active_profile = $is_logged_in ? aitrongcay_portal_profile_for_garden_context($garden_key, $current_user instanceof WP_User ? $current_user : null) : null;
$pots = aitrongcay_portal_pots($garden_key, $current_user instanceof WP_User ? $current_user : null);
$rack_record = $is_logged_in && function_exists('aitrongcay_get_rack_record') ? aitrongcay_get_rack_record($garden_key) : null;
$has_rack = is_array($rack_record) && ((int) ($rack_record['slot_count'] ?? 0) >= 2);
$has_physical_rack_device = $has_rack && function_exists('aitrongcay_rack_has_physical_device') ? aitrongcay_rack_has_physical_device($rack_record) : false;
$rack_slot_count = $has_rack ? (int) ($rack_record['slot_count'] ?? 0) : 0;
$rack_notice = isset($_GET['rack_notice']) ? sanitize_text_field((string) wp_unslash($_GET['rack_notice'])) : '';
$rack_init_status = isset($_GET['rack_init']) ? sanitize_key((string) wp_unslash($_GET['rack_init'])) : '';
$garden_display_name = $is_logged_in && function_exists('aitrongcay_get_garden_display_name')
  ? trim((string) aitrongcay_get_garden_display_name($garden_key, $current_user instanceof WP_User ? $current_user : null))
  : trim((string) ($active_profile['garden_name'] ?? ''));
$viewable_gardens = $is_logged_in && function_exists('aitrongcay_get_viewable_gardens_for_user') 
  ? aitrongcay_get_viewable_gardens_for_user($current_user instanceof WP_User ? $current_user : null) 
  : [];

if ($slug !== 'portal' && !$is_logged_in) {
  wp_safe_redirect(home_url('/dang-nhap/?auth_status=login-required'));
  exit;
}

$header_avatar_html = '👤';
if ($is_logged_in) {
    $current_user_header = $current_user instanceof WP_User ? $current_user : wp_get_current_user();
    $header_avatar_id = (int) get_user_meta($current_user_header->ID, 'aitrongcay_avatar_id', true);
    $header_avatar_url = $header_avatar_id ? (wp_get_attachment_image_url($header_avatar_id, 'thumbnail') ?: wp_get_attachment_url($header_avatar_id)) : '';
    if ($header_avatar_url) {
        $header_avatar_html = '<img src="' . esc_url($header_avatar_url) . '" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:50%;display:block;">';
    } else {
        $header_avatar_html = esc_html(mb_strtoupper(mb_substr($current_user_header->display_name ?: $current_user_header->user_login, 0, 1)));
    }
}

$hero_pot = $pots[0] ?? null;
$hero_pot_code = (string) ($hero_pot['code'] ?? '');
$rack_configs = function_exists('aitrongcay_get_rack_monitor_configs') ? aitrongcay_get_rack_monitor_configs($garden_key) : [];
$is_admin_user = current_user_can('manage_options');

// Allow admin to view virtual configured racks without DB inventory
if ($is_admin_user && !empty($rack_configs)) {
    $has_rack = true;
    if ($rack_slot_count < 1) {
        $rack_slot_count = max(3, count($rack_configs[0]['trays'] ?? []));
    }
}

// Check if user has a real provisioned subscription (order with status active)
$has_active_subscription = false;
if ($is_logged_in && $current_user instanceof WP_User && $current_user->ID > 0) {
    global $wpdb;
    $_ot = $wpdb->prefix . 'aitr_orders';
    if ($wpdb->get_var("SHOW TABLES LIKE '{$_ot}'") === $_ot) {
        $has_active_subscription = (bool) $wpdb->get_var($wpdb->prepare(
            "SELECT 1 FROM {$_ot} WHERE user_id = %d AND status = 'active' LIMIT 1",
            $current_user->ID
        ));
        if (!$has_active_subscription && $garden_key !== '') {
            $has_active_subscription = (bool) $wpdb->get_var($wpdb->prepare(
                "SELECT 1 FROM {$_ot} WHERE garden_key = %s AND status = 'active' LIMIT 1",
                $garden_key
            ));
        }
        // Fallback: đơn guest (user_id=0) đặt bằng email trước khi tạo tài khoản
        if (!$has_active_subscription && $current_user->user_email !== '') {
            $has_active_subscription = (bool) $wpdb->get_var($wpdb->prepare(
                "SELECT 1 FROM {$_ot} WHERE customer_email = %s AND status = 'active' LIMIT 1",
                $current_user->user_email
            ));
        }
    }
}

// Timelapse stream list will be built later from $switcher_pot_payload
$has_any_m3u8 = false;
foreach ($rack_configs as $_rack) {
  foreach ((array) ($_rack['trays'] ?? []) as $_tc) {
    if (str_contains(strtolower(trim((string) ($_tc['webcam_url'] ?? ''))), '.m3u8')) {
      $has_any_m3u8 = true;
      break 2;
    }
  }
}
$aitr_vn_timezone = new DateTimeZone('Asia/Bangkok');
$format_media_badge = static function (string $stream_url = '', string $captured_at = '') use ($aitr_vn_timezone): string {
  if (trim($stream_url) !== '') {
    return 'live stream . 4k';
  }
  $captured_at = trim($captured_at);
  if ($captured_at !== '') {
    $timestamp = strtotime($captured_at);
    if ($timestamp) {
      return 'Ảnh chụp lúc ' . wp_date('H:i', $timestamp, $aitr_vn_timezone) . ' giờ';
    }
  }
  return 'Ảnh chụp';
};
$hero_image = wp_make_link_relative((string) ($hero_pot['image'] ?? '')) ?: (get_template_directory_uri() . '/assets/images/hero-greenhouse.svg');
$hero_stream_url = $can_control_garden && function_exists('aitrongcay_hls_stream_url') ? aitrongcay_hls_stream_url($garden_key, (string) ($hero_pot['code'] ?? '')) : '';
$hero_snapshot_at = trim((string) ($hero_pot['latest_photo_at'] ?? ''));
$hero_media_badge = $format_media_badge($hero_stream_url, $hero_snapshot_at);
$hero_name = trim((string) ($hero_pot['name'] ?? 'Khoang trung tâm'));
$hero_plant_name = trim((string) ($hero_pot['plant_name'] ?? 'Cây chưa xác định'));
$hero_status = trim((string) ($hero_pot['status'] ?? 'Đang theo dõi'));
$cleanup_analysis_summary = static function (string $text): string {
  $text = trim($text);
  if ($text === '') {
    return '';
  }
  $text = preg_replace('/^Cindy đã đối chiếu ảnh mới với hồ sơ onboarding của .*?\.\s*/iu', '', $text) ?? $text;
  return trim($text);
};
$format_recommendation_html = static function (string $text): string {
  $text = trim($text);
  if ($text === '') {
    return '';
  }
  $upsells = [];
  $text = preg_replace_callback('/\[UPSELL\]\s*([^\n\r\.]+)/iu', static function ($matches) use (&$upsells) {
      $upsells[] = trim($matches[1]);
      return '';
  }, $text) ?? $text;
  
  $text = trim($text);
  $text = str_replace(["\r\n", "\r"], "\n", $text);
  if (!str_contains($text, "\n")) {
    $text = preg_replace('/\s*[•·]\s*/u', "\n", $text) ?? $text;
    $text = preg_replace('/\.\s+/u', ".\n", $text) ?? $text;
  }
  
  $html = nl2br(esc_html($text));
  if (!empty($upsells)) {
      $html .= '<div style="margin-top: 12px; display: flex; gap: 8px; flex-wrap: wrap;">';
      foreach ($upsells as $code) {
          $market_url = home_url('/portal/kho-nong-cu-2/?add_to_cart=' . rawurlencode($code));
          $html .= sprintf('<button onclick="window.location.href=\'%s\'" style="padding: 6px 14px; font-size: 12px; font-weight: 700; border-radius: 14px; border: 1px solid rgba(111,219,168,0.4); background: linear-gradient(180deg, rgba(111,219,168,0.15), rgba(49,163,117,0.25)); color: #6fdba8; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(49,163,117,0.15);"><span style="font-size: 14px;">🛒</span> Thêm %s</button>', esc_url($market_url), esc_html($code));
      }
      $html .= '</div>';
  }
  return $html;
};
$hero_summary = $cleanup_analysis_summary(trim((string) ($hero_pot['latest_analysis_summary'] ?? $hero_pot['status_summary'] ?? 'AI đang tổng hợp dữ liệu và diễn giải tình trạng mới nhất của khoang.')));
$hero_ph = trim((string) ($hero_pot['ph'] ?? '--'));
$garden_title = $garden_display_name !== '' ? $garden_display_name : 'Garden Command Center';
$alert_label = !empty($hero_pot['latest_analysis_label']) ? (string) $hero_pot['latest_analysis_label'] : 'Ổn định';
$alert_level = (int) ($hero_pot['latest_analysis_level'] ?? 2);
$alert_text = 'Cấp ' . $alert_level . ' - ' . $alert_label;
$harvest_eta = trim((string) ($hero_pot['harvest_eta'] ?? 'Đang cập nhật'));
$owner_name = $current_user instanceof WP_User ? ($current_user->display_name ?: $current_user->user_login) : 'Guardian';
$temperature = trim((string) ($hero_pot['temperature'] ?? '--'));
$humidity = trim((string) ($hero_pot['humidity'] ?? '--'));
$light_label = trim((string) ($hero_pot['light'] ?? '--'));
$status_summary = $cleanup_analysis_summary(trim((string) ($hero_pot['latest_analysis_summary'] ?? $hero_pot['status_summary'] ?? 'Chưa có dữ liệu cảm biến.')));
$analysis_actions = !empty($hero_pot['latest_analysis_actions']) && is_array($hero_pot['latest_analysis_actions'])
  ? array_values(array_map('strval', $hero_pot['latest_analysis_actions']))
  : [];
$recommendation = trim((string) ($hero_pot['latest_analysis_recommendation'] ?? ''));
if ($recommendation === '') {
  $recommendation = $analysis_actions ? implode(' · ', array_slice($analysis_actions, 0, 2)) : 'Tiếp tục theo dõi và cập nhật ảnh mới để AI đánh giá chính xác hơn.';
}
$analysis_updated_at = trim((string) ($hero_pot['latest_analysis_updated_at'] ?? $hero_pot['updated_at'] ?? ''));
$analysis_badge_text = $alert_text . ($analysis_updated_at !== '' ? (' · ' . wp_date('H:i d/m/Y', strtotime($analysis_updated_at . ' UTC'), new DateTimeZone('Asia/Ho_Chi_Minh'))) : '');
$health_index = max(58, min(96, 100 - ($alert_level * 6)));
$live_count = max(1, count($pots));
$active_devices = count(array_filter($pots, static fn(array $pot_item): bool => trim((string) ($pot_item['light_device'] ?? '')) !== ''));
$pot_notes = $is_logged_in && function_exists('aitrongcay_get_garden_pot_notes') ? aitrongcay_get_garden_pot_notes($garden_key) : [];
$photo_library_url = $garden_key !== '' ? add_query_arg('garden', rawurlencode($garden_key), home_url('/portal/nhat-ky-cham-soc/')) : home_url('/portal/nhat-ky-cham-soc/');
$old_dashboard_url = $garden_key !== '' ? add_query_arg('garden', rawurlencode($garden_key), home_url('/portal/dashboard-2/')) : home_url('/portal/dashboard-2/');
$flower_bio_url = add_query_arg(array_filter(['garden' => $garden_key, 'pot' => $hero_pot_code]), home_url('/portal/flower-bio/'));
$ai_url = $garden_key !== '' ? add_query_arg('garden', rawurlencode($garden_key), home_url('/portal/tro-ly-ai/')) : home_url('/portal/tro-ly-ai/');
$add_pot_url = $garden_key !== '' ? add_query_arg(['garden' => rawurlencode($garden_key), 'mode' => 'onboarding'], home_url('/portal/tro-ly-ai/')) : add_query_arg('mode', 'onboarding', home_url('/portal/tro-ly-ai/'));
$init_rack_url = wp_nonce_url(admin_url('admin-post.php?action=aitrongcay_init_rack&garden_key=' . rawurlencode($garden_key)), 'aitrongcay_init_rack');
$market_compose_url = add_query_arg(['compose' => '1', 'garden' => $garden_key], home_url('/cho-que/'));
$friends_url = $garden_key !== '' ? add_query_arg('garden', rawurlencode($garden_key), home_url('/portal/hang-xom/')) : home_url('/portal/hang-xom/');
$shared_top_links = [
  ['key' => 'doi-diem', 'label' => 'Đổi điểm', 'url' => home_url('/portal/doi-diem/')],
  ['key' => 'cho-que', 'label' => 'Chợ quê', 'url' => home_url('/cho-que/')],
  ['key' => 'kho-nong-cu', 'label' => 'Kho nông cụ', 'url' => home_url('/portal/kho-nong-cu-2/')],
  ['key' => 'hang-xom', 'label' => 'Hàng xóm', 'url' => home_url('/portal/hang-xom/')],
  ['key' => 'dashboard-2', 'label' => 'Vào khu vườn của tôi', 'url' => home_url('/portal/dashboard-2/')],
];
foreach ($shared_top_links as &$shared_top_link) {
  if ($garden_key !== '') {
    $shared_top_link['url'] = add_query_arg('garden', $garden_key, $shared_top_link['url']);
  }
}
unset($shared_top_link);
$active_pot_code = isset($_GET['pot']) ? sanitize_text_field((string) wp_unslash($_GET['pot'])) : '';
if ($active_pot_code !== '') {
  foreach ($pots as $candidate_pot) {
    if ((string) ($candidate_pot['code'] ?? '') === $active_pot_code) {
      $hero_pot = $candidate_pot;
      break;
    }
  }
  $hero_image = (wp_make_link_relative((string) ($hero_pot['image'] ?? ''))) ?: $hero_image;
  $hero_stream_url = $can_control_garden && function_exists('aitrongcay_hls_stream_url') ? aitrongcay_hls_stream_url($garden_key, (string) ($hero_pot['code'] ?? '')) : $hero_stream_url;
  $hero_snapshot_at = trim((string) ($hero_pot['latest_photo_at'] ?? $hero_snapshot_at));
  $hero_media_badge = $format_media_badge($hero_stream_url, $hero_snapshot_at);
  $hero_name = trim((string) ($hero_pot['name'] ?? $hero_name));
  $hero_plant_name = trim((string) ($hero_pot['plant_name'] ?? $hero_plant_name));
  $hero_status = trim((string) ($hero_pot['status'] ?? $hero_status));
  $hero_summary = $cleanup_analysis_summary(trim((string) ($hero_pot['latest_analysis_summary'] ?? $hero_pot['status_summary'] ?? $hero_summary)));
  $hero_ph = trim((string) ($hero_pot['ph'] ?? $hero_ph));
  $alert_label = !empty($hero_pot['latest_analysis_label']) ? (string) $hero_pot['latest_analysis_label'] : $alert_label;
  $alert_level = (int) ($hero_pot['latest_analysis_level'] ?? $alert_level);
  $alert_text = 'Cấp ' . $alert_level . ' - ' . $alert_label;
  $harvest_eta = trim((string) ($hero_pot['harvest_eta'] ?? $harvest_eta));
  $temperature = trim((string) ($hero_pot['temperature'] ?? $temperature));
  $humidity = trim((string) ($hero_pot['humidity'] ?? $humidity));
  $light_label = trim((string) ($hero_pot['light'] ?? $light_label));
  $status_summary = $cleanup_analysis_summary(trim((string) ($hero_pot['latest_analysis_summary'] ?? $hero_pot['status_summary'] ?? $status_summary)));
  $analysis_actions = !empty($hero_pot['latest_analysis_actions']) && is_array($hero_pot['latest_analysis_actions'])
    ? array_values(array_map('strval', $hero_pot['latest_analysis_actions']))
    : $analysis_actions;
  $recommendation = trim((string) ($hero_pot['latest_analysis_recommendation'] ?? ''));
  if ($recommendation === '') {
    $recommendation = $analysis_actions ? implode(' · ', array_slice($analysis_actions, 0, 2)) : $recommendation;
  }
  $analysis_updated_at = trim((string) ($hero_pot['latest_analysis_updated_at'] ?? $hero_pot['updated_at'] ?? $analysis_updated_at));
  $analysis_badge_text = $alert_text . ($analysis_updated_at !== '' ? (' · ' . wp_date('H:i d/m/Y', strtotime($analysis_updated_at . ' UTC'), new DateTimeZone('Asia/Ho_Chi_Minh'))) : '');
  $health_index = max(58, min(96, 100 - ($alert_level * 6)));
}


$switcher_pots = array_values($pots);
$rack_slots = $has_rack && function_exists('aitrongcay_get_rack_slots') ? aitrongcay_get_rack_slots($garden_key) : [];
$hero_pot_code = (string) ($hero_pot['code'] ?? '');
$photo_library_pot_url = $hero_pot_code !== ''
  ? add_query_arg(['garden' => $garden_key], home_url('/portal/nhat-ky-cham-soc/#photo-' . strtolower($hero_pot_code)))
  : $photo_library_url;
$market_compose_pot_url = add_query_arg(['compose' => '1', 'garden' => $garden_key, 'pot' => $hero_pot_code], home_url('/cho-que/'));
$hero_has_light = trim((string) ($hero_pot['light_device'] ?? '')) !== '';
$hero_has_pump = trim((string) ($hero_pot['pump'] ?? '')) !== '';
$hero_has_nutrient = true;
$hero_has_mist = false;
$hero_note_text = trim((string) ($pot_notes[$hero_pot_code]['note_text'] ?? ''));
$hero_journal_text = $hero_note_text !== '' ? $hero_note_text : 'Chưa có nhật ký canh tác cho khoang này.';

$build_growth_journey = static function (int $plant_id, int $analysis_level = 2, string $pot_started_at = '', string $detected_stage = ''): array {
  $fallback = [
    'hasGrowthJourney' => false,
    'hasStageSignal' => false,
    'currentStage' => $analysis_level >= 4 ? 'Cần chú ý' : 'Đang cập nhật',
    'activeStagePosition' => 1,
    'growthStageTotal' => 0,
    'progressWidth' => 0,
    'ageDays' => null,
    'startedAt' => '',
    'stages' => [],
    'emptyMessage' => $detected_stage !== ''
      ? 'AI phân tích khoang đang ở giai đoạn "' . $detected_stage . '", nhưng bạn chưa cập nhật giống cây trồng cho khoang này nên không thể vẽ biểu đồ sinh trưởng. Hãy bổ sung tên loại cây để xem hành trình nhé!'
      : 'Khoang này hiện tại chưa có dữ liệu biểu đồ sinh trưởng. Bạn vui lòng cập nhật loại cây đang trồng để hệ thống AI bắt đầu theo dõi và thiết lập biểu đồ hành trình phát triển nhé!',
  ];

  if ($plant_id <= 0 || !function_exists('aitrongcay_plant_growth_stages')) {
    return $fallback;
  }

  $growth_stages = aitrongcay_plant_growth_stages($plant_id);
  $growth_stage_items = [];
  foreach ($growth_stages as $stage_row) {
    $stage_name = trim((string) ($stage_row['stage_name'] ?? ''));
    if ($stage_name === '') {
      continue;
    }
    $growth_stage_items[] = [
      'name' => $stage_name,
      'index' => (int) ($stage_row['stage_index'] ?? 0),
    ];
  }

  if ($growth_stage_items === []) {
    return $fallback;
  }

  $growth_stage_total = count($growth_stage_items);
  $started_at = trim($pot_started_at);
  $detected_stage = trim($detected_stage);
  $detected_stage_lc = strtolower(remove_accents($detected_stage));
  $age_days = null;
  if ($started_at !== '') {
    try {
      $timezone = wp_timezone();
      $start_dt = new DateTimeImmutable($started_at, $timezone);
      $start_day = $start_dt->setTime(0, 0, 0);
      $today_day = (new DateTimeImmutable('now', $timezone))->setTime(0, 0, 0);
      $age_days = max(0, (int) $start_day->diff($today_day)->format('%a'));
    } catch (Throwable $e) {
      $age_days = null;
    }
  }

  $plant = function_exists('aitrongcay_onboarding_plant_record') ? (aitrongcay_onboarding_plant_record($plant_id) ?: []) : [];
  $cycle_days = max($growth_stage_total, (int) ($plant['default_cycle_days'] ?? 0));
  if ($cycle_days <= 0) {
    $cycle_days = max(24, $growth_stage_total * 7);
  }
  $germination_days = max(1, min($cycle_days, (int) ($plant['germination_days'] ?? max(3, (int) ceil($cycle_days * 0.12)))));
  $harvest_start = max($germination_days + 1, min($cycle_days, (int) ($plant['harvest_start_day'] ?? max($germination_days + 1, $cycle_days - max(5, (int) ceil($cycle_days * 0.18))))));

  $active_stage_position = 1;
  $has_stage_signal = $detected_stage_lc !== '';
  if ($has_stage_signal) {
    $stage_signal_map = [
      'nay mam' => ['nay mam', 'mam', 'cay mam', 'germ'],
      'phat trien sinh duong' => ['sinh truong', 'sinh duong', 'than la', 'ra la', 'vegetative'],
      'ra hoa & thu phan' => ['ra hoa', 'thu phan', 'co hoa', 'flower'],
      'dau qua & phat trien qua' => ['dau qua', 'nuoi qua', 'qua non', 'phat trien qua', 'mang qua', 'fruit set', 'fruit'],
      'chin & thu hoach' => ['chin', 'thu hoach', 'sap thu', 'harvest'],
    ];
    foreach ($growth_stage_items as $stage_index => $stage_item) {
      $stage_name_lc = strtolower(remove_accents((string) ($stage_item['name'] ?? '')));
      $signals = [$stage_name_lc];
      foreach ($stage_signal_map as $anchor => $aliases) {
        if ($stage_name_lc !== '' && str_contains($stage_name_lc, $anchor)) {
          $signals = array_merge($signals, $aliases);
        }
      }
      foreach (array_unique(array_filter($signals)) as $signal) {
        if (str_contains($stage_name_lc, $detected_stage_lc) || str_contains($detected_stage_lc, $stage_name_lc) || str_contains($detected_stage_lc, $signal) || str_contains($signal, $detected_stage_lc)) {
          $active_stage_position = $stage_index + 1;
          break 2;
        }
      }
    }
  }
  if (!$has_stage_signal) {
    $active_stage_position = 1;
    if ($age_days !== null) {
      if ($growth_stage_total === 1) {
        $active_stage_position = 1;
      } elseif ($age_days <= $germination_days) {
        $active_stage_position = 1;
      } elseif ($age_days >= $harvest_start) {
        $active_stage_position = $growth_stage_total;
      } else {
        $middle_stage_total = max(1, $growth_stage_total - 2);
        $middle_start_day = $germination_days + 1;
        $middle_end_day = max($middle_start_day, $harvest_start - 1);
        $middle_span = max(1, $middle_end_day - $middle_start_day + 1);
        $offset_day = max(0, min($middle_span - 1, $age_days - $middle_start_day));
        $middle_index = (int) floor(($offset_day / $middle_span) * $middle_stage_total);
        $active_stage_position = min($growth_stage_total - 1, max(2, 2 + $middle_index));
      }
    }
  }

  $current_stage = (string) ($growth_stage_items[$active_stage_position - 1]['name'] ?? ($analysis_level >= 4 ? 'Cần chú ý' : 'Phát triển'));
  $progress_width = 0;
  
  if ($growth_stage_total > 1) {
    // Khoảng cách giữa 2 node khi các node chia đều (flex: 1)
    $step_width = 100 / $growth_stage_total;

    // Chiều rộng lý thuyết để chạm đến tâm node hiện tại
    $base_width = ((2 * $active_stage_position - 1) / (2 * $growth_stage_total)) * 100;
    
    if ($active_stage_position < $growth_stage_total) {
       // Thêm một đoạn ngắn qua node hiện tại để hiển thị trạng thái "đang xử lý" ở giai đoạn này
       $progress_width = $base_width + ($step_width * 0.35); 
    } else {
       $progress_width = 100;
    }
  }
  
  $progress_width = min(100, max(0, $progress_width));

  return [
    'hasGrowthJourney' => true,
    'hasStageSignal' => $has_stage_signal,
    'currentStage' => $current_stage,
    'activeStagePosition' => $active_stage_position,
    'growthStageTotal' => $growth_stage_total,
    'progressWidth' => $progress_width,
    'ageDays' => $age_days,
    'startedAt' => $started_at,
    'stages' => $growth_stage_items,
  ];
};

$hero_growth_journey = $build_growth_journey((int) ($hero_pot['plant_id'] ?? 0), (int) ($hero_pot['latest_analysis_level'] ?? $alert_level), (string) ($hero_pot['created_at'] ?? ''), (string) ($hero_pot['latest_analysis_current_stage'] ?? ''));

// Map pot_code → { webcam_url, tray_name } dựa trên slot_index hoặc cấu hình
$rack_slot_webcam_map = [];
$rack_slot_tray_name_map = [];
if (!empty($rack_slots)) {
  foreach ($rack_slots as $_slot) {
    if (!is_array($_slot)) {
      continue;
    }
    $slot_pot_code = trim((string) ($_slot['pot_code'] ?? ''));
    if ($slot_pot_code === '') {
      continue;
    }
    $cam_url = trim((string) ($_slot['camera_stream_url'] ?? ''));
    if ($cam_url !== '') {
      $rack_slot_webcam_map[$slot_pot_code] = $cam_url;
    }
    $slot_name = trim((string) ($_slot['slot_name'] ?? ''));
    if ($slot_name !== '') {
      $rack_slot_tray_name_map[$slot_pot_code] = $slot_name;
    }
  }
}

if (!empty($rack_slots) && !empty($rack_configs)) {
  $tray_map_by_rack = [];
  foreach ($rack_configs as $_rc) {
    $rack_id = (int) ($_rc['rack_id'] ?? 0);
    // Fallback if rack_id is not present (e.g. from generic/default config)
    if ($rack_id === 0) continue;
    foreach ((array) ($_rc['trays'] ?? []) as $_ti => $_rt) {
      $tray_map_by_rack[$rack_id][$_ti + 1] = [
        'webcam_url' => trim((string) ($_rt['webcam_url'] ?? '')),
        'name'       => trim((string) ($_rt['name'] ?? '')),
      ];
    }
  }
  
  // If no rack_id mapped (legacy default fallback), try to map by flat index
  $is_legacy = empty($tray_map_by_rack);
  $flat_trays = [];
  if ($is_legacy) {
      foreach ($rack_configs as $_rc) {
          foreach ((array) ($_rc['trays'] ?? []) as $_rt) {
              $flat_trays[] = [
                  'webcam_url' => trim((string) ($_rt['webcam_url'] ?? '')),
                  'name'       => trim((string) ($_rt['name'] ?? '')),
              ];
          }
      }
  }

  foreach ($rack_slots as $_slot) {
    if (!is_array($_slot)) {
      continue;
    }
    $slot_index = (int) ($_slot['slot_index'] ?? 0);
    $rack_id = (int) ($_slot['rack_id'] ?? 0);
    $slot_pot_code = trim((string) ($_slot['pot_code'] ?? ''));
    if ($slot_pot_code === '' || $slot_index < 1) {
      continue;
    }
    
    $tray_info = null;
    if (!$is_legacy && isset($tray_map_by_rack[$rack_id][$slot_index])) {
        $tray_info = $tray_map_by_rack[$rack_id][$slot_index];
    } else if ($is_legacy) {
        $tray_info = $flat_trays[$slot_index - 1] ?? null;
    }
    
    if ($tray_info === null) {
      continue;
    }
    if (empty($rack_slot_webcam_map[$slot_pot_code]) && $tray_info['webcam_url'] !== '') {
      $rack_slot_webcam_map[$slot_pot_code] = $tray_info['webcam_url'];
    }
    if (empty($rack_slot_tray_name_map[$slot_pot_code]) && $tray_info['name'] !== '') {
      $rack_slot_tray_name_map[$slot_pot_code] = $tray_info['name'];
    }
  }
}



// Tray thumbnails: go2rtc snapshot URL + latest timelapse photo per pot_code
$pot_snap_map      = [];  // pot_code → go2rtc /api/frame.jpeg URL (live refresh)
$pot_latest_tl_map = [];  // pot_code → URL ảnh timelapse mới nhất
foreach ($rack_slot_webcam_map as $_pc => $_wu) {
  if (strpos($_wu, 'src=') === false) {
    continue;
  }
  $_parsed = parse_url($_wu);
  $_base   = ($_parsed['scheme'] ?? 'http') . '://' . ($_parsed['host'] ?? 'localhost');
  if (!empty($_parsed['port'])) {
    $_base .= ':' . $_parsed['port'];
  }
  parse_str($_parsed['query'] ?? '', $_qp);
  $_slug = sanitize_key($_qp['src'] ?? '');
  if ($_slug === '') {
    continue;
  }
  $pot_snap_map[$_pc] = $_base . '/api/frame.jpeg?src=' . rawurlencode($_slug);

  // Tìm ảnh timelapse mới nhất
  $_fs_gk   = sanitize_key($garden_key);
  $_base_tl_dir = WP_CONTENT_DIR . '/uploads/timelapse/';
  $_tl_dir = $_base_tl_dir . $_fs_gk . '/' . $_slug . '/';
  if (!is_dir($_tl_dir) && is_dir($_base_tl_dir . 'global/' . $_slug . '/')) {
      $_fs_gk = 'global';
      $_tl_dir = $_base_tl_dir . 'global/' . $_slug . '/';
  }

  if (is_dir($_tl_dir)) {
    $_date_dirs = glob($_tl_dir . '[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]', GLOB_ONLYDIR);
    if (!empty($_date_dirs)) {
      rsort($_date_dirs);
      $_photos = glob($_date_dirs[0] . '/*.jpg');
      if (!empty($_photos)) {
        rsort($_photos);
        $pot_latest_tl_map[strtoupper($_pc)] = wp_make_link_relative(content_url(
          'uploads/timelapse/' . $_fs_gk . '/' . $_slug . '/' . basename($_date_dirs[0]) . '/' . basename($_photos[0])
        ));
      }
    }
  }
}

// Fallback: Fetch latest photos from Media Library (Robot captures) for ALL pots
// This ensures pots without a live camera still show the latest robot photo
if ($garden_key !== '') {
    $photo_query = new WP_Query([
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => 100,
        'meta_query'     => [
            [
                'key'   => '_aitrongcay_photo_garden_key',
                'value' => $garden_key,
            ],
        ],
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);
    if ($photo_query->have_posts()) {
        foreach ($photo_query->posts as $p) {
            $p_code = strtoupper(trim((string) get_post_meta($p->ID, '_aitrongcay_pot_code', true)));
            if ($p_code !== '' && empty($pot_latest_tl_map[$p_code])) {
                $img_url = wp_get_attachment_image_url($p->ID, 'large') ?: wp_get_attachment_url($p->ID);
                if ($img_url) {
                    $pot_latest_tl_map[$p_code] = wp_make_link_relative((string) $img_url);
                }
            }
        }
    }
    
    // DEBUG: print found images
    echo '<!-- DEBUG_ROBOT_PHOTOS: found ' . $photo_query->found_posts . ' photos. Keys in map: ' . implode(', ', array_keys($pot_latest_tl_map)) . ' -->';
}

// Build tray_name → {ri, ti, hasToken} for reliable lane routing (bypasses JS string matching)
$tray_name_to_lane = [];
foreach ($rack_configs as $_ri => $_rack) {
  foreach ((array) ($_rack['trays'] ?? []) as $_ti => $_tray) {
    $_name = trim((string) ($_tray['name'] ?? ''));
    if ($_name !== '') {
      $tray_name_to_lane[$_name] = [
        'ri'       => (int) $_ri,
        'ti'       => (int) $_ti,
        'hasToken' => trim((string) ($_tray['blynk_token'] ?? '')) !== '',
      ];
    }
  }
}

$switcher_pot_payload = array_values(array_map(static function (array $pot_item) use ($garden_key, $can_control_garden, $pot_notes, $photo_library_url, $hero_image, $build_growth_journey, $rack_configs, $rack_slot_webcam_map, $rack_slot_tray_name_map, $tray_name_to_lane, $pot_snap_map, $pot_latest_tl_map): array {
  $pot_code = (string) ($pot_item['code'] ?? '');
  $stream_url = $can_control_garden && function_exists('aitrongcay_hls_stream_url') ? aitrongcay_hls_stream_url($garden_key, $pot_code) : '';

  // Fallback 1: dùng slot_index map (chính xác)
  if ($stream_url === '' && isset($rack_slot_webcam_map[$pot_code])) {
    $stream_url = $rack_slot_webcam_map[$pot_code];
  }

  $snapshot_at = trim((string) ($pot_item['latest_photo_at'] ?? ''));
  $analysis_actions = !empty($pot_item['latest_analysis_actions']) && is_array($pot_item['latest_analysis_actions'])
    ? array_values(array_map('strval', $pot_item['latest_analysis_actions']))
    : [];
  $recommendation_text = trim((string) ($pot_item['latest_analysis_recommendation'] ?? ''));
  if ($recommendation_text === '') {
    $recommendation_text = $analysis_actions ? implode(' · ', array_slice($analysis_actions, 0, 2)) : 'Tiếp tục theo dõi và cập nhật ảnh mới để AI đánh giá chính xác hơn.';
  }
  $note_text = trim((string) ($pot_notes[$pot_code]['note_text'] ?? ''));
  $journal_text = $note_text !== '' ? $note_text : 'Chưa có nhật ký canh tác cho khoang này.';
  $temperature_value = preg_replace('/[^0-9.,-]/', '', trim((string) ($pot_item['temperature'] ?? '24')));
  $humidity_value = preg_replace('/[^0-9.,-]/', '', trim((string) ($pot_item['humidity'] ?? '62')));
  $growthJourney = $build_growth_journey((int) ($pot_item['plant_id'] ?? 0), (int) ($pot_item['latest_analysis_level'] ?? 2), (string) ($pot_item['created_at'] ?? ''), (string) ($pot_item['latest_analysis_current_stage'] ?? ''));
  $tray_name = $rack_slot_tray_name_map[$pot_code] ?? '';
  $_lane_info = $tray_name !== '' && isset($tray_name_to_lane[$tray_name]) ? $tray_name_to_lane[$tray_name] : null;
  return [
    'code' => $pot_code,
    'name' => trim((string) ($pot_item['name'] ?? 'Khoang cây')),
    'plantName' => trim((string) ($pot_item['plant_name'] ?? '')),
    'slotLabel' => $tray_name,
    'lane' => $_lane_info ? ['ri' => $_lane_info['ri'], 'ti' => $_lane_info['ti']] : null,
    'hasToken' => $_lane_info ? (bool) $_lane_info['hasToken'] : false,
    'image' => (!empty($pot_latest_tl_map[strtoupper($pot_code)]) ? $pot_latest_tl_map[strtoupper($pot_code)] : (wp_make_link_relative((string) ($pot_item['image'] ?? '')) ?: (get_template_directory_uri() . '/assets/images/tool-tray-real.png'))),
    'streamUrl' => $stream_url,
    'snapshotAt' => $snapshot_at,
    'mediaBadge' => $stream_url !== ''
      ? 'live stream . 4k'
      : ($snapshot_at !== '' && strtotime($snapshot_at)
        ? 'Ảnh chụp lúc ' . wp_date('H:i', strtotime($snapshot_at), wp_timezone()) . ' giờ'
        : 'Ảnh chụp'),
    'status_summary' => trim((string) ($pot_item['latest_analysis_summary'] ?? $pot_item['status_summary'] ?? 'AI đang đọc ảnh và dữ liệu gần nhất của khoang này.')),
    'recommendation' => $recommendation_text,
    'alertText' => 'Cấp ' . ((int) ($pot_item['latest_analysis_level'] ?? 2)) . ' - ' . trim((string) ($pot_item['latest_analysis_label'] ?? 'Ổn định')),
    'currentStage' => trim((string) ($pot_item['latest_analysis_current_stage'] ?? '')),
    'analysisUpdatedAt' => (trim((string) ($pot_item['latest_analysis_updated_at'] ?? $pot_item['updated_at'] ?? '')) !== '') 
        ? wp_date('H:i d/m/Y', strtotime(trim((string) ($pot_item['latest_analysis_updated_at'] ?? $pot_item['updated_at'] ?? '')) . ' UTC'), new DateTimeZone('Asia/Ho_Chi_Minh')) 
        : '',
    'ph' => trim((string) ($pot_item['ph'] ?? '--')),
    'healthIndex' => max(58, min(96, 100 - (((int) ($pot_item['latest_analysis_level'] ?? 2)) * 6))),
    'temperature' => $temperature_value !== '' ? $temperature_value : '--',
    'humidity' => $humidity_value !== '' ? $humidity_value : '--',
    'journalText' => $journal_text,
    'photoLibraryUrl' => $pot_code !== ''
      ? add_query_arg(['garden' => $garden_key], home_url('/portal/nhat-ky-cham-soc/#photo-' . strtolower($pot_code)))
      : $photo_library_url,
    'analyzePotCode' => $pot_code,
    'dashboardUrl' => add_query_arg(['garden' => $garden_key, 'pot' => $pot_code], home_url('/portal/dashboard-2/')),
    'lightDevice' => trim((string) ($pot_item['light_device'] ?? '')),
    'growthJourney' => $growthJourney,
    'snapUrl' => $pot_snap_map[$pot_code] ?? '',
  ];
}, $switcher_pots));

$switcher_pot_map = [];
foreach ($switcher_pot_payload as $pot_item) {
  $pot_code = trim((string) ($pot_item['code'] ?? ''));
  if ($pot_code !== '') {
    $switcher_pot_map[$pot_code] = $pot_item;
  }
}

$empty_tray_image = function_exists('aitrongcay_default_empty_tray_image_url')
  ? aitrongcay_default_empty_tray_image_url()
  : (get_template_directory_uri() . '/assets/images/tool-tray-real.png');

$rack_one_pots = [];
if ($rack_slots) {
  foreach ($rack_slots as $slot) {
    if (!is_array($slot)) {
      continue;
    }
    $pot_code = trim((string) ($slot['pot_code'] ?? ''));
    $slot_index = (int) ($slot['slot_index'] ?? 0);
    $slot_meta = function_exists('aitrongcay_slot_to_compartment') ? aitrongcay_slot_to_compartment($slot_index > 0 ? $slot_index : 1) : ['slot_label' => 'Khoang ' . max(1, $slot_index)];
    if ($pot_code !== '' && isset($switcher_pot_map[$pot_code])) {
      $mapped_pot = $switcher_pot_map[$pot_code];
      if (empty($mapped_pot['streamUrl'])) {
        $mapped_pot['streamUrl'] = trim((string) ($slot['camera_stream_url'] ?? ''));
        if ($mapped_pot['streamUrl'] !== '') {
          $mapped_pot['mediaBadge'] = 'live stream . 4k';
        }
      }
      $rack_one_pots[] = $mapped_pot;
      continue;
    }

    $empty_code = $pot_code !== '' ? $pot_code : ('EMPTY-' . max(1, $slot_index));
    $rack_one_pots[] = [
      'code' => $empty_code,
      'name' => (string) ($slot_meta['slot_label'] ?? ('Khoang ' . max(1, $slot_index))) . ' trống',
      'plantName' => 'Khoang trống',
      'image' => $empty_tray_image,
      'streamUrl' => trim((string) ($slot['camera_stream_url'] ?? '')),
      'mediaBadge' => trim((string) ($slot['camera_stream_url'] ?? '')) !== '' ? 'live stream . 4k' : 'Ảnh chụp',
      'status_summary' => 'Khoang này đang trống và sẵn sàng để đặt khoang mới.',
      'recommendation' => 'Anh có thể chọn khoang này để bắt đầu theo dõi khoang mới.',
      'alertText' => 'Sẵn sàng',
      'currentStage' => '',
      'analysisUpdatedAt' => '',
      'ph' => '--',
      'healthIndex' => 100,
      'temperature' => '--',
      'humidity' => '--',
      'journalText' => 'Khoang này đang trống và chưa có khoang nào được gắn vào.',
      'photoLibraryUrl' => $photo_library_url,
      'analyzePotCode' => '',
      'dashboardUrl' => '#',
      'lightDevice' => '',
      'growthJourney' => [
        'hasGrowthJourney' => false,
        'hasStageSignal' => false,
        'currentStage' => '',
        'activeStagePosition' => 0,
        'growthStageTotal' => 0,
        'progressWidth' => 0,
        'ageDays' => null,
        'startedAt' => '',
        'stages' => [],
        'emptyMessage' => 'Khoang này đang trống nên chưa có hành trình sinh trưởng.',
      ],
      'isEmpty' => true,
    ];
  }
}
if (!$rack_one_pots) {
  $rack_one_pots = $switcher_pot_payload;
}

// Build rack switcher from monitor config (WP option aitrongcay_rack_cfg_{garden_key}).
// This is the authoritative source for multi-rack: each rack has independent
// Blynk tokens, cameras, vpins per tray. The DB table (wp_aitr_garden_racks)
// only tracks inventory ownership (1 row per garden_key due to UNIQUE constraint).
$rack_switcher_payload = [];
if ($has_active_subscription || !empty($rack_configs)) {
  $_flat_pots  = $rack_one_pots ?: [];
  $_used_codes = [];
  foreach ($_flat_pots as $_p) {
      if (!empty($_p['code'])) {
          $_used_codes[$_p['code']] = true;
      }
  }
  foreach ($switcher_pot_payload as $_p) {
      if (!empty($_p['code']) && !isset($_used_codes[$_p['code']])) {
          $_flat_pots[] = $_p;
      }
  }
  $_pot_offset = 0;
  foreach ($rack_configs as $_ri => $_rcfg) {
    $_tray_count_from_cfg = count($_rcfg['trays'] ?? []);
    $_tray_count = $_ri === 0 && isset($rack_slot_count) && $rack_slot_count > $_tray_count_from_cfg ? $rack_slot_count : $_tray_count_from_cfg;
    $_tray_count = max(1, $_tray_count);
    $_per_rack_pots = array_values(array_slice($_flat_pots, $_pot_offset, $_tray_count));
    // Override streamUrl theo vị trí hiển thị (0,1,2...) thay vì slot_index vật lý.
    // Đảm bảo "Khoang 1 trên dashboard = Tray 1 trong cài đặt" không phụ thuộc slot_index DB.
    foreach ($_per_rack_pots as $_ti => &$_rp) {
      $_ct = $_rcfg['trays'][$_ti] ?? [];
      $_cs = trim((string) ($_ct['webcam_url'] ?? ''));
      if ($_cs !== '') {
        $_rp['streamUrl']  = $_cs;
        $_rp['mediaBadge'] = 'live stream . 4k';
      } else {
        $_rp['streamUrl']  = '';
        if (strpos(strtolower($_rp['mediaBadge'] ?? ''), 'live stream') !== false) {
            $_rp['mediaBadge'] = 'Ảnh chụp';
        }
      }
      $_rp['lane'] = ['ri' => $_ri, 'ti' => $_ti, 'hasToken' => trim((string) ($_rcfg['blynk_auth_token'] ?? '')) !== ''];
      if (!$_rp['lane']['hasToken']) {
        $_rp['temperature'] = '--';
        $_rp['humidity'] = '--';
      }
    }
    unset($_rp);
    // Pad với empty tray nếu DB chưa có đủ slot cho rack này (rack mới tạo trong config)
    for ($_ti = count($_per_rack_pots); $_ti < $_tray_count; $_ti++) {
      $_cfg_tray      = $_rcfg['trays'][$_ti] ?? [];
      $_global_slot   = $_pot_offset + $_ti + 1;
      $_tray_name     = trim((string) ($_cfg_tray['name'] ?? '')) ?: ('Khoang ' . $_global_slot);
      $_cfg_stream  = trim((string) ($_cfg_tray['webcam_url'] ?? ''));
      $_per_rack_pots[] = [
        'code'              => 'EMPTY-' . $_global_slot,
        'name'              => $_tray_name . ' trống',
        'plantName'         => 'Khoang trống',
        'slotLabel'         => $_tray_name,
        'lane'              => ['ri' => $_ri, 'ti' => $_ti, 'hasToken' => trim((string) ($_rcfg['blynk_auth_token'] ?? '')) !== ''],
        'image'             => $empty_tray_image,
        'streamUrl'         => $_cfg_stream,
        'snapUrl'           => '',
        'mediaBadge'        => $_cfg_stream !== '' ? 'live stream . 4k' : 'Ảnh chụp',
        'snapshotAt'        => '',
        'status_summary'    => 'Khoang này đang trống và sẵn sàng để đặt khoang mới.',
        'recommendation'    => '',
        'alertText'         => 'Sẵn sàng',
        'currentStage'      => '',
        'analysisUpdatedAt' => '',
        'ph'                => '--',
        'healthIndex'       => 100,
        'temperature'       => '--',
        'humidity'          => '--',
        'journalText'       => 'Khoang này đang trống.',
        'photoLibraryUrl'   => $photo_library_url,
        'analyzePotCode'    => '',
        'dashboardUrl'      => '#',
        'lightDevice'       => '',
        'growthJourney'     => ['hasGrowthJourney' => false, 'hasStageSignal' => false, 'currentStage' => '', 'activeStagePosition' => 0, 'growthStageTotal' => 0, 'progressWidth' => 0, 'ageDays' => null, 'startedAt' => '', 'stages' => [], 'emptyMessage' => 'Khoang này đang trống.'],
        'isEmpty'           => true,
      ];
    }
    $rack_switcher_payload[] = [
      'key'   => 'rack-' . $_ri,
      'label' => trim((string) ($_rcfg['rack_name'] ?? '')) ?: 'Rack ' . ($_ri + 1),
      'pots'  => $_per_rack_pots,
    ];
    $_pot_offset += $_tray_count;
  }
  if (empty($rack_switcher_payload)) {
    $rack_switcher_payload = [['key' => 'rack-0', 'label' => 'Rack 1', 'pots' => $_flat_pots]];
  }
  // Rebuild switcher_pot_payload from the definitive rack_switcher_payload
  // This ensures that padded empty trays (which might have camera URLs configured by Admin) are included.
  $switcher_pot_payload = [];
  foreach ($rack_switcher_payload as $_rack_sync) {
    foreach ($_rack_sync['pots'] as $_pot_sync) {
      $switcher_pot_payload[] = $_pot_sync;
    }
  }
} // Closes if ($has_active_subscription || !empty($rack_configs))

// Update hero variables from the fully resolved payload
if (!empty($switcher_pot_payload)) {
  $_resolved_hero = null;
  if (!empty($_GET['pot'])) {
      foreach ($switcher_pot_payload as $_pot) {
          if ($_pot['code'] === $_GET['pot']) {
              $_resolved_hero = $_pot;
              break;
          }
      }
  } elseif (!empty($_GET['rack'])) {
      $req_rack = sanitize_text_field((string) wp_unslash($_GET['rack']));
      foreach ($rack_switcher_payload as $_rack_sync) {
          if ($_rack_sync['key'] === $req_rack && !empty($_rack_sync['pots'])) {
              $_resolved_hero = $_rack_sync['pots'][0];
              break;
          }
      }
  }
  if (!$_resolved_hero) {
      $_resolved_hero = $switcher_pot_payload[0];
  }
  if ($_resolved_hero) {
      $hero_pot = $_resolved_hero;
      $hero_pot_code = $_resolved_hero['code'] ?? '';
      $hero_stream_url = $_resolved_hero['streamUrl'] ?? '';
      $hero_media_badge = $_resolved_hero['mediaBadge'] ?? 'Ảnh chụp';
      $hero_name = $_resolved_hero['name'] ?? $hero_name;
      $hero_plant_name = $_resolved_hero['plantName'] ?? 'Khoang trống';
      $hero_status = $_resolved_hero['status'] ?? '';
      $hero_summary = $_resolved_hero['status_summary'] ?? '';
      $hero_ph = $_resolved_hero['ph'] ?? '--';
      $alert_text = $_resolved_hero['alertText'] ?? 'Sẵn sàng';
      $temperature = $_resolved_hero['temperature'] ?? '--';
      $humidity = $_resolved_hero['humidity'] ?? '--';
      $light_label = $_resolved_hero['lightDevice'] ?? '';
      $status_summary = $_resolved_hero['status_summary'] ?? '';
      $recommendation = $_resolved_hero['recommendation'] ?? '';
      $analysis_updated_at = $_resolved_hero['analysisUpdatedAt'] ?? '';
      $analysis_badge_text = $alert_text . ($analysis_updated_at !== '' ? (' · ' . $analysis_updated_at) : '');
      $hero_journal_text = $_resolved_hero['journalText'] ?? 'Chưa có nhật ký canh tác cho khoang này.';
      $hero_growth_journey = $_resolved_hero['growthJourney'] ?? [];
      $hero_image = $_resolved_hero['image'] ?? $hero_image;

      $hero_light_device = trim((string) ($_resolved_hero['lightDevice'] ?? ''));
      if ($hero_light_device === '' && !empty($rack_slots)) {
        foreach ($rack_slots as $slot) {
          if (trim((string)($slot['pot_code'] ?? '')) === $hero_pot_code) {
            $hero_light_device = trim((string)($slot['control_channel'] ?? ''));
            break;
          }
        }
      }
      $hero_has_light = $hero_light_device !== '';
      $hero_has_pump = trim((string) ($hero_pot['pump'] ?? '')) !== '';
      $hero_has_nutrient = true;
      $hero_has_mist = false;
      $hero_note_text = trim((string) ($pot_notes[$hero_pot_code]['note_text'] ?? ''));
      $hero_journal_text = $hero_note_text !== '' ? $hero_note_text : 'Chưa có nhật ký canh tác cho khoang này.';
  }
}
$rent_rack_url = home_url('/portal/kho-nong-cu-2/');
?>
<section class="d2-app">
  <style>
    .site-header,
    .site-footer {
      display: none !important
    }

    .d2-app {
      --bg: #121411;
      --bg-2: #1a1c19;
      --panel: #292b27;
      --panel-2: #333532;
      --text: #e3e3de;
      --muted: #bdcac0;
      --primary: #6fdba8;
      --primary-dark: #31a375;
      --yellow: #ffe16d;
      --amber: #ffb68c;
      --line: #3e4942;
      --glass: rgba(51, 53, 50, .4);
      font-family: 'Manrope', system-ui, sans-serif;
      background: #121411;
      color: var(--text);
      min-height: 100vh;
      margin: -32px calc(50% - 50vw) 0;
      padding: 0;
      overflow-x: hidden;
      overflow-y: visible
    }

    .d2-app .font-serif {
      font-family: 'Noto Serif', Georgia, serif
    }

    .d2-shell {
      display: grid;
      grid-template-columns: 272px minmax(0, 1fr);
      min-height: 100vh
    }

    .d2-side {
      background: linear-gradient(180deg, rgba(6, 27, 14, .78), rgba(0, 42, 32, .84));
      backdrop-filter: blur(28px) saturate(120%);
      padding: 24px 16px 28px;
      border-right: 1px solid rgba(255, 255, 255, .08);
      box-shadow: 0 30px 80px rgba(0, 0, 0, .34), inset -1px 0 0 rgba(255, 255, 255, .05)
    }

    .d2-brand {
      font-family: 'Noto Serif', Georgia, serif;
      font-size: 34px;
      font-style: italic;
      color: var(--primary);
      margin: 2px 12px 28px
    }

    .d2-level {
      display: flex;
      gap: 14px;
      align-items: center;
      margin: 0 8px 14px;
      padding: 12px 14px;
      border-radius: 24px;
      background: rgba(111, 219, 168, .08);
      border: 1px solid rgba(255, 255, 255, .06);
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, .08), 0 12px 28px rgba(0, 0, 0, .12)
    }

    .d2-level-badge {
      width: 44px;
      height: 44px;
      border-radius: 16px;
      background: rgba(111, 219, 168, .14);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--primary);
      font-weight: 800
    }

    .d2-upgrade {
      display: flex;
      align-items: center;
      gap: 14px;
      margin: 0 6px 8px;
      padding: 16px 18px;
      border-radius: 20px;
      background: transparent;
      color: rgba(227, 227, 222, .46);
      font-weight: 600;
      text-align: left;
      box-shadow: none;
      transition: color .18s ease, background .18s ease, box-shadow .18s ease, text-shadow .18s ease
    }

    .d2-upgrade:hover {
      color: var(--primary);
      text-shadow: 0 0 12px rgba(111, 219, 168, .28)
    }

    .d2-icon-btn,
    .d2-inline-control {
      transition: transform .18s ease, box-shadow .18s ease, background .18s ease, border-color .18s ease, filter .18s ease
    }

    .d2-icon-btn:hover,
    .d2-icon-btn:focus-visible {
      transform: translateY(-2px);
      box-shadow: 0 14px 28px rgba(0, 0, 0, .22);
      filter: brightness(1.06)
    }

    .d2-icon-btn:focus-visible {
      outline: 2px solid rgba(111, 219, 168, .45);
      outline-offset: 2px
    }

    .d2-inline-control:hover:not(.is-disabled):not(:disabled),
    .d2-inline-control:focus-visible:not(.is-disabled):not(:disabled) {
      transform: translateY(-2px);
      box-shadow: 0 16px 30px rgba(0, 0, 0, .18);
      border-color: rgba(111, 219, 168, .28);
      filter: brightness(1.03)
    }

    .d2-inline-control:focus-visible {
      outline: 2px solid rgba(111, 219, 168, .45);
      outline-offset: 2px
    }

    .d2-icon-btn:disabled,
    .d2-inline-control:disabled {
      transform: none !important;
      box-shadow: none !important;
      filter: none !important
    }

    .d2-upgrade.active {
      background: rgba(6, 77, 58, .7);
      color: var(--primary);
      box-shadow: inset 0 0 0 1px rgba(111, 219, 168, .08)
    }

    .d2-nav {
      display: grid;
      gap: 8px;
      padding: 0 6px
    }

    .d2-nav a {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 16px 18px;
      border-radius: 20px;
      color: rgba(227, 227, 222, .46);
      font-weight: 600;
      transition: color .18s ease, background .18s ease, box-shadow .18s ease, text-shadow .18s ease
    }

    .d2-nav .bottom-nav-short {
      display: none
    }

    .d2-nav a:hover {
      color: var(--primary);
      text-shadow: 0 0 12px rgba(111, 219, 168, .28)
    }

    .d2-nav a.active {
      background: rgba(6, 77, 58, .7);
      color: var(--primary);
      box-shadow: inset 0 0 0 1px rgba(111, 219, 168, .08)
    }

    .d2-side-footer {
      margin-top: auto;
      padding: 24px 16px 0;
      color: rgba(227, 227, 222, .5);
      display: grid;
      gap: 10px;
      font-size: 14px
    }

    .d2-main {
      padding: 30px 28px 40px;
      background: radial-gradient(circle at top right, rgba(111, 219, 168, .06), transparent 24%), radial-gradient(circle at top left, rgba(255, 225, 109, .05), transparent 22%), var(--bg)
    }

    .d2-top {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 16px;
      padding: 8px 4px 18px;
      border-bottom: 1px solid rgba(111, 219, 168, .08);
      margin-bottom: 26px;
      position: relative;
      z-index: 120;
      overflow: visible
    }

    .d2-garden-rename {
      display: flex;
      align-items: center;
      gap: 10px;
      min-width: 0;
      max-width: 40vw
    }

    .d2-garden-name {
      font-family: 'Noto Serif', Georgia, serif;
      font-size: 30px;
      line-height: 1.05;
      color: var(--primary);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis
    }

    .d2-garden-edit {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 36px;
      height: 36px;
      border: none;
      border-radius: 999px;
      background: rgba(111, 219, 168, .12);
      color: var(--primary);
      cursor: pointer
    }

    .d2-garden-input {
      min-width: 260px;
      max-width: 100%;
      padding: 10px 14px;
      border-radius: 14px;
      border: 1px solid rgba(111, 219, 168, .16);
      background: rgba(51, 53, 50, .68);
      color: var(--text)
    }

    .d2-garden-status {
      font-size: 12px;
      color: rgba(227, 227, 222, .56)
    }

    .d2-top-links {
      display: flex;
      gap: 28px;
      min-width: 0;
      flex: 1 1 auto;
      overflow: auto;
      scrollbar-width: none
    }

    .d2-top-links::-webkit-scrollbar {
      display: none
    }

    .d2-top-links a {
      color: rgba(227, 227, 222, .56);
      padding-bottom: 8px;
      transition: color .18s ease, border-color .18s ease, text-shadow .18s ease
    }

    .d2-top-links a:hover {
      color: var(--primary);
      text-shadow: 0 0 12px rgba(111, 219, 168, .28)
    }

    .d2-top-links a.active {
      color: var(--primary);
      border-bottom: 2px solid var(--primary)
    }

    .d2-select {
      background: rgba(51, 53, 50, .44);
      color: var(--text);
      border: 1px solid rgba(255, 255, 255, .08);
      border-radius: 14px;
      padding: 10px 14px;
      min-width: 220px;
      backdrop-filter: blur(18px);
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, .08), 0 14px 28px rgba(0, 0, 0, .12)
    }

    .d2-top-actions {
      display: flex;
      flex: 0 0 auto;
      align-items: center;
      justify-content: center;
      gap: 10px;
      padding: 8px 10px;
      border-radius: 20px;
      border: 1px solid rgba(111, 219, 168, .14);
      background: rgba(18, 20, 17, .58);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      box-shadow: 0 14px 30px rgba(0, 0, 0, .14);
      position: relative;
      z-index: 130;
      overflow: visible
    }

    .d2-profile-trigger {
      width: 42px;
      height: 42px;
      border-radius: 999px;
      border: 2px solid var(--primary);
      display: grid;
      place-items: center;
      background: #1a1c19;
      cursor: pointer;
      padding: 0;
      overflow: hidden;
      box-sizing: border-box;
      color: #fff;
      font-weight: bold;
    }

    .d2-profile-popup {
      position: absolute;
      top: calc(100% + 14px);
      right: 0;
      min-width: 240px;
      background: rgba(26, 28, 25, .98);
      border: 1px solid rgba(255, 255, 255, .06);
      border-radius: 22px;
      padding: 10px;
      box-shadow: 0 24px 52px rgba(0, 0, 0, .28);
      z-index: 99999
    }

    .d2-profile-popup[hidden] {
      display: none
    }

    .d2-profile-popup a {
      display: block;
      padding: 12px 14px;
      border-radius: 14px;
      color: #e3e3de
    }

    .d2-profile-popup a:hover {
      background: rgba(51, 53, 50, .56)
    }

    .d2-head {
      margin-bottom: 20px
    }

    .d2-head h1 {
      font-family: 'Noto Serif', Georgia, serif;
      font-style: italic;
      font-size: 68px;
      line-height: 1.02;
      color: var(--primary);
      margin: 0 0 6px;
      letter-spacing: -.04em
    }

    .d2-head p {
      margin: 0;
      color: var(--muted)
    }

    .d2-grid {
      display: grid;
      grid-template-columns: minmax(0, 1.32fr) 220px;
      gap: 20px;
      align-items: start;
      overflow: visible
    }

    .d2-live {
      display: grid;
      gap: 22px
    }

    .d2-growth-card {
      padding: 24px 26px 22px;
      border-radius: 32px;
      background: linear-gradient(180deg, rgba(26, 28, 25, .96), rgba(22, 24, 21, .94));
      border: 1px solid rgba(255, 255, 255, .06);
      box-shadow: 0 24px 60px rgba(0, 0, 0, .22), inset 0 1px 0 rgba(255, 255, 255, .03)
    }

    .d2-growth-head {
      display: flex;
      justify-content: flex-end;
      align-items: center;
      gap: 14px;
      margin-bottom: 16px;
      min-height: 18px
    }

    .d2-growth-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 14px;
      border-radius: 999px;
      background: linear-gradient(180deg, rgba(35, 68, 52, .72), rgba(23, 46, 35, .8));
      border: 1px solid rgba(111, 219, 168, .16);
      font-size: 12px;
      font-weight: 700;
      letter-spacing: .01em;
      color: #deece3;
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, .04), 0 10px 24px rgba(0, 0, 0, .14)
    }

    .d2-growth-badge strong {
      font-weight: 800;
      color: #f1fbf5
    }

    .d2-growth-badge-dot {
      width: 8px;
      height: 8px;
      border-radius: 999px;
      background: var(--primary);
      box-shadow: 0 0 10px rgba(111, 219, 168, .45)
    }

    .d2-growth-track {
      position: relative;
      display: flex;
      justify-content: space-between;
      gap: 10px;
      padding-top: 10px
    }

    .d2-growth-track::before {
      content: "";
      position: absolute;
      top: 43px;
      left: 0;
      right: 0;
      height: 2px;
      border-radius: 999px;
      background: linear-gradient(90deg, rgba(255, 255, 255, .08), rgba(255, 255, 255, .14), rgba(255, 255, 255, .08));
      opacity: .9
    }

    .d2-growth-progress {
      position: absolute;
      top: 43px;
      left: 0;
      height: 2px;
      border-radius: 999px;
      background: linear-gradient(90deg, rgba(80, 201, 139, .92) 0%, rgba(124, 232, 182, .95) 55%, rgba(166, 255, 219, .82) 100%);
      box-shadow: 0 0 10px rgba(111, 219, 168, .22), 0 0 22px rgba(111, 219, 168, .12)
    }

    .d2-growth-step {
      position: relative;
      z-index: 1;
      flex: 1 1 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      color: rgba(227, 227, 222, .28);
      padding: 0 10px;
      max-width: 210px;
      transition: opacity .22s ease, transform .22s ease, filter .22s ease
    }

    .d2-growth-step.is-past {
      color: rgba(220, 235, 226, .72);
      opacity: .76
    }

    .d2-growth-step.is-active {
      color: #effaf3;
      opacity: 1;
      z-index: 2
    }

    .d2-growth-step.is-future {
      opacity: .24;
      filter: saturate(.72)
    }

    .d2-growth-icon {
      width: 68px;
      height: 68px;
      border-radius: 999px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 12px;
      background: #242723;
      border: 7px solid #111310;
      box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .04);
      font-size: 28px;
      line-height: 1;
      transition: box-shadow .22s ease, transform .22s ease, border-color .22s ease, opacity .22s ease, filter .22s ease
    }

    .d2-growth-step.is-past .d2-growth-icon {
      background: rgba(111, 219, 168, .82);
      box-shadow: 0 8px 18px rgba(0, 0, 0, .12);
      filter: saturate(.92)
    }

    .d2-growth-step.is-active .d2-growth-icon {
      background: radial-gradient(circle at 50% 45%, rgba(31, 42, 34, .98), rgba(18, 20, 17, 1));
      border-color: #0f130f;
      box-shadow: 0 0 0 1px rgba(111, 219, 168, .62), 0 0 20px rgba(111, 219, 168, .24), 0 0 42px rgba(111, 219, 168, .24), inset 0 0 0 2px rgba(111, 219, 168, .98);
      transform: scale(1.08)
    }

    .d2-growth-step.is-active::after {
      content: "";
      position: absolute;
      top: -6px;
      left: 50%;
      transform: translateX(-50%);
      width: 94px;
      height: 94px;
      border-radius: 999px;
      border: 1px solid rgba(111, 219, 168, .2);
      box-shadow: 0 0 34px rgba(111, 219, 168, .12);
      pointer-events: none
    }

    .d2-growth-icon-emoji {
      display: block;
      transform: translateY(1px)
    }

    .d2-growth-step.is-active .d2-growth-icon-emoji {
      transform: translateY(0) scale(1.12);
      filter: drop-shadow(0 0 10px rgba(111, 219, 168, .18))
    }

    .d2-growth-icon-check {
      position: absolute;
      right: -2px;
      bottom: -2px;
      width: 24px;
      height: 24px;
      border-radius: 999px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(180deg, #58d68d, #2fbf71);
      border: 2px solid #111310;
      color: #fff;
      font-size: 13px;
      font-weight: 900;
      box-shadow: 0 10px 18px rgba(0, 0, 0, .18), 0 0 14px rgba(111, 219, 168, .24)
    }

    .d2-growth-step-name {
      font-size: 14px;
      font-weight: 700;
      line-height: 1.28;
      max-width: 150px;
      word-break: break-word;
      text-wrap: balance;
      transition: color .22s ease, opacity .22s ease, text-shadow .22s ease
    }

    .d2-growth-step.is-past .d2-growth-step-name {
      color: rgba(227, 227, 222, .72)
    }

    .d2-growth-step.is-future .d2-growth-step-name {
      font-weight: 600;
      color: rgba(227, 227, 222, .14)
    }

    .d2-growth-step.is-active .d2-growth-step-name {
      color: #8cf0be;
      text-shadow: 0 0 14px rgba(111, 219, 168, .22)
    }

    .d2-growth-empty {
      padding: 20px 22px;
      border-radius: 24px;
      background: rgba(255, 255, 255, .04);
      border: 1px dashed rgba(255, 255, 255, .08);
      color: var(--muted)
    }

    .d2-frame {
      position: relative;
      border-radius: 34px;
      overflow: hidden;
      aspect-ratio: 16/9;
      min-height: auto;
      background: #0b0d0b;
      box-shadow: 0 36px 90px rgba(0, 0, 0, .36), 0 0 36px rgba(111, 219, 168, .06);
      border: 1px solid rgba(255, 255, 255, .07)
    }

    .d2-hero-iframe {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      border: none;
      border-radius: inherit;
      z-index: 1;
    }

    .d2-frame img,
    .d2-frame video {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block
    }

    .d2-frame::after {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(180deg, rgba(0, 0, 0, .26), rgba(0, 0, 0, .10) 30%, rgba(0, 0, 0, .46) 100%)
    }

    .d2-frame:fullscreen {
      border-radius: 0;
      border: none;
      background: #000;
    }

    .d2-frame:fullscreen img,
    .d2-frame:fullscreen video {
      object-fit: contain;
    }

    .d2-fullscreen-btn {
      position: absolute;
      top: 16px;
      right: 16px;
      z-index: 10;
      width: 44px;
      height: 44px;
      border-radius: 12px;
      background: rgba(11, 13, 11, .42);
      backdrop-filter: blur(12px);
      border: 1px solid rgba(255, 255, 255, .15);
      color: #fff;
      display: grid;
      place-items: center;
      font-size: 24px;
      cursor: pointer;
      transition: all .2s ease;
      line-height: 1;
    }

    .d2-fullscreen-btn:hover {
      background: rgba(11, 13, 11, .7);
      transform: scale(1.05);
    }

    .d2-no-rack-hint {
      position: absolute;
      bottom: 64px;
      left: 16px;
      right: 16px;
      z-index: 10;
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 10px 12px;
      background: rgba(18, 20, 17, .88);
      border: 1px solid #31a375;
      border-radius: 10px;
      backdrop-filter: blur(8px)
    }

    .d2-no-rack-hint.is-inline {
      position: relative;
      bottom: auto;
      left: auto;
      right: auto;
      margin: -24px auto 16px 16px;
      width: calc(100% - 32px);
      box-sizing: border-box;
      z-index: 10;
    }

    .d2-no-rack-hint-text {
      flex: 1;
      margin: 0;
      font-size: 12px;
      color: #e3e3de;
      line-height: 1.3;
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .d2-no-rack-hint-cta {
      display: inline-block;
      padding: 6px 10px;
      background: #31a375;
      color: #062013;
      font-size: 11px;
      font-weight: 800;
      border-radius: 6px;
      text-decoration: none;
      white-space: nowrap
    }

    .d2-no-rack-hint-close {
      background: none;
      border: none;
      color: #bdcac0;
      font-size: 20px;
      line-height: 1;
      cursor: pointer;
      padding: 0 2px;
      flex-shrink: 0
    }

    .d2-pill {
      position: absolute;
      z-index: 2;
      display: inline-flex;
      align-items: center;
      gap: 10px;
      padding: 10px 16px;
      border-radius: 999px;
      background: rgba(51, 53, 50, .34);
      backdrop-filter: blur(22px) saturate(120%);
      border: 1px solid rgba(255, 255, 255, .10);
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, .10), 0 14px 28px rgba(0, 0, 0, .18), 0 0 18px rgba(111, 219, 168, .06);
      font-weight: 800;
      font-size: 12px;
      letter-spacing: .12em;
      text-transform: uppercase
    }

    .d2-live-tag {
      top: 22px;
      left: 22px
    }

    .d2-dot {
      width: 8px;
      height: 8px;
      border-radius: 999px;
      background: #ff5b57;
      box-shadow: 0 0 0 6px rgba(255, 91, 87, .14)
    }

    .d2-vitals {
      display: flex;
      gap: 14px
    }

    .d2-vital {
      width: 52px;
      height: 52px;
      border-radius: 999px;
      background: rgba(51, 53, 50, .34);
      backdrop-filter: blur(22px) saturate(120%);
      display: grid;
      place-items: center;
      text-align: center;
      box-shadow: 0 18px 38px rgba(0, 0, 0, .22), 0 0 18px rgba(111, 219, 168, .05);
      border: 1px solid rgba(255, 255, 255, .10);
      flex-shrink: 0;
      font-size: 16px;
    }

    .d2-vital .value {
      font-size: 11px;
      font-weight: 800;
      margin-top: 1px;
      line-height: 1
    }

    .d2-bottom {
      position: absolute;
      left: 16px;
      right: 16px;
      bottom: 12px;
      z-index: 2;
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      gap: 12px
    }

    .d2-info {
      max-width: 560px;
      border-radius: 32px;
      background: rgba(51, 53, 50, .32);
      backdrop-filter: blur(24px) saturate(120%);
      padding: 26px 28px;
      box-shadow: 0 20px 44px rgba(0, 0, 0, .22), 0 0 24px rgba(111, 219, 168, .05);
      border: 1px solid rgba(255, 255, 255, .10);
      position: relative
    }

    .d2-info::after {
      content: '';
      position: absolute;
      inset: 1px;
      border-radius: 31px;
      pointer-events: none;
      background: linear-gradient(180deg, rgba(255, 255, 255, .06), transparent 26%)
    }

    .d2-pot-rename {
      display: flex;
      align-items: center;
      gap: 10px;
      flex-wrap: wrap;
      min-width: 0
    }

    .d2-pot-name {
      font-family: 'Noto Serif', Georgia, serif;
      font-size: 34px;
      line-height: 1.05;
      color: var(--primary);
      margin: 0;
      min-width: 0
    }

    .d2-pot-edit {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 34px;
      height: 34px;
      border: none;
      border-radius: 999px;
      background: rgba(111, 219, 168, .12);
      color: var(--primary);
      cursor: pointer
    }

    .d2-pot-input {
      min-width: 260px;
      max-width: 100%;
      padding: 10px 14px;
      border-radius: 14px;
      border: 1px solid rgba(111, 219, 168, .16);
      background: rgba(51, 53, 50, .68);
      color: var(--text)
    }

    .d2-pot-status {
      font-size: 12px;
      color: rgba(227, 227, 222, .56);
      width: 100%
    }

    .d2-info h3 {
      margin: 0 0 10px;
      font-size: 34px;
      line-height: 1.05;
      font-family: 'Noto Serif', Georgia, serif;
      color: var(--primary)
    }

    .d2-bar {
      height: 6px;
      background: rgba(111, 219, 168, .16);
      border-radius: 999px;
      overflow: hidden;
      margin-top: 12px
    }

    .d2-bar>span {
      display: block;
      height: 100%;
      width: 88%;
      background: var(--primary)
    }

    .d2-info small {
      display: block;
      margin-top: 10px;
      color: rgba(227, 227, 222, .55);
      font-size: 10px;
      letter-spacing: .16em;
      text-transform: uppercase
    }

    .d2-actions {
      display: flex;
      gap: 14px;
      flex-wrap: nowrap;
      justify-content: flex-end;
      overflow-x: auto;
      max-width: 100%;
      padding-bottom: 8px;
      scrollbar-width: thin;
      scrollbar-color: rgba(255, 255, 255, 0.25) transparent;
    }
    .d2-actions::-webkit-scrollbar {
      height: 4px;
    }
    .d2-actions::-webkit-scrollbar-track {
      background: transparent;
    }
    .d2-actions::-webkit-scrollbar-thumb {
      background: rgba(255, 255, 255, 0.25);
      border-radius: 4px;
    }

    .d2-icon-btn {
      width: 52px;
      height: 52px;
      border-radius: 999px;
      background: rgba(51, 53, 50, .32);
      backdrop-filter: blur(22px) saturate(120%);
      display: grid;
      place-items: center;
      color: var(--primary);
      font-size: 20px;
      border: 1px solid rgba(255, 255, 255, .10);
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, .08), 0 16px 34px rgba(0, 0, 0, .18), 0 0 18px rgba(111, 219, 168, .05);
      flex-shrink: 0;
    }

    .d2-real-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      padding: 14px 18px;
      border-radius: 18px;
      background: linear-gradient(135deg, #31a375, #6fdba8);
      color: #062013;
      font-weight: 800
    }

    .d2-ghost-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      padding: 14px 18px;
      border-radius: 18px;
      background: rgba(51, 53, 50, .42);
      color: var(--text);
      font-weight: 700;
      border: 1px solid rgba(111, 219, 168, .1)
    }

    .d2-switcher {
      display: flex;
      align-items: center;
      gap: 18px;
      padding: 20px 24px;
      border-radius: 34px;
      background: rgba(41, 43, 39, .56);
      backdrop-filter: blur(22px) saturate(120%);
      box-shadow: 0 20px 44px rgba(0, 0, 0, .20), 0 0 20px rgba(111, 219, 168, .04);
      border: 1px solid rgba(255, 255, 255, .08);
      overflow: auto
    }

    .d2-switch {
      display: grid;
      justify-items: center;
      gap: 8px;
      min-width: 82px
    }

    .d2-thumb {
      width: 72px;
      height: 72px;
      border-radius: 22px;
      overflow: hidden;
      border: 2px solid transparent
    }

    .d2-switch.active .d2-thumb {
      border-color: var(--primary);
      box-shadow: 0 0 0 4px rgba(111, 219, 168, .10)
    }

    .d2-thumb img {
      width: 100%;
      height: 100%;
      object-fit: cover
    }

    .d2-switch span {
      font-size: 10px;
      letter-spacing: .08em;
      text-transform: uppercase;
      color: rgba(227, 227, 222, .72);
      font-weight: 800;
      text-align: center;
      display: flex;
      align-items: center;
      gap: 4px;
      justify-content: center
    }

    .d2-cam-dot {
      display: inline-block;
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: var(--primary);
      flex-shrink: 0;
      animation: d2-cam-pulse 2s ease-in-out infinite
    }

    @keyframes d2-cam-pulse {
      0%, 100% { opacity: 1 }
      50% { opacity: .35 }
    }

    .d2-pot-journal {
      margin-top: 18px;
      padding: 24px 26px;
      border-radius: 30px;
      background: rgba(41, 43, 39, .52);
      backdrop-filter: blur(22px) saturate(120%);
      box-shadow: 0 20px 44px rgba(0, 0, 0, .18);
      border: 1px solid rgba(255, 255, 255, .08);
      width: 100%
    }

    .d2-pot-journal-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      margin-bottom: 12px
    }

    .d2-pot-journal .label {
      display: inline-flex;
      padding: 6px 10px;
      border-radius: 999px;
      background: rgba(111, 219, 168, .08);
      color: var(--primary);
      font-size: 10px;
      letter-spacing: .12em;
      text-transform: uppercase;
      font-weight: 800;
      margin-bottom: 0
    }

    .d2-pot-journal-edit {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 36px;
      height: 36px;
      border: none;
      border-radius: 999px;
      background: rgba(111, 219, 168, .12);
      color: var(--primary);
      cursor: pointer;
      font-size: 16px
    }

    .d2-pot-journal-text {
      margin: 0;
      color: rgba(227, 227, 222, .78);
      line-height: 1.8;
      white-space: pre-line
    }

    .d2-pot-journal-input {
      width: 100%;
      min-height: 180px;
      padding: 14px 16px;
      border-radius: 18px;
      border: 1px solid rgba(111, 219, 168, .16);
      background: rgba(51, 53, 50, .68);
      color: var(--text);
      font: inherit;
      line-height: 1.8;
      resize: vertical
    }

    .d2-pot-journal-status {
      margin-top: 10px;
      font-size: 12px;
      color: rgba(227, 227, 222, .56)
    }

    .d2-pot-journal-status.is-saving {
      color: #ffcf8c
    }

    .d2-pot-journal-status.is-success {
      color: var(--primary)
    }

    .d2-pot-journal-status.is-error {
      color: #ff9d9d
    }

    .d2-capture-toast {
      position: fixed;
      right: 18px;
      bottom: 18px;
      z-index: 80;
      max-width: min(92vw, 360px);
      padding: 12px 14px;
      border-radius: 16px;
      background: rgba(26, 28, 25, .96);
      border: 1px solid rgba(111, 219, 168, .14);
      box-shadow: 0 18px 40px rgba(0, 0, 0, .28);
      color: var(--text);
      font-size: 13px;
      line-height: 1.5
    }

    .d2-capture-toast[hidden] {
      display: none
    }

    .d2-capture-toast.is-success {
      border-color: rgba(111, 219, 168, .28);
      color: var(--primary)
    }

    .d2-capture-toast.is-error {
      border-color: rgba(255, 157, 157, .28);
      color: #ffb3b3
    }
    
    .d2-cam-hint {
      bottom: auto;
      top: 16px;
      left: auto;
      right: auto;
      max-width: 380px;
    }
    
    .d2-cam-hint[hidden], .d2-cam-hint[style*="display: none"] {
      display: none !important;
    }
    
    .d2-upsell-content p {
      margin: 0 0 24px;
      font-size: 14px;
      color: rgba(227, 227, 222, 0.7);
      line-height: 1.6;
    }
    
    .d2-upsell-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 12px 28px;
      background: linear-gradient(135deg, #31a375, #6fdba8);
      color: #062013;
      font-weight: 800;
      border-radius: 14px;
      text-decoration: none;
      font-size: 15px;
      transition: filter 0.2s;
    }
    
    .d2-upsell-btn:hover {
      filter: brightness(1.1);
      color: #062013;
    }

    .d2-sidecards {
      display: grid;
      gap: 20px;
      align-content: start;
      overflow: visible
    }

    .d2-card {
      background: rgba(41, 43, 39, .50);
      backdrop-filter: blur(24px) saturate(120%);
      border-radius: 36px;
      padding: 28px 30px;
      box-shadow: 0 28px 56px rgba(0, 0, 0, .22), 0 0 24px rgba(111, 219, 168, .04);
      border: 1px solid rgba(255, 255, 255, .08);
      position: relative
    }

    .d2-card::after {
      content: '';
      position: absolute;
      inset: 1px;
      border-radius: 35px;
      pointer-events: none;
      background: linear-gradient(180deg, rgba(255, 255, 255, .05), transparent 24%)
    }

    .d2-card h3 {
      margin: 0 0 22px;
      font-family: 'Noto Serif', Georgia, serif;
      font-size: 24px
    }

    .d2-ai-strip {
      margin-top: 22px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      align-items: stretch;
      width: 100%
    }

    .d2-ai-strip .d2-alert {
      margin: 0;
      height: 100%
    }

    .d2-trays-card {
      padding: 18px 16px 20px;
      border-radius: 28px;
      max-width: 164px;
      justify-self: end;
      align-self: start;
      position: relative;
      background: rgba(41, 43, 39, .62);
      backdrop-filter: blur(26px) saturate(120%);
      border: 1px solid rgba(255, 255, 255, .06);
      box-shadow: 0 24px 48px rgba(0, 0, 0, .22), 0 0 18px rgba(111, 219, 168, .03);
      z-index: 6;
      transition: transform .22s ease, box-shadow .22s ease, top .22s ease
    }

    .d2-trays-card.is-floating {
      position: fixed;
      top: 38px;
      right: 28px;
      transform: scale(.96);
      box-shadow: 0 34px 80px rgba(0, 0, 0, .34), 0 0 28px rgba(111, 219, 168, .08);
      border-color: rgba(255, 255, 255, .10)
    }

    .d2-trays-card-placeholder {
      display: none
    }

    .d2-trays-card.is-floating+.d2-trays-card-placeholder {
      display: block;
      width: 180px;
      min-height: 520px
    }

    .d2-trays-card::after {
      display: none
    }

    .d2-rack-link {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-height: 32px;
      padding: 0 12px;
      border-radius: 999px;
      color: var(--primary);
      font-size: 13px;
      font-weight: 800;
      letter-spacing: .04em;
      background: rgba(111, 219, 168, .08);
      margin-bottom: 8px
    }

    .d2-rack-dropdown {
      position: relative;
      margin-bottom: 16px;
      padding-bottom: 16px;
      border-bottom: 1px solid rgba(255, 255, 255, .06)
    }

    .d2-rack-dropdown-trigger {
      display: flex;
      align-items: center;
      gap: 8px;
      width: 100%;
      padding: 9px 13px;
      background: rgba(111, 219, 168, .08);
      border: 1px solid rgba(111, 219, 168, .16);
      border-radius: 12px;
      color: #fff;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
      transition: background .18s, border-color .18s
    }

    .d2-rack-dropdown-trigger:hover {
      background: rgba(111, 219, 168, .13);
      border-color: rgba(111, 219, 168, .28)
    }

    .d2-rack-dropdown-trigger svg {
      margin-left: auto;
      opacity: .6;
      flex-shrink: 0;
      transition: transform .18s
    }

    .d2-rack-dropdown.is-open .d2-rack-dropdown-trigger svg {
      transform: rotate(180deg)
    }

    .d2-rack-dropdown-menu {
      display: none;
      position: absolute;
      top: calc(100% - 12px);
      left: 0;
      right: 0;
      background: #151e2d;
      border: 1px solid rgba(255, 255, 255, .09);
      border-radius: 12px;
      overflow: hidden;
      z-index: 120;
      box-shadow: 0 12px 32px rgba(0, 0, 0, .5)
    }

    .d2-rack-dropdown.is-open .d2-rack-dropdown-menu {
      display: block
    }

    .d2-rack-dropdown-add {
      display: flex;
      align-items: center;
      gap: 6px;
      padding: 10px 14px;
      color: var(--primary);
      font-size: 12px;
      font-weight: 800;
      letter-spacing: .04em;
      border-bottom: 1px solid rgba(255, 255, 255, .06);
      text-decoration: none
    }

    .d2-rack-dropdown-add:hover {
      background: rgba(111, 219, 168, .07)
    }

    .d2-rack-item {
      display: flex;
      align-items: center;
      width: 100%;
      padding: 10px 14px;
      background: transparent;
      border: none;
      color: rgba(227, 227, 222, .7);
      font-size: 13px;
      font-weight: 600;
      text-align: left;
      cursor: pointer;
      transition: background .14s, color .14s
    }

    .d2-rack-item:hover {
      background: rgba(255, 255, 255, .05);
      color: #fff
    }

    .d2-rack-item.is-active {
      color: var(--primary);
      background: rgba(111, 219, 168, .08);
      font-weight: 700
    }

    .d2-trays-vertical {
      display: grid;
      grid-template-columns: 1fr;
      gap: 14px;
      padding: 0;
      background: transparent;
      box-shadow: none;
      border-radius: 0;
      overflow: visible;
      border: none;
      outline: none
    }

    .d2-trays-vertical .d2-switch {
      justify-items: center;
      display: grid;
      align-items: center;
      row-gap: 0;
      min-width: 0;
      padding: 8px 4px;
      border-radius: 24px;
      transition: background .18s ease, box-shadow .18s ease, transform .18s ease
    }

    .d2-trays-vertical .d2-switch:hover {
      background: rgba(255, 255, 255, .03)
    }

    .d2-trays-vertical .d2-switch span {
      display: none
    }

    .d2-trays-vertical .d2-thumb {
      width: 92px;
      height: 92px;
      border-radius: 26px;
      background: transparent;
      box-shadow: none;
      position: relative;
      overflow: hidden;
      border: 1px solid rgba(255, 255, 255, .06)
    }

    .d2-trays-vertical .d2-thumb img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block
    }

    .d2-trays-vertical .d2-switch.active {
      background: linear-gradient(180deg, rgba(111, 219, 168, .10), rgba(111, 219, 168, .05));
      box-shadow: inset 0 0 0 1px rgba(111, 219, 168, .12), 0 10px 22px rgba(0, 0, 0, .12);
      transform: translateY(-1px)
    }

    .d2-trays-vertical .d2-switch.active .d2-thumb {
      border-color: rgba(111, 219, 168, .78);
      box-shadow: 0 0 0 4px rgba(111, 219, 168, .14), 0 0 18px rgba(111, 219, 168, .16), 0 14px 28px rgba(0, 0, 0, .18), inset 0 0 0 1px rgba(255, 255, 255, .10)
    }

    .d2-trays-vertical .d2-switch.active .d2-thumb::after {
      content: '';
      position: absolute;
      inset: 0;
      border-radius: inherit;
      box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .14);
      pointer-events: none
    }

    .d2-tray-empty {
      width: 100%;
      height: 100%;
      display: grid;
      place-items: center;
      position: relative;
      border-radius: inherit;
      background: radial-gradient(circle at 50% 22%, rgba(111, 219, 168, .16), transparent 34%), linear-gradient(180deg, rgba(20, 30, 24, .96), rgba(15, 22, 18, .98));
      color: rgba(227, 227, 222, .86);
      overflow: hidden
    }

    .d2-tray-empty::before {
      content: '';
      position: absolute;
      left: 18px;
      right: 18px;
      top: 24px;
      height: 16px;
      border-radius: 999px;
      background: rgba(111, 219, 168, .14);
      box-shadow: 0 18px 0 rgba(111, 219, 168, .07), 0 36px 0 rgba(111, 219, 168, .04)
    }

    .d2-tray-empty::after {
      content: '';
      position: absolute;
      left: 20px;
      right: 20px;
      bottom: 18px;
      height: 28px;
      border-radius: 16px;
      background: linear-gradient(180deg, rgba(111, 219, 168, .12), rgba(111, 219, 168, .03));
      border: 1px solid rgba(111, 219, 168, .08)
    }

    .d2-switch.is-empty .d2-thumb::before {
      content: '+';
      position: absolute;
      left: 50%;
      top: 50%;
      transform: translate(-50%, -66%);
      width: 34px;
      height: 34px;
      border-radius: 999px;
      display: grid;
      place-items: center;
      background: rgba(111, 219, 168, .18);
      border: 1px solid rgba(111, 219, 168, .28);
      color: #f3fbf6;
      font-size: 24px;
      line-height: 1;
      font-weight: 600;
      box-shadow: 0 10px 22px rgba(0, 0, 0, .18);
      z-index: 3;
      pointer-events: none
    }

    .d2-tray-empty-mark {
      position: relative;
      z-index: 1;
      display: grid;
      gap: 6px;
      justify-items: center;
      text-transform: uppercase;
      letter-spacing: .08em;
      padding-top: 24px
    }

    .d2-tray-empty-mark strong {
      font-size: 11px;
      color: #eef7f1
    }

    .d2-tray-empty-mark small {
      font-size: 10px;
      color: rgba(227, 227, 222, .58)
    }

    .d2-alert {
      border-radius: 26px;
      padding: 18px 18px 18px 20px;
      margin-bottom: 18px;
      position: relative;
      overflow: hidden;
      border: 1px solid rgba(255, 255, 255, .08);
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, .05), 0 12px 26px rgba(0, 0, 0, .14)
    }

    .d2-alert::before {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      bottom: 0;
      width: 4px;
      border-radius: 999px
    }

    .d2-alert.green {
      background: rgba(6, 77, 58, .28)
    }

    .d2-alert.green::before {
      background: var(--primary)
    }

    .d2-alert.amber {
      background: rgba(122, 72, 35, .24)
    }

    .d2-alert.amber::before {
      background: #ff9d4d
    }

    .d2-alert-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 6px 10px;
      border-radius: 999px;
      background: rgba(255, 255, 255, .06);
      color: rgba(227, 227, 222, .82);
      font-size: 11px;
      font-weight: 700;
      letter-spacing: .04em;
      margin-bottom: 10px
    }

    .d2-alert-text {
      line-height: 1.7;
      color: #f3f4ef
    }

    .d2-mini {
      display: flex;
      align-items: center;
      gap: 14px;
      margin-top: 18px
    }

    .d2-mini .img {
      width: 58px;
      height: 58px;
      border-radius: 18px;
      overflow: hidden
    }

    .d2-mini .img img {
      width: 100%;
      height: 100%;
      object-fit: cover
    }

    .d2-mini .grow {
      flex: 1
    }

    .d2-mini .meta {
      display: flex;
      justify-content: space-between;
      gap: 8px;
      color: var(--text)
    }

    .d2-mini .line {
      height: 6px;
      background: rgba(111, 219, 168, .12);
      border-radius: 999px;
      overflow: hidden;
      margin-top: 8px
    }

    .d2-mini .line span {
      display: block;
      height: 100%;
      width: 86%;
      background: var(--primary)
    }

    .d2-controls {
      display: none
    }

    .d2-ctl,
    .d2-inline-control {
      width: 52px;
      height: 52px;
      border-radius: 999px;
      background: rgba(51, 53, 50, .32);
      backdrop-filter: blur(22px) saturate(120%);
      display: grid;
      place-items: center;
      text-align: center;
      color: rgba(227, 227, 222, .78);
      border: 1px solid rgba(255, 255, 255, .10);
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, .08), 0 16px 34px rgba(0, 0, 0, .18), 0 0 18px rgba(111, 219, 168, .05);
      cursor: pointer;
      text-decoration: none;
      transition: transform .18s ease, opacity .18s ease, box-shadow .18s ease;
      padding: 0;
      flex-shrink: 0;
    }

    .d2-ctl:hover,
    .d2-inline-control:hover {
      transform: translateY(-2px)
    }

    .d2-ctl[disabled],
    .d2-ctl.is-disabled,
    .d2-inline-control[disabled],
    .d2-inline-control.is-disabled {
      opacity: .38;
      cursor: not-allowed;
      pointer-events: none
    }

    .d2-ctl .round,
    .d2-inline-control .round {
      width: 26px;
      height: 26px;
      border-radius: 999px;
      background: rgba(6, 77, 58, .48);
      display: grid;
      place-items: center;
      color: var(--primary);
      margin: 0 auto 3px;
      font-size: 13px;
      transition: background .18s ease, color .18s ease
    }

    .d2-ctl strong,
    .d2-inline-control strong {
      display: block;
      font-size: 8px;
      letter-spacing: .06em;
      text-transform: uppercase;
      line-height: 1.1;
      padding: 0 2px
    }

    .d2-ctl.is-on .round,
    .d2-inline-control.is-on .round {
      background: linear-gradient(135deg, #31a375, #6fdba8);
      color: #062013
    }

    .d2-ctl.is-off .round,
    .d2-inline-control.is-off .round {
      background: rgba(6, 77, 58, .48);
      color: var(--primary)
    }

    .d2-journal {
      display: grid;
      gap: 14px
    }

    .d2-journal-item {
      border-radius: 24px;
      background: rgba(18, 20, 17, .42);
      padding: 18px
    }

    .d2-journal-item .code {
      display: inline-flex;
      padding: 6px 10px;
      border-radius: 999px;
      background: rgba(111, 219, 168, .08);
      color: var(--primary);
      font-size: 10px;
      letter-spacing: .12em;
      text-transform: uppercase;
      font-weight: 800;
      margin-bottom: 10px
    }

    .d2-journal-item strong {
      display: block;
      margin-bottom: 8px;
      color: #fff
    }

    .d2-journal-item p {
      margin: 0;
      color: rgba(227, 227, 222, .72);
      line-height: 1.65
    }

    @media (max-width:1200px) {
      .d2-grid {
        grid-template-columns: 1fr
      }

      .d2-sidecards {
        grid-template-columns: 1fr 1fr
      }

      .d2-ai-strip {
        grid-template-columns: 1fr
      }

      .d2-trays-card {
        max-width: none;
        justify-self: stretch;
        position: static
      }

      .d2-trays-card.is-floating {
        position: static;
        transform: none;
        box-shadow: 0 24px 48px rgba(0, 0, 0, .22), 0 0 18px rgba(111, 219, 168, .03)
      }

      .d2-trays-card-placeholder {
        display: none !important
      }

      .d2-trays-vertical {
        grid-template-columns: repeat(4, minmax(0, 1fr))
      }

      .d2-trays-vertical .d2-thumb {
        width: 72px;
        height: 72px
      }
    }

    @media (max-width:980px) {
      .d2-app {
        padding-bottom: calc(104px + env(safe-area-inset-bottom, 0px))
      }

      .d2-shell {
        grid-template-columns: minmax(0, 1fr)
      }

      .d2-side {
        display: block;
        position: fixed;
        left: 12px;
        right: 12px;
        bottom: calc(16px + env(safe-area-inset-bottom, 0px));
        top: auto;
        z-index: 70;
        height: auto;
        padding: 11px 12px calc(11px + env(safe-area-inset-bottom, 0px));
        border-right: none;
        border-radius: 26px;
        background: rgba(6, 27, 14, .88);
        backdrop-filter: blur(26px) saturate(120%);
        -webkit-backdrop-filter: blur(26px) saturate(120%);
        box-shadow: 0 20px 44px rgba(0, 0, 0, .30), inset 0 1px 0 rgba(255, 255, 255, .06)
      }

      .d2-brand,
      .d2-level,
      .d2-upgrade,
      .d2-side-footer {
        display: none
      }

      .d2-nav {
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 4px;
        padding: 0
      }

      .d2-nav a {
        flex-direction: column;
        justify-content: center;
        text-align: center;
        padding: 8px 4px;
        border-radius: 14px;
        font-size: 9px;
        line-height: 1.15;
        gap: 4px;
        font-weight: 700;
        color: rgba(227, 227, 222, .74);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }

      .d2-nav a.active {
        border-radius: 18px;
        background: linear-gradient(180deg, rgba(111, 219, 168, .24), rgba(49, 163, 117, .92));
        color: #f7fff9;
        font-weight: 800;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .16), 0 10px 22px rgba(49, 163, 117, .22)
      }

      .d2-nav .bottom-nav-label {
        display: none
      }

      .d2-nav .bottom-nav-short {
        display: block
      }

      .d2-nav .bottom-nav-icon {
        font-size: 20px;
        line-height: 1
      }

      .d2-main {
        padding: calc(32px + env(safe-area-inset-top, 0px)) 16px 16px;
      }

      .d2-top {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        padding: 0 0 14px;
        margin-bottom: 18px
      }

      .d2-garden-rename {
        flex: 1 1 0;
        min-width: 0;
        max-width: none;
        order: 1;
      }

      .d2-garden-name {
        font-size: 26px;
        white-space: normal;
        line-height: 1.25;
        padding-top: 2px;
      }

      .d2-garden-input {
        min-width: 0;
        width: 100%
      }

      .d2-top-links {
        order: 3;
        width: 100%;
        gap: 14px;
        overflow-x: auto;
        padding-bottom: 4px;
        flex: 0 0 100%;
        white-space: nowrap;
        -webkit-overflow-scrolling: touch;
      }
      
      .d2-top-links a {
        display: inline-block;
      }

      .d2-top-actions {
        order: 2;
        flex: 0 0 auto;
        padding: 6px 8px;
        border-radius: 18px
      }

      .d2-select {
        min-width: 0;
        width: 100%
      }

      .d2-grid {
        grid-template-columns: minmax(0, 1fr);
        gap: 16px
      }

      .d2-live,
      .d2-sidecards {
        grid-template-columns: minmax(0, 1fr);
        gap: 16px
      }

      .d2-growth-card {
        padding: 20px 18px;
        border-radius: 26px
      }

      .d2-growth-head {
        justify-content: flex-start;
        margin-bottom: 14px
      }

      .d2-growth-track {
        overflow-x: auto;
        gap: 18px;
        padding-bottom: 16px;
        justify-content: flex-start;
        -webkit-overflow-scrolling: touch;
      }
      
      .d2-growth-track::-webkit-scrollbar {
        height: 6px;
      }
      .d2-growth-track::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 4px;
      }
      .d2-growth-track::-webkit-scrollbar-thumb {
        background: rgba(111, 219, 168, 0.4);
        border-radius: 4px;
      }

      .d2-growth-track::before,
      .d2-growth-progress {
        min-width: 640px
      }

      .d2-growth-track::before,
      .d2-growth-progress {
        top: 40px
      }

      .d2-growth-step {
        min-width: 120px;
        max-width: none
      }

      .d2-growth-icon {
        width: 60px;
        height: 60px;
        margin-bottom: 10px
      }

      .d2-growth-badge {
        padding: 7px 12px;
        font-size: 11px
      }

      .d2-frame {
        min-height: 0;
        padding-bottom: 0;
        border-radius: 22px;
        aspect-ratio: 16/9
      }

      .d2-frame img {
        aspect-ratio: auto;
        height: 100%
      }

      .d2-pill {
        top: 14px;
        left: 14px;
        padding: 8px 12px;
        font-size: 10px
      }

      .d2-info {
        max-width: none;
        width: 100%;
        padding: 18px 18px;
        border-radius: 24px;
        margin-bottom: 0 !important
      }

      .d2-pot-name {
        font-size: 26px
      }

      .d2-bottom {
        position: absolute;
        left: 12px;
        right: 12px;
        bottom: 34px;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: flex-end;
        gap: 10px;
        z-index: 3
      }

      .d2-bottom>div:first-child {
        display: none
      }

      .d2-actions {
        justify-content: flex-start;
        margin: 0 auto;
        width: auto;
        gap: 8px;
        flex-wrap: nowrap;
        overflow-x: auto;
        max-width: 100%;
        padding: 6px 2px 10px;
        scrollbar-width: thin;
        scrollbar-color: rgba(255, 255, 255, 0.25) transparent;
        -webkit-overflow-scrolling: touch;
      }
      .d2-actions::-webkit-scrollbar {
        height: 3px;
      }
      .d2-actions::-webkit-scrollbar-track {
        background: transparent;
      }
      .d2-actions::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.25);
        border-radius: 3px;
      }

      .d2-vitals {
        gap: 8px;
        flex: 0 0 auto
      }

      .d2-vital,
      .d2-icon-btn {
        width: 44px;
        height: 44px;
        flex: 0 0 auto
      }
      
      .d2-inline-control {
        width: auto;
        height: 44px;
        flex: 0 0 auto;
        padding: 0 12px;
      }

      .d2-vital {
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 0;
      }

      .d2-vital > div:first-child {
        font-size: 14px;
        line-height: 1;
        margin-bottom: 2px;
      }

      .d2-vital .value {
        font-size: 11px;
        line-height: 1;
        margin-top: 0;
      }

      .d2-ctl .round,
      .d2-inline-control .round {
        width: 24px;
        height: 24px;
        font-size: 12px;
        margin-bottom: 2px
      }

      .d2-ctl strong,
      .d2-inline-control strong {
        font-size: 8px;
        letter-spacing: .02em
      }

      .d2-icon-btn {
        font-size: 18px
      }

      .d2-ai-strip {
        grid-template-columns: minmax(0, 1fr);
        gap: 12px;
        margin-top: 16px
      }

      .d2-pot-journal {
        margin-top: 14px;
        padding: 18px 18px;
        border-radius: 24px
      }

      .d2-sidecards {
        grid-template-columns: minmax(0, 1fr)
      }

      .d2-trays-card {
        max-width: none;
        width: 100%;
        justify-self: stretch;
        position: static;
        top: auto;
        right: auto;
        transform: none !important
      }

      .d2-growth-track {
        overflow-x: auto;
        padding-bottom: 12px;
        scrollbar-width: none;
        -webkit-overflow-scrolling: touch;
        justify-content: flex-start;
        gap: 16px;
      }
      .d2-growth-track::-webkit-scrollbar { display: none; }
      
      .d2-growth-track::before {
        left: 0;
        right: 0;
        top: 36px;
      }
      
      .d2-growth-progress {
        left: 0;
        top: 36px;
      }
      
      .d2-growth-step {
        flex: 0 0 68px;
        max-width: none;
        padding: 0;
      }
      
      .d2-growth-icon {
        width: 52px;
        height: 52px;
        font-size: 22px;
        border-width: 5px;
        margin-bottom: 8px;
      }
      
      .d2-growth-step.is-active::after {
        width: 72px;
        height: 72px;
        top: -5px;
      }
      
      .d2-growth-icon-check {
        width: 18px;
        height: 18px;
        font-size: 10px;
        right: -4px;
        bottom: -4px;
        border-width: 2px;
      }
      
      .d2-growth-step-name {
        font-size: 11px;
        line-height: 1.2;
      }

      .d2-trays-card.is-floating {
        position: static;
        top: auto;
        right: auto;
        transform: none;
        box-shadow: 0 24px 48px rgba(0, 0, 0, .22), 0 0 18px rgba(111, 219, 168, .03)
      }

      .d2-trays-card-placeholder {
        display: none !important
      }

      .d2-trays-vertical {
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px
      }

      .d2-trays-vertical .d2-thumb {
        width: 64px;
        height: 64px;
        border-radius: 20px
      }

      .d2-trays-vertical .d2-switch {
        justify-items: center
      }
    }

    @media (max-width:640px) {
      .d2-top-links a {
        font-size: 14px
      }

      .d2-info small {
        font-size: 9px;
        letter-spacing: .12em
      }

      .d2-switcher {
        padding: 14px 12px;
        border-radius: 24px
      }

      .d2-trays-vertical {
        grid-template-columns: repeat(3, minmax(0, 1fr))
      }

      .d2-trays-vertical .d2-thumb {
        width: 58px;
        height: 58px;
        border-radius: 18px
      }

      .d2-vital,
      .d2-icon-btn,
      .d2-inline-control {
        width: 48px;
        height: 48px
      }

      .d2-actions {
        gap: 6px
      }
    }

    /* ── Rack Monitor ─────────────────────────────────────────────────── */
    .d2-rack-monitor {
      margin: 28px 0 0;
      padding: 0 0 40px
    }

    .d2-rack-monitor-head {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 20px;
      font-weight: 700;
      font-size: 15px;
      color: var(--primary)
    }

    .d2-rack-monitor-hint {
      font-size: 12px;
      font-weight: 400;
      color: var(--muted);
      opacity: .7
    }

    .d2-rack-section {
      margin-bottom: 28px
    }

    .d2-rack-section:last-child {
      margin-bottom: 0
    }

    .d2-rack-label {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 12px;
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .08em;
      color: var(--muted)
    }

    .d2-rack-tray-count {
      background: rgba(255, 255, 255, .07);
      padding: 2px 8px;
      border-radius: 20px;
      font-weight: 400;
      letter-spacing: .03em
    }

    .d2-tray-lanes {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 18px
    }

    @media(max-width:900px) {
      .d2-tray-lanes {
        grid-template-columns: 1fr
      }
    }

    .d2-tray-lane {
      background: var(--panel);
      border: 1px solid var(--line);
      border-radius: 16px;
      padding: 18px;
      display: flex;
      flex-direction: column;
      gap: 14px
    }

    .d2-tray-lane-head {
      display: flex;
      align-items: center;
      gap: 8px;
      font-weight: 700;
      font-size: 14px
    }

    .d2-tray-dot {
      width: 9px;
      height: 9px;
      border-radius: 50%;
      background: #6fdba8;
      flex-shrink: 0;
      transition: background .3s
    }

    .d2-tray-dot.is-uncfg {
      background: #555
    }

    .d2-tray-dot.is-ok {
      background: #6fdba8
    }

    .d2-tray-dot.is-warn {
      background: #ffe16d
    }

    .d2-tray-dot.is-err {
      background: #ff7e7e
    }

    .d2-tray-dot.is-loading {
      background: #555;
      animation: dot-blink .9s ease-in-out infinite
    }

    @keyframes dot-blink {

      0%,
      100% {
        opacity: .3
      }

      50% {
        opacity: 1
      }
    }

    .d2-tray-uncfg-tag {
      margin-left: auto;
      font-size: 10px;
      font-weight: 600;
      letter-spacing: .06em;
      text-transform: uppercase;
      color: #8c8f94;
      background: rgba(255, 255, 255, .06);
      padding: 2px 7px;
      border-radius: 20px
    }

    .d2-tray-cam {
      background: #0d1410;
      border-radius: 10px;
      overflow: hidden;
      aspect-ratio: 16/9;
      display: flex;
      align-items: center;
      justify-content: center
    }

    .d2-tray-cam img,
    .d2-tray-cam video {
      width: 100%;
      height: 100%;
      object-fit: cover
    }

    .d2-tray-cam-empty {
      color: rgba(189, 202, 192, .3);
      font-size: 13px;
      text-align: center;
      padding: 8px
    }

    .d2-tray-sensors {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 8px
    }

    .d2-tray-sensor {
      background: var(--panel-2);
      border-radius: 10px;
      padding: 10px 12px;
      display: flex;
      flex-direction: column;
      gap: 2px;
      transition: border-color .3s;
      border: 1px solid transparent
    }

    .d2-tray-sensor.is-ok {
      border-color: rgba(111, 219, 168, .2)
    }

    .d2-tray-sensor.is-warn {
      border-color: rgba(255, 225, 109, .3)
    }

    .d2-tray-sensor.is-alert {
      border-color: rgba(255, 100, 100, .3)
    }

    .dts-icon {
      font-size: 13px
    }

    .dts-label {
      font-size: 10px;
      color: var(--muted);
      text-transform: uppercase;
      letter-spacing: .06em
    }

    .dts-val {
      font-size: 20px;
      font-weight: 800;
      color: var(--text);
      transition: color .3s
    }

    .d2-tray-sensor.is-warn .dts-val {
      color: #ffe16d
    }

    .d2-tray-sensor.is-alert .dts-val {
      color: #ff7e7e
    }

    .dts-unit {
      font-size: 10px;
      color: var(--muted)
    }

    .d2-tray-controls {
      display: flex;
      gap: 8px
    }

    .d2-tray-ctrl-btn {
      flex: 1;
      padding: 9px 6px;
      border-radius: 10px;
      border: 1px solid var(--line);
      background: var(--panel-2);
      color: var(--muted);
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      transition: background .2s, color .2s, border-color .2s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 5px
    }

    .d2-tray-ctrl-btn.is-on {
      background: rgba(111, 219, 168, .15);
      border-color: rgba(111, 219, 168, .35);
      color: var(--primary)
    }

    .d2-tray-ctrl-btn:disabled {
      opacity: .45;
      cursor: default
    }

    /* ── Tray settings modal ─────────────────────────────────────────── */
    .d2-tray-settings-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, .65);
      z-index: 9000;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 16px
    }

    .d2-tray-settings-overlay[hidden] {
      display: none
    }

    .d2-tray-settings-box {
      background: var(--bg-2);
      border: 1px solid var(--line);
      border-radius: 18px;
      width: 100%;
      max-width: 860px;
      max-height: 90vh;
      display: flex;
      flex-direction: column;
      overflow: hidden
    }

    .d2-tray-settings-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 16px 22px;
      border-bottom: 1px solid var(--line);
      font-weight: 700;
      font-size: 15px;
      flex-shrink: 0
    }

    .d2-tray-settings-close {
      background: none;
      border: none;
      color: var(--muted);
      font-size: 18px;
      cursor: pointer;
      padding: 4px 8px;
      border-radius: 6px
    }

    .d2-tray-settings-close:hover {
      color: var(--text);
      background: rgba(255, 255, 255, .08)
    }

    .d2-tray-settings-body {
      overflow-y: auto;
      flex: 1;
      display: flex;
      flex-direction: column
    }

    /* ── Per-rack accordion ── */
    .d2-rack-cfg {
      border-bottom: 1px solid var(--line)
    }

    .d2-rack-cfg-head {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 12px 22px;
      cursor: pointer;
      user-select: none
    }

    .d2-rack-cfg-head:hover {
      background: rgba(255, 255, 255, .03)
    }

    .d2-rack-cfg-arrow {
      font-size: 10px;
      color: var(--muted);
      transition: transform .2s;
      width: 14px;
      flex-shrink: 0
    }

    .d2-rack-cfg:not(.is-open) .d2-rack-cfg-arrow {
      transform: rotate(-90deg)
    }

    .d2-rack-name-input {
      flex: 1;
      padding: 5px 10px;
      border: 1px solid var(--line);
      border-radius: 7px;
      background: var(--panel);
      color: var(--text);
      font-size: 13px;
      font-weight: 600
    }

    .d2-rack-name-input:focus {
      outline: 2px solid rgba(111, 219, 168, .4);
      border-color: transparent
    }

    .d2-rack-remove-btn {
      margin-left: 4px;
      background: none;
      border: none;
      color: var(--muted);
      font-size: 14px;
      cursor: pointer;
      padding: 3px 7px;
      border-radius: 5px;
      flex-shrink: 0
    }

    .d2-rack-remove-btn:hover {
      color: #ff7e7e;
      background: rgba(255, 80, 80, .1)
    }

    .d2-rack-cfg-body {
      padding: 0 22px 16px;
      display: none
    }

    .d2-rack-cfg.is-open .d2-rack-cfg-body {
      display: block
    }

    .d2-tray-cfg-list {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
      gap: 14px;
      margin-bottom: 10px
    }

    .d2-tray-cfg-item {
      border: 1px solid var(--line);
      border-radius: 10px;
      padding: 12px;
      display: flex;
      flex-direction: column;
      gap: 6px
    }

    .d2-tray-cfg-title {
      font-weight: 700;
      font-size: 12px;
      color: var(--primary);
      padding-bottom: 6px;
      border-bottom: 1px solid var(--line);
      display: flex;
      align-items: center;
      justify-content: space-between
    }

    .d2-tray-remove-btn {
      background: none;
      border: none;
      color: var(--muted);
      font-size: 12px;
      cursor: pointer;
      padding: 2px 5px;
      border-radius: 4px
    }

    .d2-tray-remove-btn:hover {
      color: #ff7e7e
    }

    .d2-tray-cfg-row label,
    .d2-tray-cfg-vpins label {
      display: flex;
      flex-direction: column;
      gap: 3px;
      font-size: 10px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: .05em;
      color: var(--muted)
    }

    .d2-tray-cfg-row input,
    .d2-tray-cfg-vpins input {
      padding: 6px 8px;
      border: 1px solid var(--line);
      border-radius: 6px;
      background: var(--panel);
      color: var(--text);
      font-size: 12px
    }

    .d2-tray-cfg-vpins {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 5px
    }

    .d2-add-tray-btn {
      font-size: 12px;
      padding: 6px 12px;
      border: 1px dashed rgba(111, 219, 168, .3);
      border-radius: 8px;
      background: none;
      color: var(--primary);
      cursor: pointer;
      margin-top: 4px
    }

    .d2-add-tray-btn:hover {
      background: rgba(111, 219, 168, .08)
    }

    /* ── Footer ── */
    .d2-tray-settings-foot {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 14px 22px;
      border-top: 1px solid var(--line);
      flex-shrink: 0
    }

    .d2-add-rack-btn {
      font-size: 12px;
      padding: 7px 14px;
      border: 1px dashed rgba(111, 219, 168, .4);
      border-radius: 8px;
      background: none;
      color: var(--primary);
      cursor: pointer;
      font-weight: 600;
      flex-shrink: 0
    }

    .d2-add-rack-btn:hover {
      background: rgba(111, 219, 168, .08)
    }

    .d2-tray-settings-status {
      font-size: 12px;
      color: var(--muted);
      flex: 1;
      text-align: center
    }

    .d2-tray-settings-status.is-ok {
      color: #6fdba8
    }

    .d2-tray-settings-status.is-err {
      color: #ff7e7e
    }

    .d2-tray-settings-save-btn {
      padding: 8px 18px;
      border-radius: 10px;
      border: 1px solid rgba(111, 219, 168, .3);
      background: rgba(111, 219, 168, .12);
      color: var(--primary);
      font-weight: 700;
      font-size: 13px;
      cursor: pointer;
      flex-shrink: 0
    }

    .d2-tray-settings-save-btn:hover {
      background: rgba(111, 219, 168, .22)
    }

    .d2-tray-settings-btn {
      font-size: 16px
    }

    /* ── Pump modal ───────────────────────────────────────────── */
    .d2-pump-modal-box { max-width: 560px }
    .d2-pump-modal-body { overflow-y: auto; flex: 1; padding: 0 20px 4px }
    .d2-pump-status-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
      margin-bottom: 14px
    }
    .d2-pump-stat {
      background: var(--bg);
      border-radius: 10px;
      padding: 10px 12px
    }
    .d2-pump-stat-label {
      font-size: 11px;
      text-transform: uppercase;
      color: var(--muted);
      margin-bottom: 4px
    }
    .d2-pump-stat-val { font-size: 18px; font-weight: 700; color: var(--primary) }
    .d2-pump-soil-bar {
      height: 4px;
      background: var(--panel);
      border-radius: 2px;
      margin: 6px 0;
      overflow: hidden
    }
    .d2-pump-soil-fill {
      height: 100%;
      width: var(--pct, 0%);
      background: var(--primary);
      border-radius: 2px;
      transition: width .4s
    }
    .d2-pump-state-badge {
      font-size: 13px;
      font-weight: 600;
      padding: 3px 8px;
      border-radius: 6px;
      display: inline-block;
      margin-top: 4px
    }
    .d2-pump-state-badge.is-on  { background: rgba(111,219,168,.15); color: #6fdba8 }
    .d2-pump-state-badge.is-off { background: rgba(255,255,255,.06); color: var(--muted) }
    .d2-pump-auto-badge { font-size: 12px; font-weight: 600; margin-top: 4px }
    .d2-pump-auto-badge.is-on  { color: #6fdba8 }
    .d2-pump-auto-badge.is-off { color: var(--muted) }
    .d2-pump-manual-row { display: flex; gap: 10px; margin-bottom: 18px }
    .d2-pump-btn {
      flex: 1;
      padding: 10px 0;
      border-radius: 10px;
      border: none;
      cursor: pointer;
      font-size: 14px;
      font-weight: 600;
      transition: opacity .15s
    }
    .d2-pump-btn:disabled { opacity: .4; cursor: default }
    .d2-pump-btn--on  { background: rgba(111,219,168,.18); color: #6fdba8; border: 1px solid rgba(111,219,168,.3) }
    .d2-pump-btn--on:hover:not(:disabled)  { background: rgba(111,219,168,.28) }
    .d2-pump-btn--off { background: rgba(255,100,100,.12); color: #ff7e7e; border: 1px solid rgba(255,100,100,.25) }
    .d2-pump-btn--off:hover:not(:disabled) { background: rgba(255,100,100,.22) }
    .d2-pump-section { border-top: 1px solid var(--line); padding-top: 14px; margin-bottom: 14px }
    .d2-pump-section-head {
      font-size: 11px;
      text-transform: uppercase;
      font-weight: 700;
      color: var(--muted);
      margin-bottom: 12px;
      letter-spacing: .04em
    }
    .d2-pump-rule-row { margin-bottom: 12px }
    .d2-pump-rule-row--toggle { font-size: 14px; color: var(--text) }
    .d2-pump-rule-row--toggle input[type="checkbox"] { accent-color: var(--primary); margin-right: 6px }
    .d2-pump-rule-grid {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: 10px;
      margin-bottom: 12px
    }
    @media (max-width: 480px) { .d2-pump-rule-grid { grid-template-columns: 1fr 1fr } }
    .d2-pump-rule-item { display: flex; flex-direction: column; gap: 4px }
    .d2-pump-rule-label { font-size: 11px; color: var(--muted); text-transform: uppercase }
    .d2-pump-rule-input {
      background: var(--bg);
      border: 1px solid var(--line);
      border-radius: 8px;
      color: var(--text);
      font-size: 14px;
      padding: 7px 10px;
      width: 100%;
      box-sizing: border-box
    }
    .d2-pump-rule-input:focus { outline: none; border-color: var(--primary) }
    .d2-pump-rule-hint { font-size: 10px; color: var(--muted); opacity: .7 }
    .d2-pump-time-row { display: flex; align-items: center; gap: 8px; margin-top: 6px }
    .d2-pump-time-input {
      background: var(--bg);
      border: 1px solid var(--line);
      border-radius: 8px;
      color: var(--text);
      font-size: 14px;
      padding: 7px 10px
    }
    .d2-pump-time-input:focus { outline: none; border-color: var(--primary) }
    .d2-pump-days-row { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 6px }
    .d2-pump-day-label {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 3px;
      cursor: pointer
    }
    .d2-pump-day-label input { display: none }
    .d2-pump-day-label span {
      background: var(--bg);
      border: 1px solid var(--line);
      border-radius: 6px;
      padding: 4px 8px;
      font-size: 12px;
      color: var(--muted);
      transition: background .15s, color .15s
    }
    .d2-pump-day-label input:checked + span {
      background: rgba(111,219,168,.18);
      border-color: rgba(111,219,168,.4);
      color: #6fdba8
    }
    .d2-pump-log-wrap { overflow-x: auto }
    .d2-pump-log-table { width: 100%; border-collapse: collapse; font-size: 12px }
    .d2-pump-log-table th {
      text-align: left;
      padding: 6px 8px;
      color: var(--muted);
      font-weight: 500;
      border-bottom: 1px solid var(--line)
    }
    .d2-pump-log-table td {
      padding: 6px 8px;
      border-bottom: 1px solid rgba(255,255,255,.04);
      color: var(--text)
    }
    .pump-log-ok   { color: #6fdba8; font-weight: 600 }
    .pump-log-warn { color: #ff7e7e; font-weight: 600 }

    .d2-timelapse-panel {
      padding: 0 0 40px
    }

    .d2-tl-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      padding: 0 4px 20px;
      border-bottom: 1px solid rgba(111, 219, 168, .08);
      margin-bottom: 24px
    }

    .d2-tl-header h2 {
      font-size: 22px;
      font-weight: 800;
      color: var(--primary);
      margin: 0
    }

    .d2-tl-back-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 18px;
      border-radius: 12px;
      border: 1px solid rgba(111, 219, 168, .18);
      background: transparent;
      color: var(--muted);
      font-weight: 600;
      font-size: 14px;
      cursor: pointer;
      transition: color .18s ease, border-color .18s ease
    }

    .d2-tl-back-btn:hover {
      color: var(--primary);
      border-color: rgba(111, 219, 168, .4)
    }

    .d2-tl-controls {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
      margin-bottom: 20px
    }

    .d2-tl-controls label {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 14px;
      color: var(--muted)
    }

    .d2-tl-controls select {
      padding: 8px 12px;
      border-radius: 10px;
      border: 1px solid var(--line);
      background: var(--panel);
      color: var(--text);
      font-size: 14px;
      cursor: pointer
    }

    .d2-tl-load-btn {
      padding: 10px 20px;
      border-radius: 10px;
      border: none;
      background: rgba(111, 219, 168, .18);
      color: var(--primary);
      font-weight: 700;
      font-size: 14px;
      cursor: pointer;
      transition: background .18s ease
    }

    .d2-tl-load-btn:hover {
      background: rgba(111, 219, 168, .28)
    }

    .d2-tl-load-btn:disabled {
      opacity: .5;
      cursor: not-allowed
    }

    .d2-tl-player {
      background: var(--panel);
      border-radius: 16px;
      padding: 20px;
      border: 1px solid var(--line)
    }

    .d2-tl-frame-wrap {
      position: relative;
      border-radius: 10px;
      overflow: hidden;
      background: #000;
      aspect-ratio: 16/9;
      margin-bottom: 16px;
      display: flex;
      align-items: center;
      justify-content: center
    }

    .d2-tl-frame-wrap img {
      width: 100%;
      height: 100%;
      object-fit: contain
    }

    .d2-tl-empty {
      position: absolute;
      inset: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--muted);
      font-size: 15px;
      text-align: center;
      padding: 20px;
      line-height: 1.6
    }

    .d2-tl-empty-state {
      padding: 40px 20px;
      text-align: center;
      color: var(--muted);
      line-height: 1.7;
      font-size: 15px
    }

    .d2-tl-sensor-bar {
      display: flex;
      flex-wrap: wrap;
      gap: 8px 16px;
      padding: 10px 4px 4px;
      font-size: 13px;
      color: var(--text)
    }

    .tls-item {
      background: var(--panel-2);
      border-radius: 8px;
      padding: 5px 12px;
      font-variant-numeric: tabular-nums;
      white-space: nowrap
    }

    .tls-item:empty {
      display: none
    }

    .d2-tl-progress-wrap {
      margin-bottom: 12px
    }

    .d2-tl-progress-wrap input[type=range] {
      width: 100%;
      accent-color: var(--primary);
      cursor: pointer
    }

    .d2-tl-playbar {
      display: flex;
      align-items: center;
      gap: 10px;
      flex-wrap: wrap
    }

    .d2-tl-playbar button {
      padding: 8px 14px;
      border-radius: 8px;
      border: 1px solid var(--line);
      background: var(--panel-2);
      color: var(--text);
      font-size: 16px;
      cursor: pointer;
      transition: background .18s ease
    }

    .d2-tl-playbar button:hover {
      background: rgba(111, 219, 168, .14)
    }

    .d2-tl-info {
      font-size: 13px;
      color: var(--muted);
      font-variant-numeric: tabular-nums
    }

    .d2-tl-playbar select {
      margin-left: auto;
      padding: 8px 12px;
      border-radius: 8px;
      border: 1px solid var(--line);
      background: var(--panel-2);
      color: var(--text);
      font-size: 13px;
      cursor: pointer
    }

    .d2-tl-share-row {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 12px 0 4px;
      flex-wrap: wrap
    }

    .d2-tl-share-label {
      font-size: 12px;
      color: var(--muted);
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: .4px;
      margin-right: 4px
    }

    .d2-tl-share-btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 8px 14px;
      border-radius: 20px;
      border: none;
      cursor: pointer;
      font-size: 13px;
      font-weight: 600;
      transition: opacity .15s, transform .12s
    }

    .d2-tl-share-btn:hover {
      opacity: .88;
      transform: translateY(-1px)
    }

    .d2-tl-share-btn:active {
      transform: translateY(0)
    }

    .d2-tl-share-btn.tl-save {
      background: rgba(111, 219, 168, .12);
      color: var(--primary);
      border: 1px solid rgba(111, 219, 168, .22)
    }

    .d2-tl-share-btn.tl-fb {
      background: #1877F2;
      color: #fff
    }

    .d2-tl-share-btn.tl-zalo {
      background: #0068FF;
      color: #fff
    }

    .d2-tl-share-btn.tl-native {
      background: rgba(255, 255, 255, .08);
      color: var(--text);
      border: 1px solid var(--line)
    }

    .d2-tl-privacy-note {
      font-size: 11px;
      color: rgba(189, 202, 192, .5);
      display: flex;
      align-items: center;
      gap: 4px;
      padding: 6px 0 0;
      width: 100%
    }
    
    .d2-custom-modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.7);
      backdrop-filter: blur(4px);
      z-index: 9999;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .d2-custom-modal {
      background: var(--panel);
      border: 1px solid var(--line);
      border-radius: 16px;
      padding: 24px;
      width: 90%;
      max-width: 400px;
      text-align: center;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
      animation: d2ModalIn 0.3s ease;
    }
    @keyframes d2ModalIn {
      from { opacity: 0; transform: scale(0.95) translateY(10px); }
      to { opacity: 1; transform: scale(1) translateY(0); }
    }
    .d2-custom-modal-icon {
      font-size: 40px;
      margin-bottom: 12px;
    }
    .d2-custom-modal-title {
      color: var(--text);
      font-size: 18px;
      font-weight: 700;
      margin: 0 0 10px;
    }
    .d2-custom-modal-text {
      color: var(--muted);
      font-size: 14px;
      line-height: 1.5;
      margin: 0 0 24px;
    }
    .d2-custom-modal-actions {
      display: flex;
      gap: 12px;
    }
    .d2-custom-modal-btn {
      flex: 1;
      padding: 10px 16px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      border: none;
      transition: all 0.2s;
    }
    .d2-custom-modal-btn.cancel {
      background: rgba(255, 255, 255, 0.1);
      color: var(--text);
    }
    .d2-custom-modal-btn.cancel:hover {
      background: rgba(255, 255, 255, 0.15);
    }
    .d2-custom-modal-btn.confirm {
      background: rgba(255, 182, 140, 0.15);
      color: #ffb68c;
      border: 1px solid rgba(255, 182, 140, 0.3);
    }
    .d2-custom-modal-btn.confirm:hover {
      background: rgba(255, 182, 140, 0.25);
    }
    
    .d2-top-actions .eco-noti-popup {
      top: calc(100% + 14px);
      right: 50px;
    }
    
    @media (max-width:820px) {
      .d2-top-actions .eco-noti-popup {
        right: -10px;
        left: auto;
        width: 340px;
        max-width: calc(100vw - 20px);
      }
    }
    
  </style>
  <div class="d2-shell">
    <aside class="d2-side">
      <div class="d2-brand">Ai trồng cây</div>
      <div class="d2-level">
        <div class="d2-level-badge">🛡</div>
        <div>
          <div style="font-weight:800;color:var(--primary)">Level 42</div>
          <div style="font-size:12px;letter-spacing:.14em;text-transform:uppercase;color:rgba(227,227,222,.46)">
            <?php echo esc_html($owner_name); ?></div>
        </div>
      </div>
      <a class="d2-upgrade" href="<?php echo esc_url($friends_url); ?>">👥 Hàng xóm</a>
      <nav class="d2-nav">
        <a href="<?php echo esc_url($photo_library_url); ?>"><span class="bottom-nav-icon"
            aria-hidden="true">🖼</span><span class="bottom-nav-label">Galleries</span><span
            class="bottom-nav-short">Ảnh</span></a>
        <a href="<?php echo esc_url(add_query_arg(['garden' => $garden_key], home_url('/portal/hydration/'))); ?>"><span class="bottom-nav-icon" aria-hidden="true">💧</span><span
            class="bottom-nav-label">Hydration</span><span class="bottom-nav-short">Nước</span></a>
        <a href="<?php echo esc_url(add_query_arg(['garden' => $garden_key], home_url('/portal/soil-health/'))); ?>"><span class="bottom-nav-icon" aria-hidden="true">🌿</span><span class="bottom-nav-label">Soil
            Health</span><span class="bottom-nav-short">Giá thể</span></a>
        <?php if ($can_control_garden): ?>
        <a href="#" data-tl-nav-link><span class="bottom-nav-icon" aria-hidden="true">📽</span><span
            class="bottom-nav-label">Timelapse</span><span class="bottom-nav-short">TL</span></a>
        <?php endif; ?>
        <a href="<?php echo esc_url($flower_bio_url); ?>"><span class="bottom-nav-icon"
            aria-hidden="true">🍃</span><span class="bottom-nav-label">Bách thảo</span><span
            class="bottom-nav-short">Bách thảo</span></a>
        <a
          href="<?php echo esc_url(add_query_arg(array_filter(['garden' => $garden_key]), home_url('/portal/kho-nong-cu-2/'))); ?>"><span
            class="bottom-nav-icon" aria-hidden="true">🗄</span><span class="bottom-nav-label">Seed Bank</span><span
            class="bottom-nav-short">Kho</span></a>
      </nav>
      <div class="d2-side-footer">
        <div>🤖 AI Status: Active</div>
        <div>🌱 <?php echo esc_html((string) count($pots)); ?> khoang đang theo dõi</div>
      </div>
    </aside>
    <div class="d2-main">
      <?php
      $garden_notices = get_option('aitr_garden_notices_' . $garden_key, []);
      if (!empty($garden_notices)):
          foreach ($garden_notices as $notice):
      ?>
          <div class="aitr-garden-notice" style="background: <?php echo (($notice['type'] ?? '') === 'success') ? 'rgba(74, 222, 128, 0.1)' : 'rgba(250, 204, 21, 0.1)'; ?>; border-left: 4px solid <?php echo (($notice['type'] ?? '') === 'success') ? '#4ade80' : '#facc15'; ?>; padding: 12px 16px; margin: 16px 24px 0; border-radius: 4px; color: #f8fafc; font-size: 14px; display: flex; justify-content: space-between; align-items: center;">
              <div><?php echo wp_kses_post($notice['message']); ?></div>
              <button type="button" onclick="this.parentElement.style.display='none'; fetch('<?php echo admin_url('admin-ajax.php?action=aitrongcay_dismiss_notice&id=' . $notice['id'] . '&garden_key=' . $garden_key); ?>', {method: 'POST'})" style="background: none; border: none; color: #94a3b8; font-size: 18px; cursor: pointer; padding: 0 4px;" aria-label="Đóng thông báo">&times;</button>
          </div>
      <?php
          endforeach;
      endif;
      ?>
      <style>
      @media (max-width: 820px) {
        .d2-garden-rename-responsive { display: block !important; }
        .d2-garden-name-responsive { display: inline !important; padding-top: 0 !important; }
        .eco-garden-switcher-responsive { display: inline-block !important; vertical-align: middle !important; margin-top: -4px !important; margin-left: 6px !important; }
      }
      </style>
      <div class="d2-top">
        <div class="d2-garden-rename d2-garden-rename-responsive" data-garden-inline-name
          data-garden-name="<?php echo esc_attr($garden_title !== '' ? $garden_title : 'Khu vườn của bạn'); ?>">
          <div class="d2-garden-name d2-garden-name-responsive" data-garden-display-name>
            <?php echo esc_html($garden_title !== '' ? $garden_title : 'Khu vườn của bạn'); ?></div>
          <input type="text" class="d2-garden-input"
            value="<?php echo esc_attr($garden_title !== '' ? $garden_title : 'Khu vườn của bạn'); ?>"
            data-garden-inline-input hidden>
          <?php if (is_array($viewable_gardens) && count($viewable_gardens) > 1) : ?>
            <div class="eco-garden-switcher eco-garden-switcher-responsive" style="position:relative; display:inline-block; margin-left:4px; margin-top:2px;">
                <button class="d2-garden-edit eco-garden-btn" type="button" onclick="var p = this.nextElementSibling; p.hidden = !p.hidden; event.stopPropagation();" aria-label="Chuyển đổi khu vườn" style="background:rgba(111,219,168,.12); color:var(--primary); display:inline-flex; align-items:center; justify-content:center; border:none; border-radius:999px; width:36px; height:36px; cursor:pointer;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.8;"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
                <div class="eco-garden-popup" hidden style="position:absolute; top:calc(100% + 8px); left:0; min-width:240px; background:rgba(26,28,25,.98); border:1px solid rgba(255,255,255,.08); border-radius:16px; padding:8px; box-shadow:0 24px 48px rgba(0,0,0,.4); z-index:100; font-family:var(--ui-font,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif); font-style:normal; letter-spacing:normal; font-size:14px; line-height:1.4; text-align:left; max-width: calc(100vw - 32px);" onclick="event.stopPropagation();">
                    <?php 
                    try {
                        $has_own_garden = false;
                        foreach ($viewable_gardens as $g_key => $g_data) {
                            if (($g_data['role'] ?? '') === 'owner') {
                                $has_own_garden = true;
                                $is_active = $g_key === $garden_key;
                                echo '<a href="'.esc_url(home_url('/portal/dashboard-2/?garden='.urlencode((string)$g_key))).'" style="display:flex; justify-content:space-between; align-items:center; padding:12px 14px; border-radius:12px; color:'.($is_active?'#6fdba8':'#e3e3de').'; text-decoration:none; background:'.($is_active?'rgba(111,219,168,.1)':'transparent').'; margin-bottom:4px; font-weight:700; transition:0.2s;">';
                                echo '<span>Vườn của tôi</span>';
                                if ($is_active) echo '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                                echo '</a>';
                                break;
                            }
                        }
                        $has_shared = false;
                        foreach ($viewable_gardens as $g_key => $g_data) {
                            if (($g_data['role'] ?? '') !== 'owner') {
                                if (!$has_shared) {
                                    if ($has_own_garden) {
                                        echo '<div style="height:1px; background:rgba(255,255,255,.06); margin:6px 0;"></div>';
                                    }
                                    $has_shared = true;
                                }
                                $g_prof = $g_data['profile'] ?? [];
                                $owner = $g_data['owner'] ?? null;
                                if ($owner instanceof \WP_User) {
                                    $g_name = $owner->display_name ?: $owner->first_name ?: $owner->user_login;
                                } else {
                                    $g_name = is_array($g_prof) ? ($g_prof['name'] ?? 'Hàng xóm') : 'Hàng xóm';
                                }
                                $is_active = $g_key === $garden_key;
                                echo '<a href="'.esc_url(home_url('/portal/dashboard-2/?garden='.urlencode((string)$g_key))).'" style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; border-radius:12px; color:'.($is_active?'#6fdba8':'#a9b5ab').'; text-decoration:none; background:'.($is_active?'rgba(111,219,168,.1)':'transparent').'; margin-bottom:2px; font-weight:600; transition:0.2s;">';
                                echo '<span style="display:flex; align-items:center; gap:8px;">Vườn của '.esc_html((string)$g_name).' <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="rgba(111,219,168,0.7)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg></span>';
                                if ($is_active) echo '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                                echo '</a>';
                            }
                        }
                    } catch (\Throwable $e) {
                        echo '<div style="color:red; padding:10px;">Lỗi: ' . esc_html($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine()) . '</div>';
                    }
                    ?>
                </div>
            </div>
            <script>
            document.addEventListener('click', function(e) {
                var popups = document.querySelectorAll('.eco-garden-popup');
                popups.forEach(function(p) { p.hidden = true; });
            });
            </script>
          <?php endif; ?>
        </div>
        <div class="d2-top-links">
          <?php foreach ($shared_top_links as $top_link): ?>
            <?php if ($top_link['key'] === 'dashboard-2' && $garden_key === '') {
              continue;
            } ?>
            <a href="<?php echo esc_url($top_link['url']); ?>"><?php echo esc_html($top_link['label']); ?></a>
          <?php endforeach; ?>
        </div>
        <div class="d2-top-actions">
          <?php if ($is_admin_user): ?>
            <button class="d2-icon-btn d2-tray-settings-btn" type="button" title="Cài đặt sensor 3 khoang"
              data-tray-settings-open>⚙️</button>
          <?php endif; ?>
          <?php get_template_part('template-parts/site/eco-notification-bell'); ?>
          <button class="d2-profile-trigger" type="button" data-d2-profile-trigger aria-expanded="false"><?php echo $header_avatar_html; ?></button>
          <div class="d2-profile-popup" data-d2-profile-popup hidden>
            <a href="<?php echo esc_url(home_url('/tai-khoan/')); ?>">Quản lý tài khoản</a>
            <a href="<?php echo esc_url(aitrongcay_logout_url()); ?>">Đăng xuất</a>
          </div>
        </div>
      </div>
      <?php if ($rack_notice !== ''): ?>
        <div class="d2-card"
          style="margin-bottom:18px;border-color:<?php echo esc_attr($rack_init_status === 'ok' ? 'rgba(111,219,168,.24)' : 'rgba(255,182,140,.28)'); ?>;background:<?php echo esc_attr($rack_init_status === 'ok' ? 'rgba(49,163,117,.12)' : 'rgba(255,182,140,.10)'); ?>">
          <strong><?php echo esc_html($rack_init_status === 'ok' ? 'Rack đã sẵn sàng' : 'Chưa thể khởi tạo rack'); ?></strong>
          <div style="margin-top:6px;color:var(--muted)"><?php echo esc_html($rack_notice); ?></div>
        </div>
      <?php endif; ?>
      <div class="d2-grid">
        <div class="d2-live">
          <div class="d2-info" style="margin-bottom:-8px;position:relative;z-index:3" data-d2-hero-card>
            <div class="d2-pot-rename" data-pot-inline-name data-pot-code="<?php echo esc_attr($hero_pot_code); ?>"
              data-pot-name="<?php echo esc_attr($hero_name); ?>">
              <h3 class="d2-pot-name pot-inline-name-text" data-d2-hero-name><?php echo esc_html($hero_name); ?></h3>
              <input type="text" class="d2-pot-input pot-inline-input" value="<?php echo esc_attr($hero_name); ?>"
                data-pot-inline-input hidden>
              <?php if ($can_control_garden): ?>
              <button class="d2-pot-edit pot-inline-name-edit" type="button" data-pot-inline-edit
                aria-label="Đổi tên khoang <?php echo esc_attr($hero_pot_code); ?>">✏️</button>
              <?php endif; ?>
              <span class="d2-pot-status pot-inline-save-status" data-pot-inline-status hidden>Nhấn Enter hoặc click ra
                ngoài để lưu</span>
            </div>
            <div class="d2-bar"><span></span></div>
            <small
              data-d2-hero-meta><?php echo esc_html('Loại cây: ' . ($hero_plant_name !== '' ? $hero_plant_name : 'Cây chưa xác định')); ?></small>
          </div>
          <div class="d2-frame">
            <?php
            $hero_is_hls = str_contains(strtolower($hero_stream_url), '.m3u8');
            $hero_is_iframe = $hero_stream_url !== '' && str_contains($hero_stream_url, '/stream.html');
            $hero_is_video = !$hero_is_iframe && $hero_stream_url !== '' && (
              $hero_is_hls
              || str_contains(strtolower($hero_stream_url), '.mp4')
              || str_contains(strtolower($hero_stream_url), '.webm')
            );
            ?>
            <?php if ($hero_is_iframe): ?>
              <iframe src="<?php echo esc_url($hero_stream_url); ?>"
                class="d2-hero-iframe" frameborder="0"
                allow="autoplay; camera; microphone" allowfullscreen></iframe>
              <img src="<?php echo esc_url($hero_image); ?>" alt="<?php echo esc_attr($hero_name); ?>" loading="eager"
                decoding="async" fetchpriority="high" data-d2-hero-image hidden>
            <?php elseif ($hero_is_video): ?>
              <video autoplay muted playsinline controls crossorigin="anonymous" preload="metadata"
                poster="<?php echo esc_url($hero_image); ?>" data-d2-hero-video
                data-stream-url="<?php echo esc_url($hero_stream_url); ?>"></video>
              <img src="<?php echo esc_url($hero_image); ?>" alt="<?php echo esc_attr($hero_name); ?>" loading="eager"
                decoding="async" fetchpriority="high" data-d2-hero-image hidden>
            <?php elseif ($hero_stream_url !== ''): ?>
              <img src="<?php echo esc_url($hero_stream_url); ?>" alt="<?php echo esc_attr($hero_name); ?>"
                loading="eager" decoding="async" fetchpriority="high" data-d2-hero-image
                data-d2-hero-mjpeg="<?php echo esc_url($hero_stream_url); ?>">
            <?php else: ?>
              <img src="<?php echo esc_url($hero_image); ?>" alt="<?php echo esc_attr($hero_name); ?>" loading="eager"
                decoding="async" fetchpriority="high" data-d2-hero-image>
            <?php endif; ?>
            <div class="d2-pill d2-live-tag" data-d2-media-badge><?php echo esc_html($hero_media_badge); ?></div>
            <button class="d2-fullscreen-btn" type="button" aria-label="Toàn màn hình" title="Xem toàn màn hình" onclick="if(document.fullscreenElement){document.exitFullscreen();}else{this.closest('.d2-frame').requestFullscreen();}">⛶</button>
            <div class="d2-bottom">
              <div></div>
              <div class="d2-actions">
                <div class="d2-vitals">
                  <div class="d2-vital">
                    <div>🌡</div>
                    <div class="value" data-d2-temp>
                      <?php echo esc_html($temperature !== '' ? preg_replace('/[^0-9.,-]/', '', $temperature) : '--'); ?>°C
                    </div>
                  </div>
                  <div class="d2-vital">
                    <div>💧</div>
                    <div class="value" data-d2-humidity>
                      <?php echo esc_html($humidity !== '' ? preg_replace('/[^0-9.,-]/', '', $humidity) : '--'); ?>%
                    </div>
                  </div>
                </div>
                <div class="d2-blynk-notice" data-d2-blynk-notice hidden></div>
                <?php if ($can_control_garden): ?>
                <button class="d2-icon-btn" type="button" title="Chụp ảnh khoang hiện tại" data-d2-capture-photo
                  data-pot-code="<?php echo esc_attr($hero_pot_code); ?>">📷</button>
                <button class="d2-icon-btn" type="button" title="Phân tích ảnh mới nhất" data-analyze-latest-photo
                  data-pot-code="<?php echo esc_attr((string) ($hero_pot['code'] ?? '')); ?>">🔎</button>
                <button class="d2-inline-control is-off<?php echo $hero_has_light ? '' : ' is-disabled'; ?>"
                  type="button" <?php echo $hero_has_light ? '' : 'disabled'; ?>
                  data-d2-light-toggle="<?php echo esc_attr($hero_light_device); ?>" data-state="0">
                  <div>
                    <div class="round">💡</div><strong
                      data-d2-light-label><?php echo esc_html($hero_has_light ? 'Bật đèn' : 'Chưa có đèn'); ?></strong>
                  </div>
                </button>
                <button class="d2-inline-control is-off<?php echo $hero_has_pump ? '' : ' is-disabled'; ?>"
                  type="button" <?php echo $hero_has_pump ? '' : 'disabled'; ?> data-d2-pump-toggle="pump"
                  data-state="0">
                  <div>
                    <div class="round">🫧</div><strong
                      data-d2-pump-label><?php echo esc_html($hero_has_pump ? 'Bật bơm' : 'Chưa có bơm'); ?></strong>
                  </div>
                </button>
                <button class="d2-icon-btn" type="button" title="Lịch trình & cài đặt bơm tự động"
                  data-pump-modal-open>⏱</button>
                <button class="d2-inline-control is-off<?php echo $hero_has_mist ? '' : ' is-disabled'; ?>"
                  type="button" <?php echo $hero_has_mist ? '' : 'disabled'; ?> data-d2-mist-toggle="mist"
                  data-state="0">
                  <div>
                    <div class="round">🌫️</div><strong
                      data-d2-mist-label><?php echo esc_html($hero_has_mist ? 'Phun sương' : 'Chưa có phun sương'); ?></strong>
                  </div>
                </button>
                <?php endif; ?>
              </div>
            </div>
          </div>
          
          <?php if (!$has_rack): ?>
            <div class="d2-no-rack-hint is-inline" id="d2NoRackHint" style="top: 16px; max-width: 380px;">
              <p class="d2-no-rack-hint-text">🌱 Bạn chưa có rack. Hãy tiến hành thuê rack để bắt đầu trồng cây!</p>
              <a class="d2-no-rack-hint-cta" href="<?php echo esc_url($rent_rack_url); ?>">Đặt dịch vụ →</a>
              <button type="button" class="d2-no-rack-hint-close" onclick="this.parentElement.style.display='none'">×</button>
            </div>
          <?php else: ?>
            <div class="d2-no-rack-hint is-inline d2-cam-hint" id="d2CamHint" data-upsell-overlay <?php if ($hero_stream_url !== '') echo 'style="display: none;"'; ?>>
              <?php if (str_contains($hero_image, 'hero-greenhouse.svg')): ?>
                <p class="d2-no-rack-hint-text" data-hint-text>📹 Khoang này chưa có ảnh và luồng Camera trực tiếp. Chờ robot tới chụp hoặc lắp đặt thêm để xem 24/7.</p>
              <?php else: ?>
                <p class="d2-no-rack-hint-text" data-hint-text>📹 Bạn muốn xem khu vườn trực tiếp 24/7? Tiến hành lắp đặt Camera ngay.</p>
              <?php endif; ?>
              <a class="d2-no-rack-hint-cta" href="<?php echo esc_url(add_query_arg('garden', $garden_key, home_url('/nang-cap-goi/'))); ?>">Nâng cấp gói →</a>
              <button type="button" class="d2-no-rack-hint-close" onclick="this.parentElement.style.display='none'">×</button>
            </div>
          <?php endif; ?>

          <div class="d2-growth-card" data-d2-growth-journey>
            <div class="d2-growth-head" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
              <div class="d2-growth-badge" data-d2-growth-age-wrap<?php echo isset($hero_growth_journey['ageDays']) && $hero_growth_journey['ageDays'] !== null ? '' : ' hidden'; ?>>
                <span class="d2-growth-badge-dot"></span>
                <span>Ngày thứ <strong
                    data-d2-growth-age><?php echo esc_html((string) ((int) ($hero_growth_journey['ageDays'] ?? 0) + 1)); ?></strong></span>
              </div>
              <?php 
              $is_harvest_stage = false;
              if (!empty($hero_growth_journey['hasGrowthJourney'])) {
                  $active_pos = (int) ($hero_growth_journey['activeStagePosition'] ?? 1);
                  $total_stages = (int) ($hero_growth_journey['growthStageTotal'] ?? 1);
                  if ($active_pos >= $total_stages) {
                      $is_harvest_stage = true;
                  }
              }
              $can_reset_crop = $is_admin_user || $owner_name === ($current_user->display_name ?: $current_user->user_login);
              
              if ($can_reset_crop): 
              ?>
                <?php if ($is_harvest_stage): ?>
                  <button type="button" class="d2-reset-crop-btn" data-reset-crop="<?php echo esc_attr($hero_pot_code); ?>" style="background: rgba(255, 182, 140, 0.15); color: #ffb68c; border: 1px solid rgba(255, 182, 140, 0.4); padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer; transition: all 0.2s; box-shadow: 0 0 10px rgba(255, 182, 140, 0.1);">🍅 Thu hoạch & Lứa mới</button>
                <?php else: ?>
                  <div class="d2-pot-menu-wrap" style="position: relative;">
                    <button type="button" class="d2-pot-menu-trigger" style="background:none;border:none;color:var(--muted);cursor:pointer;font-size:18px;padding:0 8px;line-height:1;transition: color 0.2s;" onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'" onclick="var menu = this.nextElementSibling; if(menu.style.display==='none'){menu.style.display='block';}else{menu.style.display='none';}">⋮</button>
                    <div class="d2-pot-menu-dropdown" style="display:none; position: absolute; right: 0; top: 100%; background: var(--panel); border: 1px solid var(--line); border-radius: 8px; padding: 4px; min-width: 150px; z-index: 10; box-shadow: 0 4px 12px rgba(0,0,0,0.5);">
                      <button type="button" class="d2-reset-crop-btn" data-reset-crop="<?php echo esc_attr($hero_pot_code); ?>" style="width:100%; text-align:left; background:none; border:none; color:var(--muted); padding:8px 12px; font-size:12px; font-weight:500; cursor:pointer; border-radius:4px;" onmouseover="this.style.background='rgba(255,255,255,0.05)'; this.style.color='#ffb68c'" onmouseout="this.style.background='none'; this.style.color='var(--muted)'">🗑️ Hủy vụ & Dọn khoang</button>
                    </div>
                  </div>
                <?php endif; ?>
              <?php endif; ?>
            </div>
            <?php if (!empty($hero_growth_journey['hasGrowthJourney'])): ?>
              <div class="d2-growth-track" data-d2-growth-track>
                <div class="d2-growth-progress" data-d2-growth-progress
                  style="width: <?php echo esc_attr((string) ($hero_growth_journey['progressWidth'] ?? 0)); ?>%"></div>
                <?php foreach ((array) ($hero_growth_journey['stages'] ?? []) as $stage_loop_index => $stage_item): ?>
                  <?php
                  $position = $stage_loop_index + 1;
                  $active_pos = (int) ($hero_growth_journey['activeStagePosition'] ?? 1);
                  $is_active_stage = $position === $active_pos;
                  $is_completed_stage = $position < $active_pos;
                  $stage_state_class = $is_active_stage ? 'is-active' : ($is_completed_stage ? 'is-past' : 'is-future');
                  $stage_icon = $is_active_stage ? '✨' : ($position === 1 ? '🌱' : ($position === (int) ($hero_growth_journey['growthStageTotal'] ?? 1) ? '🍅' : '🪴'));
                  ?>
                  <div class="d2-growth-step <?php echo esc_attr($stage_state_class); ?>" data-d2-growth-step
                    data-stage-position="<?php echo esc_attr((string) $position); ?>">
                    <div class="d2-growth-icon"><span
                        class="d2-growth-icon-emoji"><?php echo esc_html($stage_icon); ?></span><?php if ($is_completed_stage): ?><span
                          class="d2-growth-icon-check">✓</span><?php endif; ?></div>
                    <div class="d2-growth-step-name"><?php echo esc_html((string) ($stage_item['name'] ?? 'Giai đoạn')); ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div class="d2-growth-empty" data-d2-growth-empty><?php echo esc_html($hero_growth_journey['emptyMessage'] ?? 'Chưa có dữ liệu hành trình tăng trưởng cho khoang này.'); ?></div>
            <?php endif; ?>
          </div>
          <div class="d2-ai-strip">
            <div class="d2-alert green">
              <div style="font-weight:800;color:var(--primary);margin-bottom:6px">Current Event</div>
              <div class="d2-alert-badge" data-d2-analysis-badge><?php echo esc_html($analysis_badge_text); ?></div>
              <div class="d2-alert-text" data-d2-current-event>
                <?php echo esc_html($status_summary !== '' ? $status_summary : 'AI đang đọc ảnh và dữ liệu gần nhất của khoang trung tâm.'); ?>
              </div>
            </div>
            <div class="d2-alert amber">
              <div style="font-weight:800;color:#ffb68c;margin-bottom:6px">Recommendation</div>
              <div data-d2-recommendation><?php echo $format_recommendation_html($recommendation); ?>
              </div>
            </div>
          </div>
          <div class="d2-pot-journal" data-d2-journal-wrap data-pot-code="<?php echo esc_attr($hero_pot_code); ?>" style="background: linear-gradient(180deg, rgba(41, 43, 39, .8), rgba(26, 28, 25, .9)); border: 1px solid rgba(111, 219, 168, 0.15);">
            <div class="d2-pot-journal-head">
              <div class="label" data-d2-journal-label style="background: rgba(111, 219, 168, 0.15); border: 1px solid rgba(111, 219, 168, 0.3);">
                <span style="margin-right: 6px">📖</span> Nhật ký sinh trưởng AI
              </div>
              <?php if ($can_control_garden): ?>
              <button class="d2-pot-journal-edit" type="button" title="Ghi chú thủ công" data-d2-journal-edit>✍️</button>
              <?php endif; ?>
            </div>
            
            <div class="d2-ai-daily-logs" style="margin-bottom: 16px; max-height: 300px; overflow-y: auto; padding-right: 8px;">
              <?php 
                $ai_log_entries = [];
                $real_ai_summary = trim((string) ($hero_pot['latest_analysis_summary'] ?? ''));
                $real_ai_updated = trim((string) ($hero_pot['latest_analysis_updated_at'] ?? ''));
                
                if ($real_ai_summary !== '') {
                  $ai_log_entries[] = [
                    'date' => $real_ai_updated !== '' ? wp_date('H:i d/m/Y', strtotime($real_ai_updated)) : wp_date('H:i d/m/Y', current_time('timestamp')),
                    'content' => $real_ai_summary,
                    'type' => 'ai'
                  ];
                } else {
                  $ai_log_entries[] = [
                    'date' => wp_date('H:i d/m/Y', current_time('timestamp')),
                    'content' => 'Chưa có phân tích AI nào cho khoang này. AI đang chờ thu thập dữ liệu hình ảnh và cảm biến để đưa ra đánh giá đầu tiên.',
                    'type' => 'ai'
                  ];
                }
              ?>
              
              <?php foreach($ai_log_entries as $log): ?>
              <div class="d2-log-entry" style="margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px dashed rgba(255,255,255,0.05);">
                <div style="font-size: 11px; color: var(--primary); font-weight: 700; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                  <span>🤖 AI Auto-log</span>
                  <span style="color: rgba(227, 227, 222, 0.4);"><?php echo esc_html($log['date']); ?></span>
                </div>
                <div style="font-size: 13.5px; color: rgba(227, 227, 222, 0.85); line-height: 1.6;">
                  <?php echo esc_html($log['content']); ?>
                </div>
              </div>
              <?php endforeach; ?>
              
              <?php if(trim($hero_journal_text) !== ''): ?>
              <div class="d2-log-entry user-note" style="margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px dashed rgba(255,255,255,0.05);">
                <div style="font-size: 11px; color: #ffb68c; font-weight: 700; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                  <span>👤 Ghi chú của bạn</span>
                </div>
                <div style="font-size: 13.5px; color: rgba(227, 227, 222, 0.85); line-height: 1.6; white-space: pre-line;" data-d2-journal-text>
                  <?php echo esc_html($hero_journal_text); ?>
                </div>
              </div>
              <?php else: ?>
              <p class="d2-pot-journal-text" data-d2-journal-text style="display:none;"></p>
              <?php endif; ?>
            </div>

            <textarea class="d2-pot-journal-input" data-d2-journal-input hidden
              placeholder="Ghi ngắn gọn việc đã làm hôm nay..."><?php echo esc_textarea($hero_journal_text); ?></textarea>
            <div class="d2-pot-journal-status" data-d2-journal-status hidden></div>
          </div>
        </div>
        <div class="d2-sidecards">
          <div class="d2-card d2-trays-card" data-d2-floating-trays>
            <a class="d2-rack-link" href="<?php echo esc_url($rent_rack_url); ?>">+ rack</a>
            <div class="d2-rack-dropdown" data-d2-rack-dropdown <?php echo ! $has_rack ? 'style="display:none;"' : ''; ?>>
              <button class="d2-rack-dropdown-trigger" type="button" data-d2-rack-trigger>
                <span data-d2-rack-label><?php echo esc_html((string) ($rack_switcher_payload[0]['label'] ?? 'Rack 1')); ?></span>
                <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </button>
              <div class="d2-rack-dropdown-menu" data-d2-rack-menu>
                <?php foreach ($rack_switcher_payload as $rack_index => $rack_item): ?>
                  <button class="d2-rack-item<?php echo $rack_index === 0 ? ' is-active' : ''; ?>" type="button" data-d2-rack-item
                    data-rack-key="<?php echo esc_attr((string) ($rack_item['key'] ?? '')); ?>"><?php echo esc_html((string) ($rack_item['label'] ?? 'Rack')); ?></button>
                <?php endforeach; ?>
              </div>
            </div>
            <div class="d2-switcher d2-trays-vertical" data-d2-rack-trays <?php echo ! $has_rack ? 'style="display:none;"' : ''; ?>>
              <?php foreach ($switcher_pots as $pot_item): ?>
                <?php
                $pot_item_url = add_query_arg(['garden' => $garden_key, 'pot' => (string) ($pot_item['code'] ?? '')], home_url('/portal/dashboard-2/'));
                $pot_is_empty = !empty($pot_item['isEmpty']);
                $_pot_code_tmp = (string) ($pot_item['code'] ?? '');
                $_tray_name_tmp = $rack_slot_tray_name_map[$_pot_code_tmp] ?? '';
                $_has_cam_tmp = isset($rack_slot_webcam_map[$_pot_code_tmp]);
                $pot_label = $_tray_name_tmp !== '' ? $_tray_name_tmp : (string) (($pot_item['plant_name'] ?? '') !== '' ? $pot_item['plant_name'] : ($pot_item['name'] ?? 'Khoang cây'));
                ?>
                <?php
                $_snap_url  = $pot_snap_map[$_pot_code_tmp]      ?? '';
                $_latest_tl = $pot_latest_tl_map[$_pot_code_tmp] ?? '';
                // Chỉ dùng ảnh local: timelapse photo hoặc transparent placeholder (JS sẽ load snap)
                // Tránh dùng $pot_item['image'] vì có thể là HTTPS external URL bị lỗi SSL
                if ($_latest_tl !== '') {
                  $_thumb_src = $_latest_tl;
                } elseif ($_snap_url !== '') {
                  $_thumb_src = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
                } else {
                  $_thumb_src = '';
                }
                ?>
                <div
                  class="d2-switch<?php echo ((string) ($pot_item['code'] ?? '') === ($hero_pot['code'] ?? '')) ? ' active' : ''; ?><?php echo $pot_is_empty ? ' is-empty' : ''; ?>"
                  data-d2-switch-item data-pot-code="<?php echo esc_attr((string) ($pot_item['code'] ?? '')); ?>">
                  <a href="<?php echo esc_url($pot_item_url); ?>" class="d2-thumb" data-d2-switch-link
                    data-pot-code="<?php echo esc_attr((string) ($pot_item['code'] ?? '')); ?>"
                    data-pot-empty="<?php echo $pot_is_empty ? '1' : '0'; ?>">
                    <?php if ($pot_is_empty): ?>
                      <span class="d2-tray-empty" aria-hidden="true"><span class="d2-tray-empty-mark"><strong>Khoang trống</strong><small>Sẵn sàng đặt khoang</small></span></span>
                    <?php elseif ($_thumb_src !== ''): ?>
                      <img src="<?php echo esc_attr($_thumb_src); ?>"
                        alt="<?php echo esc_attr($pot_label); ?>"
                        <?php if ($_snap_url !== ''): ?>data-snap="<?php echo esc_attr($_snap_url); ?>"<?php endif; ?>
                        loading="lazy" decoding="async" fetchpriority="low">
                    <?php endif; ?>
                  </a>
                  <span><?php echo esc_html($pot_label); ?><?php if ($_has_cam_tmp): ?><i class="d2-cam-dot" aria-label="Có camera"></i><?php endif; ?></span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="d2-trays-card-placeholder" aria-hidden="true"></div>
        </div>
      </div>

      <?php
        // Build stream list for timelapse viewer directly from rack_slots to ensure correct physical mapping
        $pots_by_rack = [];
        $rack_names_tl = [];
        
        global $wpdb;
        $racks_table = function_exists('aitrongcay_garden_racks_table') ? aitrongcay_garden_racks_table() : $wpdb->prefix . 'aitr_garden_racks';
        $assigned_racks = $wpdb->get_results($wpdb->prepare("SELECT id, garden_key, slot_count FROM {$racks_table} WHERE garden_key = %s", $garden_key), ARRAY_A);
        
        $cloned_rack_ids = get_option('aitrongcay_cloned_racks_' . $garden_key, []);
        $cloned_rack_ids = is_array($cloned_rack_ids) ? array_values($cloned_rack_ids) : (is_string($cloned_rack_ids) && $cloned_rack_ids !== '' ? explode(',', $cloned_rack_ids) : array_values((array) $cloned_rack_ids));
        if (!empty($cloned_rack_ids)) {
            $ids_placeholder = implode(',', array_fill(0, count($cloned_rack_ids), '%d'));
            $cloned_racks = $wpdb->get_results($wpdb->prepare("SELECT id, garden_key, slot_count FROM {$racks_table} WHERE id IN ($ids_placeholder)", ...$cloned_rack_ids), ARRAY_A);
            if (!empty($cloned_racks)) {
                if (!is_array($assigned_racks)) $assigned_racks = [];
                $assigned_racks = array_merge($assigned_racks, $cloned_racks);
            }
        }
        
        $rack_slot_counts = [];
        $tl_rack_slots = [];
        if ($assigned_racks) {
            $unique_gks = [];
            foreach ($assigned_racks as $r) {
                $rack_slot_counts[(int)$r['id']] = (int)$r['slot_count'];
                if (!empty($r['garden_key'])) {
                    $unique_gks[$r['garden_key']] = true;
                }
            }
            
            if (function_exists('aitrongcay_get_rack_slots')) {
                foreach (array_keys($unique_gks) as $gk) {
                    $s = aitrongcay_get_rack_slots((string) $gk);
                    if (!empty($s)) {
                        $tl_rack_slots = array_merge($tl_rack_slots, $s);
                    }
                }
            }
        }

        if (!empty($tl_rack_slots)) {
            foreach ($tl_rack_slots as $slot) {
                $_rack_id = (int)($slot['rack_id'] ?? 0);
                if ($_rack_id === 0) continue;
                
                if (!isset($rack_slot_counts[$_rack_id])) continue; // Skip racks we don't own/clone
                
                $_slot_index = (int)($slot['slot_index'] ?? 1);
                $_slot_count = $rack_slot_counts[$_rack_id] ?? 100;
                if ($_slot_index > $_slot_count) continue;
                
                $rack_names_tl[$_rack_id] = trim((string)($slot['rack_name'] ?? '')) ?: 'Rack ' . $_rack_id;
                
                $_code = trim((string)($slot['pot_code'] ?? ''));
                if ($_code === '') continue; // Requires a valid pot_code for library attachment
                
                $_tl_wurl = trim((string)($slot['camera_stream_url'] ?? ''));
                $_tl_slug = '';
                if ($_tl_wurl !== '' && str_contains($_tl_wurl, 'src=')) {
                    parse_str(parse_url($_tl_wurl, PHP_URL_QUERY) ?? '', $_tl_qp);
                    $_tl_slug = sanitize_key($_tl_qp['src'] ?? '');
                }

                // Get name from active pots if available, else fallback to physical slot label
                $_label = 'Khoang ' . max(1, (int)($slot['slot_index'] ?? 0));
                if (isset($switcher_pot_map[$_code]) && trim((string)($switcher_pot_map[$_code]['name'] ?? '')) !== '') {
                    $_label = trim((string)$switcher_pot_map[$_code]['name']);
                }

                if (!isset($pots_by_rack[$_rack_id])) {
                    $pots_by_rack[$_rack_id] = [];
                }
                
                $pots_by_rack[$_rack_id][] = [
                    'slug' => $_code, // Using pot_code to match with Media Library
                    'legacy_slug' => $_tl_slug,
                    'label' => $_label
                ];
            }
        }
      ?>

      <div class="d2-timelapse-panel" id="d2TimelapsePanel" style="display:none">
        <div class="d2-tl-header">
          <h2>📽 Timelapse</h2>
          <button type="button" class="d2-tl-back-btn" id="d2TlBack">← Quay lại vườn</button>
        </div>
        <?php if (empty($pots_by_rack)): ?>
          <div class="d2-tl-empty-state">
            Chưa có khoang cây nào được cấu hình cho vườn này.
          </div>
        <?php else: 
          $selected_rack_id = isset($_GET['tl_rack']) ? (int)$_GET['tl_rack'] : -999;
          if (!isset($pots_by_rack[$selected_rack_id])) {
              $selected_rack_id = array_key_first($pots_by_rack);
          }
          $selected_stream_slug = isset($_GET['tl_stream']) ? sanitize_text_field($_GET['tl_stream']) : '';
        ?>
          <div class="d2-tl-controls">
            <?php if (count($pots_by_rack) > 1): ?>
            <label>Rack:
              <select id="d2TlRack" onchange="AITR_UPDATE_TIMELAPSE_CAMERA_DROPDOWN(this.value)">
                <?php foreach ($pots_by_rack as $r_id => $r_pots): ?>
                  <option value="<?php echo esc_attr((string)$r_id); ?>" <?php selected($r_id, $selected_rack_id); ?>><?php echo esc_html($rack_names_tl[$r_id] ?? 'Rack'); ?></option>
                <?php endforeach; ?>
              </select>
            </label>
            <?php endif; ?>
            <label>Khoang:
              <select id="d2TlStream">
                <?php
                $active_rack_pots = $pots_by_rack[$selected_rack_id] ?? [];
                foreach ($active_rack_pots as $ts): ?>
                  <option value="<?php echo esc_attr($ts['slug']); ?>" data-legacy="<?php echo esc_attr($ts['legacy_slug']); ?>" <?php selected($ts['slug'], $selected_stream_slug); ?>><?php echo esc_html($ts['label']); ?></option>
                <?php endforeach; ?>
              </select>
            </label>
            <label>Xem:
              <select id="d2TlDays">
                <option value="7" selected>7 ngày qua</option>
                <option value="30">30 ngày qua</option>
                <option value="all">Tất cả</option>
              </select>
            </label>
            <button type="button" class="d2-tl-load-btn" id="d2TlLoad">▷ Tải timelapse</button>
            <?php if ($is_admin_user): ?>
              <button type="button" class="d2-tl-load-btn" onclick="document.getElementById('d2RobotModal').style.display='flex'" style="background:rgba(111,219,168,.18);color:#6fdba8;border:1px solid rgba(111,219,168,.3)" title="Điều khiển Robot đến điểm chụp">🤖 ĐK Robot</button>
            <?php endif; ?>
            <button type="button" class="d2-tl-load-btn" id="d2TlCaptureNow" style="background:rgba(255,182,140,.18);color:#ffb68c" title="Test: chụp ảnh ngay từ go2rtc và lưu vào thư viện">📷 Chụp ngay</button>
            <span id="d2TlCaptureStatus" style="font-size:13px;color:var(--muted)"></span>
          </div>
          <div class="d2-tl-player">
            <div class="d2-tl-frame-wrap">
              <img id="d2TlImg" src="" alt="Timelapse frame" style="display:none">
              <div class="d2-tl-empty" id="d2TlEmpty">Nhấn "Tải timelapse" để xem ảnh đã lưu</div>
            </div>
            <div id="d2TlSensors" class="d2-tl-sensor-bar" style="display:none">
              <span class="tls-item" id="tlsTemp">🌡 --°C</span>
              <span class="tls-item" id="tlsHum">💧 --%</span>
              <span class="tls-item" id="tlsSoil">🌿 --% đất</span>
              <span class="tls-item" id="tlsPh">⚗️ pH --</span>
              <span class="tls-item" id="tlsEc">🌱 -- mS</span>
            </div>
            <div class="d2-tl-progress-wrap" id="d2TlProgressWrap" style="display:none">
              <input type="range" id="d2TlScrub" min="0" max="0" value="0">
            </div>
            <div class="d2-tl-playbar" id="d2TlPlaybar" style="display:none">
              <button type="button" id="d2TlFirst" title="Ảnh đầu">⏮</button>
              <button type="button" id="d2TlPlayPause" title="Phát / Tạm dừng">▶</button>
              <button type="button" id="d2TlLast" title="Ảnh cuối">⏭</button>
              <span class="d2-tl-info" id="d2TlInfo">--</span>
              <label style="margin-left:auto;display:flex;align-items:center;gap:8px;font-size:13px;color:var(--muted)">Tốc độ:
                <select id="d2TlSpeed">
                  <option value="3">3 fps</option>
                  <option value="5">5 fps</option>
                  <option value="10" selected>10 fps</option>
                  <option value="20">20 fps</option>
                </select>
              </label>
              <button type="button" id="d2TlDownload" title="Ghép tất cả ảnh thành video và tải về">⬇ Tải video</button>
              <span id="d2TlDownloadStatus" style="font-size:12px;color:var(--muted)"></span>
            </div>
            <!-- Share row — hiện sau khi tải ảnh -->
            <div class="d2-tl-share-row" id="d2TlShareRow" style="display:none">
              <span class="d2-tl-share-label">Chia sẻ:</span>
              <button type="button" class="d2-tl-share-btn tl-save" id="d2TlSaveFrame" title="Tải ảnh hiện tại về máy">📷 Lưu ảnh</button>
              <button type="button" class="d2-tl-share-btn tl-fb" id="d2TlShareFb" title="Chia sẻ lên Facebook">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                Facebook
              </button>
              <button type="button" class="d2-tl-share-btn tl-zalo" id="d2TlShareZalo" title="Chia sẻ lên Zalo">
                <svg width="14" height="14" viewBox="0 0 48 48" fill="currentColor"><path d="M24 4C13 4 4 13 4 24s9 20 20 20 20-9 20-20S35 4 24 4zm7.6 28.4c-.5.4-1.2.2-1.7-.1l-4.2-3.1c-.3-.2-.7-.2-1 0l-5.8 4.3c-.6.4-1.4.1-1.6-.6L13.5 17c-.2-.7.4-1.4 1.1-1.2l16.8 5.2c.7.2 1 1 .6 1.6l-2.8 4.6c-.2.3-.1.7.1 1l3.5 3.5c.5.5.3 1.4-.2 1.7z"/></svg>
                Zalo
              </button>
              <?php if (isset($_SERVER['HTTPS']) || (isset($_SERVER['SERVER_NAME']) && str_contains((string)($_SERVER['SERVER_NAME'] ?? ''), 'localhost') === false)): ?>
              <button type="button" class="d2-tl-share-btn tl-native" id="d2TlShareNative" title="Chia sẻ qua ứng dụng khác">↗ Chia sẻ</button>
              <?php endif; ?>
              <span class="d2-tl-privacy-note">🔒 Chỉ bạn mới xem được timelapse vườn này</span>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <?php if (false && !empty($rack_configs)): ?>
      <div class="d2-rack-monitor" id="d2-rack-monitor" hidden>
        <div class="d2-rack-monitor-head">
          <span>🌿 Rack Monitor – Cảm biến thời gian thực</span>
          <span class="d2-rack-monitor-hint">Cập nhật mỗi 5 giây</span>
        </div>
        <?php foreach ($rack_configs as $ri => $rack):
          $rack_trays = (array) ($rack['trays'] ?? []);
          ?>
          <div class="d2-rack-section" data-rack-section="<?php echo $ri; ?>">
            <div class="d2-rack-label">
              <span>🗂 <?php echo esc_html($rack['rack_name'] ?: ('Rack ' . ($ri + 1))); ?></span>
              <span class="d2-rack-tray-count"><?php echo count($rack_trays); ?> khoang</span>
            </div>
            <div class="d2-tray-lanes">
              <?php foreach ($rack_trays as $ti => $tray):
                $wurl = trim((string) ($tray['webcam_url'] ?? ''));
                $is_hls = $wurl !== '' && str_ends_with(strtolower($wurl), '.m3u8');
                $is_img = $wurl !== '' && !$is_hls;
                $no_tok = trim((string) ($tray['blynk_token'] ?? '')) === '';
                $lane_id = $ri . '-' . $ti;
                ?>
                <div class="d2-tray-lane" data-tray-lane="<?php echo $lane_id; ?>">
                  <div class="d2-tray-lane-head">
                    <span class="d2-tray-dot<?php echo $no_tok ? ' is-uncfg' : ''; ?>"
                      data-tray-dot="<?php echo $lane_id; ?>"></span>
                    <strong><?php echo esc_html($tray['name'] ?: ('Khoang ' . ($ti + 1))); ?></strong>
                    <?php if ($no_tok): ?><span class="d2-tray-uncfg-tag">Chưa cấu hình</span><?php endif; ?>
                  </div>
                  <div class="d2-tray-cam">
                    <?php if ($is_hls): ?>
                      <video class="d2-tray-video" data-tray-hls="<?php echo esc_attr($wurl); ?>" autoplay muted
                        playsinline></video>
                    <?php elseif ($is_img): ?>
                      <img class="d2-tray-mjpeg" data-tray-mjpeg="<?php echo esc_attr($wurl); ?>"
                        src="<?php echo esc_attr($wurl); ?>"
                        alt="<?php echo esc_attr($tray['name'] ?: ('Khoang ' . ($ti + 1))); ?>">
                    <?php else: ?>
                      <div class="d2-tray-cam-empty">📷 Chưa có webcam</div>
                    <?php endif; ?>
                  </div>
                  <div class="d2-tray-sensors">
                    <div class="d2-tray-sensor" data-tray-sensor-wrap="<?php echo $lane_id; ?>" data-sensor-key="temp"><span
                        class="dts-icon">🌡</span><span class="dts-label">Nhiệt độ</span><span class="dts-val"
                        data-tray-val>--</span><span class="dts-unit">°C</span></div>
                    <div class="d2-tray-sensor" data-tray-sensor-wrap="<?php echo $lane_id; ?>" data-sensor-key="hum"><span
                        class="dts-icon">💧</span><span class="dts-label">Độ ẩm</span><span class="dts-val"
                        data-tray-val>--</span><span class="dts-unit">%</span></div>
                    <div class="d2-tray-sensor" data-tray-sensor-wrap="<?php echo $lane_id; ?>" data-sensor-key="ph"><span
                        class="dts-icon">⚗️</span><span class="dts-label">pH</span><span class="dts-val"
                        data-tray-val>--</span><span class="dts-unit"></span></div>
                    <div class="d2-tray-sensor" data-tray-sensor-wrap="<?php echo $lane_id; ?>" data-sensor-key="ec"><span
                        class="dts-icon">🌱</span><span class="dts-label">EC</span><span class="dts-val"
                        data-tray-val>--</span><span class="dts-unit">mS</span></div>
                  </div>
                  <div class="d2-tray-controls">
                    <button class="d2-tray-ctrl-btn is-off" type="button" data-tray-ctrl-btn
                      data-rack-index="<?php echo $ri; ?>" data-tray-index="<?php echo $ti; ?>" data-tray-ctrl="light"
                      data-state="0">💡 <span>Đèn</span></button>
                    <button class="d2-tray-ctrl-btn is-off" type="button" data-tray-ctrl-btn
                      data-rack-index="<?php echo $ri; ?>" data-tray-index="<?php echo $ti; ?>" data-tray-ctrl="pump"
                      data-state="0">🔄 <span>Bơm</span></button>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($is_admin_user || $can_control_garden): ?>
      <!-- ROBOT CONTROL MODAL -->
      <div class="d2-tray-settings-overlay" id="d2RobotModal" style="display:none; align-items:center; justify-content:center; z-index: 10000; background: rgba(0,0,0,0.8); backdrop-filter: blur(4px);">
        <div class="d2-tray-settings-box" style="max-width: 800px; width: 95%; padding: 24px; position: relative; max-height: 95vh; overflow-y: auto;">
          <div class="d2-tray-settings-head">
            <strong>🤖 Điều khiển Robot Camera</strong>
            <button type="button" class="d2-tray-settings-close" onclick="document.getElementById('d2RobotModal').style.display='none'">✕</button>
          </div>
          <div style="color: var(--muted); font-size: 13px; margin-bottom: 20px; line-height: 1.5;">
            Chọn <strong>Vị trí</strong> để điều hướng robot đến chụp ảnh. Lệnh sẽ được gửi tới Firebase.
          </div>
          
          <div style="display:flex; justify-content:center; gap:10px; margin-bottom: 20px;">
            <button type="button" id="rbView2dBtn" style="padding: 8px 20px; background: rgba(111, 219, 168, 0.25); border: 1px solid #6fdba8; color: #fff; border-radius: 8px; cursor: pointer; font-weight: 600; transition: 0.2s;">Mặt bằng 2D</button>
            <button type="button" id="rbView3dBtn" style="padding: 8px 20px; background: #1c1f1c; border: 1px solid rgba(111, 219, 168, 0.2); color: #8e9c91; border-radius: 8px; cursor: pointer; font-weight: 600; transition: 0.2s;">Mô hình 3D</button>
          </div>

          <div id="rb3dView" style="display:none; height: 550px; margin-bottom: 24px; border-radius: 12px; overflow: hidden; border: 1px solid var(--line);">
            <iframe id="rb3dIframe" src="<?php echo esc_url(home_url('/test-3d.html?v=' . time())); ?>" style="width:100%; height:100%; border:none;"></iframe>
          </div>

          <style>
          .robot-map { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 20px; }
          .rb-node { background: #1c1f1c; border: 1px solid rgba(111, 219, 168, 0.2); color: #8e9c91; padding: 12px 0; text-align: center; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 700; transition: all 0.2s; }
          .rb-node:hover { background: rgba(111, 219, 168, 0.1); color: #fff; }
          .rb-node.active { background: rgba(111, 219, 168, 0.25); border-color: #6fdba8; color: #fff; box-shadow: 0 0 12px rgba(111, 219, 168, 0.3); }
          .rb-tier { display: flex; gap: 10px; margin-bottom: 24px; }
          .rb-tier-btn { flex: 1; padding: 12px; background: #1c1f1c; border: 1px solid rgba(255, 182, 140, 0.2); color: #bcaaa0; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 13px; transition: 0.2s; }
          .rb-tier-btn:hover { background: rgba(255, 182, 140, 0.1); }
          .rb-tier-btn.active { background: rgba(255, 182, 140, 0.2); border-color: #ffb68c; color: #ffb68c; }
          .rb-rail { height: 4px; background: #4a544d; grid-column: 1 / -1; margin: 0 10px -20px 10px; position: relative; top: -5px; border-radius: 4px; }
          .rb-path { width: 2px; height: 100%; background: #333a35; position: absolute; left: 50%; top: 0; z-index: -1; }
          </style>

          <div id="rb2dView">
            <div style="background: rgba(0,0,0,0.4); border: 1px solid var(--line); border-radius: 12px; padding: 16px; margin-bottom: 24px; position: relative; z-index: 1;">
              <div style="text-align:center; font-size: 11px; color: var(--muted); margin-bottom: 10px; letter-spacing: 1px;">BẢN ĐỒ MẶT BẰNG (X, Y)</div>
              <div class="robot-map" id="rbMap">
                <div class="rb-rail"></div>
                <div class="rb-node" data-node="N09">N09</div>
                <div class="rb-node" data-node="N06">N06</div>
                <div class="rb-node" data-node="N03">N03</div>
                <div class="rb-node" data-node="N00">N00</div>

                <div class="rb-node" data-node="N10">N10</div>
                <div class="rb-node" data-node="N07">N07</div>
                <div class="rb-node" data-node="N04">N04</div>
                <div class="rb-node" data-node="N01">N01</div>

                <div class="rb-node" data-node="N11">N11</div>
                <div class="rb-node" data-node="N08">N08</div>
                <div class="rb-node" data-node="N05">N05</div>
                <div class="rb-node" data-node="N02">N02</div>
              </div>
            </div>

            <div style="text-align:center; font-size: 11px; color: var(--muted); margin-bottom: 10px; letter-spacing: 1px;">CHỌN CAO ĐỘ (Z)</div>
            <div class="rb-tier" id="rbTiers">
              <button class="rb-tier-btn" data-tier="H0">Tầng 1 (H0)</button>
              <button class="rb-tier-btn" data-tier="H1">Tầng 2 (H1)</button>
              <button class="rb-tier-btn" data-tier="H2">Tầng 3 (H2)</button>
            </div>
          </div>

          <div style="background: rgba(111,219,168,0.05); border: 1px dashed rgba(111,219,168,0.3); padding: 16px; border-radius: 8px; margin-bottom: 24px; text-align: center;">
            <div style="font-size: 12px; color: var(--muted); margin-bottom: 4px;">Chuỗi lệnh xuất ra:</div>
            <div id="rbCommandOutput" style="font-size: 24px; font-weight: 800; color: #6fdba8; letter-spacing: 2px;">--</div>
          </div>

          <button id="rbSendCmdBtn" style="width: 100%; padding: 14px; background: var(--primary); color: #000; border: none; border-radius: 12px; font-size: 15px; font-weight: 800; cursor: pointer; transition: 0.2s;" disabled>
            🚀 Gửi Lệnh Chụp Ảnh
          </button>
          <div id="rbStatusMsg" style="text-align: center; font-size: 13px; margin-top: 12px; font-weight: 600; display: none;"></div>
        </div>
      </div>
      <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-app.js";
        import { getDatabase, ref, set, onValue, update } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-database.js";

        const firebaseConfig = {
          apiKey: "AIzaSyC44ooWwCvohb4_rc3fdQrV0nzHebyHg7Y",
          authDomain: "aitrongcay-robot.firebaseapp.com",
          databaseURL: "https://aitrongcay-robot-default-rtdb.asia-southeast1.firebasedatabase.app",
          projectId: "aitrongcay-robot",
          storageBucket: "aitrongcay-robot.firebasestorage.app",
          messagingSenderId: "599719734667",
          appId: "1:599719734667:web:9f16972ae67470e4c2138e"
        };

        const app = initializeApp(firebaseConfig);
        const db = getDatabase(app);

        let selectedNode = null;
        let selectedTier = null;
        const nodes = document.querySelectorAll('.rb-node');
        const tiers = document.querySelectorAll('.rb-tier-btn');
        const output = document.getElementById('rbCommandOutput');
        const btn = document.getElementById('rbSendCmdBtn');
        const msg = document.getElementById('rbStatusMsg');

        const btn2d = document.getElementById('rbView2dBtn');
        const btn3d = document.getElementById('rbView3dBtn');
        const view2d = document.getElementById('rb2dView');
        const view3d = document.getElementById('rb3dView');

        btn2d.addEventListener('click', () => {
          view2d.style.display = 'block';
          view3d.style.display = 'none';
          btn2d.style.background = 'rgba(111, 219, 168, 0.25)';
          btn2d.style.color = '#fff';
          btn2d.style.borderColor = '#6fdba8';
          btn3d.style.background = '#1c1f1c';
          btn3d.style.color = '#8e9c91';
          btn3d.style.borderColor = 'rgba(111, 219, 168, 0.2)';
        });

        btn3d.addEventListener('click', () => {
          view2d.style.display = 'none';
          view3d.style.display = 'block';
          btn3d.style.background = 'rgba(111, 219, 168, 0.25)';
          btn3d.style.color = '#fff';
          btn3d.style.borderColor = '#6fdba8';
          btn2d.style.background = '#1c1f1c';
          btn2d.style.color = '#8e9c91';
          btn2d.style.borderColor = 'rgba(111, 219, 168, 0.2)';
        });

        window.addEventListener('message', (event) => {
          if (event.data && event.data.type === 'robot_node_selected') {
            const nodeId = event.data.id; // e.g. "N00H2"
            if (nodeId && nodeId.length >= 5) {
               const nodePart = nodeId.substring(0, 3);
               const tierPart = nodeId.substring(3);
               
               // Cập nhật giao diện 2D cho đồng bộ
               nodes.forEach(x => {
                 x.classList.remove('active');
                 if(x.getAttribute('data-node') === nodePart) x.classList.add('active');
               });
               tiers.forEach(x => {
                 x.classList.remove('active');
                 if(x.getAttribute('data-tier') === tierPart) x.classList.add('active');
               });
               
               selectedNode = nodePart;
               selectedTier = tierPart;
               updateOutput();
            }
          }
        });

        function updateOutput() {
          if (selectedNode && selectedTier) {
            output.textContent = selectedNode + selectedTier;
            btn.disabled = false;
            btn.style.opacity = '1';
          } else {
            output.textContent = '--';
            btn.disabled = true;
            btn.style.opacity = '0.5';
          }
        }

        nodes.forEach(n => {
          n.addEventListener('click', function() {
            nodes.forEach(x => x.classList.remove('active'));
            this.classList.add('active');
            selectedNode = this.getAttribute('data-node');
            updateOutput();
          });
        });

        tiers.forEach(t => {
          t.addEventListener('click', function() {
            tiers.forEach(x => x.classList.remove('active'));
            this.classList.add('active');
            selectedTier = this.getAttribute('data-tier');
            updateOutput();
          });
        });

        const commandRef = ref(db, 'robot/camera_command');

        // Lắng nghe tín hiệu "đã đến nơi" từ robot để tự động chụp ảnh
        // Quy ước chuẩn: 0 = pending (đang chạy/chưa đến), 1 = arrived (đã đến)
        onValue(commandRef, (snapshot) => {
          const data = snapshot.val();
          if (data && data.status === 1 && data.command !== '') {
            msg.style.display = 'block';
            msg.style.color = '#6fdba8';
            msg.innerHTML = `✅ Robot đã đến điểm <b>${data.command || ''}</b>! Đang tự động chụp ảnh...`;
            
            // Tự động gọi API server để lấy ảnh từ camera của Robot (bỏ qua cấu hình riêng của từng khoang)
            const captureFormData = new FormData();
            captureFormData.append('action', 'aitrongcay_capture_photo_server');
            captureFormData.append('garden_key', data.garden_key || '');
            captureFormData.append('pot_code', data.pot_code || '');
            captureFormData.append('robot_stream', '<?php echo esc_js(get_option('aitrongcay_robot_camera_url', 'https://determine-exchanges-modification-include.trycloudflare.com/api/frame.jpeg?src=vuon2')); ?>');
            captureFormData.append('nonce', typeof AITR_AJAX_NONCE !== 'undefined' ? AITR_AJAX_NONCE : '');

            fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', { method: 'POST', body: captureFormData })
              .then(res => res.json())
              .then(res => {
                  if (res && res.success) {
                      let pointsText = '';
                      if (res.data.bonus_points && res.data.bonus_points > 0) {
                          pointsText = ` Nhận được +${res.data.bonus_points} Eco Points!`;
                      }
                      msg.innerHTML += `<br><span style="color:#6fdba8;">✅ Đã lưu ảnh tự động vào Kho ảnh của ${data.pot_code}.${pointsText} (<a href="${res.data.url}" target="_blank" style="color:#fff;text-decoration:underline;">Xem ảnh gốc</a>)</span>`;
                      console.log('✅ THÀNH CÔNG: Ảnh đã được chụp và lưu!', res.data);
                      
                      // Hiển thị Popup ảnh vừa chụp
                      const popup = document.createElement('div');
                      popup.style.cssText = 'position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.85);z-index:999999;display:flex;align-items:center;justify-content:center;flex-direction:column;cursor:pointer;';
                      popup.innerHTML = `
                        <div style="background:#fff;padding:16px;border-radius:16px;text-align:center;max-width:90%;box-shadow:0 10px 30px rgba(0,0,0,0.5);">
                            <h3 style="margin-top:0;margin-bottom:12px;color:#2f7b45;">✅ Đã chụp ảnh khoang ${data.pot_code}</h3>
                            <img src="${res.data.url}" style="max-width:100%;max-height:65vh;border-radius:8px;display:block;">
                            <p style="margin-top:12px;margin-bottom:0;color:#666;font-size:14px;">(Bấm vào bất kỳ đâu để đóng)</p>
                        </div>
                      `;
                      popup.onclick = () => popup.remove();
                      document.body.appendChild(popup);
                  } else {
                      msg.innerHTML += `<br><span style="color:#ffb68c;">❌ Lỗi lưu ảnh: ${(res && res.data && res.data.message) || 'Chưa chụp được ảnh.'}</span>`;
                      console.error('❌ LỖI CHỤP ẢNH:', res);
                  }
              })
              .catch(err => {
                  msg.innerHTML += `<br><span style="color:#ffb68c;">❌ Lỗi kết nối khi lưu ảnh.</span>`;
                  console.error('❌ LỖI MẠNG / SERVER:', err);
              });

            // Xóa lệnh hiện tại để tránh việc tự động chụp lại khi F5 (đưa về trạng thái rảnh)
            update(commandRef, { command: '', status: 0 });
            
            // Ẩn thông báo sau 5 giây
            setTimeout(() => { msg.style.display = 'none'; }, 5000);
          }
        });

        btn.addEventListener('click', function() {
          if(!selectedNode || !selectedTier) return;
          const cmd = selectedNode + selectedTier;
          btn.disabled = true;
          btn.textContent = 'Đang gửi lệnh...';
          
          // Gửi AJAX để phân tích xem Node/Tier này thuộc Rack của Khách hàng nào
          const formData = new FormData();
          formData.append('action', 'aitrongcay_resolve_robot_node');
          formData.append('command', cmd);
          
          fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
            method: 'POST',
            body: formData
          })
          .then(res => res.json())
          .then(res => {
            let targetGardenKey = AITR_GARDEN_KEY || '';
            let targetPotCode = '<?php echo esc_js($hero_pot_code); ?>';
            
            if (res && res.success && res.data) {
                if (res.data.garden_key) targetGardenKey = res.data.garden_key;
                if (res.data.pot_code) targetPotCode = res.data.pot_code;
            }

            const payload = {
              command: cmd,
              status: 0,
              timestamp: Date.now(),
              garden_key: targetGardenKey,
              pot_code: targetPotCode
            };

            // GỬI LẦN 1: Đánh thức robot (Wakeup Ping)
            set(commandRef, payload).then(() => {
              // Đợi 300ms (đủ để robot kết nối lại)
              setTimeout(() => {
                // GỬI LẦN 2: Chốt lệnh thật để robot chạy
                payload.timestamp = Date.now(); // Cập nhật lại time để Firebase hiểu là có thay đổi
              set(commandRef, payload)
              .then(() => {
                msg.style.display = 'block';
                msg.style.color = '#6fdba8';
                msg.innerHTML = `✅ Lệnh <b>${cmd}</b> đã được gửi tới Firebase. Đang chờ robot di chuyển...`;
                btn.textContent = '🚀 Gửi Lệnh Chụp Ảnh';
                btn.disabled = false;
              })
              .catch((error) => {
                console.error(error);
                msg.style.display = 'block';
                msg.style.color = '#ffb68c';
                msg.innerHTML = `❌ Lỗi khi gửi lệnh: ${error.message}`;
                btn.textContent = '🚀 Thử Lại';
                btn.disabled = false;
              });
            }, 300);
          });
          }).catch(err => {
              console.error('AJAX Error:', err);
              btn.textContent = 'Lỗi kết nối';
              btn.disabled = false;
          });
        });
      </script>
      <!-- END ROBOT CONTROL MODAL -->
    <?php endif; ?>

    <?php if ($is_admin_user): ?>
      <div class="d2-tray-settings-overlay" data-tray-settings-modal hidden>
        <div class="d2-tray-settings-box">
          <div class="d2-tray-settings-head">
            <strong>⚙️ Cài đặt Rack Monitor</strong>
            <button type="button" class="d2-tray-settings-close" data-tray-settings-close>✕</button>
          </div>
          <div class="d2-tray-settings-body" data-tray-settings-racks>
            <?php foreach ($rack_configs as $ri => $rack): ?>
              <div class="d2-rack-cfg is-open" data-rack-cfg>
                <div class="d2-rack-cfg-head" data-rack-cfg-toggle>
                  <span class="d2-rack-cfg-arrow">▼</span>
                  <input class="d2-rack-name-input" type="text" data-cfg-rack-name
                    value="<?php echo esc_attr($rack['rack_name']); ?>" placeholder="Rack <?php echo $ri + 1; ?>">
                  <button type="button" class="d2-rack-remove-btn" data-remove-rack title="Xóa rack">✕</button>
                </div>
                <div class="d2-rack-cfg-body">
                  <div class="d2-tray-cfg-list" data-tray-cfg-list>
                    <?php foreach ($rack['trays'] as $ti => $tray): ?>
                      <div class="d2-tray-cfg-item" data-tray-cfg-item>
                        <div class="d2-tray-cfg-title">
                          <span>🌿 Khoang <?php echo $ti + 1; ?></span>
                          <button type="button" class="d2-tray-remove-btn" data-remove-tray title="Xóa khoang">✕</button>
                        </div>
                        <div class="d2-tray-cfg-row"><label>Tên khoang<input type="text" data-tray-field data-key="name"
                              value="<?php echo esc_attr($tray['name']); ?>"></label></div>
                        <div class="d2-tray-cfg-row"><label>Blynk Token<input type="text" data-tray-field
                              data-key="blynk_token" value="<?php echo esc_attr($tray['blynk_token']); ?>"
                              placeholder="Auth token"></label></div>
                        <div class="d2-tray-cfg-row"><label>Blynk Base URL<input type="text" data-tray-field
                              data-key="blynk_base" value="<?php echo esc_attr($tray['blynk_base']); ?>"></label></div>
                        <div class="d2-tray-cfg-row"><label>Webcam URL<input type="text" data-tray-field data-key="webcam_url"
                              value="<?php echo esc_attr($tray['webcam_url']); ?>"
                              placeholder="http://... hoặc .m3u8"></label></div>
                        <div class="d2-tray-cfg-vpins">
                          <?php foreach (['vpin_temp' => '🌡 Temp', 'vpin_hum' => '💧 Hum', 'vpin_soil' => '🌿 Đất', 'vpin_ph' => '⚗️ pH', 'vpin_ec' => '🌱 EC', 'vpin_light' => '💡 Đèn', 'vpin_pump' => '🔄 Bơm'] as $vk => $vl): ?>
                            <label><?php echo $vl; ?><input type="text" data-tray-field data-key="<?php echo $vk; ?>"
                                value="<?php echo esc_attr($tray[$vk] ?? ''); ?>" placeholder="V0"></label>
                          <?php endforeach; ?>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                  <button type="button" class="d2-add-tray-btn" data-add-tray>＋ Thêm khoang</button>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="d2-tray-settings-foot">
            <button type="button" class="d2-add-rack-btn" data-add-rack>＋ Thêm Rack</button>
            <span class="d2-tray-settings-status" data-tray-settings-status></span>
            <button type="button" class="d2-tray-settings-save-btn" data-tray-settings-save>💾 Lưu</button>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <?php
    $pump_rules_modal = function_exists('aitrongcay_get_pump_rules') ? aitrongcay_get_pump_rules($garden_key) : [];
    ?>
    <div class="d2-tray-settings-overlay" data-pump-modal hidden>
      <div class="d2-tray-settings-box d2-pump-modal-box">

        <div class="d2-tray-settings-head">
          <strong>💧 Bơm tự động</strong>
          <button type="button" class="d2-tray-settings-close" data-pump-modal-close>✕</button>
        </div>

        <div class="d2-pump-modal-body">

          <!-- Trạng thái hiện tại -->
          <div class="d2-pump-status-row">
            <div class="d2-pump-stat">
              <div class="d2-pump-stat-label">🌱 Độ ẩm đất</div>
              <div class="d2-pump-soil-bar"><div class="d2-pump-soil-fill" data-pump-soil-bar></div></div>
              <div class="d2-pump-stat-val" data-pump-soil>—</div>
            </div>
            <div class="d2-pump-stat">
              <div class="d2-pump-stat-label">Trạng thái bơm</div>
              <div class="d2-pump-state-badge is-off" data-pump-running>● Đang nghỉ</div>
            </div>
            <div class="d2-pump-stat">
              <div class="d2-pump-stat-label">Lần bơm cuối</div>
              <div class="d2-pump-stat-val" style="font-size:13px" data-pump-last>—</div>
            </div>
            <div class="d2-pump-stat">
              <div class="d2-pump-stat-label">Chế độ tự động</div>
              <div class="d2-pump-auto-badge is-<?php echo !empty($pump_rules_modal['enabled']) ? 'on' : 'off' ?>"
                   data-pump-auto-badge>
                <?php echo !empty($pump_rules_modal['enabled'])
                  ? '✅ Tự động BẬT (Timer Loop)'
                  : '⭕ Tự động TẮT'; ?>
              </div>
            </div>
          </div>

          <!-- Bật / tắt thủ công -->
          <div class="d2-pump-manual-row">
            <button type="button" class="d2-pump-btn d2-pump-btn--on" data-pump-on>⚡ Bật bơm ngay</button>
            <button type="button" class="d2-pump-btn d2-pump-btn--off" data-pump-off>■ Tắt bơm</button>
          </div>

          <?php if ($is_admin_user): ?>
          <!-- Cài đặt tự động (admin only) -->
          <div class="d2-pump-section">
            <div class="d2-pump-section-head">⚙ Cài đặt bơm tự động</div>

            <div class="d2-pump-rule-row d2-pump-rule-row--toggle">
              <label>
                <input type="checkbox" data-pump-rule="enabled"
                  <?php echo !empty($pump_rules_modal['enabled']) ? 'checked' : '' ?>>
                Kích hoạt tự động bơm theo chu kỳ (Timer)
              </label>
            </div>

            <div class="d2-pump-rule-grid" style="grid-template-columns: 1fr 1fr;">
              <div class="d2-pump-rule-item">
                <span class="d2-pump-rule-label">TIME ON (Phút)</span>
                <input type="number" class="d2-pump-rule-input" data-pump-rule="time_on"
                  min="1" max="120"
                  value="<?php echo esc_attr((string)($pump_rules_modal['time_on'] ?? 10)) ?>">
                <span class="d2-pump-rule-hint">Thời gian chạy máy bơm (V17)</span>
              </div>
              <div class="d2-pump-rule-item">
                <span class="d2-pump-rule-label">TIME OFF (Phút)</span>
                <input type="number" class="d2-pump-rule-input" data-pump-rule="time_off"
                  min="1" max="1440"
                  value="<?php echo esc_attr((string)($pump_rules_modal['time_off'] ?? 5)) ?>">
                <span class="d2-pump-rule-hint">Thời gian nghỉ (V18)</span>
              </div>
            </div>

            <div class="d2-pump-rule-row">
              <span class="d2-pump-rule-label">Khung giờ hoạt động</span>
              <div class="d2-pump-time-row">
                <input type="time" class="d2-pump-time-input" data-pump-rule="time_start"
                  value="<?php echo esc_attr((string)($pump_rules_modal['time_start'] ?? '06:00')) ?>">
                <span style="color:var(--muted)">→</span>
                <input type="time" class="d2-pump-time-input" data-pump-rule="time_end"
                  value="<?php echo esc_attr((string)($pump_rules_modal['time_end'] ?? '22:00')) ?>">
              </div>
            </div>

            <div class="d2-pump-rule-row">
              <span class="d2-pump-rule-label">Ngày trong tuần</span>
              <div class="d2-pump-days-row">
                <?php foreach (['CN','T2','T3','T4','T5','T6','T7'] as $pmd_i => $pmd_n): ?>
                <label class="d2-pump-day-label">
                  <input type="checkbox" data-pump-day value="<?= $pmd_i ?>"
                    <?= in_array($pmd_i, (array)($pump_rules_modal['days'] ?? [0,1,2,3,4,5,6]), true) ? 'checked' : '' ?>>
                  <span><?= esc_html($pmd_n) ?></span>
                </label>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
          <?php else: ?>
          <div class="d2-pump-section" style="text-align:center;padding:10px 0">
            <span style="font-size:12px;color:var(--muted)">Liên hệ admin để cấu hình bơm tự động.</span>
          </div>
          <?php endif; ?>

          <!-- Lịch sử bơm -->
          <div class="d2-pump-section">
            <div class="d2-pump-section-head">📋 Lịch sử bơm gần nhất</div>
            <div class="d2-pump-log-wrap">
              <table class="d2-pump-log-table">
                <thead>
                  <tr>
                    <th>Thời điểm</th>
                    <th>Loại</th>
                    <th>Độ ẩm trước</th>
                    <th>Giây</th>
                    <th>TT</th>
                  </tr>
                </thead>
                <tbody data-pump-log-body>
                  <tr><td colspan="5" style="text-align:center;padding:12px;color:var(--muted)">Đang tải…</td></tr>
                </tbody>
              </table>
            </div>
          </div>

        </div><!-- .d2-pump-modal-body -->

        <div class="d2-tray-settings-foot">
          <?php if ($is_admin_user): ?>
          <span class="d2-tray-settings-status" data-pump-save-status></span>
          <button type="button" class="d2-tray-settings-save-btn" data-pump-save>💾 Lưu cài đặt</button>
          <?php else: ?>
          <span style="font-size:12px;color:var(--muted)"></span>
          <?php endif; ?>
        </div>

      </div><!-- .d2-tray-settings-box -->
    </div><!-- data-pump-modal -->

    <div class="d2-capture-toast" data-d2-capture-toast hidden></div>
  </div>
  <script>
    var AITR_AJAX_URL = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;
    var AITR_AJAX_NONCE = <?php echo wp_json_encode(wp_create_nonce('aitrongcay_portal_actions')); ?>;
    var AITR_GARDEN_KEY = <?php echo wp_json_encode($garden_key); ?>;
    var AITR_SWITCHER_POTS = <?php echo wp_json_encode($switcher_pot_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    var AITR_RACKS = <?php echo wp_json_encode($rack_switcher_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    var AITR_RACK_CONFIGS = <?php echo wp_json_encode(
      array_map(static function (array $rack): array {
      $trays = array_map(static function (array $t) use ($rack): array {
        return [
          'name' => (string) ($t['name'] ?? ''),
          'hasToken' => trim((string) ($rack['blynk_auth_token'] ?? '')) !== '',
          'hasWebcam' => trim((string) ($t['webcam_url'] ?? '')) !== '',
          'webcamUrl' => (string) ($t['webcam_url'] ?? ''),
        ];
      }, (array) ($rack['trays'] ?? []));
      return [
        'rack_name' => (string) ($rack['rack_name'] ?? ''),
        'trays' => array_values($trays),
      ];
    }, $rack_configs),
      JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    ); ?>;
    var AITR_IS_ADMIN = <?php echo $is_admin_user ? 'true' : 'false'; ?>;
    var AITR_HAS_M3U8 = <?php echo $has_any_m3u8 ? 'true' : 'false'; ?>;
    var AITR_TIMELAPSE_STREAMS = <?php echo wp_json_encode($timelapse_streams ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    var AITR_HLS_JS_URL = 'https://cdn.jsdelivr.net/npm/hls.js@latest';
    (function () {
      var gardenWrap = document.querySelector('[data-garden-inline-name]');
      if (gardenWrap) {
        var gardenEditButton = gardenWrap.querySelector('[data-garden-inline-edit]');
        var gardenInput = gardenWrap.querySelector('[data-garden-inline-input]');
        var gardenStatus = gardenWrap.querySelector('[data-garden-inline-status]');
        var gardenText = gardenWrap.querySelector('[data-garden-display-name]');
        var gardenOriginalName = (gardenWrap.getAttribute('data-garden-name') || gardenText.textContent || gardenInput.value || 'Khu vườn của bạn').trim();
        var gardenIsEditing = false;
        var gardenIsSaving = false;
        function setGardenStatus(message, className, show) {
          gardenStatus.textContent = message || '';
          gardenStatus.className = 'd2-garden-status';
          if (className) gardenStatus.classList.add(className);
          gardenStatus.hidden = !show;
        }
        function openGardenEditor() {
          if (gardenIsSaving) return;
          gardenIsEditing = true;
          gardenText.hidden = true;
          gardenInput.hidden = false;
          gardenEditButton.hidden = true;
          gardenInput.value = gardenOriginalName;
          setGardenStatus('Nhấn Enter hoặc click ra ngoài để lưu', '', true);
          window.requestAnimationFrame(function () { gardenInput.focus(); gardenInput.select(); });
        }
        function closeGardenEditor(keepStatus) {
          gardenIsEditing = false;
          gardenText.hidden = false;
          gardenInput.hidden = true;
          gardenEditButton.hidden = false;
          gardenInput.value = gardenOriginalName;
          if (!keepStatus) gardenStatus.hidden = true;
        }
        function applyGardenName(nextName) {
          gardenOriginalName = nextName;
          gardenWrap.setAttribute('data-garden-name', nextName);
          gardenText.textContent = nextName;
          document.querySelectorAll('[data-garden-display-name]').forEach(function (node) { node.textContent = nextName; });
        }
        function saveGardenName(nextName) {
          nextName = (nextName || '').trim().replace(/\s+/g, ' ');
          if (!nextName) nextName = gardenOriginalName || 'Khu vườn của bạn';
          if (nextName === gardenOriginalName) { closeGardenEditor(); return; }
          if (gardenIsSaving) return;
          gardenIsSaving = true;
          setGardenStatus('Đang lưu tên mới...', 'is-saving', true);
          post('aitrongcay_rename_garden', { garden_key: AITR_GARDEN_KEY, garden_name: nextName }).then(function (res) {
            if (!res || !res.success || !res.data) throw new Error((res && res.data && res.data.message) || 'Lưu thất bại');
            applyGardenName(res.data.garden_name || nextName);
            setGardenStatus('Đã lưu tự động.', 'is-success', true);
            closeGardenEditor(true);
            window.setTimeout(function () { if (!gardenIsEditing && !gardenIsSaving) gardenStatus.hidden = true; }, 1600);
          }).catch(function (err) {
            setGardenStatus((err && err.message) || 'Chưa lưu được tên khu vườn.', 'is-error', true);
            window.requestAnimationFrame(function () { gardenInput.focus(); gardenInput.select(); });
          }).finally(function () { gardenIsSaving = false; });
        }
        if (gardenEditButton && gardenInput && gardenStatus && gardenText) {
          gardenEditButton.addEventListener('click', function (e) { e.preventDefault(); e.stopPropagation(); openGardenEditor(); });
          gardenInput.addEventListener('click', function (e) { e.stopPropagation(); });
          gardenInput.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); saveGardenName(gardenInput.value); } else if (e.key === 'Escape') { e.preventDefault(); gardenInput.value = gardenOriginalName; closeGardenEditor(); } });
          gardenInput.addEventListener('blur', function () { saveGardenName(gardenInput.value); });
        }
      }
      document.querySelectorAll('[data-pot-inline-name]').forEach(function (wrap) {
        var editButton = wrap.querySelector('[data-pot-inline-edit]');
        var input = wrap.querySelector('[data-pot-inline-input]');
        var status = wrap.querySelector('[data-pot-inline-status]');
        var text = wrap.querySelector('.pot-inline-name-text');
        if (!editButton || !input || !status || !text) return;
        var isSaving = false;
        var isEditing = false;
        function currentPotCode() {
          return wrap.getAttribute('data-pot-code') || '';
        }
        function currentPotName() {
          return (wrap.getAttribute('data-pot-name') || text.textContent || '').trim();
        }
        function setStatus(textValue, cls, keepVisible) {
          status.textContent = textValue;
          status.classList.remove('is-saving', 'is-error', 'is-success');
          if (cls) status.classList.add(cls);
          status.hidden = !keepVisible && !textValue;
        }
        function closeEditor(keepStatus) {
          isEditing = false;
          input.hidden = true;
          text.hidden = false;
          editButton.hidden = false;
          if (!keepStatus) {
            status.hidden = true;
            setStatus('', '', false);
          }
        }
        function openEditor() {
          isEditing = true;
          input.hidden = false;
          text.hidden = true;
          editButton.hidden = true;
          input.value = currentPotName();
          setStatus('Nhấn Enter hoặc click ra ngoài để lưu', '', true);
          window.requestAnimationFrame(function () { input.focus(); input.select(); });
        }
        function applyName(nextName) {
          var potCode = currentPotCode();
          wrap.setAttribute('data-pot-name', nextName);
          text.textContent = nextName;
          input.value = nextName;
          if (Array.isArray(window.AITR_SWITCHER_POTS)) {
            window.AITR_SWITCHER_POTS.forEach(function (pot) {
              if (pot && pot.code === potCode) {
                pot.name = nextName;
              }
            });
          }
          if (Array.isArray(window.AITR_RACKS)) {
            window.AITR_RACKS.forEach(function (rack) {
              if (!rack || !Array.isArray(rack.trays)) return;
              rack.trays.forEach(function (pot) {
                if (pot && pot.code === potCode) {
                  pot.name = nextName;
                }
              });
            });
          }
          document.querySelectorAll('[data-d2-switch-item]').forEach(function (item) {
            if (item.getAttribute('data-pot-code') !== potCode) return;
            var thumbImage = item.querySelector('.d2-thumb img');
            if (thumbImage) thumbImage.alt = nextName;
            var label = item.querySelector('span');
            if (label) label.textContent = nextName;
          });
        }
        function saveName(nextName) {
          var originalName = currentPotName();
          var potCode = currentPotCode();
          nextName = (nextName || '').trim().replace(/\s+/g, ' ');
          if (!nextName) nextName = originalName || 'Khoang cây';
          if (nextName === originalName) { closeEditor(); return; }
          if (isSaving || !potCode) return;
          isSaving = true;
          setStatus('Đang lưu tên mới...', 'is-saving', true);
          post('aitrongcay_rename_pot', { garden_key: AITR_GARDEN_KEY || '', pot_code: potCode, pot_name: nextName }).then(function (res) {
            if (!res || !res.success || !res.data) throw new Error((res && res.data && res.data.message) || 'Lưu thất bại');
            applyName(res.data.pot_name || nextName);
            setStatus('Đã lưu tự động.', 'is-success', true);
            closeEditor(true);
            window.setTimeout(function () { if (!isEditing && !isSaving) status.hidden = true; }, 1600);
          }).catch(function (err) {
            setStatus((err && err.message) || 'Chưa lưu được tên khoang.', 'is-error', true);
            window.requestAnimationFrame(function () { input.focus(); input.select(); });
          }).finally(function () { isSaving = false; });
        }
        editButton.addEventListener('click', function (event) { event.preventDefault(); event.stopPropagation(); openEditor(); });
        input.addEventListener('click', function (event) { event.stopPropagation(); });
        input.addEventListener('keydown', function (event) {
          if (event.key === 'Enter') { event.preventDefault(); saveName(input.value); }
          else if (event.key === 'Escape') { event.preventDefault(); input.value = originalName; closeEditor(); }
        });
        input.addEventListener('blur', function () { saveName(input.value); });
      });
      document.querySelectorAll('[data-d2-journal-wrap]').forEach(function (wrap) {
        var editButton = wrap.querySelector('[data-d2-journal-edit]');
        var text = wrap.querySelector('[data-d2-journal-text]');
        var input = wrap.querySelector('[data-d2-journal-input]');
        var status = wrap.querySelector('[data-d2-journal-status]');
        if (!editButton || !text || !input || !status) return;
        var isEditing = false;
        var isSaving = false;
        var timer = null;
        var queuedValue = null;
        var lastSavedValue = input.value || '';
        function setStatus(message, cls, show) {
          status.textContent = message || '';
          status.className = 'd2-pot-journal-status';
          if (cls) status.classList.add(cls);
          status.hidden = !show;
        }
        function renderText(value) {
          text.textContent = value || 'Chưa có nhật ký canh tác cho khoang này.';
        }
        function buildTodayDraft(value) {
          var today = new Date();
          var dd = String(today.getDate()).padStart(2, '0');
          var mm = String(today.getMonth() + 1).padStart(2, '0');
          var yyyy = String(today.getFullYear());
          var todayLabel = dd + '/' + mm + '/' + yyyy;
          value = (value || '').replace(/\r\n?/g, '\n').trim();
          if (!value) return todayLabel + '\n';
          if (new RegExp('^' + todayLabel.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '$', 'm').test(value)) {
            return value;
          }
          var lines = value.split('\n');
          var firstNonEmpty = '';
          for (var i = 0; i < lines.length; i += 1) {
            if ((lines[i] || '').trim() !== '') { firstNonEmpty = lines[i].trim(); break; }
          }
          if (firstNonEmpty === todayLabel) return value;
          return todayLabel + '\n\n' + value;
        }
        function openEditor() {
          if (isSaving) return;
          isEditing = true;
          text.hidden = true;
          input.hidden = false;
          editButton.hidden = true;
          var nextDraft = buildTodayDraft(lastSavedValue);
          if (nextDraft !== lastSavedValue) {
            input.value = nextDraft;
            renderText(nextDraft);
            scheduleSave();
          } else {
            input.value = lastSavedValue;
          }
          setStatus('Em đã thêm sẵn dòng ngày hôm nay ở trên cùng. Anh gõ tiếp là hệ thống tự lưu.', '', true);
          window.requestAnimationFrame(function () {
            input.focus();
            var firstLineEnd = input.value.indexOf('\n');
            var cursorPos = firstLineEnd >= 0 ? firstLineEnd + 1 : input.value.length;
            while (input.value.charAt(cursorPos) === '\n') cursorPos += 1;
            input.setSelectionRange(cursorPos, cursorPos);
          });
        }
        function closeEditor(keepStatus) {
          isEditing = false;
          text.hidden = false;
          input.hidden = true;
          editButton.hidden = false;
          if (!keepStatus) status.hidden = true;
        }
        function pushSave(value) {
          if (isSaving) { queuedValue = value; return; }
          isSaving = true;
          setStatus('Đang lưu nhật ký...', 'is-saving', true);
          var activePotCode = wrap.getAttribute('data-pot-code') || '';
          if (!activePotCode) {
            isSaving = false;
            setStatus('Chưa xác định được khoang để lưu nhật ký.', 'is-error', true);
            return;
          }
          post('aitrongcay_save_pot_note', { garden_key: AITR_GARDEN_KEY || '', pot_code: activePotCode, note_text: value }).then(function (res) {
            if (!res || !res.success || !res.data) throw new Error((res && res.data && res.data.message) || 'Chưa lưu được nhật ký');
            lastSavedValue = res.data.note_text || '';
            renderText(lastSavedValue);
            if (!isEditing) {
              input.value = lastSavedValue;
            }
            setStatus('Đã lưu tự động.', 'is-success', true);
            if (!isEditing) {
              closeEditor(true);
              window.setTimeout(function () { if (!isEditing && !isSaving) status.hidden = true; }, 1800);
            }
          }).catch(function (err) {
            setStatus((err && err.message) || 'Chưa lưu được nhật ký.', 'is-error', true);
            window.requestAnimationFrame(function () { input.focus(); });
          }).finally(function () {
            isSaving = false;
            if (queuedValue !== null && queuedValue !== lastSavedValue) {
              var nextValue = queuedValue;
              queuedValue = null;
              pushSave(nextValue);
            }
          });
        }
        function scheduleSave() {
          var nextValue = input.value;
          if (nextValue === lastSavedValue) {
            setStatus('Đã lưu.', 'is-success', true);
            return;
          }
          if (timer) clearTimeout(timer);
          setStatus('Đang lưu nhật ký...', 'is-saving', true);
          timer = window.setTimeout(function () { pushSave(input.value); }, 500);
        }
        editButton.addEventListener('click', function (event) { event.preventDefault(); event.stopPropagation(); openEditor(); });
        input.addEventListener('input', scheduleSave);
        input.addEventListener('keydown', function (event) {
          if ((event.metaKey || event.ctrlKey) && event.key === 'Enter') {
            event.preventDefault();
            if (timer) clearTimeout(timer);
            pushSave(input.value);
          } else if (event.key === 'Escape') {
            event.preventDefault();
            input.value = lastSavedValue;
            renderText(lastSavedValue);
            closeEditor();
          }
        });
        input.addEventListener('blur', function () {
          if (!isEditing) return;
          if (timer) clearTimeout(timer);
          if (input.value !== lastSavedValue) {
            pushSave(input.value);
          }
        });
      });
      var d2ProfileTrigger = document.querySelector('[data-d2-profile-trigger]');
      var d2ProfilePopup = document.querySelector('[data-d2-profile-popup]');
      if (d2ProfileTrigger && d2ProfilePopup) {
        function closeD2Profile() { d2ProfilePopup.hidden = true; d2ProfileTrigger.setAttribute('aria-expanded', 'false'); }
        d2ProfileTrigger.addEventListener('click', function (event) {
          event.preventDefault(); event.stopPropagation();
          var willOpen = d2ProfilePopup.hidden;
          d2ProfilePopup.hidden = !willOpen;
          d2ProfileTrigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });
        document.addEventListener('click', function (event) {
          if (!d2ProfilePopup.hidden && !d2ProfilePopup.contains(event.target) && event.target !== d2ProfileTrigger) closeD2Profile();
        });
      }
      function post(action, extra) {
        var body = new FormData();
        body.append('action', action);
        body.append('nonce', AITR_AJAX_NONCE);
        Object.keys(extra || {}).forEach(function (key) { body.append(key, extra[key]); });
        return fetch(AITR_AJAX_URL, { method: 'POST', body: body, credentials: 'same-origin' }).then(function (r) {
          return r.text().then(function (text) {
            var data = null;
            try {
              data = text ? JSON.parse(text) : null;
            } catch (e) {
              if (!r.ok) {
                throw new Error('Máy chủ trả về lỗi ' + r.status + '.');
              }
              throw new Error('Phản hồi từ máy chủ không hợp lệ.');
            }
            if (!r.ok && (!data || !data.data || !data.data.message)) {
              throw new Error('Máy chủ trả về lỗi ' + r.status + '.');
            }
            return data;
          });
        }).catch(function (error) {
          if (error && /Failed to fetch/i.test(String(error.message || ''))) {
            throw new Error('Không kết nối được tới máy chủ phân tích.');
          }
          throw error;
        });
      }
      var d2BlynkNotice = document.querySelector('[data-d2-blynk-notice]');
      function showD2BlynkNotice(text) {
        if (!d2BlynkNotice) return;
        d2BlynkNotice.textContent = text;
        d2BlynkNotice.hidden = !text;
      }
      // Per-pot sensor cache: { 'P-003': { temp: 26.1, hum: 61 }, ... }
      var potSensorCache = {};
      var activePotCodeForSensor = <?php echo wp_json_encode($hero_pot_code ?? ''); ?>;
      // Maps: pot codes with token, and pot → {ri, ti} — built from PHP-injected lane info (reliable)
      var potsWithToken = {};
      var potToLane = {};
      (window.AITR_SWITCHER_POTS || []).forEach(function (pot) {
        if (!pot.code) return;
        if (pot.hasToken) potsWithToken[pot.code] = true;
        if (pot.lane) potToLane[pot.code] = pot.lane;
      });
      // Also populate from AITR_RACKS (empty trays and racks not in SWITCHER_POTS)
      (window.AITR_RACKS || []).forEach(function (rack) {
        (rack.pots || []).forEach(function (pot) {
          if (!pot || !pot.code) return;
          if (pot.lane && !potToLane[pot.code]) potToLane[pot.code] = pot.lane;
          if (pot.hasToken && !potsWithToken[pot.code]) potsWithToken[pot.code] = true;
        });
      });
      var activeHeroLane = <?php echo wp_json_encode($hero_pot['lane'] ?? null); ?>;
      function applyBlynkSensorsToHero(d) {
        var tempEl = document.querySelector('[data-d2-temp]');
        var humEl = document.querySelector('[data-d2-humidity]');
        if (tempEl) {
          if (d.temp !== null && d.temp !== undefined) {
            tempEl.textContent = Number(d.temp).toFixed(1).replace(/\.0$/, '') + '°C';
          } else {
            tempEl.textContent = '--°C';
          }
        }
        if (humEl) {
          if (d.hum !== null && d.hum !== undefined) {
            humEl.textContent = Number(d.hum).toFixed(0) + '%';
          } else {
            humEl.textContent = '--%';
          }
        }
      }
      function clearHeroSensors() {
        var tempEl = document.querySelector('[data-d2-temp]');
        var humEl = document.querySelector('[data-d2-humidity]');
        if (tempEl) tempEl.textContent = '--°C';
        if (humEl) humEl.textContent = '--%';
      }
      function updateHeroControlsForPot(hasToken) {
        var lightBtn = document.querySelector('[data-d2-light-toggle]');
        var pumpBtn = document.querySelector('[data-d2-pump-toggle]');
        if (lightBtn) {
          lightBtn.disabled = !hasToken;
          lightBtn.title = hasToken ? '' : 'Khoang này chưa được kết nối Blynk';
        }
        if (pumpBtn) {
          pumpBtn.disabled = !hasToken;
          pumpBtn.title = hasToken ? '' : 'Khoang này chưa được kết nối Blynk';
        }
      }
      function updateControlsFromData(d) {
        var lightBtn = document.querySelector('[data-d2-light-toggle]');
        if (lightBtn) {
          var device = lightBtn.getAttribute('data-d2-light-toggle');
          var state = device ? d[device] : null;
          if (state !== null && state !== undefined) {
            lightBtn.setAttribute('data-state', String(state));
            lightBtn.classList.toggle('is-on', Number(state) === 1);
            lightBtn.classList.toggle('is-off', Number(state) !== 1);
            var lbl = lightBtn.querySelector('[data-d2-light-label]');
            if (lbl) lbl.textContent = Number(state) === 1 ? 'Tắt đèn' : 'Bật đèn';
          }
        }
        var pumpBtn = document.querySelector('[data-d2-pump-toggle]');
        if (pumpBtn && d.pump !== null && d.pump !== undefined) {
          pumpBtn.setAttribute('data-state', String(d.pump));
          pumpBtn.classList.toggle('is-on', Number(d.pump) === 1);
          pumpBtn.classList.toggle('is-off', Number(d.pump) !== 1);
          var plbl = pumpBtn.querySelector('[data-d2-pump-label]');
          if (plbl) plbl.textContent = Number(d.pump) === 1 ? 'Tắt bơm' : 'Bật bơm';
        }
      }

      function loadSensorDataForPot(potCode, lane) {
        if (!potCode) return;
        var cacheKey = lane ? (lane.ri + '-' + lane.ti) : potCode;
        if (potSensorCache[cacheKey]) {
          if (activePotCodeForSensor === potCode) {
            if (activeHeroLane && lane) {
              if (activeHeroLane.ri !== lane.ri || activeHeroLane.ti !== lane.ti) {
                return;
              }
            }
            applyBlynkSensorsToHero(potSensorCache[cacheKey]);
            updateControlsFromData(potSensorCache[cacheKey]);
          }
          return;
        }
        clearHeroSensors();
        var reqData = { garden_key: AITR_GARDEN_KEY || '', pot_code: potCode };
        if (lane) {
          reqData.rack_index = lane.ri;
          reqData.tray_index = lane.ti;
        }
        post('aitrongcay_blynk_get_status', reqData).then(function (res) {
          if (!res || !res.success || !res.data) {
            if (res && res.data && res.data.message && /quota|giới hạn/i.test(String(res.data.message))) {
              showD2BlynkNotice('Blynk đang giới hạn quota, vui lòng thử lại sau');
            }
            return;
          }
          showD2BlynkNotice('');
          var d = res.data;
          potSensorCache[cacheKey] = d;
          if (activePotCodeForSensor === potCode) {
            if (activeHeroLane && lane) {
              if (activeHeroLane.ri !== lane.ri || activeHeroLane.ti !== lane.ti) {
                return;
              }
            }
            applyBlynkSensorsToHero(d);
            updateControlsFromData(d);
          }
        }).catch(function () {
          showD2BlynkNotice('Blynk đang giới hạn quota, vui lòng thử lại sau');
        });
      }

      updateHeroControlsForPot(!!(activePotCodeForSensor && potsWithToken[activePotCodeForSensor]));
      loadSensorDataForPot(activePotCodeForSensor, activeHeroLane);
      function control(btn, deviceAttr, labelSel, onText, offText) {
        if (!btn) return;
        btn.addEventListener('click', function () {
          if (btn.disabled) return;
          var deviceRaw = btn.getAttribute(deviceAttr);
          if (!deviceRaw) return;
          var current = btn.getAttribute('data-state') === '1';
          var next = current ? '0' : '1';
          btn.disabled = true;
          var payload = { garden_key: AITR_GARDEN_KEY || '', device: deviceRaw, state: next, pot_code: activePotCodeForSensor || '' };
          if (activeHeroLane) {
            payload.rack_index = activeHeroLane.ri;
            payload.tray_index = activeHeroLane.ti;
          }
          post('aitrongcay_blynk_control', payload)
            .then(function (res) {
              if (!res || !res.success) {
                if (res && res.data && res.data.message && /quota|giới hạn/i.test(String(res.data.message))) {
                  showD2BlynkNotice('Blynk đang giới hạn quota, vui lòng thử lại sau');
                }
                throw new Error('control_failed');
              }
              showD2BlynkNotice('');
              btn.setAttribute('data-state', next);
              btn.classList.toggle('is-on', next === '1');
              btn.classList.toggle('is-off', next !== '1');
              var lbl = btn.querySelector(labelSel);
              if (lbl) lbl.textContent = next === '1' ? onText : offText;
              btn.disabled = false;
            }).catch(function () { btn.disabled = false; });
        });
      }
      control(document.querySelector('[data-d2-light-toggle]'), 'data-d2-light-toggle', '[data-d2-light-label]', 'Tắt đèn', 'Bật đèn');
      control(document.querySelector('[data-d2-pump-toggle]'), 'data-d2-pump-toggle', '[data-d2-pump-label]', 'Tắt bơm', 'Bật bơm');

      // ── Tray thumbnail: auto-refresh từ go2rtc snapshot mỗi 10s ──
      function refreshTraySnaps() {
        document.querySelectorAll('[data-d2-rack-trays] img[data-snap]').forEach(function (img) {
          var snap = img.getAttribute('data-snap');
          if (!snap) return;
          var tmp = new Image();
          tmp.onload = function () { img.src = tmp.src; };
          tmp.src = snap + (snap.indexOf('?') >= 0 ? '&' : '?') + '_t=' + Date.now();
        });
      }
      setInterval(refreshTraySnaps, 10000);

      var rackMap = Array.isArray(AITR_RACKS) ? AITR_RACKS.reduce(function (map, item) {
        if (item && item.key) map[item.key] = item;
        return map;
      }, {}) : {};
      var switcherPotMap = Array.isArray(AITR_SWITCHER_POTS) ? AITR_SWITCHER_POTS.reduce(function (map, item) {
        if (item && item.code) map[item.code] = item;
        return map;
      }, {}) : {};
      if (Array.isArray(AITR_RACKS)) {
        AITR_RACKS.forEach(function (rack) {
          var pots = rack && Array.isArray(rack.pots) ? rack.pots : [];
          pots.forEach(function (pot) {
            if (pot && pot.code && !switcherPotMap[pot.code]) {
              switcherPotMap[pot.code] = pot;
            }
          });
        });
      }
      var rackTrayWrap = document.querySelector('[data-d2-rack-trays]');
      function buildRackTrayItem(pot, isActive) {
        if (!pot) return '';
        var activeClass = isActive ? ' active' : '';
        var emptyClass = pot.isEmpty ? ' is-empty' : '';
        var snapUrl = pot.snapUrl || '';
        var image = snapUrl ? 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7' : (pot.image || '');
        var label = pot.slotLabel || pot.plantName || pot.name || 'Khoang cây';
        var name = pot.plantName || pot.name || 'Khoang cây';
        var href = pot.isEmpty ? '#' : (pot.dashboardUrl || '#');
        var camDot = (!pot.isEmpty && pot.streamUrl) ? '<i class="d2-cam-dot" aria-label="Có camera"></i>' : '';
        var snapAttr = snapUrl ? ' data-snap="' + snapUrl.replace(/"/g, '&quot;') + '"' : '';
        var media = pot.isEmpty
          ? '<span class="d2-tray-empty" aria-hidden="true"><span class="d2-tray-empty-mark"><strong>Khoang trống</strong><small>Sẵn sàng trồng</small></span></span>'
          : '<img src="' + image + '"' + snapAttr + ' alt="' + name.replace(/"/g, '&quot;') + '" loading="lazy" decoding="async" fetchpriority="low">';
        var ri = pot.lane ? pot.lane.ri : '';
        var ti = pot.lane ? pot.lane.ti : '';
        return '<div class="d2-switch' + activeClass + emptyClass + '" data-d2-switch-item data-pot-code="' + (pot.code || '') + '" data-ri="' + ri + '" data-ti="' + ti + '">' +
          '<a href="' + href + '" class="d2-thumb" data-d2-switch-link data-pot-code="' + (pot.code || '') + '" data-ri="' + ri + '" data-ti="' + ti + '" data-pot-empty="' + (pot.isEmpty ? '1' : '0') + '">' + media + '</a>' +
          '<span>' + label + camDot + '</span>' +
          '</div>';
      }
      function bindTrayLinks(scope) {
        (scope || document).querySelectorAll('[data-d2-switch-link]').forEach(function (link) {
          var potCode = link.getAttribute('data-pot-code') || '';
          if (link.dataset.boundIntent !== '1') {
            link.dataset.boundIntent = '1';
            ['mouseenter', 'touchstart', 'focus'].forEach(function (evt) {
              link.addEventListener(evt, function () {
                if (link.getAttribute('data-pot-empty') === '1') return;
                preloadPotAssets(potCode);
                preloadNeighborPotAssets(potCode);
              }, { passive: true });
            });
            link.addEventListener('pointerdown', function () {
              if (link.getAttribute('data-pot-empty') === '1') return;
              preloadPotAssets(potCode);
              if (switcherPotMap[potCode] && switcherPotMap[potCode].streamUrl) ensureHlsScript().catch(function () { });
            }, { passive: true });
          }
          if (link.dataset.boundClick === '1') return;
          link.dataset.boundClick = '1';
          link.addEventListener('click', function (event) {
            var potCode = link.getAttribute('data-pot-code') || '';
            var riAttr = link.getAttribute('data-ri');
            var tiAttr = link.getAttribute('data-ti');
            var targetPot = null;
            if (riAttr !== null && riAttr !== '' && tiAttr !== null && tiAttr !== '') {
              var ri = parseInt(riAttr, 10);
              var ti = parseInt(tiAttr, 10);
              if (AITR_RACKS[ri] && AITR_RACKS[ri].pots && AITR_RACKS[ri].pots[ti]) {
                targetPot = AITR_RACKS[ri].pots[ti];
              }
            }
            if (!targetPot) {
              targetPot = switcherPotMap[potCode];
            }
            if (!targetPot) return;
            event.preventDefault();
            applyHeroPot(targetPot);
          });
        });
      }
      function renderRackTrays(rackKey) {
        if (!rackTrayWrap || !rackMap[rackKey]) return;
        var rack = rackMap[rackKey] || {};
        var pots = Array.isArray(rack.pots) ? rack.pots : [];
        var activePotCode = '';
        var analyzeButton = document.querySelector('[data-analyze-latest-photo]');
        if (analyzeButton) activePotCode = analyzeButton.getAttribute('data-pot-code') || '';
        var html = '';
        pots.forEach(function (pot) { html += buildRackTrayItem(pot, pot && pot.code === activePotCode); });
        rackTrayWrap.innerHTML = html;
        bindTrayLinks(rackTrayWrap);
        refreshTraySnaps();
      }
      function refreshPotAnalysisUi(potCode, analysis) {
        if (!potCode || !analysis) return;
        var targetPot = switcherPotMap[potCode] || { code: potCode };
        targetPot.status_summary = analysis.summary || targetPot.status_summary || '';
        targetPot.alertText = analysis.level ? ('Cấp ' + analysis.level + ' - ' + (analysis.label || 'Đang cập nhật')) : (targetPot.alertText || 'Đang cập nhật');
        targetPot.analysisUpdatedAt = analysis.updated_at_formatted || analysis.updated_at || targetPot.analysisUpdatedAt || '';
        targetPot.recommendation = analysis.recommendation
          ? analysis.recommendation
          : (Array.isArray(analysis.actions) && analysis.actions.length
            ? analysis.actions.slice(0, 2).join(' · ')
            : (targetPot.recommendation || 'Tiếp tục theo dõi và cập nhật ảnh mới để AI đánh giá chính xác hơn.'));
        targetPot.currentStage = analysis.current_stage || targetPot.currentStage || '';
        if (targetPot.growthJourney && Array.isArray(targetPot.growthJourney.stages) && targetPot.growthJourney.stages.length && targetPot.currentStage) {
          var currentStageNeedle = String(targetPot.currentStage).toLowerCase();
          var matchedIndex = -1;
          var stageSignalMap = {
            'nay mam': ['nay mam', 'mam', 'cay mam', 'germ'],
            'phat trien sinh duong': ['sinh truong', 'sinh duong', 'than la', 'ra la', 'vegetative'],
            'ra hoa & thu phan': ['ra hoa', 'thu phan', 'co hoa', 'flower'],
            'dau qua & phat trien qua': ['dau qua', 'nuoi qua', 'qua non', 'phat trien qua', 'mang qua', 'fruit set', 'fruit'],
            'chin & thu hoach': ['chin', 'thu hoach', 'sap thu', 'harvest']
          };
          targetPot.growthJourney.stages.forEach(function (stage, index) {
            var stageName = stage && stage.name ? String(stage.name).toLowerCase() : '';
            if (matchedIndex !== -1 || !stageName) return;
            var signals = [stageName];
            Object.keys(stageSignalMap).forEach(function (anchor) {
              if (stageName.indexOf(anchor) !== -1) signals = signals.concat(stageSignalMap[anchor]);
            });
            signals.some(function (signal) {
              if (!signal) return false;
              if (stageName.indexOf(currentStageNeedle) !== -1 || currentStageNeedle.indexOf(stageName) !== -1 || currentStageNeedle.indexOf(signal) !== -1 || signal.indexOf(currentStageNeedle) !== -1) {
                matchedIndex = index;
                return true;
              }
              return false;
            });
          });
          if (matchedIndex >= 0) {
            targetPot.growthJourney.hasStageSignal = true;
            targetPot.growthJourney.activeStagePosition = matchedIndex + 1;
            targetPot.growthJourney.currentStage = targetPot.growthJourney.stages[matchedIndex].name || targetPot.currentStage;
            var totalStages = targetPot.growthJourney.stages.length;
            var baseWidth = ((matchedIndex + 0.5) / totalStages) * 100;
            var stepWidth = 100 / totalStages;
            var newProgress = baseWidth;
            if (matchedIndex < totalStages - 1) {
              newProgress += stepWidth * 0.35;
            } else {
              newProgress = 100;
            }
            targetPot.growthJourney.progressWidth = Math.max(0, Math.min(100, newProgress));
          } else if (typeof targetPot.growthJourney.hasStageSignal === 'undefined') {
            targetPot.growthJourney.hasStageSignal = false;
          }
        }
        targetPot.journalText = targetPot.journalText || '';
        switcherPotMap[potCode] = targetPot;

        var activeAnalyzeButton = document.querySelector('[data-analyze-latest-photo]');
        var activePotCode = activeAnalyzeButton ? (activeAnalyzeButton.getAttribute('data-pot-code') || '') : '';
        if (activePotCode === potCode) {
          applyHeroPot(targetPot);
        }
      }
      var d2HlsInstance = null;
      var d2HlsScriptPromise = null;
      var d2PreloadedImages = {};
      function primeSwitcherThumbnails() {
        var thumbs = Array.prototype.slice.call(document.querySelectorAll('.d2-switcher img[loading="lazy"]')).slice(0, 4);
        thumbs.forEach(function (img) {
          if (!img) return;
          img.loading = 'eager';
        });
      }
      function preloadImage(url) {
        url = url || '';
        if (!url || d2PreloadedImages[url]) return;
        d2PreloadedImages[url] = true;
        var img = new Image();
        img.decoding = 'async';
        img.src = url;
      }
      function preloadPotAssets(potCode) {
        var pot = potCode && switcherPotMap[potCode] ? switcherPotMap[potCode] : null;
        if (!pot) return;
        preloadImage(pot.image || '');
      }
      function preloadNeighborPotAssets(potCode) {
        if (!potCode) return;
        var codes = Object.keys(switcherPotMap || {});
        var index = codes.indexOf(potCode);
        if (index === -1) return;
        [index - 1, index + 1].forEach(function (nextIndex) {
          if (nextIndex < 0 || nextIndex >= codes.length) return;
          preloadPotAssets(codes[nextIndex]);
        });
      }
      function ensureHlsScript() {
        if (d2HlsScriptPromise) return d2HlsScriptPromise;
        if (window.Hls) return Promise.resolve(window.Hls);
        d2HlsScriptPromise = new Promise(function (resolve, reject) {
          function fail(err) {
            d2HlsScriptPromise = null;
            reject(err);
          }
          var existing = document.querySelector('script[data-aitr-hls]');
          if (existing) {
            existing.addEventListener('load', function () { resolve(window.Hls); }, { once: true });
            existing.addEventListener('error', fail, { once: true });
            return;
          }
          var script = document.createElement('script');
          script.src = AITR_HLS_JS_URL;
          script.async = true;
          script.setAttribute('data-aitr-hls', '1');
          script.onload = function () { resolve(window.Hls); };
          script.onerror = fail;
          document.head.appendChild(script);
        });
        return d2HlsScriptPromise;
      }
      function buildMediaBadge(streamUrl, snapshotAt) {
        if (streamUrl) return 'live stream . 4k';
        if (snapshotAt) {
          var raw = String(snapshotAt).trim();
          var normalized = raw.indexOf('T') === -1 ? raw.replace(' ', 'T') : raw;
          var isoCandidate = normalized;
          if (/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(:\d{2})?$/.test(normalized)) {
            isoCandidate = normalized + '+00:00';
          }
          var date = new Date(isoCandidate);
          if (!isNaN(date.getTime())) {
            var vnTime = new Intl.DateTimeFormat('vi-VN', {
              timeZone: 'Asia/Bangkok',
              hour: '2-digit',
              minute: '2-digit',
              hour12: false
            }).format(date);
            return 'Ảnh chụp lúc ' + vnTime + ' giờ';
          }
        }
        return 'Ảnh chụp';
      }
      var d2HeroMjpegTimer = null;
      var d2IframePool = {};
      function isHlsUrl(url) {
        return url && /\.m3u8(\?|$)/i.test(url);
      }
      function isVideoUrl(url) {
        return url && /\.(mp4|webm|ogg)(\?|$)/i.test(url);
      }
      function isIframeUrl(url) {
        return url && url.indexOf('stream.html') !== -1;
      }
      function initIframePool() {
        var frame = document.querySelector('.d2-frame');
        if (!frame) return;
        var seen = {};
        var initialUrl = '';
        // Adopt PHP-rendered iframe vào pool
        var existing = frame.querySelector('.d2-hero-iframe');
        if (existing && existing.src && existing.src !== window.location.href) {
          initialUrl = existing.src;
          d2IframePool[initialUrl] = existing;
          existing.style.opacity = '0';
          existing.style.pointerEvents = 'none';
          existing.style.zIndex = '0';
          seen[initialUrl] = true;
        }
        // Pre-create iframe cho mỗi stream URL duy nhất trong AITR_SWITCHER_POTS
        if (Array.isArray(AITR_SWITCHER_POTS)) {
          AITR_SWITCHER_POTS.forEach(function (pot) {
            if (pot && pot.streamUrl && isIframeUrl(pot.streamUrl) && !seen[pot.streamUrl]) {
              seen[pot.streamUrl] = true;
              var iframe = document.createElement('iframe');
              iframe.src = pot.streamUrl;
              iframe.className = 'd2-hero-iframe';
              iframe.frameBorder = '0';
              iframe.setAttribute('allow', 'autoplay; camera; microphone');
              iframe.allowFullscreen = true;
              iframe.style.opacity = '0';
              iframe.style.pointerEvents = 'none';
              iframe.style.zIndex = '0';
              frame.insertBefore(iframe, frame.firstChild);
              d2IframePool[pot.streamUrl] = iframe;
            }
          });
        }
        // Hiện lại iframe ban đầu nếu có
        if (initialUrl && d2IframePool[initialUrl]) {
          d2IframePool[initialUrl].style.opacity = '1';
          d2IframePool[initialUrl].style.pointerEvents = 'auto';
          d2IframePool[initialUrl].style.zIndex = '2';
          var heroFallback = frame.querySelector('[data-d2-hero-image]');
          if (heroFallback) heroFallback.hidden = true;
        }
      }
      function mountHeroStream(streamUrl, fallbackImage, fallbackAlt) {
        var frame = document.querySelector('.d2-frame');
        if (!frame) return;
        var currentVideo = frame.querySelector('[data-d2-hero-video]');
        var currentImage = frame.querySelector('[data-d2-hero-image]');
        var upsell = document.querySelector('[data-upsell-overlay]');
        
        // Prevent re-mounting the same stream to avoid slow loading
        var currentMountedUrl = currentVideo ? currentVideo.getAttribute('data-stream-url') : (currentImage ? currentImage.getAttribute('data-d2-hero-mjpeg') : null);
        if (streamUrl && currentMountedUrl === streamUrl) {
          if (upsell) upsell.style.display = 'none';
          if (currentImage && !currentVideo) currentImage.hidden = false;
          return;
        }

        if (d2HlsInstance) {
          try { d2HlsInstance.destroy(); } catch (e) { }
          d2HlsInstance = null;
        }
        if (d2HeroMjpegTimer) { clearInterval(d2HeroMjpegTimer); d2HeroMjpegTimer = null; }
        function ensureHeroFallbackVisible() {
          if (!currentImage) {
            var img = document.createElement('img');
            img.setAttribute('data-d2-hero-image', '');
            img.setAttribute('loading', 'eager');
            img.setAttribute('decoding', 'async');
            img.setAttribute('fetchpriority', 'high');
            frame.insertBefore(img, frame.firstChild);
            currentImage = img;
          }
          currentImage.src = fallbackImage || currentImage.src || '';
          currentImage.alt = fallbackAlt || 'Khoang cây';
          currentImage.hidden = false;
        }
        function hideHeroFallback() {
          if (currentImage) currentImage.hidden = true;
        }
        function hideAllPoolIframes() {
          Object.keys(d2IframePool).forEach(function (url) {
            var f = d2IframePool[url];
            if (f) { f.style.opacity = '0'; f.style.pointerEvents = 'none'; f.style.zIndex = '0'; }
          });
        }
        if (!streamUrl) {
          ensureHeroFallbackVisible();
          if (currentVideo) currentVideo.remove();
          hideAllPoolIframes();
          if (upsell) {
            var isFallbackSVG = fallbackImage && String(fallbackImage).indexOf('hero-greenhouse.svg') !== -1;
            var textEl = upsell.querySelector('[data-hint-text]') || upsell.querySelector('p');
            if (textEl) {
                textEl.textContent = isFallbackSVG 
                    ? '📹 Khoang này chưa có ảnh và luồng Camera trực tiếp. Chờ robot tới chụp hoặc lắp đặt thêm để xem 24/7.' 
                    : '📹 Bạn muốn xem khu vườn trực tiếp 24/7? Tiến hành lắp đặt Camera ngay.';
            }
            upsell.style.display = 'flex';
          }
          return;
        }
        if (upsell) upsell.style.display = 'none';
        // iframe URL (go2rtc WebRTC) — dùng pool, chỉ show/hide, không reconnect
        if (isIframeUrl(streamUrl)) {
          if (currentVideo) { currentVideo.remove(); currentVideo = null; }
          hideAllPoolIframes();
          var pooled = d2IframePool[streamUrl];
          if (!pooled) {
            pooled = document.createElement('iframe');
            pooled.src = streamUrl;
            pooled.className = 'd2-hero-iframe';
            pooled.frameBorder = '0';
            pooled.setAttribute('allow', 'autoplay; camera; microphone');
            pooled.allowFullscreen = true;
            pooled.style.opacity = '0';
            pooled.style.pointerEvents = 'none';
            pooled.style.zIndex = '0';
            frame.insertBefore(pooled, frame.firstChild);
            d2IframePool[streamUrl] = pooled;
          }
          pooled.style.opacity = '1';
          pooled.style.pointerEvents = 'auto';
          pooled.style.zIndex = '2';
          if (currentImage) currentImage.hidden = true;
          return;
        }
        // Ẩn pool iframes khi chuyển sang non-iframe stream
        hideAllPoolIframes();
        // MJPEG / snapshot URL: hiển thị trong <img> với refresh mỗi 2 giây
        if (!isHlsUrl(streamUrl) && !isVideoUrl(streamUrl)) {
          if (currentVideo) { currentVideo.remove(); currentVideo = null; }
          if (!currentImage) {
            var img = document.createElement('img');
            img.setAttribute('data-d2-hero-image', '');
            img.setAttribute('loading', 'eager');
            img.setAttribute('decoding', 'async');
            img.setAttribute('fetchpriority', 'high');
            frame.insertBefore(img, frame.firstChild);
            currentImage = img;
          }
          var mjpegBase = streamUrl.split('?')[0];
          currentImage.src = mjpegBase + '?_t=' + Date.now();
          currentImage.alt = fallbackAlt || 'Khoang cây';
          currentImage.setAttribute('data-d2-hero-mjpeg', mjpegBase);
          currentImage.hidden = false;
          d2HeroMjpegTimer = setInterval(function () {
            if (!document.querySelector('[data-d2-hero-mjpeg]')) { clearInterval(d2HeroMjpegTimer); return; }
            currentImage.src = mjpegBase + '?_t=' + Date.now();
          }, 2000);
          return;
        }
        ensureHeroFallbackVisible();
        if (!currentVideo) {
          currentVideo = document.createElement('video');
          currentVideo.autoplay = true;
          currentVideo.muted = true;
          currentVideo.playsInline = true;
          currentVideo.controls = true;
          currentVideo.preload = 'metadata';
          currentVideo.crossOrigin = 'anonymous';
          currentVideo.setAttribute('data-d2-hero-video', '');
          frame.insertBefore(currentVideo, frame.firstChild);
        }
        currentVideo.poster = fallbackImage || '';
        currentVideo.setAttribute('data-stream-url', streamUrl);
        var revealVideo = function () {
          hideHeroFallback();
          currentVideo.removeEventListener('loadeddata', revealVideo);
          currentVideo.removeEventListener('canplay', revealVideo);
        };
        currentVideo.addEventListener('loadeddata', revealVideo, { once: true });
        currentVideo.addEventListener('canplay', revealVideo, { once: true });
        if (currentVideo.canPlayType('application/vnd.apple.mpegurl') || (isVideoUrl(streamUrl) && !isHlsUrl(streamUrl))) {
          currentVideo.src = streamUrl;
          currentVideo.load();
          currentVideo.play().catch(function () { });
          return;
        }
        ensureHlsScript().then(function (Hls) {
          if (!Hls || !Hls.isSupported()) {
            console.error('[HLS] Browser not supported');
            return;
          }
          console.log('[HLS] Initializing for:', streamUrl);
          d2HlsInstance = new Hls({ 
            enableWorker: true, 
            lowLatencyMode: true, 
            backBufferLength: 60,
            manifestLoadingMaxRetry: 10,
            manifestLoadingRetryDelay: 1000
          });
          
          d2HlsInstance.on(Hls.Events.ERROR, function (event, data) {
            console.warn('[HLS Error]', data.type, data.details, data.fatal);
            if (data.fatal) {
              switch (data.type) {
                case Hls.ErrorTypes.NETWORK_ERROR:
                  console.log('[HLS] Fatal network error, trying to recover...');
                  d2HlsInstance.startLoad();
                  break;
                case Hls.ErrorTypes.MEDIA_ERROR:
                  console.log('[HLS] Fatal media error, trying to recover...');
                  d2HlsInstance.recoverMediaError();
                  break;
                default:
                  console.error('[HLS] Unrecoverable error, destroying instance');
                  d2HlsInstance.destroy();
                  break;
              }
            }
          });

          d2HlsInstance.on(Hls.Events.MANIFEST_PARSED, function() {
            console.log('[HLS] Manifest parsed, playing...');
            currentVideo.play().catch(function (e) { console.warn('[HLS] Auto-play blocked:', e); });
          });

          d2HlsInstance.loadSource(streamUrl);
          d2HlsInstance.attachMedia(currentVideo);
        }).catch(function (err) { console.error('[HLS] Script load failed:', err); });
      }
      function renderGrowthJourney(growthJourney) {
        var card = document.querySelector('[data-d2-growth-journey]');
        if (!card) return;
        var ageWrap = card.querySelector('[data-d2-growth-age-wrap]');
        var ageEl = card.querySelector('[data-d2-growth-age]');
        var track = card.querySelector('[data-d2-growth-track]');
        var empty = card.querySelector('[data-d2-growth-empty]');
        var safeJourney = growthJourney && typeof growthJourney === 'object' ? growthJourney : {};
        var hasJourney = !!safeJourney.hasGrowthJourney && Array.isArray(safeJourney.stages) && safeJourney.stages.length;
        var hasAgeDays = typeof safeJourney.ageDays === 'number' && safeJourney.ageDays >= 0;
        if (ageWrap) ageWrap.hidden = !hasAgeDays;
        if (ageEl) ageEl.textContent = hasAgeDays ? String(safeJourney.ageDays + 1) : '';
        if (!hasJourney) {
          if (track) {
            track.hidden = true;
            track.innerHTML = '';
          }
          if (empty) {
            empty.hidden = false;
            empty.textContent = safeJourney.emptyMessage || 'Chưa có dữ liệu hành trình tăng trưởng cho khoang này.';
          } else {
            var emptyNode = document.createElement('div');
            emptyNode.className = 'd2-growth-empty';
            emptyNode.setAttribute('data-d2-growth-empty', '');
            emptyNode.textContent = safeJourney.emptyMessage || 'Chưa có dữ liệu hành trình tăng trưởng cho khoang này.';
            card.appendChild(emptyNode);
          }
          return;
        }
        var total = safeJourney.growthStageTotal || safeJourney.stages.length || 1;
        var hasStageSignal = !!safeJourney.hasStageSignal;
        var activePos = safeJourney.activeStagePosition || 1;
        var progressWidth = typeof safeJourney.progressWidth !== 'undefined' ? safeJourney.progressWidth : 0;
        progressWidth = Math.max(0, Math.min(100, progressWidth));
        var html = '<div class="d2-growth-progress" data-d2-growth-progress style="width:' + progressWidth + '%"></div>';
        safeJourney.stages.forEach(function (stage, index) {
          var position = index + 1;
          var isActiveStage = position === activePos;
          var isCompletedStage = position < activePos;
          var stateClass = isActiveStage ? 'is-active' : (isCompletedStage ? 'is-past' : 'is-future');
          var icon = isActiveStage ? '✨' : (position === 1 ? '🌱' : (position === total ? '🍅' : '🪴'));
          html += '<div class="d2-growth-step ' + stateClass + '" data-d2-growth-step data-stage-position="' + position + '">' +
            '<div class="d2-growth-icon"><span class="d2-growth-icon-emoji">' + icon + '</span>' + (isCompletedStage ? '<span class="d2-growth-icon-check">✓</span>' : '') + '</div>' +
            '<div class="d2-growth-step-name">' + (stage && stage.name ? stage.name : 'Giai đoạn') + '</div>' +
            '</div>';
        });
        if (track) {
          track.innerHTML = html;
          track.hidden = false;
        } else {
          var trackNode = document.createElement('div');
          trackNode.className = 'd2-growth-track';
          trackNode.setAttribute('data-d2-growth-track', '');
          trackNode.innerHTML = html;
          card.appendChild(trackNode);
        }
        empty = card.querySelector('[data-d2-growth-empty]');
        if (empty) empty.hidden = true;
      }
      function cleanupAnalysisSummary(text) {
        text = String(text || '').trim();
        if (!text) return '';
        return text.replace(/^Cindy đã đối chiếu ảnh mới với hồ sơ onboarding của .*?\.\s*/iu, '').trim();
      }
      function formatRecommendationHtml(text) {
        text = String(text || '').trim();
        if (!text) return '';
        var upsells = [];
        text = text.replace(/\[UPSELL\]\s*([^\n\r\.]+)/gi, function(match, code) {
          upsells.push(code.trim());
          return '';
        });
        text = text.trim();
        if (text.indexOf('\n') === -1) {
          text = text.replace(/\s*[•·]\s*/gu, '\n');
          text = text.replace(/\.\s+/gu, '.\n');
        }
        var html = text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>');
        if (upsells.length > 0) {
          html += '<div style="margin-top: 12px; display: flex; gap: 8px; flex-wrap: wrap;">';
          upsells.forEach(function(code) {
            html += '<button onclick="window.location.href=\'../kho-nong-cu-2/?add_to_cart=' + encodeURIComponent(code) + '\'" style="padding: 6px 14px; font-size: 12px; font-weight: 700; border-radius: 14px; border: 1px solid rgba(111,219,168,0.4); background: linear-gradient(180deg, rgba(111,219,168,0.15), rgba(49,163,117,0.25)); color: #6fdba8; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(49,163,117,0.15);"><span style="font-size: 14px;">🛒</span> Thêm ' + code.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</button>';
          });
          html += '</div>';
        }
        return html;
      }
      function applyHeroPot(pot) {
        if (!pot || !pot.code) return;
        var heroName = document.querySelector('[data-d2-hero-name]');
        var heroImage = document.querySelector('[data-d2-hero-image]');
        var heroMeta = document.querySelector('[data-d2-hero-meta]');
        var eventBox = document.querySelector('[data-d2-current-event]');
        var recoBox = document.querySelector('[data-d2-recommendation]');
        var badgeBox = document.querySelector('[data-d2-analysis-badge]');
        var journalLabel = document.querySelector('[data-d2-journal-label]');
        var journalWrap = document.querySelector('[data-d2-journal-wrap]');
        var journalText = document.querySelector('[data-d2-journal-text]');
        var journalInput = document.querySelector('[data-d2-journal-input]');
        var analyzeButton = document.querySelector('[data-analyze-latest-photo]');
        var captureButton = document.querySelector('[data-d2-capture-photo]');
        var dashboardLink = document.querySelector('[data-d2-dashboard-link]');
        var tempEl = document.querySelector('[data-d2-temp]');
        var humEl = document.querySelector('[data-d2-humidity]');
        var mediaBadge = document.querySelector('[data-d2-media-badge]');
        var potWrap = document.querySelector('[data-pot-inline-name]');
        var potText = document.querySelector('.pot-inline-name-text');
        var potInput = document.querySelector('[data-pot-inline-input]');
        if (heroName) heroName.textContent = pot.name || 'Khoang cây';
        if (heroImage) {
          heroImage.src = pot.image || heroImage.src;
          heroImage.alt = pot.name || 'Khoang cây';
        }
        mountHeroStream(pot.streamUrl || '', pot.image || (heroImage && heroImage.src) || '', pot.name || 'Khoang cây');
        if (mediaBadge) mediaBadge.textContent = pot.mediaBadge || 'Ảnh chụp';
        if (heroMeta) heroMeta.textContent = pot.isEmpty ? 'Trạng thái: Khoang trống' : ('Loại cây: ' + (pot.plantName || 'Cây chưa xác định'));
        if (eventBox) eventBox.textContent = cleanupAnalysisSummary(pot.status_summary || 'AI đang đọc ảnh và dữ liệu gần nhất của khoang này.');
        if (recoBox) recoBox.innerHTML = formatRecommendationHtml(pot.recommendation || 'Tiếp tục theo dõi và cập nhật ảnh mới để AI đánh giá chính xác hơn.');
        if (badgeBox) {
          var badgeText = pot.alertText || 'Đang cập nhật';
          if (pot.analysisUpdatedAt) badgeText += ' · ' + pot.analysisUpdatedAt;
          badgeBox.textContent = badgeText;
        }
        var isEmptyPot = pot.isEmpty === true;
        if (journalLabel) journalLabel.textContent = (isEmptyPot ? 'Trạng thái khoang · ' : 'Nhật ký canh tác · ') + (pot.code || 'Khoang');
        if (journalWrap) journalWrap.setAttribute('data-pot-code', isEmptyPot ? '' : (pot.code || ''));
        if (journalText) journalText.textContent = pot.journalText || (isEmptyPot ? 'Khoang này đang trống và sẵn sàng để trồng cây mới.' : 'Chưa có nhật ký canh tác cho khoang này.');
        if (journalInput) journalInput.value = isEmptyPot ? '' : (pot.journalText || '');
        
        // Cập nhật giao diện Auto-log AI
        var aiLogWrap = document.querySelector('.d2-ai-daily-logs');
        if (aiLogWrap) {
          // Xóa hết các log AI hiện tại (chừa lại log ghi chú user)
          var existingAiLogs = aiLogWrap.querySelectorAll('.d2-log-entry:not(.user-note)');
          existingAiLogs.forEach(function(el) { el.remove(); });
          
          var newAiLogHtml = '';
          var summaryText = pot.status_summary ? pot.status_summary.trim() : '';
          if (summaryText) {
            var updatedDate = pot.analysisUpdatedAt ? pot.analysisUpdatedAt : new Date().toLocaleDateString('vi-VN');
            newAiLogHtml = '<div class="d2-log-entry" style="margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px dashed rgba(255,255,255,0.05);">' +
                '<div style="font-size: 11px; color: var(--primary); font-weight: 700; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">' +
                  '<span>🤖 AI Auto-log</span>' +
                  '<span style="color: rgba(227, 227, 222, 0.4);">' + updatedDate + '</span>' +
                '</div>' +
                '<div style="font-size: 13.5px; color: rgba(227, 227, 222, 0.85); line-height: 1.6;">' +
                  summaryText +
                '</div>' +
              '</div>';
          } else {
            newAiLogHtml = '<div class="d2-log-entry" style="margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px dashed rgba(255,255,255,0.05);">' +
                '<div style="font-size: 11px; color: var(--primary); font-weight: 700; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">' +
                  '<span>🤖 AI Auto-log</span>' +
                '</div>' +
                '<div style="font-size: 13.5px; color: rgba(227, 227, 222, 0.85); line-height: 1.6;">' +
                  'Chưa có phân tích AI nào cho khoang này. AI đang chờ thu thập dữ liệu hình ảnh và cảm biến để đưa ra đánh giá đầu tiên.' +
                '</div>' +
              '</div>';
          }
          aiLogWrap.insertAdjacentHTML('afterbegin', newAiLogHtml);
        }
        var hasCamera = !!pot.streamUrl;
        if (analyzeButton) {
          analyzeButton.setAttribute('data-pot-code', (isEmptyPot && !hasCamera) ? '' : (pot.analyzePotCode || pot.code || ''));
          analyzeButton.disabled = isEmptyPot && !hasCamera;
          analyzeButton.title = (isEmptyPot && !hasCamera) ? 'Khoang trống, chưa có ảnh để phân tích' : 'Phân tích ảnh mới nhất';
        }
        if (captureButton) {
          captureButton.setAttribute('data-pot-code', (isEmptyPot && !hasCamera) ? '' : (pot.code || ''));
          captureButton.disabled = isEmptyPot && !hasCamera;
          captureButton.title = (isEmptyPot && !hasCamera) ? 'Khoang trống, chưa thể chụp ảnh theo khoang' : 'Chụp ảnh khoang hiện tại';
        }
        if (dashboardLink && pot.dashboardUrl) dashboardLink.href = pot.dashboardUrl;
        // Per-pot sensor cache: fetch live data if this pot doesn't have it yet,
        // or apply it immediately if cached.
        activePotCodeForSensor = pot.code || '';
        activeHeroLane = pot.lane || null;
        updateHeroControlsForPot(!!(pot.code && potsWithToken[pot.code]));
        loadSensorDataForPot(activePotCodeForSensor, activeHeroLane);
        if (mediaBadge) mediaBadge.textContent = pot.mediaBadge || buildMediaBadge(pot.streamUrl || '', pot.snapshotAt || '');
        if (potWrap) {
          potWrap.setAttribute('data-pot-code', pot.code || '');
          potWrap.setAttribute('data-pot-name', pot.name || 'Khoang cây');
        }
        if (potText) potText.textContent = pot.name || 'Khoang cây';
        if (potInput) potInput.value = pot.name || 'Khoang cây';
        preloadNeighborPotAssets(pot.code || '');
        renderGrowthJourney(pot.growthJourney || null);
        document.querySelectorAll('[data-d2-switch-item]').forEach(function (item) {
          item.classList.toggle('active', item.getAttribute('data-pot-code') === pot.code);
        });
        try {
          var nextUrl = new URL(window.location.href);
          nextUrl.searchParams.set('pot', pot.code);
          if (AITR_GARDEN_KEY) nextUrl.searchParams.set('garden', AITR_GARDEN_KEY);
          window.history.replaceState({ pot: pot.code }, '', nextUrl.toString());
        } catch (e) { }
      }
      var initialHeroVideo = document.querySelector('[data-d2-hero-video]');
      if (initialHeroVideo) {
        var initialStreamUrl = initialHeroVideo.getAttribute('data-stream-url') || '';
        var initialHeroImage = (document.querySelector('[data-d2-hero-image]') || {}).src || '';
        if (initialStreamUrl) {
          window.requestAnimationFrame(function () {
            mountHeroStream(initialStreamUrl, initialHeroImage, 'Khoang cây');
          });
        }
      }
      initIframePool();
      primeSwitcherThumbnails();
      bindTrayLinks(document);
      var _rackDropdown  = document.querySelector('[data-d2-rack-dropdown]');
      var _rackTrigger   = document.querySelector('[data-d2-rack-trigger]');
      var _rackLabelEl   = document.querySelector('[data-d2-rack-label]');
      var _rackMenu      = document.querySelector('[data-d2-rack-menu]');

      if (_rackTrigger && _rackDropdown) {
        _rackTrigger.addEventListener('click', function (e) {
          e.stopPropagation();
          _rackDropdown.classList.toggle('is-open');
        });
        document.addEventListener('click', function (e) {
          if (_rackDropdown && !_rackDropdown.contains(e.target)) {
            _rackDropdown.classList.remove('is-open');
          }
        });
      }

      document.querySelectorAll('[data-d2-rack-item]').forEach(function (btn) {
        btn.addEventListener('click', function (event) {
          event.preventDefault();
          var rackKey = btn.getAttribute('data-rack-key') || '';
          if (!rackKey || !rackMap[rackKey]) return;
          document.querySelectorAll('[data-d2-rack-item]').forEach(function (item) { item.classList.toggle('is-active', item === btn); });
          if (_rackLabelEl) _rackLabelEl.textContent = btn.textContent.trim();
          if (_rackDropdown) _rackDropdown.classList.remove('is-open');
          renderRackTrays(rackKey);
          try {
            var _rUrl = new URL(window.location.href);
            _rUrl.searchParams.set('rack', rackKey);
            _rUrl.searchParams.delete('pot');
            window.history.pushState({ rack: rackKey }, '', _rUrl.toString());
          } catch (e) { }
          
          var _rk = rackMap[rackKey];
          if (_rk && Array.isArray(_rk.pots) && _rk.pots.length) {
            var _firstPot = _rk.pots.find(function(p) { return p && p.code && switcherPotMap[p.code]; });
            if (_firstPot) {
              applyHeroPot(switcherPotMap[_firstPot.code]);
            }
          }
        });
      });
      // Restore rack từ URL ?rack= (sau F5), hoặc tìm rack chứa ?pot=
      var _urlParams      = new URL(window.location.href).searchParams;
      var _rackFromUrl    = _urlParams.get('rack') || '';
      var _potFromUrl     = _urlParams.get('pot') || '';
      var _initialRackKey = (Array.isArray(AITR_RACKS) && AITR_RACKS[0] && AITR_RACKS[0].key) ? AITR_RACKS[0].key : 'rack-0';
      if (_rackFromUrl && rackMap[_rackFromUrl]) {
        _initialRackKey = _rackFromUrl;
      } else if (_potFromUrl) {
        (window.AITR_RACKS || []).some(function (rack) {
          return (rack.pots || []).some(function (p) {
            if (p && p.code === _potFromUrl) { _initialRackKey = rack.key; return true; }
          });
        });
      }
      // Activate đúng rack tab trong dropdown
      document.querySelectorAll('[data-d2-rack-item]').forEach(function (item) {
        item.classList.toggle('is-active', item.getAttribute('data-rack-key') === _initialRackKey);
      });
      if (_rackLabelEl && rackMap[_initialRackKey]) _rackLabelEl.textContent = rackMap[_initialRackKey].label || 'Rack 1';
      renderRackTrays(_initialRackKey);
      
      // Khôi phục pot đang chọn từ URL nếu F5
      if (_potFromUrl && switcherPotMap[_potFromUrl]) {
        var currentRenderedPot = document.querySelector('[data-d2-hero-name]');
        var currentPotName = currentRenderedPot ? currentRenderedPot.textContent : '';
        var _urlPot = switcherPotMap[_potFromUrl];
        if (currentPotName !== (_urlPot.name || 'Khoang cây')) {
          applyHeroPot(_urlPot);
        } else {
          activePotCodeForSensor = _urlPot.code || '';
          activeHeroLane = _urlPot.lane || null;
          updateHeroControlsForPot(!!(_urlPot.code && potsWithToken[_urlPot.code]));
          loadSensorDataForPot(activePotCodeForSensor, activeHeroLane);
        }
      } else {
        var currentRenderedPot = document.querySelector('[data-d2-hero-name]');
        var currentPotName = currentRenderedPot ? currentRenderedPot.textContent.trim() : '';
        var _rk = rackMap[_initialRackKey];
        if (_rk && Array.isArray(_rk.pots) && _rk.pots.length) {
          var _firstPot = _rk.pots.find(function(p) { return p && p.code && switcherPotMap[p.code]; });
          if (_firstPot) {
            if (currentPotName !== (_firstPot.name || 'Khoang cây')) {
              applyHeroPot(switcherPotMap[_firstPot.code]);
            } else {
              activePotCodeForSensor = _firstPot.code || '';
              activeHeroLane = _firstPot.lane || null;
              updateHeroControlsForPot(!!(_firstPot.code && potsWithToken[_firstPot.code]));
              loadSensorDataForPot(activePotCodeForSensor, activeHeroLane);
            }
          }
        }
      }
      var captureToast = document.querySelector('[data-d2-capture-toast]');
      function showCaptureToast(message, type) {
        if (!message) return;
        var isError = type === 'is-error';
        var toast = document.createElement('div');
        toast.style.cssText = 'position:fixed;bottom:30px;right:30px;padding:16px 24px;border-radius:20px;background:' + (isError ? 'rgba(239,68,68,0.95)' : 'rgba(16,185,129,0.95)') + ';color:#fff;font-family:"Inter",sans-serif;font-weight:600;font-size:15px;box-shadow:0 20px 40px rgba(0,0,0,0.3);z-index:99999;transform:translateY(100px);opacity:0;transition:all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.1);display:flex;align-items:center;gap:12px;';
        toast.innerHTML = (isError ? '<span>⚠️</span>' : '<span>📸</span>') + '<span>' + message + '</span>';
        document.body.appendChild(toast);
        
        requestAnimationFrame(function() {
            requestAnimationFrame(function() {
                toast.style.transform = 'translateY(0)';
                toast.style.opacity = '1';
            });
        });
        
        setTimeout(function() {
            toast.style.transform = 'translateY(100px)';
            toast.style.opacity = '0';
            setTimeout(function() { toast.remove(); }, 400);
        }, 3500);
      }
      function captureCurrentFrame() {
        var video = document.querySelector('[data-d2-hero-video]');
        var image = document.querySelector('[data-d2-hero-image]');
        var canvas = document.createElement('canvas');
        var width = 0;
        var height = 0;
        try {
          if (video && video.videoWidth > 0 && video.videoHeight > 0) {
            width = video.videoWidth;
            height = video.videoHeight;
            canvas.width = width;
            canvas.height = height;
            canvas.getContext('2d').drawImage(video, 0, 0, width, height);
            return { ok: true, dataUrl: canvas.toDataURL('image/jpeg', 0.92), source: 'video' };
          }
          if (image && image.complete) {
            width = image.naturalWidth || image.width;
            height = image.naturalHeight || image.height;
            if (width > 0 && height > 0) {
              canvas.width = width;
              canvas.height = height;
              canvas.getContext('2d').drawImage(image, 0, 0, width, height);
              return { ok: true, dataUrl: canvas.toDataURL('image/jpeg', 0.92), source: 'image' };
            }
          }
          return { ok: false, message: 'Chưa có khung hình khả dụng để chụp.' };
        } catch (error) {
          return { ok: false, message: 'Browser đang chặn chụp frame từ live cam khác domain (CORS/canvas).', error: error && error.message ? error.message : '' };
        }
      }
      var captureButton = document.querySelector('[data-d2-capture-photo]');
      if (captureButton) {
        captureButton.addEventListener('click', function () {
          var potCode = captureButton.getAttribute('data-pot-code') || '';
          if (!potCode) return;
          captureButton.disabled = true;
          showCaptureToast('Đang chụp ảnh khoang ' + potCode + '...', '');
          post('aitrongcay_capture_photo_server', { garden_key: AITR_GARDEN_KEY || '', pot_code: potCode })
            .then(function (res) {
              if (!res || !res.success || !res.data) throw new Error((res && res.data && res.data.message) || 'Chưa lưu được ảnh.');
              var msg = 'Đã lưu vào Kho ảnh · ' + potCode + (res.data.source === 'timelapse' ? ' (timelapse)' : ' (live)') + '.';
              if (res.data.bonus_points && res.data.bonus_points > 0) {
                  msg += ' ✅ +' + res.data.bonus_points + ' Eco Points!';
              }
              showCaptureToast(msg, 'is-success');
            })
            .catch(function (err) {
              showCaptureToast((err && err.message) || 'Chưa chụp được ảnh.', 'is-error');
            })
            .finally(function () { captureButton.disabled = false; });
        });
      }
      function triggerGeminiAnalyze(potCode, silent, force) {
        var btn = document.querySelector('[data-analyze-latest-photo]');
        if (btn) btn.disabled = true;
        var payload = { garden_key: AITR_GARDEN_KEY || '', pot_code: potCode };
        if (force) payload.force = '1';
        if (!silent) showCaptureToast(force ? 'Đang phân tích mới khoang ' + potCode + '...' : 'Đang kiểm tra kết quả phân tích ' + potCode + '...', '');
        return post('aitrongcay_analyze_timelapse_gemini', payload)
          .then(function (res) {
            if (!res || !res.success || !res.data || !res.data.analysis) {
              throw new Error((res && res.data && res.data.message) || 'Chưa nhận được kết quả phân tích.');
            }
            refreshPotAnalysisUi(potCode, res.data.analysis || {});
            var isCached = !!(res.data && res.data.cached);
            if (!silent) showCaptureToast(isCached ? 'Kết quả phân tích gần nhất của khoang ' + potCode + ' (Shift+click để làm mới).' : 'Gemini đã phân tích xong khoang ' + potCode + '.', 'is-success');
          })
          .catch(function (err) {
            if (!silent) showCaptureToast((err && err.message) || 'Phân tích chưa thành công.', 'is-error');
          })
          .finally(function () { if (btn) btn.disabled = false; });
      }

      document.querySelectorAll('[data-analyze-latest-photo]').forEach(function (button) {
        button.addEventListener('click', function (e) {
          var potCode = button.getAttribute('data-pot-code') || '';
          if (!potCode) return;
          triggerGeminiAnalyze(potCode, false, e.shiftKey);
        });
      });

      var floatingTrays = document.querySelector('[data-d2-floating-trays]');
      if (floatingTrays && window.innerWidth > 1200) {
        var startTop = floatingTrays.getBoundingClientRect().top + window.scrollY;
        var placeholder = floatingTrays.nextElementSibling;
        function syncFloatingTrays() {
          if (window.innerWidth <= 1200) {
            floatingTrays.classList.remove('is-floating');
            return;
          }
          var shouldFloat = window.scrollY > (startTop - 38);
          floatingTrays.classList.toggle('is-floating', shouldFloat);
          if (placeholder) {
            placeholder.style.display = shouldFloat ? 'block' : 'none';
            placeholder.style.width = shouldFloat ? (floatingTrays.offsetWidth + 'px') : '0';
            placeholder.style.minHeight = shouldFloat ? (floatingTrays.offsetHeight + 'px') : '0';
          }
        }
        window.addEventListener('scroll', syncFloatingTrays, { passive: true });
        window.addEventListener('resize', function () {
          startTop = (placeholder && placeholder.style.display === 'block' ? placeholder.getBoundingClientRect().top + window.scrollY : floatingTrays.getBoundingClientRect().top + window.scrollY);
          syncFloatingTrays();
        });
        syncFloatingTrays();
      }

      // ── Rack Monitor: multi-rack sensor polling + webcam + controls ───
      (function () {
        var POLL_INTERVAL = 5000;
        var THRESHOLDS = {
          temp: { ok: [18, 28], warn: [14, 34] },
          hum: { ok: [50, 85], warn: [35, 95] },
          ph: { ok: [5.5, 7.0], warn: [5.0, 7.5] },
          ec: { ok: [0.8, 2.5], warn: [0.4, 3.2] }
        };

        function sensorStatus(key, val) {
          if (val === null || val === undefined) return '';
          var t = THRESHOLDS[key];
          if (!t) return 'is-ok';
          if (val >= t.ok[0] && val <= t.ok[1]) return 'is-ok';
          if (val >= t.warn[0] && val <= t.warn[1]) return 'is-warn';
          return 'is-alert';
        }
        function overallStatus(sensors) {
          if (sensors.error) return 'is-err';
          var worst = 'is-ok';
          ['temp', 'hum', 'ph', 'ec'].forEach(function (k) {
            var s = sensorStatus(k, sensors[k] !== undefined ? sensors[k] : null);
            if (s === 'is-alert') worst = 'is-alert';
            else if (s === 'is-warn' && worst !== 'is-alert') worst = 'is-warn';
          });
          return worst;
        }

        // laneId = "ri-ti" (e.g. "0-1", "1-2")
        function updateTrayUI(laneId, ri, ti, sensors) {
          var dot = document.querySelector('[data-tray-dot="' + laneId + '"]');
          if (dot) dot.className = 'd2-tray-dot ' + overallStatus(sensors);
          ['temp', 'hum', 'ph', 'ec'].forEach(function (key) {
            var wrap = document.querySelector('[data-tray-sensor-wrap="' + laneId + '"][data-sensor-key="' + key + '"]');
            if (!wrap) return;
            var valEl = wrap.querySelector('[data-tray-val]');
            var val = (sensors[key] !== undefined && sensors[key] !== null) ? sensors[key] : null;
            if (valEl) valEl.textContent = val !== null ? (Math.round(val * 10) / 10) : '--';
            wrap.className = 'd2-tray-sensor' + (sensorStatus(key, val) ? ' ' + sensorStatus(key, val) : '');
          });
          if (sensors.light !== undefined && sensors.light !== null) updateCtrlState(ri, ti, 'light', sensors.light);
          if (sensors.pump !== undefined && sensors.pump !== null) updateCtrlState(ri, ti, 'pump', sensors.pump);
        }

        function updateCtrlState(ri, ti, ctrl, val) {
          var btn = document.querySelector(
            '[data-tray-ctrl-btn][data-rack-index="' + ri + '"][data-tray-index="' + ti + '"][data-tray-ctrl="' + ctrl + '"]'
          );
          if (!btn) return;
          var isOn = parseInt(val, 10) === 1;
          btn.setAttribute('data-state', isOn ? '1' : '0');
          btn.classList.toggle('is-on', isOn);
          btn.innerHTML = ctrl === 'light'
            ? (isOn ? '💡 Tắt đèn' : '💡 Bật đèn')
            : (isOn ? '🔄 Tắt bơm' : '🔄 Bật bơm');
        }

        // Build laneId ("ri-ti") → pot_code map using the exact lane assignment
        var trayPotMap = {};
        (function () {
          var switcherPots = window.AITR_SWITCHER_POTS || [];
          switcherPots.forEach(function (pot) {
            if (pot.lane && pot.lane.ri !== undefined && pot.lane.ti !== undefined) {
              trayPotMap[pot.lane.ri + '-' + pot.lane.ti] = pot.code;
            }
          });
        })();

        function fetchTray(ri, ti) {
          var laneId = ri + '-' + ti;
          var dot = document.querySelector('[data-tray-dot="' + laneId + '"]');
          if (dot) dot.classList.add('is-loading');
          post('aitrongcay_tray_sensors', { garden_key: AITR_GARDEN_KEY, rack_index: ri, tray_index: ti })
            .then(function (res) {
              if (!res || !res.success) {
                window.setTimeout(function() { fetchTray(ri, ti); }, POLL_INTERVAL);
                return;
              }
              var sensors = res.data || {};
              updateTrayUI(laneId, ri, ti, sensors);
              // Update per-pot sensor cache so hero area stays in sync when switching pots
              var potCode = trayPotMap[laneId];
              if (potCode && (sensors.temp !== undefined || sensors.hum !== undefined)) {
                var cacheKey = ri + '-' + ti;
                if (!potSensorCache[cacheKey]) potSensorCache[cacheKey] = {};
                if (sensors.temp !== null && sensors.temp !== undefined) potSensorCache[cacheKey].temp = sensors.temp;
                if (sensors.hum !== null && sensors.hum !== undefined) potSensorCache[cacheKey].hum = sensors.hum;
                if (potCode === activePotCodeForSensor) {
                  if (activeHeroLane && activeHeroLane.ri === ri && activeHeroLane.ti === ti) {
                    applyBlynkSensorsToHero(potSensorCache[cacheKey]);
                  }
                }
              }
              window.setTimeout(function() { fetchTray(ri, ti); }, POLL_INTERVAL);
            })
            .catch(function () {
              if (dot) dot.className = 'd2-tray-dot is-err';
              console.warn('Đã dừng cập nhật cảm biến cho rack ' + ri + ' khay ' + ti + ' để tránh rác console.');
            });
        }

        // Start polling — one interval per rack+tray with 300ms stagger
        var stagger = 0;
        var racks = window.AITR_RACK_CONFIGS || [];
        for (var _ri = 0; _ri < racks.length; _ri++) {
          var trays = racks[_ri].trays || [];
          for (var _ti = 0; _ti < trays.length; _ti++) {
            if (!trays[_ti].hasToken) continue;
            (function (ri, ti, delay) {
              window.setTimeout(function () { fetchTray(ri, ti); }, delay);
            })(_ri, _ti, stagger * 2000);
            stagger++;
          }
        }

        // MJPEG refresh every 2s
        document.querySelectorAll('[data-tray-mjpeg]').forEach(function (img) {
          var base = img.getAttribute('data-tray-mjpeg') || '';
          if (!base) return;
          window.setInterval(function () {
            img.src = base + (base.indexOf('?') >= 0 ? '&' : '?') + '_t=' + Date.now();
          }, 2000);
        });

        // HLS.js init
        if (window.AITR_HAS_M3U8) {
          var hlsScript = document.createElement('script');
          hlsScript.src = 'https://cdn.jsdelivr.net/npm/hls.js@latest';
          hlsScript.onload = function () {
            document.querySelectorAll('[data-tray-hls]').forEach(function (video) {
              var src = video.getAttribute('data-tray-hls') || '';
              if (!src) return;
              if (window.Hls && window.Hls.isSupported()) {
                var hls = new window.Hls();
                hls.loadSource(src);
                hls.attachMedia(video);
              } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                video.src = src;
              }
            });
          };
          document.head.appendChild(hlsScript);
        }

        // Control buttons (rack+tray aware)
        document.querySelectorAll('[data-tray-ctrl-btn]').forEach(function (btn) {
          btn.addEventListener('click', function () {
            var ri = parseInt(btn.getAttribute('data-rack-index') || '0', 10);
            var ti = parseInt(btn.getAttribute('data-tray-index') || '0', 10);
            var ctrl = btn.getAttribute('data-tray-ctrl') || '';
            var state = parseInt(btn.getAttribute('data-state') || '0', 10);
            var next = state === 1 ? 0 : 1;
            btn.disabled = true;
            post('aitrongcay_tray_control', { garden_key: AITR_GARDEN_KEY, rack_index: ri, tray_index: ti, device: ctrl, value: next })
              .then(function (res) { if (res && res.success) updateCtrlState(ri, ti, ctrl, next); })
              .finally(function () { btn.disabled = false; });
          });
        });

        // ── Settings modal ────────────────────────────────────────────
        var settingsModal = document.querySelector('[data-tray-settings-modal]');
        var settingsOpen = document.querySelector('[data-tray-settings-open]');
        var settingsClose = document.querySelector('[data-tray-settings-close]');
        var settingsSave = document.querySelector('[data-tray-settings-save]');
        var settingsStatus = document.querySelector('[data-tray-settings-status]');
        var racksWrap = document.querySelector('[data-tray-settings-racks]');

        function openSettings() { if (settingsModal) settingsModal.hidden = false; }
        function closeSettings() { if (settingsModal) settingsModal.hidden = true; }
        function setSettingsStatus(msg, cls) {
          if (!settingsStatus) return;
          settingsStatus.textContent = msg;
          settingsStatus.className = 'd2-tray-settings-status' + (cls ? ' ' + cls : '');
        }

        // Accordion toggle
        document.querySelectorAll('[data-rack-cfg-toggle]').forEach(function (head) {
          head.addEventListener('click', function (e) {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'BUTTON') return;
            var rack = head.closest('[data-rack-cfg]');
            if (rack) rack.classList.toggle('is-open');
          });
        });

        // Remove rack
        function bindRackRemove(rack) {
          var btn = rack.querySelector('[data-remove-rack]');
          if (btn) btn.addEventListener('click', function () {
            if (document.querySelectorAll('[data-rack-cfg]').length <= 1) {
              setSettingsStatus('Phải có ít nhất 1 rack.', 'is-err');
              return;
            }
            rack.remove();
          });
        }
        document.querySelectorAll('[data-rack-cfg]').forEach(bindRackRemove);

        // Remove tray
        function bindTrayRemove(item) {
          var btn = item.querySelector('[data-remove-tray]');
          if (btn) btn.addEventListener('click', function () {
            var list = item.closest('[data-tray-cfg-list]');
            if (list && list.querySelectorAll('[data-tray-cfg-item]').length <= 1) {
              setSettingsStatus('Rack phải có ít nhất 1 khoang.', 'is-err');
              return;
            }
            item.remove();
          });
        }
        document.querySelectorAll('[data-tray-cfg-item]').forEach(bindTrayRemove);

        // Add tray
        var BLANK_TRAY_FIELDS = ['name', 'blynk_token', 'blynk_base', 'webcam_url', 'vpin_temp', 'vpin_hum', 'vpin_soil', 'vpin_ph', 'vpin_ec', 'vpin_light', 'vpin_pump'];
        var TRAY_LABELS = { name: 'Tên khoang', blynk_token: 'Blynk Token', blynk_base: 'Blynk Base URL', webcam_url: 'Webcam URL', vpin_temp: '🌡 Temp', vpin_hum: '💧 Hum', vpin_soil: '🌿 Đất', vpin_ph: '⚗️ pH', vpin_ec: '🌱 EC', vpin_light: '💡 Đèn', vpin_pump: '🔄 Bơm' };
        var TRAY_PLACEHOLDERS = { blynk_token: 'Auth token', webcam_url: 'http://... hoặc .m3u8', vpin_temp: 'V0', vpin_hum: 'V1', vpin_soil: 'V6', vpin_ph: 'V2', vpin_ec: 'V3', vpin_light: 'V4', vpin_pump: 'V5' };
        var VPIN_KEYS = ['vpin_temp', 'vpin_hum', 'vpin_soil', 'vpin_ph', 'vpin_ec', 'vpin_light', 'vpin_pump'];

        function makeTrayItem(num) {
          var div = document.createElement('div');
          div.className = 'd2-tray-cfg-item';
          div.setAttribute('data-tray-cfg-item', '');
          var html = '<div class="d2-tray-cfg-title"><span>🌿 Khoang ' + num + '</span><button type="button" class="d2-tray-remove-btn" data-remove-tray title="Xóa khoang">✕</button></div>';
          ['name', 'blynk_token', 'blynk_base', 'webcam_url'].forEach(function (k) {
            html += '<div class="d2-tray-cfg-row"><label>' + TRAY_LABELS[k] + '<input type="text" data-tray-field data-key="' + k + '" value="" placeholder="' + (TRAY_PLACEHOLDERS[k] || '') + '"></label></div>';
          });
          html += '<div class="d2-tray-cfg-vpins">';
          VPIN_KEYS.forEach(function (k) {
            html += '<label>' + TRAY_LABELS[k] + '<input type="text" data-tray-field data-key="' + k + '" value="" placeholder="' + (TRAY_PLACEHOLDERS[k] || 'V0') + '"></label>';
          });
          html += '</div>';
          div.innerHTML = html;
          bindTrayRemove(div);
          return div;
        }

        document.querySelectorAll('[data-add-tray]').forEach(function (btn) {
          btn.addEventListener('click', function () {
            var list = btn.closest('.d2-rack-cfg-body').querySelector('[data-tray-cfg-list]');
            if (!list) return;
            var num = list.querySelectorAll('[data-tray-cfg-item]').length + 1;
            list.appendChild(makeTrayItem(num));
          });
        });

        // Add rack
        var addRackBtn = document.querySelector('[data-add-rack]');
        if (addRackBtn) {
          addRackBtn.addEventListener('click', function () {
            if (!racksWrap) return;
            var rackNum = racksWrap.querySelectorAll('[data-rack-cfg]').length + 1;
            var rackDiv = document.createElement('div');
            rackDiv.className = 'd2-rack-cfg is-open';
            rackDiv.setAttribute('data-rack-cfg', '');
            rackDiv.innerHTML = '<div class="d2-rack-cfg-head" data-rack-cfg-toggle>'
              + '<span class="d2-rack-cfg-arrow">▼</span>'
              + '<input class="d2-rack-name-input" type="text" data-cfg-rack-name value="Rack ' + rackNum + '" placeholder="Rack ' + rackNum + '">'
              + '<button type="button" class="d2-rack-remove-btn" data-remove-rack title="Xóa rack">✕</button>'
              + '</div>'
              + '<div class="d2-rack-cfg-body">'
              + '<div class="d2-tray-cfg-list" data-tray-cfg-list></div>'
              + '<button type="button" class="d2-add-tray-btn" data-add-tray>＋ Thêm khoang</button>'
              + '</div>';
            racksWrap.appendChild(rackDiv);
            // Wire up events
            var toggle = rackDiv.querySelector('[data-rack-cfg-toggle]');
            if (toggle) toggle.addEventListener('click', function (e) {
              if (e.target.tagName === 'INPUT' || e.target.tagName === 'BUTTON') return;
              rackDiv.classList.toggle('is-open');
            });
            bindRackRemove(rackDiv);
            var addTray = rackDiv.querySelector('[data-add-tray]');
            if (addTray) addTray.addEventListener('click', function () {
              var list = rackDiv.querySelector('[data-tray-cfg-list]');
              if (!list) return;
              var num = list.querySelectorAll('[data-tray-cfg-item]').length + 1;
              list.appendChild(makeTrayItem(num));
            });
            // Auto-add first tray
            var list = rackDiv.querySelector('[data-tray-cfg-list]');
            if (list) list.appendChild(makeTrayItem(1));
          });
        }

        // Collect & save
        function collectRacks() {
          var racks = [];
          document.querySelectorAll('[data-rack-cfg]').forEach(function (rackEl) {
            var nameEl = rackEl.querySelector('[data-cfg-rack-name]');
            var rackName = nameEl ? nameEl.value : '';
            var trays = [];
            rackEl.querySelectorAll('[data-tray-cfg-item]').forEach(function (trayEl) {
              var tray = {};
              trayEl.querySelectorAll('[data-tray-field]').forEach(function (f) {
                var key = f.getAttribute('data-key') || '';
                if (key) tray[key] = f.value || '';
              });
              trays.push(tray);
            });
            racks.push({ rack_name: rackName, trays: trays });
          });
          return racks;
        }

        if (settingsOpen) settingsOpen.addEventListener('click', openSettings);
        if (settingsClose) settingsClose.addEventListener('click', closeSettings);
        if (settingsModal) settingsModal.addEventListener('click', function (e) { if (e.target === settingsModal) closeSettings(); });

        if (settingsSave) {
          settingsSave.addEventListener('click', function () {
            var racks = collectRacks();
            if (!racks.length) { setSettingsStatus('Không có rack nào.', 'is-err'); return; }
            setSettingsStatus('Đang lưu...', '');
            settingsSave.disabled = true;
            post('aitrongcay_tray_config_save', { garden_key: AITR_GARDEN_KEY, racks: JSON.stringify(racks) })
              .then(function (res) {
                if (!res || !res.success) throw new Error((res && res.data && res.data.message) || 'Lưu thất bại.');
                setSettingsStatus('Đã lưu ' + (res.data.rack_count || '') + ' rack! Tải lại trang để áp dụng.', 'is-ok');
              })
              .catch(function (err) { setSettingsStatus((err && err.message) || 'Lưu thất bại.', 'is-err'); })
              .finally(function () { settingsSave.disabled = false; });
          });
        }
      })();

      // ── Timelapse panel ──────────────────────────────────────────────────────
      window.AITR_POTS_BY_RACK = <?php echo wp_json_encode($pots_by_rack); ?>;
      window.AITR_UPDATE_TIMELAPSE_CAMERA_DROPDOWN = function(rackId) {
        var tlStreamSel = document.getElementById('d2TlStream');
        if (!tlStreamSel) return;
        tlStreamSel.innerHTML = '';
        var pots = window.AITR_POTS_BY_RACK[rackId] || [];
        pots.forEach(function(ts) {
          var opt = document.createElement('option');
          opt.value = ts.slug;
          opt.setAttribute('data-legacy', ts.legacy_slug);
          opt.textContent = ts.label;
          tlStreamSel.appendChild(opt);
        });
      };

      (function () {
        var tlPanel = document.getElementById('d2TimelapsePanel');
        var tlNavLink = document.querySelector('[data-tl-nav-link]');
        var tlBack = document.getElementById('d2TlBack');
        var mainGrid = document.querySelector('.d2-grid');
        if (!tlPanel || !tlNavLink || !mainGrid) return;

        function openTimelapse() {
          mainGrid.style.display = 'none';
          tlPanel.style.display = 'block';
          tlNavLink.classList.add('active');
          
          var url = new URL(window.location);
          if (url.searchParams.get('view') !== 'timelapse') {
            url.searchParams.set('view', 'timelapse');
            window.history.replaceState({}, '', url);
          }
        }
        function closeTimelapse() {
          mainGrid.style.display = '';
          tlPanel.style.display = 'none';
          tlNavLink.classList.remove('active');
          tlStop();

          var url = new URL(window.location);
          if (url.searchParams.get('view') === 'timelapse') {
            url.searchParams.delete('view');
            window.history.replaceState({}, '', url);
          }
        }

        tlNavLink.addEventListener('click', function (e) { e.preventDefault(); openTimelapse(); });
        if (tlBack) tlBack.addEventListener('click', closeTimelapse);

        <?php if (isset($_GET['view']) && $_GET['view'] === 'timelapse'): ?>
        setTimeout(openTimelapse, 300);
        <?php endif; ?>

        // ── Player state ──
        var tlFrames = [];
        var tlIndex = 0;
        var tlPlaying = false;
        var tlTimer = null;
        var tlImages = {};

        var tlStreamSel = document.getElementById('d2TlStream');
        var tlDaysSel = document.getElementById('d2TlDays');
        var tlLoadBtn = document.getElementById('d2TlLoad');
        var tlImg = document.getElementById('d2TlImg');
        var tlEmpty = document.getElementById('d2TlEmpty');
        var tlScrub = document.getElementById('d2TlScrub');
        var tlProgressWrap = document.getElementById('d2TlProgressWrap');
        var tlPlaybar = document.getElementById('d2TlPlaybar');
        var tlFirst = document.getElementById('d2TlFirst');
        var tlPlayPause = document.getElementById('d2TlPlayPause');
        var tlLast = document.getElementById('d2TlLast');
        var tlInfo = document.getElementById('d2TlInfo');
        var tlSpeedSel = document.getElementById('d2TlSpeed');

        if (!tlLoadBtn) return;

        var tlSensorsEl = document.getElementById('d2TlSensors');

        function tlRender() {
          if (!tlFrames.length) return;
          var f = tlFrames[tlIndex];
          if (tlImg) { tlImg.src = f.url; tlImg.style.display = 'block'; }
          if (tlEmpty) tlEmpty.style.display = 'none';
          if (tlInfo) tlInfo.textContent = f.date + ' ' + f.time + ' (' + (tlIndex + 1) + '/' + tlFrames.length + ')';
          if (tlScrub) tlScrub.value = tlIndex;
          // Show sensor data captured at this moment
          var s = f.sensors || null;
          if (tlSensorsEl) {
            if (s) {
              tlSensorsEl.style.display = 'flex';
              var def = function (v, u) { return (v !== null && v !== undefined) ? v + (u || '') : '--'; };
              var el;
              el = document.getElementById('tlsTemp');  if (el) el.textContent = '🌡 ' + def(s.temp, '°C');
              el = document.getElementById('tlsHum');   if (el) el.textContent = '💧 ' + def(s.hum, '%');
              el = document.getElementById('tlsSoil');  if (el) el.textContent = s.soil !== undefined ? '🌿 ' + def(s.soil, '% đất') : '';
              el = document.getElementById('tlsPh');    if (el) el.textContent = '⚗️ pH ' + def(s.ph, '');
              el = document.getElementById('tlsEc');    if (el) el.textContent = '🌱 ' + def(s.ec, ' mS');
            } else {
              tlSensorsEl.style.display = 'none';
            }
          }
        }

        function tlStop() {
          tlPlaying = false;
          clearInterval(tlTimer);
          if (tlPlayPause) tlPlayPause.textContent = '▶';
        }

        function tlPlay() {
          tlPlaying = true;
          if (tlPlayPause) tlPlayPause.textContent = '⏸';
          var fps = tlSpeedSel ? parseInt(tlSpeedSel.value, 10) : 10;
          tlTimer = setInterval(function () {
            tlIndex = (tlIndex + 1) % tlFrames.length;
            tlRender();
          }, 1000 / fps);
        }

        function tlPreload(frames) {
          frames.forEach(function (f) {
            if (!tlImages[f.url]) {
              tlImages[f.url] = new Image();
              tlImages[f.url].src = f.url;
            }
          });
        }

        tlLoadBtn.addEventListener('click', function () {
          var slug = tlStreamSel ? tlStreamSel.value : '';
          var legacySlug = tlStreamSel && tlStreamSel.options[tlStreamSel.selectedIndex] ? tlStreamSel.options[tlStreamSel.selectedIndex].getAttribute('data-legacy') : '';
          var days = tlDaysSel ? tlDaysSel.value : '7';
          if (!slug) return;
          tlStop();
          tlFrames = [];
          tlLoadBtn.disabled = true;
          tlLoadBtn.textContent = 'Đang tải...';
          if (tlImg) tlImg.style.display = 'none';
          if (tlEmpty) { tlEmpty.textContent = 'Đang tải danh sách ảnh...'; tlEmpty.style.display = 'flex'; }
          if (tlProgressWrap) tlProgressWrap.style.display = 'none';
          if (tlPlaybar) tlPlaybar.style.display = 'none';
          if (tlShareRow) tlShareRow.style.display = 'none';

          var body = new FormData();
          body.append('action', 'aitrongcay_timelapse_list');
          body.append('nonce', AITR_AJAX_NONCE);
          body.append('garden_key', AITR_GARDEN_KEY || '');
          body.append('stream', slug);
          body.append('legacy_stream', legacySlug);
          body.append('days', days);

          fetch(AITR_AJAX_URL, { method: 'POST', body: body, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (res) {
              if (!res || !res.success) throw new Error((res && res.data && res.data.message) || 'Tải thất bại');
              var frames = (res.data && res.data.frames) || [];
              if (!frames.length) {
                if (tlEmpty) { tlEmpty.textContent = 'Chưa có ảnh nào trong khoảng thời gian này. Hệ thống sẽ tự động chụp mỗi 15 phút.'; tlEmpty.style.display = 'flex'; }
                return;
              }
              tlFrames = frames;
              tlIndex = 0;
              tlPreload(frames);
              tlRender();
              if (tlScrub) { tlScrub.max = frames.length - 1; tlScrub.value = 0; }
              if (tlProgressWrap) tlProgressWrap.style.display = 'block';
              if (tlPlaybar) tlPlaybar.style.display = 'flex';
              if (tlShareRow) tlShareRow.style.display = 'flex';
            })
            .catch(function (err) {
              if (tlEmpty) { tlEmpty.textContent = (err && err.message) || 'Lỗi khi tải ảnh.'; tlEmpty.style.display = 'flex'; }
            })
            .finally(function () { tlLoadBtn.disabled = false; tlLoadBtn.textContent = '▷ Tải timelapse'; });
        });

        if (tlPlayPause) tlPlayPause.addEventListener('click', function () {
          if (!tlFrames.length) return;
          tlPlaying ? tlStop() : tlPlay();
        });
        if (tlFirst) tlFirst.addEventListener('click', function () { tlStop(); tlIndex = 0; tlRender(); });
        if (tlLast) tlLast.addEventListener('click', function () { tlStop(); tlIndex = Math.max(0, tlFrames.length - 1); tlRender(); });
        if (tlScrub) tlScrub.addEventListener('input', function () { tlStop(); tlIndex = parseInt(this.value, 10); tlRender(); });
        if (tlSpeedSel) tlSpeedSel.addEventListener('change', function () { if (tlPlaying) { tlStop(); tlPlay(); } });

        // Hàm tạo video Blob từ frames để dùng chung cho Tải về & Chia sẻ
        function tlGenerateVideoBlob(cb, onProgress) {
          if (!tlFrames.length) { alert('Tải timelapse trước rồi mới xuất video được.'); cb(null); return; }
          if (!window.MediaRecorder || !HTMLCanvasElement.prototype.captureStream) {
            alert('Trình duyệt không hỗ trợ tính năng này. Vui lòng dùng Chrome hoặc Edge.'); cb(null); return;
          }
          var fps = tlSpeedSel ? parseInt(tlSpeedSel.value, 10) : 10;
          var canvas = document.createElement('canvas');
          var ctx = canvas.getContext('2d');
          var firstImg = tlImages[tlFrames[0].url];
          canvas.width  = (firstImg && firstImg.naturalWidth)  ? firstImg.naturalWidth  : 640;
          canvas.height = (firstImg && firstImg.naturalHeight) ? firstImg.naturalHeight : 480;

          var mimeType = ['video/webm;codecs=vp9','video/webm;codecs=vp8','video/webm']
            .find(function (m) { return MediaRecorder.isTypeSupported(m); }) || 'video/webm';
          var recorder = new MediaRecorder(canvas.captureStream(fps), { mimeType: mimeType, videoBitsPerSecond: 16000000 });
          var chunks = [];
          recorder.ondataavailable = function (e) { if (e.data && e.data.size > 0) chunks.push(e.data); };
          recorder.onstop = function () {
            var blob = new Blob(chunks, { type: 'video/webm' });
            var fileName = 'timelapse-' + (tlFrames[0].date || 'video') + '.webm';
            cb(blob, fileName);
          };

          recorder.start();

          var i = 0; var total = tlFrames.length; var ms = Math.round(1000 / fps);
          function drawWatermarkedFrame(imgObj, frameData) {
            ctx.drawImage(imgObj, 0, 0, canvas.width, canvas.height);
            ctx.fillStyle = 'rgba(0,0,0,.55)';
            ctx.fillRect(0, canvas.height - 36, canvas.width, 36);
            ctx.fillStyle = '#fff';
            ctx.font = 'bold 13px "Manrope",sans-serif';
            ctx.fillText('🌿 Ai trồng cây  |  ' + (frameData.date || '') + '  ' + (frameData.time || ''), 12, canvas.height - 13);
          }

          function nextFrame() {
            if (i >= total) { recorder.stop(); return; }
            var frameData = tlFrames[i];
            var img = tlImages[frameData.url];
            if (img && img.complete && img.naturalWidth) {
              drawWatermarkedFrame(img, frameData);
            } else {
              var tmp = new Image();
              tmp.onload = function () { drawWatermarkedFrame(tmp, frameData); };
              tmp.src = frameData.url;
            }
            if (onProgress) onProgress(i + 1, total);
            i++;
            setTimeout(nextFrame, ms);
          }
          nextFrame();
        }

        // Tải video: ghép frames → WebM qua Canvas + MediaRecorder
        var tlDownloadBtn = document.getElementById('d2TlDownload');
        var tlDownloadStatus = document.getElementById('d2TlDownloadStatus');
        if (tlDownloadBtn) {
          tlDownloadBtn.addEventListener('click', function () {
            tlDownloadBtn.disabled = true;
            tlDownloadBtn.textContent = '⏳ Đang tạo...';
            if (tlDownloadStatus) tlDownloadStatus.textContent = '';

            tlGenerateVideoBlob(function (blob, fileName) {
              if (!blob) {
                tlDownloadBtn.disabled = false;
                tlDownloadBtn.textContent = '⬇ Tải video';
                return;
              }
              var a = document.createElement('a');
              a.href = URL.createObjectURL(blob);
              a.download = fileName;
              document.body.appendChild(a); a.click(); document.body.removeChild(a);
              URL.revokeObjectURL(a.href);
              
              tlDownloadBtn.disabled = false;
              tlDownloadBtn.textContent = '⬇ Tải video';
              if (tlDownloadStatus) { tlDownloadStatus.textContent = '✓ Đã tải xong!'; setTimeout(function () { tlDownloadStatus.textContent = ''; }, 3000); }
            }, function (current, total) {
              if (tlDownloadStatus) tlDownloadStatus.textContent = current + ' / ' + total;
            });
          });
        }

        // ── Share: lưu ảnh / video hiện tại + chia sẻ lên mạng xã hội ──────────────────
        var tlShareRow    = document.getElementById('d2TlShareRow');
        var tlSaveFrameBtn = document.getElementById('d2TlSaveFrame');
        var tlShareFbBtn   = document.getElementById('d2TlShareFb');
        var tlShareZaloBtn = document.getElementById('d2TlShareZalo');
        var tlShareNativeBtn = document.getElementById('d2TlShareNative');

        // Vẽ frame hiện tại lên canvas, thêm watermark, trả về qua callback(blob, filename)
        function tlGetCurrentFrameBlob(cb) {
          if (!tlFrames.length) return;
          var f   = tlFrames[tlIndex];
          var img = tlImages[f.url];
          if (!img) { img = new Image(); img.src = f.url; tlImages[f.url] = img; }
          function draw() {
            var cnv = document.createElement('canvas');
            cnv.width  = img.naturalWidth  || 1280;
            cnv.height = img.naturalHeight || 720;
            var ctx2 = cnv.getContext('2d');
            ctx2.drawImage(img, 0, 0, cnv.width, cnv.height);
            // Watermark dải dưới
            ctx2.fillStyle = 'rgba(0,0,0,.55)';
            ctx2.fillRect(0, cnv.height - 36, cnv.width, 36);
            ctx2.fillStyle = '#fff';
            ctx2.font = 'bold 13px "Manrope",sans-serif';
            ctx2.fillText('🌿 Ai trồng cây  |  ' + (f.date || '') + '  ' + (f.time || ''), 12, cnv.height - 13);
            var fileName = 'vuon-rau-' + (f.date || 'photo') + '.jpg';
            cnv.toBlob(function (blob) { cb(blob, fileName); }, 'image/jpeg', 0.92);
          }
          if (img.complete && img.naturalWidth) { draw(); } else { img.onload = draw; }
        }

        // Tải ảnh về máy
        function tlDownloadBlob(blob, fileName) {
          var a = document.createElement('a');
          a.href = URL.createObjectURL(blob);
          a.download = fileName;
          document.body.appendChild(a); a.click(); document.body.removeChild(a);
          setTimeout(function () { URL.revokeObjectURL(a.href); }, 1000);
        }

        // Web Share API (mobile) với fallback tải video + mở trang
        function tlShareTo(platform, btn) {
          if (btn.dataset.ready) {
            // Step 2: Synchronous share
            doShare(platform, btn._blob, btn._fileName);
            return;
          }

          // Step 1: Generate video
          var originalText = btn.textContent;
          var originalBg = btn.style.background;
          btn.disabled = true;
          btn.textContent = '⏳ Đang xử lý...';
          
          tlGenerateVideoBlob(function (blob, fileName) {
            btn.disabled = false;
            if (!blob) {
                btn.textContent = originalText;
                return;
            }
            btn._blob = blob;
            btn._fileName = fileName;
            btn.dataset.ready = "true";
            
            // Đổi text để người dùng bấm lại lần 2 (đồng bộ với sự kiện click)
            btn.textContent = '✅ Bấm để share';
            btn.style.background = '#22c55e'; // Green to indicate ready
            
            // Tự động reset sau 15 giây nếu không bấm
            setTimeout(function() {
                if (btn.dataset.ready) {
                    delete btn.dataset.ready;
                    delete btn._blob;
                    btn.textContent = originalText;
                    btn.style.background = originalBg;
                }
            }, 15000);
            
          }, function (current, total) {
            btn.textContent = '⏳ ' + Math.round((current/total)*100) + '%';
          });
        }

        function doShare(platform, blob, fileName) {
            var f = tlFrames[tlIndex] || {};
            var shareText = '🌿 Vườn rau thủy canh của tôi — Ai trồng cây (' + (f.date || '') + ')';
            var file = new File([blob], fileName, { type: 'video/webm' });
            
            // Reset state
            var btn = event.currentTarget;
            var originalText = btn.getAttribute('data-orig-text') || (platform==='fb'?'Facebook':(platform==='zalo'?'Zalo':'Chia sẻ'));
            btn.textContent = originalText;
            btn.style.background = '';
            delete btn.dataset.ready;
            delete btn._blob;

            if (navigator.share && navigator.canShare && navigator.canShare({ files: [file] })) {
              navigator.share({ files: [file], title: 'Vườn của tôi', text: shareText })
                .catch(function (e) { console.log('Share canceled or failed', e); });
              return;
            }
            
            // Fallback desktop: tải video rồi mở nền tảng để user đăng thủ công
            alert('Video đã được tải về máy.\nTrình duyệt sẽ mở ' + (platform==='fb'?'Facebook':'Zalo') + ' để bạn có thể tự đăng video này nhé!');
            tlDownloadBlob(blob, fileName);
            var urls = { fb: 'https://www.facebook.com/', zalo: 'https://chat.zalo.me/' };
            if (urls[platform]) {
              window.open(urls[platform], '_blank');
            }
        }

        if (tlSaveFrameBtn) {
          tlSaveFrameBtn.addEventListener('click', function () {
            tlGetCurrentFrameBlob(function (blob, fileName) { tlDownloadBlob(blob, fileName); });
          });
        }
        if (tlShareFbBtn) {
          tlShareFbBtn.setAttribute('data-orig-text', tlShareFbBtn.textContent);
          tlShareFbBtn.addEventListener('click', function (e) { tlShareTo('fb', this); });
        }
        if (tlShareZaloBtn) {
          tlShareZaloBtn.setAttribute('data-orig-text', tlShareZaloBtn.textContent);
          tlShareZaloBtn.addEventListener('click', function (e) { tlShareTo('zalo', this); });
        }
        if (tlShareNativeBtn) {
          tlShareNativeBtn.setAttribute('data-orig-text', tlShareNativeBtn.textContent);
          tlShareNativeBtn.addEventListener('click', function (e) { tlShareTo('', this); });
        }

        // Admin: chụp ảnh ngay
        var tlCaptureBtn = document.getElementById('d2TlCaptureNow');
        var tlCaptureStatus = document.getElementById('d2TlCaptureStatus');
        if (tlCaptureBtn) {
          tlCaptureBtn.addEventListener('click', function () {
            var slug = tlStreamSel ? tlStreamSel.value : '';
            var legacySlug = tlStreamSel && tlStreamSel.options[tlStreamSel.selectedIndex] ? tlStreamSel.options[tlStreamSel.selectedIndex].getAttribute('data-legacy') : '';
            var finalStream = legacySlug ? legacySlug : slug;
            if (!slug) { if (tlCaptureStatus) tlCaptureStatus.textContent = 'Chọn camera trước.'; return; }
            tlCaptureBtn.disabled = true;
            tlCaptureBtn.textContent = '⏳ Đang chụp...';
            if (tlCaptureStatus) tlCaptureStatus.textContent = '';
            var body = new FormData();
            body.append('action', 'aitrongcay_timelapse_capture_now');
            body.append('nonce', AITR_AJAX_NONCE);
            body.append('garden_key', AITR_GARDEN_KEY || '');
            body.append('stream', finalStream);
            fetch(AITR_AJAX_URL, { method: 'POST', body: body, credentials: 'same-origin' })
              .then(function (r) { return r.json(); })
              .then(function (res) {
                if (!res || !res.success) throw new Error((res && res.data && res.data.message) || 'Thất bại');
                var d = res.data;
                if (tlCaptureStatus) tlCaptureStatus.textContent = '✓ ' + d.message + ' (' + d.date + ' ' + d.time + ')';
                // Hiển thị ảnh vừa chụp luôn trong player
                if (tlImg) { tlImg.src = d.url; tlImg.style.display = 'block'; }
                if (tlEmpty) tlEmpty.style.display = 'none';
                if (tlInfo) tlInfo.textContent = d.date + ' ' + d.time + ' (vừa chụp)';
              })
              .catch(function (err) {
                if (tlCaptureStatus) tlCaptureStatus.textContent = '✗ ' + ((err && err.message) || 'Lỗi không xác định');
              })
              .finally(function () { tlCaptureBtn.disabled = false; tlCaptureBtn.textContent = '📷 Chụp ngay'; });
          });
        }
      })();
    })();

    // ── Pump auto modal ────────────────────────────────────────────────────────
    (function () {
      var overlay  = document.querySelector('[data-pump-modal]');
      if (!overlay) return;
      var openBtn  = document.querySelector('[data-pump-modal-open]');
      var closeBtn = overlay.querySelector('[data-pump-modal-close]');
      var isAdmin  = <?php echo wp_json_encode($is_admin_user); ?>;

      function postPump(action, extra) {
        var fd = new FormData();
        fd.append('action',     action);
        fd.append('nonce',      AITR_AJAX_NONCE);
        fd.append('garden_key', AITR_GARDEN_KEY);
        if (extra) Object.keys(extra).forEach(function (k) { fd.append(k, extra[k]); });
        return fetch(AITR_AJAX_URL, { method: 'POST', body: fd, credentials: 'same-origin' }).then(function (r) { return r.json(); });
      }

      function showPumpToast(msg) {
        var t = document.querySelector('[data-d2-capture-toast]');
        if (!t) return;
        t.textContent = msg;
        t.hidden = false;
        clearTimeout(t._tid);
        t._tid = setTimeout(function () { t.hidden = true; }, 3500);
      }

      function openModal() {
        overlay.hidden = false;
        document.body.style.overflow = 'hidden';
        loadStatus();
        loadLog();
      }
      function closeModal() {
        overlay.hidden = true;
        document.body.style.overflow = '';
      }

      if (openBtn)  openBtn.addEventListener('click', openModal);
      if (closeBtn) closeBtn.addEventListener('click', closeModal);
      overlay.addEventListener('click', function (e) { if (e.target === overlay) closeModal(); });
      document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !overlay.hidden) closeModal(); });

      function loadStatus() {
        postPump('aitrongcay_pump_status').then(function (res) {
          if (!res.success) return;
          var d = res.data;

          // Soil bar + value
          var soilEl = overlay.querySelector('[data-pump-soil]');
          var barEl  = overlay.querySelector('[data-pump-soil-bar]');
          var pct    = d.soil !== null ? Math.min(100, Math.max(0, parseFloat(d.soil))) : 0;
          if (soilEl) soilEl.textContent = d.soil !== null ? parseFloat(d.soil).toFixed(1) + '%' : '—';
          if (barEl)  barEl.style.setProperty('--pct', pct + '%');

          // Running badge
          var stateEl = overlay.querySelector('[data-pump-running]');
          if (stateEl) {
            stateEl.textContent = d.is_running ? '⚡ Đang bơm' : '● Đang nghỉ';
            stateEl.className   = 'd2-pump-state-badge ' + (d.is_running ? 'is-on' : 'is-off');
          }

          // Last pump time
          var lastEl = overlay.querySelector('[data-pump-last]');
          if (lastEl) {
            if (d.last_pump) {
              var dp = new Date(d.last_pump.replace(' ', 'T'));
              lastEl.textContent = (dp.getDate() < 10 ? '0' : '') + dp.getDate() + '/'
                + (dp.getMonth() + 1 < 10 ? '0' : '') + (dp.getMonth() + 1)
                + ' ' + (dp.getHours() < 10 ? '0' : '') + dp.getHours()
                + ':' + (dp.getMinutes() < 10 ? '0' : '') + dp.getMinutes();
            } else {
              lastEl.textContent = 'Chưa có';
            }
          }

          // Auto badge
          if (isAdmin && d.rules) {
            var r = d.rules;
            updateAutoBadge(r.enabled);
            var setField = function (sel, val) {
              var el = overlay.querySelector(sel);
              if (!el) return;
              if (el.type === 'checkbox') el.checked = Boolean(val);
              else el.value = val;
            };
            setField('[data-pump-rule="enabled"]',            r.enabled);
            setField('[data-pump-rule="time_on"]',            r.time_on);
            setField('[data-pump-rule="time_off"]',           r.time_off);
            setField('[data-pump-rule="time_start"]',         r.time_start);
            setField('[data-pump-rule="time_end"]',           r.time_end);
            overlay.querySelectorAll('[data-pump-day]').forEach(function (cb) {
              cb.checked = Array.isArray(r.days) && r.days.indexOf(parseInt(cb.value, 10)) !== -1;
            });
          }
        }).catch(function () {});
      }

      function updateAutoBadge(enabled) {
        var badge = overlay.querySelector('[data-pump-auto-badge]');
        if (!badge) return;
        badge.textContent = enabled
          ? '✅ Tự động BẬT (Timer Loop)'
          : '⭕ Tự động TẮT';
        badge.className = 'd2-pump-auto-badge ' + (enabled ? 'is-on' : 'is-off');
      }

      function loadLog() {
        var tbody = overlay.querySelector('[data-pump-log-body]');
        if (!tbody) return;
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:12px;color:var(--muted)">Đang tải…</td></tr>';
        postPump('aitrongcay_pump_log', { limit: 10 }).then(function (res) {
          if (!res.success || !res.data.logs || !res.data.logs.length) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:12px;color:var(--muted)">Chưa có lịch sử bơm.</td></tr>';
            return;
          }
          tbody.innerHTML = res.data.logs.map(function (r) {
            var soil = r.soil_before !== null ? parseFloat(r.soil_before).toFixed(1) + '%' : '—';
            var dur  = r.duration_sec !== null ? r.duration_sec + 's' : '—';
            var cls  = r.status === 'completed' ? 'pump-log-ok' : 'pump-log-warn';
            return '<tr>'
              + '<td>' + (r.pump_on_at || '—') + '</td>'
              + '<td>' + (r.triggered_by || '—') + '</td>'
              + '<td>' + soil + '</td>'
              + '<td>' + dur  + '</td>'
              + '<td class="' + cls + '">' + (r.status || '—') + '</td>'
              + '</tr>';
          }).join('');
        }).catch(function () {
          tbody.innerHTML = '<tr><td colspan="5" style="color:var(--muted)">Lỗi tải dữ liệu.</td></tr>';
        });
      }

      // Bật bơm thủ công
      var onBtn = overlay.querySelector('[data-pump-on]');
      if (onBtn) {
        onBtn.addEventListener('click', function () {
          onBtn.disabled = true;
          postPump('aitrongcay_pump_manual', { state: 1 }).then(function (res) {
            showPumpToast(res.data && res.data.message ? res.data.message : (res.success ? 'Đã bật bơm' : 'Lỗi'));
            if (res.success) { loadStatus(); loadLog(); }
          }).catch(function () { showPumpToast('Lỗi kết nối Blynk'); })
            .finally(function () { onBtn.disabled = false; });
        });
      }

      // Tắt bơm thủ công
      var offBtn = overlay.querySelector('[data-pump-off]');
      if (offBtn) {
        offBtn.addEventListener('click', function () {
          offBtn.disabled = true;
          postPump('aitrongcay_pump_manual', { state: 0 }).then(function (res) {
            showPumpToast(res.data && res.data.message ? res.data.message : (res.success ? 'Đã tắt bơm' : 'Lỗi'));
            if (res.success) { loadStatus(); loadLog(); }
          }).catch(function () { showPumpToast('Lỗi kết nối Blynk'); })
            .finally(function () { offBtn.disabled = false; });
        });
      }

      // Lưu cài đặt (admin only)
      var saveBtn  = overlay.querySelector('[data-pump-save]');
      var statusEl = overlay.querySelector('[data-pump-save-status]');
      if (saveBtn && isAdmin) {
        saveBtn.addEventListener('click', function () {
          saveBtn.disabled = true;
          if (statusEl) { statusEl.textContent = 'Đang lưu…'; statusEl.className = 'd2-tray-settings-status'; }

          var days = [];
          overlay.querySelectorAll('[data-pump-day]:checked').forEach(function (cb) {
            days.push(parseInt(cb.value, 10));
          });
          var rules = {
            enabled:            overlay.querySelector('[data-pump-rule="enabled"]') ? overlay.querySelector('[data-pump-rule="enabled"]').checked : false,
            time_on:            Number(overlay.querySelector('[data-pump-rule="time_on"]') ? overlay.querySelector('[data-pump-rule="time_on"]').value : 10),
            time_off:           Number(overlay.querySelector('[data-pump-rule="time_off"]') ? overlay.querySelector('[data-pump-rule="time_off"]').value : 5),
            time_start:         overlay.querySelector('[data-pump-rule="time_start"]') ? overlay.querySelector('[data-pump-rule="time_start"]').value : '06:00',
            time_end:           overlay.querySelector('[data-pump-rule="time_end"]')   ? overlay.querySelector('[data-pump-rule="time_end"]').value   : '22:00',
            days: days
          };

          postPump('aitrongcay_pump_rules_save', { rules: JSON.stringify(rules) }).then(function (res) {
            if (statusEl) {
              statusEl.textContent = res.success ? '✓ Đã lưu xuống Mạch' : ('✗ ' + (res.data && res.data.message ? res.data.message : 'Lỗi'));
              statusEl.className   = 'd2-tray-settings-status ' + (res.success ? 'is-ok' : 'is-err');
            }
            if (res.success) updateAutoBadge(rules.enabled);
          }).catch(function () {
            if (statusEl) { statusEl.textContent = '✗ Lỗi kết nối'; statusEl.className = 'd2-tray-settings-status is-err'; }
          }).finally(function () { saveBtn.disabled = false; });
        });
      }
    })();
  </script>

  <!-- TASK 3.4: Reset vụ mùa modal -->
  <div class="d2-custom-modal-overlay" id="d2ResetCropModal" style="display: none;">
    <div class="d2-custom-modal">
      <div class="d2-custom-modal-icon">🌱</div>
      <h3 class="d2-custom-modal-title">Bắt đầu vụ mới?</h3>
      <p class="d2-custom-modal-text">Bạn có chắc chắn muốn dọn khoang và trồng lứa mới? Việc này sẽ khởi tạo lại ngày sinh trưởng về Ngày 1 và xóa lịch sử phân tích AI cũ.<br><br><strong style="color: #e53e3e;">Khu vườn sẽ được dọn kho ảnh vĩnh viễn. Bạn hãy đảm bảo đã lưu lại những bức ảnh kỉ niệm của khu vườn trước khi dọn kho.</strong></p>
      <div class="d2-custom-modal-actions">
        <button type="button" class="d2-custom-modal-btn cancel" id="d2ResetCropCancel">Hủy</button>
        <button type="button" class="d2-custom-modal-btn confirm" id="d2ResetCropConfirm">Xác nhận trồng mới</button>
      </div>
    </div>
  </div>

  <script>
    // ── TASK 3.4: Reset vụ mùa JS ─────────────────────────
    (function () {
      var resetBtn = document.querySelector('.d2-reset-crop-btn');
      var modal = document.getElementById('d2ResetCropModal');
      var cancelBtn = document.getElementById('d2ResetCropCancel');
      var confirmBtn = document.getElementById('d2ResetCropConfirm');
      
      if (!resetBtn || !modal || !cancelBtn || !confirmBtn) return;
      
      resetBtn.addEventListener('click', function () {
        modal.style.display = 'flex';
      });
      
      cancelBtn.addEventListener('click', function () {
        modal.style.display = 'none';
      });
      
      modal.addEventListener('click', function (e) {
        if (e.target === modal) {
          modal.style.display = 'none';
        }
      });
      
      confirmBtn.addEventListener('click', function () {
        var potCode = resetBtn.getAttribute('data-reset-crop');
        var formData = new FormData();
        formData.append('action', 'aitrongcay_reset_pot_crop');
        formData.append('nonce', typeof AITR_AJAX_NONCE !== 'undefined' ? AITR_AJAX_NONCE : (window.AITR_PORTAL ? window.AITR_PORTAL.nonce : ''));
        formData.append('garden_key', typeof AITR_GARDEN_KEY !== 'undefined' ? AITR_GARDEN_KEY : (window.AITR_PORTAL ? window.AITR_PORTAL.garden_key : ''));
        formData.append('pot_code', potCode);
        
        resetBtn.textContent = 'Đang dọn...';
        resetBtn.style.opacity = '0.5';
        resetBtn.disabled = true;

        fetch((typeof AITR_AJAX_URL !== 'undefined' ? AITR_AJAX_URL : (window.AITR_PORTAL ? window.AITR_PORTAL.ajaxurl : '/wp-admin/admin-ajax.php')), {
          method: 'POST',
          body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
          if (res.success) {
            alert('Khoang đã sẵn sàng cho vụ mới! Đang tải lại khu vườn...');
            window.location.reload();
          } else {
            alert('Lỗi: ' + (res.data ? res.data.message : 'Không xác định'));
            resetBtn.textContent = '🌱 Trồng vụ mới';
            resetBtn.style.opacity = '1';
            resetBtn.disabled = false;
          }
        })
        .catch(function(e) {
          alert('Lỗi kết nối. Vui lòng thử lại.');
          resetBtn.textContent = '🌱 Trồng vụ mới';
          resetBtn.style.opacity = '1';
          resetBtn.disabled = false;
        });
      });
    })();
  </script>
</section>