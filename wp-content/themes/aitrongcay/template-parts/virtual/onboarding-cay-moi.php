<?php if (! defined('ABSPATH')) { exit; }
$current_user = wp_get_current_user();
$can_manage_catalog = function_exists('aitrongcay_can_manage_onboarding_catalog') ? aitrongcay_can_manage_onboarding_catalog($current_user) : current_user_can('manage_options');
if (! is_user_logged_in()) {
  wp_safe_redirect(wp_login_url(home_url('/portal/onboarding-cay-moi/')));
  exit;
}
if (! $can_manage_catalog) {
  wp_safe_redirect(add_query_arg('catalog_access', 'denied', home_url('/portal/kho-nong-cu-2/')));
  exit;
}

$saved_state = isset($_GET['saved']) ? sanitize_key((string) $_GET['saved']) : '';
$search_term = isset($_GET['q']) ? sanitize_text_field((string) $_GET['q']) : '';
$edit_id = isset($_GET['edit']) ? absint($_GET['edit']) : 0;
$supply_search_term = '';
$supplies = [];
$plants = $search_term !== '' && function_exists('aitrongcay_plants_latest') ? aitrongcay_plants_latest(20, $search_term) : [];
$editing_plant = function_exists('aitrongcay_plant_find') ? aitrongcay_plant_find($edit_id) : null;
$plant_status_options = function_exists('aitrongcay_plant_status_options') ? aitrongcay_plant_status_options() : ['draft'=>'Nháp','testing'=>'Thử nghiệm','active'=>'Đang vận hành','public'=>'Public'];
$selected_supply_ids = $edit_id > 0 && function_exists('aitrongcay_plant_supply_ids') ? aitrongcay_plant_supply_ids($edit_id) : [];
$linked_supplies = $edit_id > 0 && function_exists('aitrongcay_plant_supplies') ? aitrongcay_plant_supplies($edit_id) : [];
$sop_steps = $edit_id > 0 && function_exists('aitrongcay_plant_sop_steps') ? aitrongcay_plant_sop_steps($edit_id) : [];
$plant_public_content = $edit_id > 0 && function_exists('aitrongcay_plant_public_content') ? aitrongcay_plant_public_content($edit_id) : null;
$plant_environment = $edit_id > 0 && function_exists('aitrongcay_plant_environment_profile') ? aitrongcay_plant_environment_profile($edit_id) : null;
$plant_growth_stages = $edit_id > 0 && function_exists('aitrongcay_plant_growth_stages') ? aitrongcay_plant_growth_stages($edit_id) : [];
$plant_nutrition = $edit_id > 0 && function_exists('aitrongcay_plant_nutrition_profile') ? aitrongcay_plant_nutrition_profile($edit_id) : null;
$plant_checklists = $edit_id > 0 && function_exists('aitrongcay_plant_checklists') ? aitrongcay_plant_checklists($edit_id) : [];
$plant_health_issues = $edit_id > 0 && function_exists('aitrongcay_plant_health_issues') ? aitrongcay_plant_health_issues($edit_id) : [];
$plant_category_options = ['Hoa/cây cảnh', 'Rau ăn lá', 'Rau mầm', 'Cây gia vị', 'Cây ăn quả', 'Vị thuốc bắc', 'Vị thuốc nam', 'Khác'];
$selected_categories = array_values(array_filter(array_map('trim', preg_split('/\s*,\s*/', (string) ($editing_plant['category'] ?? '')))));
$sop_single_text = '';
if ($sop_steps !== []) {
  if (count($sop_steps) === 1) {
    $single_step = $sop_steps[0];
    $single_operator_tasks = trim((string) ($single_step['operator_tasks'] ?? ''));
    $single_has_extra_meta = trim((string) ($single_step['light_level'] ?? '')) !== ''
      || trim((string) ($single_step['watering_rule'] ?? '')) !== ''
      || trim((string) ($single_step['expected_state'] ?? '')) !== ''
      || trim((string) ($single_step['alert_conditions'] ?? '')) !== ''
      || trim((string) ($single_step['notes'] ?? '')) !== ''
      || (int) ($single_step['day_from'] ?? 0) > 0
      || (int) ($single_step['day_to'] ?? 0) > 0;
    if ($single_operator_tasks !== '' && ! $single_has_extra_meta) {
      $sop_single_text = $single_operator_tasks;
    }
  }
  if ($sop_single_text === '') {
    $sop_text_parts = [];
    foreach ($sop_steps as $step) {
      $step_lines = [];
      $step_title = trim((string) ($step['step_title'] ?? ''));
      $day_from = isset($step['day_from']) ? (int) $step['day_from'] : 0;
      $day_to = isset($step['day_to']) ? (int) $step['day_to'] : 0;
      $light_level = trim((string) ($step['light_level'] ?? ''));
      $watering_rule = trim((string) ($step['watering_rule'] ?? ''));
      $operator_tasks = trim((string) ($step['operator_tasks'] ?? ''));
      $expected_state = trim((string) ($step['expected_state'] ?? ''));
      $alert_conditions = trim((string) ($step['alert_conditions'] ?? ''));
      $notes = trim((string) ($step['notes'] ?? ''));

      if ($step_title !== '') {
        $step_lines[] = $step_title;
      }
      if ($day_from > 0 || $day_to > 0) {
        $step_lines[] = 'Ngày: ' . $day_from . ($day_to > $day_from ? '–' . $day_to : '');
      }
      if ($light_level !== '') {
        $step_lines[] = 'Ánh sáng: ' . $light_level;
      }
      if ($watering_rule !== '') {
        $step_lines[] = 'Tưới / cấp ẩm: ' . $watering_rule;
      }
      if ($operator_tasks !== '') {
        $step_lines[] = 'Việc cần làm: ' . $operator_tasks;
      }
      if ($expected_state !== '') {
        $step_lines[] = 'Trạng thái mong đợi: ' . $expected_state;
      }
      if ($alert_conditions !== '') {
        $step_lines[] = 'Cảnh báo / bất thường: ' . $alert_conditions;
      }
      if ($notes !== '') {
        $step_lines[] = 'Ghi chú: ' . $notes;
      }
      if ($step_lines !== []) {
        $sop_text_parts[] = implode("\n", $step_lines);
      }
    }
    $sop_single_text = trim(implode("\n\n", $sop_text_parts));
  }
}
if ($sop_single_text === '') {
  $sop_single_text = "Ví dụ:\nNgày 1–2: Ủ tối, giữ ẩm bề mặt, chưa bật đèn.\nNgày 3–6: Bật 1 đèn, kiểm tra độ đều của mầm, bổ sung ẩm nhẹ khi mặt khoang se.\nNgày 7–9: Tăng thoáng khí, theo dõi chiều cao và màu lá, loại bỏ khoang bất thường nếu có.";
}
$supply_role_options = function_exists('aitrongcay_plant_supply_usage_roles') ? aitrongcay_plant_supply_usage_roles() : ['required' => 'Bắt buộc', 'optional' => 'Tùy chọn', 'alternative' => 'Thay thế'];
$checklist_daily_text = '';
foreach ($plant_checklists as $checklist_item) {
  if ((string) ($checklist_item['checklist_type'] ?? '') === 'daily' && trim((string) ($checklist_item['item_text'] ?? '')) !== '') {
    $checklist_daily_text .= ($checklist_daily_text !== '' ? "\n" : '') . trim((string) $checklist_item['item_text']);
  }
}
$health_issues_text = '';
foreach ($plant_health_issues as $health_issue) {
  $block = trim((string) ($health_issue['symptom_title'] ?? ''));
  $detail = trim((string) ($health_issue['symptom_detail'] ?? ''));
  if ($detail !== '') {
    $block .= "\n" . $detail;
  }
  if ($block !== '') {
    $health_issues_text .= ($health_issues_text !== '' ? "\n\n" : '') . $block;
  }
}
$environment_text = trim(implode("\n", array_filter([
  (string) ($plant_environment['source_note'] ?? ''),
  (string) ($plant_environment['airflow_note'] ?? ''),
])));
$nutrition_text = trim(implode("\n", array_filter([
  (string) ($plant_nutrition['mixing_note'] ?? ''),
  (string) ($plant_nutrition['warning_note'] ?? ''),
])));
$growth_stage_names = [];
if ($plant_growth_stages !== []) {
  foreach ($plant_growth_stages as $growth_stage) {
    $stage_name = trim((string) ($growth_stage['stage_name'] ?? ''));
    if ($stage_name !== '') {
      $growth_stage_names[] = $stage_name;
    }
  }
}
if ($growth_stage_names === []) {
  $growth_stage_names = ['Gieo hạt', 'Nảy mầm', 'Ra lá nhỏ'];
}
$phase3_alert_rules_text = $edit_id > 0 && function_exists('aitrongcay_plant_longtext_pack') ? aitrongcay_plant_longtext_pack($edit_id, 'plant_alert_rules') : '';
$selected_supply_filter = isset($_GET['supply_filter']) ? sanitize_text_field((string) $_GET['supply_filter']) : '';
$show_selected_only = isset($_GET['selected_only']) && (string) $_GET['selected_only'] === '1';
$linked_supply_map = [];
foreach ($linked_supplies as $linked_supply) {
  $linked_supply_map[(int) ($linked_supply['supply_id'] ?? 0)] = $linked_supply;
}
$unlink_supply_nonce = wp_create_nonce('aitrongcay_unlink_plant_supply');
$upload_nonce = wp_create_nonce('aitrongcay_upload_media_image');
$eco_nav_items = function_exists('aitrongcay_eco_nav_items') ? aitrongcay_eco_nav_items() : [];
?>
<section class="section-tight eco-plant-onboarding-shell">
  <style>
    .site-header,.account-menu,.footer,.floating-ai-chat{display:none !important}
    main > .section, main > .section > .container, main article.page{max-width:none !important;width:100% !important;padding:0 !important;margin:0 !important;background:transparent !important;box-shadow:none !important;border:none !important}
    main article.page > .eyebrow, main article.page > h1{display:none !important}
    main article.page > .entry-content{margin:0 !important}
    .eco-plant-onboarding-shell{background:#121411;min-height:100vh;padding:0;position:relative;overflow:hidden}
    .eco-plant-onboarding-page{background:#121411;color:#e3e3de;min-height:100vh;position:relative;font-family:'Manrope',sans-serif}
    .eco-plant-onboarding-page *{box-sizing:border-box}
    .eco-plant-onboarding-page a{text-decoration:none}
    .eco-plant-topbar{position:fixed;top:0;left:0;right:0;z-index:50;height:72px;padding:0 28px;display:flex;align-items:center;justify-content:space-between;background:rgba(18,20,17,.82);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);box-shadow:0 20px 40px rgba(0,0,0,.08)}
    .eco-plant-brand{display:flex;align-items:center;gap:18px}
    .eco-plant-brand-mark{font-family:'Noto Serif',serif;font-size:30px;font-weight:700;color:#6FDBA8;letter-spacing:-.02em}
    .eco-plant-brand-divider{width:1px;height:28px;background:rgba(62,73,66,.45)}
    .eco-plant-brand-title{font-family:'Noto Serif',serif;font-size:26px;font-weight:700;letter-spacing:-.02em;color:#e3e3de}
    .eco-plant-actions{display:flex;align-items:center;gap:12px}
    .eco-btn{border:none;cursor:pointer;border-radius:18px;padding:12px 18px;font-size:14px;font-weight:700;letter-spacing:.01em;transition:all .24s ease}
    .eco-btn-secondary{background:rgba(51,53,50,.48);color:#e3e3de;backdrop-filter:blur(18px);-webkit-backdrop-filter:blur(18px);box-shadow:inset 0 0 0 1px rgba(62,73,66,.16)}
    .eco-btn-secondary:hover{transform:translateY(-1px);background:rgba(65,68,63,.7)}
    .eco-btn-primary{background:linear-gradient(135deg,#31A375 0%, #6FDBA8 100%);color:#003824;box-shadow:0 0 24px rgba(111,219,168,.22)}
    .aitr-toast{position:fixed;right:22px;bottom:22px;z-index:120;background:rgba(26,28,25,.96);color:#e3e3de;border-radius:16px;padding:12px 14px;box-shadow:0 20px 40px rgba(0,0,0,.24), inset 0 0 0 1px rgba(62,73,66,.16);opacity:0;transform:translateY(10px);pointer-events:none;transition:all .2s ease;max-width:320px}
    .aitr-toast.show{opacity:1;transform:translateY(0)}
    .aitr-toast.success{box-shadow:0 20px 40px rgba(0,0,0,.24), inset 0 0 0 1px rgba(111,219,168,.22)}
    .aitr-toast.error{box-shadow:0 20px 40px rgba(0,0,0,.24), inset 0 0 0 1px rgba(255,180,171,.22)}
    .eco-btn-primary:hover{transform:scale(1.02);filter:brightness(1.05)}
    .eco-progress-wrap{display:flex;align-items:center;gap:16px;margin-top:6px}
    .eco-progress-bar{position:relative;height:8px;flex:1;min-width:220px;border-radius:999px;background:#1e201d;overflow:hidden}
    .eco-progress-fill{position:absolute;inset:0 auto 0 0;width:42%;background:linear-gradient(90deg,#31A375 0%, #6FDBA8 100%);border-radius:999px}
    .eco-progress-meta{font-size:11px;letter-spacing:.22em;text-transform:uppercase;color:#bdcac0}
    .eco-plant-layout{display:flex;min-height:100vh;padding-top:72px;background:
      radial-gradient(circle at top right, rgba(255,225,109,.08), transparent 24%),
      radial-gradient(circle at 20% 10%, rgba(111,219,168,.12), transparent 26%),
      #121411}
    .eco-plant-sidebar{position:fixed;top:72px;left:0;bottom:0;width:296px;padding:26px 20px 24px;background:#121411;border-right:1px solid rgba(62,73,66,.18);overflow:auto}
    .eco-plant-sidebar-card{background:rgba(26,28,25,.92);border-radius:28px;padding:22px 18px 18px;box-shadow:inset 0 0 0 1px rgba(62,73,66,.12)}
    .eco-portal-nav-card{background:rgba(7,33,24,.58);backdrop-filter:blur(24px);border-radius:28px;padding:22px 0 18px;box-shadow:inset 0 0 0 1px rgba(62,73,66,.12);margin-bottom:18px}
    .eco-portal-nav-head{padding:0 24px 18px;display:flex;align-items:center;gap:12px}.eco-portal-nav-badge{width:48px;height:48px;border-radius:18px;background:linear-gradient(135deg,#31a375,#6fdba8);display:grid;place-items:center;color:#062013}
    .eco-portal-nav-head h3{margin:0;font-size:14px;color:#6FDBA8;font-weight:800}.eco-portal-nav-head p{margin:4px 0 0;font-size:10px;letter-spacing:.18em;text-transform:uppercase;color:rgba(227,227,222,.58)}
    .eco-portal-nav-list{display:flex;flex-direction:column;gap:2px}.eco-portal-nav-list a{display:flex;align-items:center;gap:14px;padding:14px 24px;color:rgba(227,227,222,.62);transition:.2s}.eco-side-link-icon{flex:0 0 auto;font-size:16px;line-height:1}.eco-side-link-short{display:none}.eco-portal-nav-list a.active{background:linear-gradient(90deg,#31a375,#6fdba8);color:#062013;border-radius:0 999px 999px 0;font-weight:900}.eco-portal-nav-list a:not(.active):hover{transform:translateX(6px);color:#6FDBA8}
    .eco-plant-sidebar-kicker{font-size:12px;letter-spacing:.22em;text-transform:uppercase;color:#6FDBA8;font-weight:800;margin:0 0 4px}
    .eco-plant-sidebar-sub{font-size:11px;letter-spacing:.22em;text-transform:uppercase;color:#87948b;margin:0 0 22px}
    .eco-plant-step-list{display:grid;gap:8px}
    .eco-plant-step{display:flex;align-items:center;gap:14px;padding:14px 14px;border-radius:18px;color:#bdcac0;transition:all .2s ease}
    .eco-plant-step:hover{background:rgba(41,43,39,.75);color:#e3e3de;transform:translateX(2px)}
    .eco-plant-step.is-active{background:linear-gradient(90deg, rgba(111,219,168,.18), rgba(111,219,168,0));color:#dff7ea;box-shadow:inset 4px 0 0 #6FDBA8}
    .eco-plant-step-index{width:34px;height:34px;border-radius:50%;display:grid;place-items:center;background:#292b27;color:#ffe16d;font-size:12px;font-weight:800;flex:0 0 auto}
    .eco-plant-step.is-active .eco-plant-step-index{background:#6FDBA8;color:#003824}
    .eco-plant-step-copy strong{display:block;font-size:14px;color:inherit}
    .eco-plant-step-copy span{display:block;margin-top:3px;font-size:11px;color:#87948b;letter-spacing:.08em;text-transform:uppercase}
    .eco-plant-sidebar-foot{margin-top:22px;padding-top:18px;border-top:1px solid rgba(62,73,66,.12);display:grid;gap:10px}
    .eco-plant-sidebar-foot .eco-btn{width:100%;justify-content:center;display:flex;align-items:center;gap:8px}
    .eco-plant-main{flex:1;margin-left:296px;padding:32px 28px 64px}
    .eco-main-grid{max-width:1380px;margin:0 auto;display:grid;grid-template-columns:minmax(0,1fr);gap:24px;align-items:start}
    .eco-main-column{display:grid;gap:22px}
    .eco-glass-card{background:rgba(51,53,50,.42);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border-radius:30px;padding:26px 26px 24px;box-shadow:0 20px 40px rgba(0,0,0,.08), inset 0 0 0 1px rgba(62,73,66,.12)}
    .eco-section-header{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:22px}
    .eco-section-header h2{margin:0;font-family:'Noto Serif',serif;font-size:34px;line-height:1.05;letter-spacing:-.03em;color:#e3e3de}
    .eco-section-header p{margin:8px 0 0;color:#bdcac0;font-size:14px;max-width:680px}
    .eco-field-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}
    .eco-field-grid--three{grid-template-columns:repeat(3,minmax(0,1fr))}
    .eco-field{display:grid;gap:8px}
    .eco-field label{font-size:11px;font-weight:800;letter-spacing:.22em;text-transform:uppercase;color:#ffb68c;padding-left:4px}
    .eco-field input,.eco-field select,.eco-field textarea{width:100%;background:#292b27;border:none;border-radius:18px;padding:16px 18px;color:#e3e3de;outline:none;font:inherit;box-shadow:none;resize:vertical;min-height:56px}
    .eco-field textarea{min-height:120px}
    .eco-field input::placeholder,.eco-field textarea::placeholder{color:rgba(189,202,192,.38)}
    .eco-field input:focus,.eco-field select:focus,.eco-field textarea:focus{box-shadow:0 0 0 1px rgba(111,219,168,.4)}
    .eco-pills{display:flex;gap:10px;flex-wrap:wrap}
    .eco-pill{padding:10px 14px;border-radius:999px;background:#292b27;color:#bdcac0;font-size:12px;font-weight:700;letter-spacing:.04em}
    .eco-pill.is-active{background:linear-gradient(135deg,#31A375 0%, #6FDBA8 100%);color:#003824}
    .eco-upload{position:relative;min-height:240px;border-radius:26px;background:rgba(41,43,39,.95);display:flex;flex-direction:column;justify-content:flex-end;padding:24px;overflow:hidden}
    .eco-upload::before{content:'';position:absolute;inset:0;background:url('https://lh3.googleusercontent.com/aida-public/AB6AXuBY5K37VgnoYGlLanP2EMEF0fGNGjZrt5UMB1C81D1m-m1diExcr0QQQMepm-vQgq8LJ1p3iL5MW24gN6omSTDUz-wq-c-4q5gswVXDRbWGLN6IZiYVApbz5FPaZW3-SJz80duT_acV-VzauJyKZdRaEc8jroP5MBtVWmljZCUyySYOK6k5oTMEOzqkPLiMmT0su68BiEBmDLlIvKLD-h2K0e9mFviFj2Qs_w4Jp9qkSCJlHgE9EXotPnoGhyID1pbAAHXVa6TPQrEC') center/cover no-repeat;opacity:.24}
    .eco-upload::after{content:'';position:absolute;inset:0;background:linear-gradient(180deg, rgba(18,20,17,.2), rgba(18,20,17,.88))}
    .eco-upload-copy{position:relative;z-index:1}
    .eco-upload-badge{width:58px;height:58px;border-radius:20px;display:grid;place-items:center;background:rgba(111,219,168,.14);color:#6FDBA8;font-size:28px;margin-bottom:18px}
    .eco-upload h3{margin:0 0 8px;font-size:22px;color:#fff;font-weight:800}
    .eco-upload p{margin:0;color:#bdcac0;font-size:14px}
    .eco-material-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
    .eco-mini-card{background:#1a1c19;border-radius:24px;padding:20px;display:grid;gap:16px;box-shadow:inset 0 0 0 1px rgba(62,73,66,.12)}
    .eco-mini-card-head{display:flex;align-items:center;gap:14px}
    .eco-mini-card-icon{width:48px;height:48px;border-radius:16px;display:grid;place-items:center;background:rgba(111,219,168,.12);color:#6FDBA8;font-size:24px}
    .eco-mini-card h3{margin:0;font-size:18px;color:#e3e3de}
    .eco-mini-card p{margin:4px 0 0;font-size:13px;color:#87948b}
    .eco-mini-card ul{margin:0;padding:0;list-style:none;display:grid;gap:8px}
    .eco-mini-card li{display:flex;justify-content:space-between;gap:14px;font-size:13px;color:#bdcac0}
    .eco-mini-card li strong{font-weight:700;color:#e3e3de}
    .eco-tray-card{display:grid;grid-template-columns:minmax(0,1.25fr) minmax(260px,.75fr);gap:20px;align-items:stretch}
    .eco-tray-visual{background:radial-gradient(circle at top right, rgba(255,225,109,.12), transparent 25%), #1a1c19;border-radius:28px;padding:24px;position:relative;overflow:hidden;display:flex;flex-direction:column;justify-content:space-between;min-height:310px}
    .eco-tray-lights{display:flex;justify-content:center;gap:16px;margin-bottom:16px}
    .eco-light{width:84px;height:26px;border-radius:999px;background:linear-gradient(180deg,#ffe16d,#d5ad00);box-shadow:0 10px 24px rgba(255,225,109,.2);opacity:.9}
    .eco-light.is-muted{opacity:.24;filter:saturate(.4)}
    .eco-tray-box{margin:0 auto;width:min(100%, 340px);height:156px;border-radius:28px;background:linear-gradient(180deg,#223127,#182018);box-shadow:inset 0 0 0 1px rgba(111,219,168,.12), 0 16px 28px rgba(0,0,0,.24);position:relative}
    .eco-tray-box::before{content:'';position:absolute;left:18px;right:18px;top:18px;bottom:18px;border-radius:20px;background:linear-gradient(180deg,rgba(111,219,168,.14),rgba(255,225,109,.06))}
    .eco-tray-microgreens{position:absolute;left:20px;right:20px;bottom:24px;height:72px;background:radial-gradient(circle at 20% 100%, rgba(111,219,168,.78), transparent 34%), radial-gradient(circle at 50% 100%, rgba(111,219,168,.84), transparent 38%), radial-gradient(circle at 80% 100%, rgba(111,219,168,.76), transparent 34%)}
    .eco-tray-note{display:flex;justify-content:space-between;align-items:flex-end;gap:12px}
    .eco-tray-note h3{margin:0;font-family:'Noto Serif',serif;font-size:28px;line-height:1.05}
    .eco-tray-note p{margin:8px 0 0;color:#bdcac0;font-size:14px}
    .eco-stat-chip{display:flex;flex-wrap:wrap;gap:10px}
    .eco-stat-chip span{padding:10px 12px;border-radius:999px;background:rgba(41,43,39,.9);font-size:12px;color:#dff7ea;font-weight:700}
    .aitr-supply-search-layout{display:grid;grid-template-columns:minmax(0,1fr);gap:12px;margin-bottom:12px}
    .aitr-supply-results{display:grid;gap:8px}
    .aitr-supply-row{display:grid;grid-template-columns:32px minmax(0,1fr) 140px 130px;gap:10px;align-items:center;background:#292b27;border-radius:14px;padding:10px 12px}
    .eco-timeline{display:grid;gap:14px}
    .eco-timeline-card{background:#1a1c19;border-radius:24px;padding:20px;display:grid;gap:14px;box-shadow:inset 0 0 0 1px rgba(62,73,66,.12)}
    .eco-timeline-top{display:flex;justify-content:space-between;gap:16px;align-items:flex-start}
    .eco-timeline-day{display:flex;align-items:center;gap:12px}
    .eco-timeline-day-badge{width:42px;height:42px;border-radius:16px;display:grid;place-items:center;background:rgba(111,219,168,.12);color:#6FDBA8;font-size:12px;font-weight:800}
    .eco-timeline-day strong{display:block;font-size:17px}
    .eco-timeline-day span{display:block;margin-top:4px;font-size:12px;color:#87948b;letter-spacing:.08em;text-transform:uppercase}
    .eco-timeline-state{padding:10px 14px;border-radius:999px;background:#292b27;color:#ffe16d;font-size:12px;font-weight:800;white-space:nowrap}
    .eco-timeline-copy{color:#bdcac0;font-size:14px;line-height:1.6}
    .eco-timeline-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
    .eco-timeline-metric{background:#292b27;border-radius:18px;padding:14px}
    .eco-timeline-metric label{display:block;font-size:10px;letter-spacing:.2em;text-transform:uppercase;color:#87948b;margin-bottom:8px}
    .eco-timeline-metric strong{font-size:14px;color:#e3e3de}
    .eco-ai-table{display:grid;gap:12px}
    .eco-ai-row{display:grid;grid-template-columns:1.1fr 1fr .8fr .95fr;gap:12px;background:#1a1c19;border-radius:20px;padding:16px;align-items:start;box-shadow:inset 0 0 0 1px rgba(62,73,66,.12)}
    .eco-ai-row strong{display:block;font-size:14px;color:#e3e3de;margin-bottom:6px}
    .eco-ai-row span{display:block;font-size:13px;line-height:1.5;color:#bdcac0}
    .eco-ai-alert{display:inline-flex;align-items:center;justify-content:center;padding:10px 12px;border-radius:999px;background:rgba(255,225,109,.12);color:#ffe16d;font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
    .eco-finance-grid{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(280px,.85fr);gap:18px}
    .eco-finance-summary{background:linear-gradient(180deg, rgba(49,163,117,.2), rgba(26,28,25,.96));border-radius:28px;padding:24px;display:grid;gap:18px;box-shadow:inset 0 0 0 1px rgba(111,219,168,.14)}
    .eco-finance-summary h3{margin:0;font-family:'Noto Serif',serif;font-size:28px;line-height:1.04}
    .eco-finance-summary p{margin:8px 0 0;color:#dff7ea;font-size:14px}
    .eco-finance-number{display:grid;gap:10px}
    .eco-finance-number div{display:flex;justify-content:space-between;gap:16px;font-size:14px;color:#e3e3de}
    .eco-finance-number strong{font-size:16px}
    .eco-public-preview{background:radial-gradient(circle at top right, rgba(111,219,168,.12), transparent 22%), #1a1c19;border-radius:28px;padding:24px;display:grid;gap:16px;box-shadow:inset 0 0 0 1px rgba(62,73,66,.12)}
    .eco-public-preview-kicker{font-size:11px;font-weight:800;letter-spacing:.22em;text-transform:uppercase;color:#ffe16d}
    .eco-public-preview h3{margin:0;font-family:'Noto Serif',serif;font-size:30px;line-height:1.08}
    .eco-public-preview p{margin:0;color:#bdcac0;font-size:15px;line-height:1.7}
    .eco-public-list{display:grid;gap:10px;padding:0;margin:0;list-style:none}
    .eco-public-list li{padding:12px 14px;border-radius:16px;background:#292b27;color:#dff7ea;font-size:13px;font-weight:700}
    .eco-review-list{display:grid;gap:14px}
    .eco-review-row{display:flex;justify-content:space-between;gap:16px;align-items:center;padding:16px 18px;background:#1a1c19;border-radius:18px;box-shadow:inset 0 0 0 1px rgba(62,73,66,.12)}
    .eco-review-row strong{font-size:15px;color:#e3e3de}
    .eco-review-row span{font-size:13px;color:#87948b;display:block;margin-top:4px}
    .eco-review-status{padding:10px 14px;border-radius:999px;background:rgba(111,219,168,.16);color:#6FDBA8;font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
    .eco-side-stack{display:grid;gap:22px;position:sticky;top:104px}
    .eco-side-hero{background:rgba(51,53,50,.42);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border-radius:30px;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,.08), inset 0 0 0 1px rgba(62,73,66,.12);overflow:hidden;position:relative}
    .eco-side-hero::before{content:'';position:absolute;inset:auto -16% -30% auto;width:220px;height:220px;background:radial-gradient(circle, rgba(111,219,168,.18), transparent 68%)}
    .eco-side-hero h3{position:relative;margin:0 0 10px;font-family:'Noto Serif',serif;font-size:30px;line-height:1.08}
    .eco-side-hero p{position:relative;margin:0;color:#bdcac0;font-size:14px;line-height:1.7}
    .eco-side-metrics{margin-top:20px;position:relative;display:grid;gap:12px}
    .eco-side-metric{background:#1a1c19;border-radius:18px;padding:16px}
    .eco-side-metric label{display:block;font-size:10px;letter-spacing:.18em;text-transform:uppercase;color:#87948b;margin-bottom:8px}
    .eco-side-metric strong{font-size:26px;line-height:1;color:#e3e3de}
    .eco-side-metric p{margin:8px 0 0;font-size:13px;color:#bdcac0}
    .eco-side-preview{background:rgba(51,53,50,.42);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border-radius:30px;padding:22px;box-shadow:0 20px 40px rgba(0,0,0,.08), inset 0 0 0 1px rgba(62,73,66,.12)}
    .eco-side-preview h3{margin:0 0 14px;font-size:22px;font-family:'Noto Serif',serif}
    .eco-side-preview img{width:100%;aspect-ratio:1/1;border-radius:24px;object-fit:cover;display:block;margin-bottom:16px;opacity:.92}
    .eco-side-preview ul{margin:0;padding:0;list-style:none;display:grid;gap:10px}
    .eco-side-preview li{display:flex;justify-content:space-between;gap:12px;font-size:13px;color:#bdcac0}
    .eco-side-preview li strong{color:#e3e3de}
    .eco-admin-list{display:grid;gap:12px}
    .eco-admin-item{background:#1a1c19;border-radius:20px;padding:16px;display:grid;gap:12px;box-shadow:inset 0 0 0 1px rgba(62,73,66,.12)}
    .eco-admin-top{display:flex;gap:12px;align-items:flex-start}
    .eco-admin-top img{width:56px;height:56px;border-radius:14px;object-fit:cover;flex:0 0 auto}
    .eco-admin-title{display:grid;gap:6px;min-width:0;flex:1}
    .eco-admin-title strong{font-size:15px;color:#e3e3de}
    .eco-admin-meta{display:flex;flex-wrap:wrap;gap:8px}
    .eco-admin-chip{display:inline-flex;align-items:center;justify-content:center;padding:8px 10px;border-radius:999px;background:#292b27;color:#bdcac0;font-size:11px;font-weight:700;letter-spacing:.04em}
    .eco-admin-chip.is-status-draft{background:rgba(255,225,109,.14);color:#ffe16d}
    .eco-admin-chip.is-status-testing{background:rgba(255,182,140,.14);color:#ffb68c}
    .eco-admin-chip.is-status-active{background:rgba(111,219,168,.16);color:#6FDBA8}
    .eco-admin-chip.is-status-public{background:rgba(139,248,195,.18);color:#8bf8c3}
    .eco-admin-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
    .eco-admin-cell{background:#292b27;border-radius:14px;padding:10px 12px}
    .eco-admin-cell label{display:block;font-size:10px;letter-spacing:.18em;text-transform:uppercase;color:#87948b;margin-bottom:6px}
    .eco-admin-cell strong{font-size:13px;color:#e3e3de}
    .eco-resizable-display{display:block;width:100%;min-height:44px;max-height:260px;overflow:auto;resize:vertical;padding:10px 12px;border-radius:12px;background:#1f211e;color:#e3e3de;line-height:1.55;white-space:pre-wrap;word-break:break-word}
    .eco-resizable-display.is-compact{min-height:34px;padding:8px 10px}
    .eco-resizable-display:focus{outline:1px solid rgba(111,219,168,.4)}
    .eco-admin-actions{display:flex;justify-content:flex-end;gap:8px;flex-wrap:wrap}
    .eco-admin-actions .eco-btn{padding:10px 14px;font-size:13px;border-radius:14px}
    .eco-side-note{background:rgba(51,53,50,.42);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border-radius:28px;padding:20px;box-shadow:0 20px 40px rgba(0,0,0,.08), inset 0 0 0 1px rgba(62,73,66,.12)}
    .eco-side-note h4{margin:0 0 12px;font-size:18px;color:#ffe16d}
    .eco-side-note ul{margin:0;padding-left:18px;color:#bdcac0;font-size:14px;line-height:1.7}
    @media (max-width: 1180px){
      .eco-main-grid{grid-template-columns:1fr}
      .eco-side-stack{position:relative;top:auto}
      .eco-finance-grid,.eco-tray-card,.eco-material-grid,.eco-field-grid--three,.eco-ai-row{grid-template-columns:1fr}
    }
    @media (max-width: 900px){
      .eco-plant-onboarding-page{padding-bottom:calc(104px + env(safe-area-inset-bottom,0px))}
      .eco-plant-sidebar{display:block;position:fixed;left:12px;right:12px;bottom:calc(16px + env(safe-area-inset-bottom,0px));top:auto;z-index:65;width:auto}
      .eco-portal-nav-card{margin-bottom:0;padding:11px 12px calc(11px + env(safe-area-inset-bottom,0px));border-radius:26px;background:rgba(7,33,24,.88);backdrop-filter:blur(26px);-webkit-backdrop-filter:blur(26px);box-shadow:0 20px 44px rgba(0,0,0,.30),inset 0 1px 0 rgba(255,255,255,.06)}
      .eco-portal-nav-head{display:none}
      .eco-portal-nav-list{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px}
      .eco-portal-nav-list a{flex-direction:column;justify-content:center;text-align:center;padding:10px 8px;border-radius:18px;font-size:11px;line-height:1.15;gap:5px;font-weight:700;color:rgba(227,227,222,.74)}
      .eco-portal-nav-list a.is-desktop-only,.eco-side-link-label{display:none}
      .eco-side-link-short{display:block}
      .eco-side-link-icon{font-size:20px}
      .eco-portal-nav-list a.active{border-radius:18px;background:linear-gradient(180deg,rgba(111,219,168,.24),rgba(49,163,117,.92));color:#f7fff9;font-weight:800;box-shadow:inset 0 1px 0 rgba(255,255,255,.16),0 10px 22px rgba(49,163,117,.22)}
      .eco-portal-nav-list a:not(.active):hover{transform:none}
      .eco-plant-main{margin-left:0;padding:24px 16px 14px}
      .eco-plant-topbar{padding:0 16px;height:auto;min-height:72px;flex-wrap:wrap;gap:14px;padding-top:14px;padding-bottom:14px}
      .eco-plant-brand{width:100%}
      .eco-plant-actions{width:100%;justify-content:space-between;flex-wrap:wrap}
      .eco-progress-bar{min-width:160px}
      .eco-field-grid,.eco-material-grid,.eco-finance-grid,.eco-tray-card,.eco-field-grid--three,.eco-timeline-grid,.eco-ai-row{grid-template-columns:1fr}
      .eco-section-header h2{font-size:28px}
    }
  </style>
  <div class="eco-plant-onboarding-page">
    <div id="aitr-toast" class="aitr-toast" aria-live="polite"></div>
    <div class="eco-plant-layout">
      <aside class="eco-plant-sidebar">
        <div class="eco-portal-nav-card">
          <div class="eco-portal-nav-head">
            <div class="eco-portal-nav-badge">🌿</div>
            <div><h3>Ai trồng cây</h3><p>Portal navigation</p></div>
          </div>
          <nav class="eco-portal-nav-list">
            <?php foreach ($eco_nav_items as $nav_item) : ?>
              <a class="<?php echo (($nav_item['key'] ?? '') === 'kho-nong-cu') ? 'active' : ''; ?><?php echo (($nav_item['key'] ?? '') === 'gioi-thieu') ? ' is-desktop-only' : ''; ?>" href="<?php echo esc_url((string) ($nav_item['url'] ?? '#')); ?>">
                <span class="eco-side-link-icon" aria-hidden="true"><?php echo esc_html((string) ($nav_item['icon'] ?? '🍃')); ?></span>
                <span class="eco-side-link-label"><?php echo esc_html((string) ($nav_item['label'] ?? '')); ?></span>
                <span class="eco-side-link-short"><?php echo esc_html((string) ($nav_item['short_label'] ?? ($nav_item['label'] ?? ''))); ?></span>
              </a>
            <?php endforeach; ?>
          </nav>
        </div>
      </aside>

      <main class="eco-plant-main">
        <div style="display:flex;gap:12px;justify-content:flex-end;flex-wrap:wrap;margin-bottom:18px">
          <a class="eco-btn eco-btn-secondary" href="<?php echo esc_url(home_url('/portal/onboarding-cay-moi/')); ?>">+ Hồ sơ cây mới</a>
          <button class="eco-btn eco-btn-primary" type="submit" form="aitr-plant-form">Lưu hồ sơ</button>
        </div>
        <?php if ($saved_state !== '') : ?>
        <div style="margin-bottom:18px">
          <?php if ($saved_state === '1') : ?><div style="background:rgba(51,53,50,.42);border-radius:18px;padding:14px 16px;color:#dff7ea;box-shadow:inset 0 0 0 1px rgba(111,219,168,.18)">Đã lưu cây mới vào DB hiện có.</div><?php endif; ?>
          <?php if ($saved_state === 'updated') : ?><div style="background:rgba(51,53,50,.42);border-radius:18px;padding:14px 16px;color:#dff7ea;box-shadow:inset 0 0 0 1px rgba(111,219,168,.18)">Đã cập nhật cây thành công.</div><?php endif; ?>
          <?php if ($saved_state === 'deleted') : ?><div style="background:rgba(51,53,50,.42);border-radius:18px;padding:14px 16px;color:#dff7ea;box-shadow:inset 0 0 0 1px rgba(111,219,168,.18)">Đã xóa cây khỏi DB.</div><?php endif; ?>
          <?php if ($saved_state === 'duplicated') : ?><div style="background:rgba(51,53,50,.42);border-radius:18px;padding:14px 16px;color:#dff7ea;box-shadow:inset 0 0 0 1px rgba(111,219,168,.18)">Đã nhân bản cây thành công.</div><?php endif; ?>
          <?php if ($saved_state === 'missing-name') : ?><div style="background:rgba(51,53,50,.42);border-radius:18px;padding:14px 16px;color:#ffdad6;box-shadow:inset 0 0 0 1px rgba(255,180,171,.18)">Chưa lưu được vì còn thiếu tên cây.</div><?php endif; ?>
        </div>
        <?php endif; ?>
        <section class="eco-side-preview" style="margin-bottom:18px">
          <h3>Tìm kiếm cây đã có</h3>
          <form method="get" action="<?php echo esc_url(home_url('/portal/onboarding-cay-moi/')); ?>">
            <div class="eco-field" style="margin-bottom:12px"><label>Từ khóa tìm kiếm</label><input type="text" name="q" value="<?php echo esc_attr($search_term); ?>" placeholder="Tìm theo tên, slug, nhóm cây..."></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
              <button class="eco-btn eco-btn-secondary" type="submit" style="width:100%;display:flex;justify-content:center;align-items:center">Tìm kiếm</button>
              <a class="eco-btn eco-btn-secondary" href="<?php echo esc_url(home_url('/portal/onboarding-cay-moi/')); ?>" style="width:100%;display:flex;justify-content:center;align-items:center">Làm mới</a>
            </div>
          </form>
        </section>
        <?php if ($search_term !== '') : ?>
        <section class="eco-side-preview" style="margin-bottom:18px">
          <h3>Kết quả tìm kiếm</h3>
          <?php if ($plants !== []) : ?>
            <div class="eco-admin-list">
              <?php foreach ($plants as $plant) : ?>
                <?php $pstatus = (string) ($plant['status'] ?? 'draft'); $pstatus_label = $plant_status_options[$pstatus] ?? $pstatus; ?>
                <article class="eco-admin-item">
                  <div class="eco-admin-top">
                    <img src="<?php echo esc_url((string) (($plant['cover_image_url'] ?? '') !== '' ? $plant['cover_image_url'] : 'https://lh3.googleusercontent.com/aida-public/AB6AXuBOHh_lMnbSpOfZ4EiqEYmNxf9xEG2sD_VU3Nlpijh-XNOgu2R_IU6GUQLFw0IbCzGqff_Hpy-ifNYVYEmNHbdpdOZrgCMY0oL8du7jNKMnVCHoUFw')); ?>" alt="Thumbnail cây">
                    <div class="eco-admin-title">
                      <strong><?php echo esc_html((string) ($plant['public_name'] ?? '')); ?></strong>
                      <div class="eco-admin-meta">
                        <span class="eco-admin-chip"><?php echo esc_html((string) (($plant['category'] ?? '') !== '' ? $plant['category'] : 'Cây')); ?></span>
                        <span class="eco-admin-chip <?php echo esc_attr('is-status-' . $pstatus); ?>"><?php echo esc_html($pstatus_label); ?></span>
                      </div>
                    </div>
                  </div>
                  <div class="eco-admin-grid">
                    <div class="eco-admin-cell"><label>Slug</label><div class="eco-resizable-display is-compact" tabindex="0"><?php echo esc_html((string) (($plant['slug'] ?? '') !== '' ? $plant['slug'] : '—')); ?></div></div>
                    <div class="eco-admin-cell"><label>Tên kỹ thuật</label><div class="eco-resizable-display" tabindex="0"><?php echo esc_html((string) (($plant['internal_name'] ?? '') !== '' ? $plant['internal_name'] : '—')); ?></div></div>
                    <div class="eco-admin-cell"><label>Ngày cập nhật</label><div class="eco-resizable-display is-compact" tabindex="0"><?php echo esc_html(isset($plant['updated_at']) ? date_i18n('d/m/Y H:i', strtotime((string) $plant['updated_at'])) : '—'); ?></div></div>
                  </div>
                  <div class="eco-admin-actions">
                    <a class="eco-btn eco-btn-secondary" href="<?php echo esc_url(add_query_arg(['edit' => (int) $plant['id'], 'q' => $search_term], home_url('/portal/onboarding-cay-moi/'))); ?>">Sửa</a>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline-flex;margin:0">
                      <input type="hidden" name="action" value="aitrongcay_plant_duplicate">
                      <input type="hidden" name="plant_id" value="<?php echo esc_attr((string) $plant['id']); ?>">
                      <?php wp_nonce_field('aitrongcay_plant_duplicate', 'aitrongcay_plant_duplicate_nonce'); ?>
                      <button class="eco-btn eco-btn-secondary" type="submit">Nhân bản</button>
                    </form>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline-flex;margin:0" onsubmit="return confirm('Xóa cây này khỏi DB?');">
                      <input type="hidden" name="action" value="aitrongcay_plant_delete">
                      <input type="hidden" name="plant_id" value="<?php echo esc_attr((string) $plant['id']); ?>">
                      <?php wp_nonce_field('aitrongcay_plant_delete', 'aitrongcay_plant_delete_nonce'); ?>
                      <button class="eco-btn eco-btn-secondary" type="submit">Xóa</button>
                    </form>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
          <?php else : ?>
            <p style="margin:0;color:#bdcac0">Không tìm thấy cây nào phù hợp.</p>
          <?php endif; ?>
        </section>
        <?php endif; ?>
        <div class="eco-main-grid">
          <div class="eco-main-column">
            <section id="ho-so-cay" class="eco-glass-card">
              <div class="eco-section-header">
                <div>
                  <h2><?php echo $editing_plant ? 'Chỉnh sửa hồ sơ cây' : 'Hồ sơ cây'; ?></h2>
                  <p>Tạo hồ sơ gốc cho cây để dùng thống nhất cho vận hành, SOP, hạch toán và nội dung public.</p>
                </div>
              </div>
              <form id="aitr-plant-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
                <input type="hidden" name="action" value="aitrongcay_plant_save">
                <input type="hidden" name="plant_id" value="<?php echo esc_attr((string) ($editing_plant['id'] ?? 0)); ?>">
                <?php wp_nonce_field('aitrongcay_plant_save', 'aitrongcay_plant_nonce'); ?>
                <input type="hidden" id="aitr-growth-stage-payload" name="growth_stage_payload" value="">
                <div id="aitr-selected-supplies-hidden" hidden></div>
              <div style="display:grid;grid-template-columns:minmax(0,1.45fr) minmax(280px,.55fr);gap:22px;align-items:start">
                <div>
                  <div class="eco-field-grid">
                    <div class="eco-field"><label>Mã cây</label><input type="text" name="plant_code" value="<?php echo esc_attr((string) ($editing_plant['plant_code'] ?? '')); ?>" placeholder="Ví dụ: XA-LACH-001"></div>
                    <div class="eco-field"><label>Tên public</label><input type="text" name="public_name" value="<?php echo esc_attr((string) ($editing_plant['public_name'] ?? '')); ?>" placeholder="Ví dụ: Rau mầm bông cải"></div>
                    <div class="eco-field"><label>Tên kỹ thuật nội bộ</label><input type="text" name="internal_name" value="<?php echo esc_attr((string) ($editing_plant['internal_name'] ?? '')); ?>" placeholder="broccoli_microgreen"></div>
                    <div class="eco-field"><label>Tên khoa học</label><input type="text" name="scientific_name" value="<?php echo esc_attr((string) ($editing_plant['scientific_name'] ?? '')); ?>" placeholder="Brassica oleracea var. italica"></div>
                    <div class="eco-field"><label>Tên giống</label><input type="text" name="variety_name" value="<?php echo esc_attr((string) ($editing_plant['variety_name'] ?? '')); ?>" placeholder="Ví dụ: Broccoli xanh"></div>
                    <div class="eco-field"><label>Số ngày 1 chu kỳ</label><input type="number" min="0" name="default_cycle_days" value="<?php echo esc_attr((string) ($editing_plant['default_cycle_days'] ?? '')); ?>" placeholder="10"></div>
                    <div class="eco-field"><label>Số ngày nảy mầm</label><input type="number" min="0" name="germination_days" value="<?php echo esc_attr((string) ($editing_plant['germination_days'] ?? '')); ?>" placeholder="2"></div>
                    <div class="eco-field"><label>Bắt đầu thu hoạch từ ngày</label><input type="number" min="0" name="harvest_start_day" value="<?php echo esc_attr((string) ($editing_plant['harvest_start_day'] ?? '')); ?>" placeholder="7"></div>
                    <div class="eco-field"><label>Chiều cao khi cây trưởng thành</label><input type="number" min="0" name="mature_height_cm" value="<?php echo esc_attr((string) ($editing_plant['mature_height_cm'] ?? ($editing_plant['harvest_end_day'] ?? ''))); ?>" placeholder="30"></div>
                    <div class="eco-field"><label>Độ khó</label><input type="text" name="difficulty_level" value="<?php echo esc_attr((string) ($editing_plant['difficulty_level'] ?? '')); ?>" placeholder="Dễ / Trung bình / Khó"></div>
                    <div class="eco-field" style="grid-column:1/-1">
                      <label>Nhóm cây</label>
                      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:10px;margin-top:8px">
                        <?php foreach ($plant_category_options as $category_option) : ?>
                          <label style="display:flex;align-items:center;gap:10px;padding:12px 14px;border-radius:14px;background:#292b27;color:#e3e3de;cursor:pointer">
                            <input type="checkbox" name="categories[]" value="<?php echo esc_attr($category_option); ?>" <?php checked(in_array($category_option, $selected_categories, true)); ?> style="width:18px;height:18px;min-height:auto">
                            <span><?php echo esc_html($category_option); ?></span>
                          </label>
                        <?php endforeach; ?>
                      </div>
                    </div>
                  </div>
                  <div class="eco-field" style="margin-top:18px"><label>Mô tả ngắn</label><textarea name="short_description" placeholder="Mô tả 1–2 câu để nhận diện nhanh cây trong hệ thống nội bộ."><?php echo esc_textarea((string) ($editing_plant['short_description'] ?? '')); ?></textarea></div>
                  <div class="eco-field" style="margin-top:18px"><label>Thành phần dinh dưỡng có trong 100 gram sản phẩm từ cây</label><textarea name="nutrition_components" placeholder="Nhập thành phần dinh dưỡng có trong 100 gram sản phẩm thu được từ cây, ví dụ năng lượng, vitamin, khoáng chất, chất xơ... "><?php echo esc_textarea((string) ($editing_plant['nutrition_components'] ?? '')); ?></textarea></div>
                  <div class="eco-field" style="margin-top:18px"><label>Thành phần dinh dưỡng đặc biệt</label><textarea name="special_nutrition_components" placeholder="Nhập các thành phần dinh dưỡng nổi bật hoặc đặc biệt của cây nếu có."><?php echo esc_textarea((string) ($editing_plant['special_nutrition_components'] ?? '')); ?></textarea></div>
                  <div style="margin-top:18px">
                    <label style="display:block;font-size:11px;font-weight:800;letter-spacing:.22em;text-transform:uppercase;color:#ffb68c;padding-left:4px;margin-bottom:10px">Trạng thái</label>
                    <select name="plant_status" style="width:100%;background:#292b27;border:none;border-radius:18px;padding:16px 18px;color:#e3e3de;outline:none;font:inherit;box-shadow:none;min-height:56px"><?php foreach ($plant_status_options as $status_key => $status_label) : ?><option value="<?php echo esc_attr($status_key); ?>" <?php selected((string) ($editing_plant['status'] ?? 'draft'), $status_key); ?>><?php echo esc_html($status_label); ?></option><?php endforeach; ?></select>
                  </div>
                </div>
                <aside class="eco-mini-card" style="padding:18px;gap:14px">
                  <div><h3 style="margin:0;font-size:18px">Ảnh đại diện</h3><p style="margin:6px 0 0;color:#87948b;font-size:13px">Dùng cho admin và preview website.</p></div>
                  <img id="plant-preview-image" src="<?php echo esc_url((string) (($editing_plant['cover_image_url'] ?? '') !== '' ? $editing_plant['cover_image_url'] : 'https://lh3.googleusercontent.com/aida-public/AB6AXuBOHh_lMnbSpOfZ4EiqEYmNxf9xEG2sD_VU3Nlpijh-XNOgu2R_IU6GUQLFw0IbCzGqff_Hpy-ifNYVYEmNHbdpdOZrgCMY0oL8du7jNKMVCHoUFw')); ?>" alt="Ảnh đại diện sản phẩm cây trồng trong giao diện admin aitrongcay.com" style="width:100%;aspect-ratio:1/1;object-fit:cover;border-radius:22px;display:block">
                  <input type="hidden" id="plant_existing_image_id" name="plant_existing_image_id" value="<?php echo esc_attr((string) ($editing_plant['cover_image_id'] ?? 0)); ?>">
                  <input type="hidden" id="cover_image_url" name="cover_image_url" value="<?php echo esc_attr((string) ($editing_plant['cover_image_url'] ?? '')); ?>">
                  <div class="eco-field"><label>Tải ảnh đại diện</label><input id="plant_image_file" type="file" name="plant_image_file" accept="image/*"></div>
                </aside>
              </div>
            </section>

            <section id="giai-doan-sinh-truong" class="eco-glass-card">
              <div class="eco-section-header">
                <div>
                  <h2>Giai đoạn sinh trưởng</h2>
                  <p>Mỗi dòng là một giai đoạn. Có thể thêm bớt linh hoạt theo từng loại cây.</p>
                </div>
                <button class="eco-btn eco-btn-primary" type="button" id="aitr-add-growth-stage">+ Thêm giai đoạn</button>
              </div>
              <div id="aitr-growth-stage-list" style="display:grid;gap:12px">
                <?php foreach ($growth_stage_names as $stage_index => $stage_name) : ?>
                  <div class="aitr-growth-stage-row" style="display:grid;grid-template-columns:84px minmax(0,1fr) auto;gap:12px;align-items:center;background:#1a1c19;border-radius:18px;padding:14px 16px;box-shadow:inset 0 0 0 1px rgba(62,73,66,.12)">
                    <div class="aitr-growth-stage-number" style="font-size:13px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#6FDBA8">GĐ <?php echo esc_html((string) ($stage_index + 1)); ?></div>
                    <input type="text" name="growth_stage_name[]" value="<?php echo esc_attr($stage_name); ?>" placeholder="Tên giai đoạn" style="width:100%;background:#292b27;border:none;border-radius:14px;padding:14px 16px;color:#e3e3de;outline:none;font:inherit;box-shadow:none;min-height:48px">
                    <button class="eco-btn eco-btn-secondary aitr-remove-growth-stage" type="button" style="padding:10px 14px;border-radius:12px">Bỏ</button>
                  </div>
                <?php endforeach; ?>
              </div>
            </section>

            <section id="moi-truong" class="eco-glass-card">
              <input type="hidden" name="env_stage_code" value="general">
              <input type="hidden" name="env_day_from" value="0">
              <input type="hidden" name="env_day_to" value="0">
              <input type="hidden" name="env_temp_air_min" value="0">
              <input type="hidden" name="env_temp_air_target" value="0">
              <input type="hidden" name="env_temp_air_max" value="0">
              <input type="hidden" name="env_humidity_min" value="0">
              <input type="hidden" name="env_humidity_target" value="0">
              <input type="hidden" name="env_humidity_max" value="0">
              <input type="hidden" name="env_ec_min" value="0">
              <input type="hidden" name="env_ec_target" value="0">
              <input type="hidden" name="env_ec_max" value="0">
              <input type="hidden" name="env_ph_min" value="0">
              <input type="hidden" name="env_ph_target" value="0">
              <input type="hidden" name="env_ph_max" value="0">
              <input type="hidden" name="env_dli_min" value="0">
              <input type="hidden" name="env_dli_target" value="0">
              <input type="hidden" name="env_dli_max" value="0">
              <div class="eco-field">
                <label>Yêu cầu về môi trường</label>
                <textarea name="env_source_note" rows="10" placeholder="Nhập mô tả tổng quát về môi trường mục tiêu ở đây"><?php echo esc_textarea($environment_text); ?></textarea>
              </div>
              <input type="hidden" name="env_airflow_note" value="">
            </section>

            <section id="dinh-duong" class="eco-glass-card">
              <input type="hidden" name="nutrition_stage_code" value="general">
              <input type="hidden" name="nutrition_day_from" value="0">
              <input type="hidden" name="nutrition_day_to" value="0">
              <input type="hidden" name="nutrition_ec_target" value="0">
              <input type="hidden" name="nutrition_ph_target" value="0">
              <input type="hidden" name="nutrition_water_ml_per_tray_per_day" value="0">
              <input type="hidden" name="nutrition_stock_a_ml" value="0">
              <input type="hidden" name="nutrition_stock_b_ml" value="0">
              <div class="eco-field">
                <label>yêu cầu về dung dịch dinh dưỡng</label>
                <textarea name="nutrition_mixing_note" rows="10" placeholder="Nhập mô tả tổng quát về dinh dưỡng cơ bản ở đây"><?php echo esc_textarea($nutrition_text); ?></textarea>
              </div>
              <input type="hidden" name="nutrition_warning_note" value="">
            </section>

            <section id="vat-tu" class="eco-glass-card">
              <div class="eco-section-header">
                <div>
                  <h2>Vật tư & thiết bị hỗ trợ</h2>
                  <p>Chọn vật tư và thiết bị đã có trong hệ thống để gắn vào cây này.</p>
                </div>
                <a class="eco-btn eco-btn-primary" href="<?php echo esc_url(home_url('/portal/vat-tu-thiet-bi-moi/')); ?>">+ Tạo vật tư mới</a>
              </div>
              <div style="display:grid;grid-template-columns:minmax(0,1fr);gap:18px;margin-bottom:18px;align-items:start">
                <div style="background:#1a1c19;border-radius:22px;padding:18px;box-shadow:inset 0 0 0 1px rgba(62,73,66,.12)">
                  <div class="aitr-supply-search-layout">
                    <div class="eco-field" style="margin:0">
                      <input id="aitr-supply-search" type="text" name="supply_q" value="<?php echo esc_attr($supply_search_term); ?>" placeholder="Tìm vật tư / thiết bị...">
                    </div>
                  </div>
                  <div class="aitr-supply-results" id="aitr-supply-results">
                    <div id="aitr-supply-loading" style="display:none;padding:12px 14px;border-radius:14px;background:#292b27;color:#bdcac0">Đang tìm vật tư...</div>
                    <div id="aitr-supply-no-results" style="display:none;padding:12px 14px;border-radius:14px;background:#292b27;color:#bdcac0">Không tìm thấy kết quả phù hợp.</div>
                    <div id="aitr-supply-idle-hint" style="padding:12px 14px;border-radius:14px;background:#292b27;color:#bdcac0">Gõ từ khóa để tải vật tư hoặc thiết bị phù hợp.</div>
                  </div>
                </div>
                <div style="background:#1a1c19;border-radius:22px;padding:18px;box-shadow:inset 0 0 0 1px rgba(62,73,66,.12)">
                  <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:12px"><strong style="font-size:16px;color:#e3e3de">Vật tư đã gắn</strong><span style="font-size:12px;color:#87948b">Cập nhật tức thời</span></div>
                  <?php if ($editing_plant && $linked_supplies !== []) : ?>
                    <div id="aitr-linked-supplies-list" style="display:grid;gap:10px">
                      <?php foreach ($linked_supplies as $linked_supply) : ?>
                        <div style="display:grid;grid-template-columns:minmax(0,1fr) auto;gap:12px;padding:12px 14px;border-radius:16px;background:#292b27;color:#bdcac0">
                          <div>
                            <div style="font-size:14px;font-weight:800;color:#e3e3de"><?php echo esc_html((string) ($linked_supply['name'] ?? '')); ?></div>
                            <div style="margin-top:4px;font-size:12px;color:#87948b"><?php echo esc_html((string) ($linked_supply['type'] ?? 'Vật tư')); ?></div>
                          </div>
                          <div style="display:flex;align-items:center;gap:10px">
                            <div style="text-align:right">
                              <div style="font-size:12px;color:#e3e3de"><?php echo esc_html((string) ($supply_role_options[(string) ($linked_supply['usage_role'] ?? '')] ?? '')); ?></div>
                              <div style="margin-top:4px;font-size:12px;color:#87948b"><?php echo esc_html((string) (($linked_supply['quantity_per_tray'] ?? '') !== '' ? $linked_supply['quantity_per_tray'] : 'Chưa nhập định mức')); ?></div>
                            </div>
                            <button class="eco-btn eco-btn-secondary aitr-unlink-btn" data-supply-id="<?php echo esc_attr((string) ($linked_supply['supply_id'] ?? '')); ?>" type="button" style="padding:8px 12px;border-radius:12px">Bỏ</button>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php elseif ($editing_plant) : ?>
                    <div id="aitr-linked-supplies-list" style="display:grid;gap:10px"></div>
                    <div id="aitr-linked-supplies-empty" style="color:#bdcac0">Cây này chưa có vật tư nào được gắn.</div>
                  <?php else : ?>
                    <div id="aitr-linked-supplies-list" style="display:grid;gap:10px"></div>
                    <div id="aitr-linked-supplies-empty" style="color:#bdcac0">Lưu cây trước, sau đó quay lại để xem danh sách vật tư đã gắn rõ ràng ở dưới.</div>
                  <?php endif; ?>
                </div>
              </div>
            </section>

            <section id="sop-theo-ngay" class="eco-glass-card">
              <div class="eco-field">
                <label>Hướng dẫn trồng và chăm sóc cây dành cho robot.</label>
                <textarea name="sop_single_text" rows="12" placeholder="Nhập SOP tổng quát của cây ở đây"><?php echo esc_textarea($sop_single_text); ?></textarea>
              </div>
            </section>

            <section id="checklist-ngay" class="eco-glass-card">
              <div class="eco-section-header">
                <div>
                  <h2>Checklist chăm sóc hằng ngày</h2>
                  <p>Mỗi dòng là một đầu việc. Hệ thống sẽ lưu từng dòng thành từng checklist item riêng.</p>
                </div>
              </div>
              <div class="eco-field">
                <label>Checklist daily</label>
                <textarea name="checklist_daily_text" rows="10" placeholder="Ví dụ:\nKiểm tra độ ẩm mặt khoang\nKiểm tra màu lá và độ đều cây\nKiểm tra mùi lạ hoặc dấu hiệu mốc\nĐối chiếu EC/pH nếu đang chạy dinh dưỡng"><?php echo esc_textarea($checklist_daily_text); ?></textarea>
              </div>
            </section>

            <section id="sau-benh" class="eco-glass-card">
              <div class="eco-section-header">
                <div>
                  <h2>Sâu bệnh và bất thường thường gặp</h2>
                  <p>Mỗi khối cách nhau một dòng trống. Dòng đầu là tên vấn đề, các dòng sau là mô tả ngắn.</p>
                </div>
              </div>
              <div class="eco-field">
                <label>Danh sách vấn đề</label>
                <textarea name="health_issues_text" rows="12" placeholder="Ví dụ:\nLá vàng nhạt đầu khoang\nHay gặp khi thiếu sáng hoặc dinh dưỡng loãng.\n\nMốc trắng mặt giá thể\nCần kiểm tra ẩm cao kéo dài và thông gió kém."><?php echo esc_textarea($health_issues_text); ?></textarea>
              </div>
            </section>

            <section id="chi-so-ai" class="eco-glass-card">
              <div class="eco-field">
                <label>Chỉ dẫn phân tích hình ảnh cho AI agents</label>
                <textarea name="ai_agent_guidance" rows="12" placeholder="Ví dụ: AI cần quan sát độ đều của cây, màu lá, thân, dấu hiệu mốc, thiếu sáng, úng nước... Sau khi phân tích phải giải thích ngắn gọn, dễ hiểu và đưa ra lời khuyên cụ thể cho người dùng theo mức độ ưu tiên."><?php echo esc_textarea((string) ($plant_public_content['ai_agent_guidance'] ?? '')); ?></textarea>
              </div>
            </section>

            <section id="phase-3" class="eco-glass-card">
              <div class="eco-section-header">
                <div>
                  <h2>Phase 3</h2>
                </div>
              </div>
              <div class="eco-field">
                <label>Alert rules / ngưỡng cảnh báo</label>
                <textarea name="phase3_alert_rules_text" rows="10" placeholder="Nhập rule cảnh báo, ngưỡng, mức độ, action ở đây"><?php echo esc_textarea($phase3_alert_rules_text); ?></textarea>
              </div>
            </section>

            <section id="public-ready" class="eco-glass-card">
              <div class="eco-section-header">
                <div>
                  <h2>Nội dung viết bài</h2>
                  <p>Chỉ cần một ô nội dung để nhập bản nháp hoặc nội dung viết bài cho cây này.</p>
                </div>
              </div>
              <div class="eco-field">
                <label>Nội dung viết bài</label>
                <textarea name="public_body" rows="14" placeholder="Nhập nội dung bài viết ở đây"><?php echo esc_textarea((string) ($plant_public_content['public_body'] ?? '')); ?></textarea>
              </div>
            </section>

            <div style="display:flex;justify-content:flex-end;gap:12px;margin-top:-4px">
              <button class="eco-btn eco-btn-secondary" type="reset">Nhập lại</button>
              <button class="eco-btn eco-btn-primary" type="submit"><?php echo $editing_plant ? 'Cập nhật cây' : 'Lưu cây vào DB'; ?></button>
            </div>
          </form>

          </div>

          <aside class="eco-side-stack"></aside>
        </div>
      </main>
    </div>
  </div>
  <script>
    (() => {
      const plantId = <?php echo json_encode((int) ($edit_id ?: 0)); ?>;
      const ajaxUrl = <?php echo json_encode(admin_url('admin-ajax.php')); ?>;
      const nonce = <?php echo json_encode($unlink_supply_nonce); ?>;
      const uploadNonce = <?php echo json_encode($upload_nonce); ?>;
      const selectedSupplyIds = new Set(<?php echo wp_json_encode(array_map('intval', $selected_supply_ids)); ?>);
      const linkedSupplyMap = <?php echo wp_json_encode(array_map(static function ($row) {
        return [
          'supply_id' => (int) ($row['supply_id'] ?? 0),
          'usage_role' => (string) ($row['usage_role'] ?? 'required'),
          'quantity_per_tray' => (string) ($row['quantity_per_tray'] ?? ''),
        ];
      }, $linked_supplies)); ?>;
      const supplyRoleOptions = <?php echo wp_json_encode($supply_role_options); ?>;
      const toastEl = document.getElementById('aitr-toast');
      const showToast = (message, type = 'success') => {
        if (!toastEl) return;
        toastEl.textContent = message;
        toastEl.className = 'aitr-toast show ' + type;
        clearTimeout(showToast._timer);
        showToast._timer = setTimeout(() => {
          toastEl.className = 'aitr-toast ' + type;
        }, 2200);
      };
      const post = async (params) => {
        const body = new URLSearchParams(params);
        const res = await fetch(ajaxUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
          body: body.toString()
        });
        return res.json();
      };
      const plantFileInput = document.getElementById('plant_image_file');
      const plantPreview = document.getElementById('plant-preview-image');
      const plantImageId = document.getElementById('plant_existing_image_id');
      const plantImageUrl = document.getElementById('cover_image_url');
      const plantForm = document.getElementById('aitr-plant-form');
      const growthStagePayload = document.getElementById('aitr-growth-stage-payload');
      const supplySearchInput = document.getElementById('aitr-supply-search');
      const supplyNoResults = document.getElementById('aitr-supply-no-results');
      const supplyIdleHint = document.getElementById('aitr-supply-idle-hint');
      const supplyResultsWrap = document.getElementById('aitr-supply-results');
      const supplyLoading = document.getElementById('aitr-supply-loading');
      const selectedSuppliesHidden = document.getElementById('aitr-selected-supplies-hidden');
      const normalizeSupplyText = (value) => String(value || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
      const roleOptionsHtml = Object.entries(supplyRoleOptions).map(([key, label]) => `<option value="${key}">${label}</option>`).join('');
      const linkedSupplyLookup = new Map((Array.isArray(linkedSupplyMap) ? linkedSupplyMap : []).map((row) => [String(row.supply_id || ''), row]));
      const syncSelectedSupplyHiddenInputs = () => {
        if (!selectedSuppliesHidden) return;
        selectedSuppliesHidden.innerHTML = '';
        Array.from(selectedSupplyIds).sort((a, b) => a - b).forEach((supplyId) => {
          const key = String(supplyId);
          const linked = linkedSupplyLookup.get(key) || {};
          const roleValue = String(linked.usage_role || 'required');
          const qtyValue = String(linked.quantity_per_tray || '');
          const selectedInput = document.createElement('input');
          selectedInput.type = 'hidden';
          selectedInput.name = 'selected_supply_ids[]';
          selectedInput.value = key;
          selectedSuppliesHidden.appendChild(selectedInput);
          const roleInput = document.createElement('input');
          roleInput.type = 'hidden';
          roleInput.name = 'supply_role[' + key + ']';
          roleInput.value = roleValue;
          selectedSuppliesHidden.appendChild(roleInput);
          const qtyInput = document.createElement('input');
          qtyInput.type = 'hidden';
          qtyInput.name = 'supply_quantity[' + key + ']';
          qtyInput.value = qtyValue;
          selectedSuppliesHidden.appendChild(qtyInput);
        });
      };
      syncSelectedSupplyHiddenInputs();
      let bindSupplyRow = () => {};
      const renderSupplyResults = (items) => {
        if (!supplyResultsWrap) return;
        Array.from(supplyResultsWrap.querySelectorAll('.aitr-supply-row')).forEach((row) => row.remove());
        const list = Array.isArray(items) ? items : [];
        list.forEach((item) => {
          const supplyId = String(item.id || '');
          const linked = linkedSupplyLookup.get(supplyId) || {};
          const checked = selectedSupplyIds.has(Number(supplyId));
          const role = linked.usage_role || 'required';
          const qty = linked.quantity_per_tray || '';
          const article = document.createElement('article');
          article.className = 'aitr-supply-row';
          article.dataset.supplyId = supplyId;
          article.dataset.supplyName = String(item.name || '');
          article.dataset.supplyType = String(item.type || 'Vật tư');
          article.style.cssText = checked ? 'box-shadow:inset 0 0 0 1px rgba(111,219,168,.24);' : '';
          article.innerHTML = '<div style="display:flex;justify-content:center"><input class="aitr-supply-checkbox" type="checkbox" name="selected_supply_ids[]" value="' + supplyId + '" ' + (checked ? 'checked' : '') + ' style="width:18px;height:18px;min-height:auto"></div>' +
            '<div style="min-width:0"><div style="font-size:14px;font-weight:800;color:#e3e3de">' + String(item.name || '') + '</div><div style="margin-top:4px;font-size:12px;color:#87948b">' + String(item.type || 'Vật tư') + (item.spec ? ' • ' + String(item.spec) : '') + '</div></div>' +
            '<div><select class="aitr-supply-role" name="supply_role[' + supplyId + ']" style="width:100%;background:#1f211e;border:none;border-radius:12px;padding:10px 12px;color:#e3e3de;outline:none;font:inherit;box-shadow:none;min-height:40px">' + roleOptionsHtml + '</select></div>' +
            '<div><input class="aitr-supply-quantity" type="text" name="supply_quantity[' + supplyId + ']" value="' + String(qty).replace(/"/g, '&quot;') + '" placeholder="Định mức / khoang" style="width:100%;background:#1f211e;border:none;border-radius:12px;padding:10px 12px;color:#e3e3de;outline:none;font:inherit;box-shadow:none;min-height:40px"></div>';
          supplyResultsWrap.insertBefore(article, supplyLoading || supplyNoResults || supplyIdleHint);
          const select = article.querySelector('.aitr-supply-role');
          if (select) select.value = role;
          bindSupplyRow(article);
        });
      };
      let searchSeq = 0;
      const runSupplySearch = async (rawValue) => {
        const needle = normalizeSupplyText(rawValue);
        if (supplyIdleHint) supplyIdleHint.style.display = needle === '' ? '' : 'none';
        if (supplyNoResults) supplyNoResults.style.display = 'none';
        if (needle === '') {
          renderSupplyResults([]);
          return;
        }
        const seq = ++searchSeq;
        if (supplyLoading) supplyLoading.style.display = '';
        try {
          const json = await post({ action: 'aitrongcay_search_supplies_for_linking', nonce, query: rawValue });
          if (seq !== searchSeq) return;
          if (!json.success) throw new Error((json.data && json.data.message) || 'Không thể tìm vật tư');
          const items = (json.data && json.data.items) || [];
          renderSupplyResults(items);
          if (supplyNoResults) supplyNoResults.style.display = items.length === 0 ? '' : 'none';
        } catch (e) {
          renderSupplyResults([]);
          if (supplyNoResults) {
            supplyNoResults.textContent = e.message || 'Không thể tìm vật tư';
            supplyNoResults.style.display = '';
          }
        } finally {
          if (seq === searchSeq && supplyLoading) supplyLoading.style.display = 'none';
        }
      };
      let supplySearchTimer = null;
      if (supplySearchInput) {
        supplySearchInput.addEventListener('input', () => {
          clearTimeout(supplySearchTimer);
          supplySearchTimer = setTimeout(() => runSupplySearch(supplySearchInput.value), 250);
        });
        supplySearchInput.addEventListener('keydown', (event) => {
          if (event.key === 'Enter') {
            event.preventDefault();
          }
        });
      }
      if (plantFileInput && plantPreview && plantImageId && plantImageUrl) {
        plantFileInput.addEventListener('change', async () => {
          const file = plantFileInput.files && plantFileInput.files[0];
          if (!file) return;
          const form = new FormData();
          form.append('action', 'aitrongcay_upload_media_image');
          form.append('nonce', uploadNonce);
          form.append('field_name', 'plant_image_file');
          form.append('plant_image_file', file);
          try {
            const res = await fetch(ajaxUrl, { method: 'POST', body: form });
            const json = await res.json();
            if (!json.success) throw new Error((json.data && json.data.message) || 'Không thể upload ảnh');
            plantPreview.src = json.data.url;
            plantImageId.value = String(json.data.attachment_id || 0);
            plantImageUrl.value = String(json.data.url || '');
            showToast('Đã upload ảnh đại diện', 'success');
          } catch (e) {
            showToast(e.message || 'Có lỗi khi upload ảnh', 'error');
          }
        });
      }
      const growthStageList = document.getElementById('aitr-growth-stage-list');
      const addGrowthStageBtn = document.getElementById('aitr-add-growth-stage');
      const syncGrowthStageNumbers = () => {
        if (!growthStageList) return;
        const rows = growthStageList.querySelectorAll('.aitr-growth-stage-row');
        rows.forEach((row, index) => {
          const numberEl = row.querySelector('.aitr-growth-stage-number');
          if (numberEl) numberEl.textContent = 'GĐ ' + String(index + 1);
          const removeBtn = row.querySelector('.aitr-remove-growth-stage');
          if (removeBtn) removeBtn.disabled = rows.length <= 1;
        });
      };
      const bindGrowthStageRow = (row) => {
        const removeBtn = row.querySelector('.aitr-remove-growth-stage');
        if (!removeBtn || removeBtn.dataset.bound === '1') return;
        removeBtn.dataset.bound = '1';
        removeBtn.addEventListener('click', () => {
          if (!growthStageList) return;
          if (growthStageList.querySelectorAll('.aitr-growth-stage-row').length <= 1) return;
          row.remove();
          syncGrowthStageNumbers();
        });
      };
      const syncGrowthStagePayload = () => {
        if (!growthStagePayload || !growthStageList) return;
        const values = Array.from(growthStageList.querySelectorAll('input[name="growth_stage_name[]"]'))
          .map((input) => String(input.value || '').trim())
          .filter(Boolean);
        growthStagePayload.value = JSON.stringify(values);
      };
      if (growthStageList) {
        growthStageList.querySelectorAll('.aitr-growth-stage-row').forEach(bindGrowthStageRow);
        syncGrowthStageNumbers();
        syncGrowthStagePayload();
      }
      addGrowthStageBtn && addGrowthStageBtn.addEventListener('click', () => {
        if (!growthStageList) return;
        const row = document.createElement('div');
        row.className = 'aitr-growth-stage-row';
        row.style.cssText = 'display:grid;grid-template-columns:84px minmax(0,1fr) auto;gap:12px;align-items:center;background:#1a1c19;border-radius:18px;padding:14px 16px;box-shadow:inset 0 0 0 1px rgba(62,73,66,.12)';
        row.innerHTML = '<div class="aitr-growth-stage-number" style="font-size:13px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#6FDBA8"></div><input type="text" name="growth_stage_name[]" value="" placeholder="Tên giai đoạn" style="width:100%;background:#292b27;border:none;border-radius:14px;padding:14px 16px;color:#e3e3de;outline:none;font:inherit;box-shadow:none;min-height:48px"><button class="eco-btn eco-btn-secondary aitr-remove-growth-stage" type="button" style="padding:10px 14px;border-radius:12px">Bỏ</button>';
        growthStageList.appendChild(row);
        bindGrowthStageRow(row);
        syncGrowthStageNumbers();
        syncGrowthStagePayload();
        const input = row.querySelector('input[name="growth_stage_name[]"]');
        if (input) {
          input.addEventListener('input', syncGrowthStagePayload);
          input.focus();
        }
      });
      if (growthStageList) {
        growthStageList.addEventListener('input', (event) => {
          const target = event.target;
          if (target && target.matches && target.matches('input[name="growth_stage_name[]"]')) {
            syncGrowthStagePayload();
          }
        });
      }
      if (plantForm) {
        plantForm.addEventListener('submit', () => {
          syncGrowthStagePayload();
          if (plantImageUrl && plantPreview && !plantImageUrl.value) {
            const previewSrc = String(plantPreview.getAttribute('src') || '').trim();
            if (previewSrc && !previewSrc.includes('googleusercontent.com/aida-public')) {
              plantImageUrl.value = previewSrc;
            }
          }
          if (plantImageId && plantImageUrl && !plantImageId.value && plantImageUrl.value) {
            const imageUrl = String(plantImageUrl.value);
            if (/\/uploads\//i.test(imageUrl)) {
              plantImageId.value = plantImageId.value || '0';
            }
          }
        });
      }
      const linkedList = document.getElementById('aitr-linked-supplies-list');
      const linkedEmpty = document.getElementById('aitr-linked-supplies-empty');
      const ensureLinkedRow = (row, roleLabel, quantityText) => {
          if (!linkedList) return;
          const supplyId = row.getAttribute('data-supply-id');
          const name = row.getAttribute('data-supply-name') || 'Vật tư';
          const type = row.getAttribute('data-supply-type') || 'Vật tư';
          let card = linkedList.querySelector('[data-linked-supply-id="' + supplyId + '"]');
          if (!card) {
            card = document.createElement('div');
            card.setAttribute('data-linked-supply-id', supplyId);
            card.style.cssText = 'display:grid;grid-template-columns:minmax(0,1fr) auto;gap:12px;padding:12px 14px;border-radius:16px;background:#292b27;color:#bdcac0';
            card.innerHTML = '<div><div class="aitr-linked-name" style="font-size:14px;font-weight:800;color:#e3e3de"></div><div class="aitr-linked-type" style="margin-top:4px;font-size:12px;color:#87948b"></div></div><div style="display:flex;align-items:center;gap:10px"><div style="text-align:right"><div class="aitr-linked-role" style="font-size:12px;color:#e3e3de"></div><div class="aitr-linked-qty" style="margin-top:4px;font-size:12px;color:#87948b"></div></div><button class="eco-btn eco-btn-secondary aitr-unlink-btn" data-supply-id="' + supplyId + '" type="button" style="padding:8px 12px;border-radius:12px">Bỏ</button></div>';
            linkedList.appendChild(card);
            bindUnlinkButton(card.querySelector('.aitr-unlink-btn'));
          }
          card.querySelector('.aitr-linked-name').textContent = name;
          card.querySelector('.aitr-linked-type').textContent = type;
          card.querySelector('.aitr-linked-role').textContent = roleLabel || 'Bắt buộc';
          card.querySelector('.aitr-linked-qty').textContent = quantityText || 'Chưa nhập định mức';
          if (linkedEmpty) linkedEmpty.style.display = 'none';
        };
      const removeLinkedRow = (supplyId) => {
        if (!linkedList) return;
        const card = linkedList.querySelector('[data-linked-supply-id="' + supplyId + '"]');
        if (card) card.remove();
        if (linkedEmpty && !linkedList.querySelector('[data-linked-supply-id]')) linkedEmpty.style.display = '';
      };
      const debounceMap = new Map();
      bindSupplyRow = (row) => {
          const checkbox = row.querySelector('.aitr-supply-checkbox');
          const role = row.querySelector('.aitr-supply-role');
          const quantity = row.querySelector('.aitr-supply-quantity');
          const supplyId = row.getAttribute('data-supply-id');
          const persistLocalSelection = () => {
            selectedSupplyIds.add(Number(supplyId));
            linkedSupplyLookup.set(String(supplyId), {
              supply_id: Number(supplyId),
              usage_role: role ? role.value : 'required',
              quantity_per_tray: quantity ? quantity.value : ''
            });
            syncSelectedSupplyHiddenInputs();
          };
          const linkNow = async () => {
            if (!checkbox || !checkbox.checked) return;
            persistLocalSelection();
            if (plantId <= 0) {
              showToast('Đã chọn vật tư, khi lưu cây hệ thống sẽ ghi lại', 'success');
              return;
            }
            const json = await post({
              action: 'aitrongcay_link_plant_supply',
              nonce,
              plant_id: String(plantId),
              supply_id: String(supplyId),
              usage_role: role ? role.value : 'required',
              quantity_per_tray: quantity ? quantity.value : ''
            });
            if (!json.success) throw new Error((json.data && json.data.message) || 'Không thể gắn vật tư');
            ensureLinkedRow(row, (json.data && json.data.roleLabel) || (role ? role.options[role.selectedIndex].text : 'Bắt buộc'), quantity ? quantity.value : '');
            showToast('Đã gắn vật tư cho cây', 'success');
          };
          checkbox && checkbox.addEventListener('change', async () => {
            try {
              if (checkbox.checked) {
                await linkNow();
              } else {
                selectedSupplyIds.delete(Number(supplyId));
                linkedSupplyLookup.delete(String(supplyId));
                syncSelectedSupplyHiddenInputs();
                if (plantId > 0) {
                  const json = await post({ action: 'aitrongcay_unlink_plant_supply', nonce, plant_id: String(plantId), supply_id: String(supplyId) });
                  if (!json.success) throw new Error((json.data && json.data.message) || 'Không thể bỏ liên kết');
                  removeLinkedRow(supplyId);
                }
                showToast('Đã bỏ liên kết vật tư', 'success');
              }
            } catch (e) {
              checkbox.checked = !checkbox.checked;
              showToast(e.message || 'Có lỗi xảy ra', 'error');
            }
          });
          role && role.addEventListener('change', async () => {
            if (!checkbox || !checkbox.checked) return;
            try { await linkNow(); } catch (e) { showToast(e.message || 'Có lỗi xảy ra', 'error'); }
          });
          quantity && quantity.addEventListener('input', () => {
            if (!checkbox || !checkbox.checked) return;
            persistLocalSelection();
            if (debounceMap.has(supplyId)) clearTimeout(debounceMap.get(supplyId));
            if (plantId <= 0) return;
            debounceMap.set(supplyId, setTimeout(async () => {
              try { await linkNow(); } catch (e) { showToast(e.message || 'Có lỗi xảy ra', 'error'); }
            }, 500));
          });
        };
      document.querySelectorAll('.aitr-supply-row').forEach(bindSupplyRow);
      if (plantId > 0) {
        const bindUnlinkButton = (btn) => {
          if (!btn || btn.dataset.bound === '1') return;
          btn.dataset.bound = '1';
          btn.addEventListener('click', async () => {
            const supplyId = btn.getAttribute('data-supply-id');
            btn.disabled = true;
            try {
              const json = await post({ action: 'aitrongcay_unlink_plant_supply', nonce, plant_id: String(plantId), supply_id: String(supplyId) });
              if (!json.success) throw new Error((json.data && json.data.message) || 'Không thể bỏ liên kết');
              const cb = document.querySelector('.aitr-supply-checkbox[value="' + supplyId + '"]');
              if (cb) cb.checked = false;
              selectedSupplyIds.delete(Number(supplyId));
              linkedSupplyLookup.delete(String(supplyId));
              removeLinkedRow(supplyId);
              showToast('Đã bỏ liên kết vật tư', 'success');
            } catch (e) {
              btn.disabled = false;
              showToast(e.message || 'Có lỗi xảy ra', 'error');
            }
          });
        };
        document.querySelectorAll('.aitr-unlink-btn').forEach(bindUnlinkButton);
      }
    })();
  </script>
</section>
