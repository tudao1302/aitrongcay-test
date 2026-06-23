# CLAUDE.md — Ai trồng cây

Hướng dẫn cho Claude Code về toàn bộ dự án. Đọc file này trước khi bắt đầu bất kỳ task nào.

---

## Tổng quan

**"Ai trồng cây"** là nền tảng vườn số (hydroponic garden SaaS) cho phép khách hàng sở hữu một khoang rau thực sự được chăm sóc bởi đội ngũ, theo dõi qua webcam, care log và AI gardener từ portal cá nhân.

- **URL localhost:** `http://localhost/aitrongcay`
- **Stack:** WordPress 6.4+ / PHP 8.1+ / MySQL, không dùng block editor
- **Theme:** `wp-content/themes/aitrongcay/` (custom, không dùng starter theme)
- **Công ty:** CÔNG TY CỔ PHẦN NGHIÊN CỨU GIẢI PHÁP VÀ PHÁT TRIỂN CÔNG NGHỆ XANH
- **Địa chỉ:** Số 180A, đường Âu Cơ, Phường Tứ Liên, Quận Tây Hồ, Hà Nội
- **SĐT:** 0983.660.988 – 0876.666.114

---

## Cấu trúc thư mục gốc

```
aitrongcay/
├── CLAUDE.md                  ← file này
├── wp-config.php              ← WP_HOME + WP_SITEURL định nghĩa ở đây (không trong DB)
├── .htaccess                  ← Rewrite rules WordPress
├── assets/                    ← CSS + JS + images dùng bởi file PHP ngoài theme
│   ├── css/styles.css         ← symlink junction → theme/assets/css/styles.css
│   ├── images/                ← symlink junction → theme/assets/images/
│   └── js/main.js             ← JS cho auth/signup pages (localStorage, mobile nav)
├── auth/
│   ├── login.php              ← Trang đăng nhập (PHP, load WordPress, form WP nonce)
│   └── login.html             ← Redirect stub → ./login.php
├── signup/
│   ├── register.php           ← Form tư vấn (PHP, WP nonce, POST → admin-post.php)
│   ├── register.html          ← Redirect stub → ./register.php
│   └── onboarding.html        ← Redirect stub → ../onboarding/ (WP virtual page)
├── portal/
│   ├── dashboard-2.html       ← Redirect stub → ./dashboard-2/
│   ├── quality-safety.html    ← Redirect stub → ./chat-luong-an-toan/
│   └── tools-warehouse.html   ← Redirect stub → ./kho-nong-cu-2/
├── theme/
│   └── partials/              ← Header/footer HTML cũ (đã migrate sang PHP virtual pages)
└── wp-content/themes/aitrongcay/
    ├── functions.php          ← Main theme file (~8300 dòng)
    ├── inc/                   ← Tất cả includes (xem bên dưới)
    ├── template-parts/        ← Template fragments
    │   ├── site/              ← Header, footer, shared components
    │   ├── home/              ← Homepage sections (hero, faq, packages, v.v.)
    │   └── virtual/           ← Templates cho từng virtual page (24 slugs)
    ├── assets/
    │   ├── css/styles.css
    │   ├── images/            ← SVG + PNG
    │   └── js/main.js         ← Theme JS (nav, brand emphasis, không handle auth)
    └── templates/             ← WordPress page templates
```

---

## Files include (thứ tự load trong functions.php)

| File | Chức năng |
|------|-----------|
| `inc/site-config.php` | Brand, company profile, nav items, footer groups, SEO meta |
| `inc/virtual-pages.php` | Đăng ký 25 virtual page slugs + rewrite rules |
| `inc/homepage-settings.php` | Nội dung mặc định homepage + admin page quản lý |
| `inc/page-content-settings.php` | Nội dung các page tĩnh |
| `inc/portal-garden-data.php` | Dataset library, profile resolution, pot/tool shelf data |
| `inc/portal-device-mapping-admin.php` | Blynk config, rack/device mapping, 3 admin pages |
| `inc/plant-onboarding-db.php` | DB tables cây/vật tư, CRUD, lifecycle handlers |
| `inc/portal-ai-agent.php` | AI gardener config (adapter-ready / remote-http / openai-chat) |
| `inc/tray-config.php` | Multi-rack sensor monitor config, AJAX handlers (sensors/control/save) |

---

## Virtual Pages (25 slugs)

Virtual pages **không có WP post** trong DB. Routing qua rewrite rules → template PHP.

Flush token hiện tại: `aitr_flush_routes=20260403-banbe-share` (phải dùng đúng token để flush rewrite).

| URL slug | Template | Ghi chú |
|----------|----------|---------|
| `/cach-hoat-dong/` | virtual/cach-hoat-dong.php | |
| `/cho-que/` | virtual/cho-que.php | Chợ quê |
| `/faq/` | virtual/faq.php | Câu hỏi thường gặp |
| `/dang-ky-tu-van/` | virtual/dang-ky-tu-van.php | Consultation form |
| `/onboarding/` | virtual/onboarding.php | Tạo tài khoản |
| `/portal/onboarding-cay-moi/` | virtual/onboarding-cay-moi.php | |
| `/dang-nhap/` | virtual/dang-nhap.php | |
| `/dang-xuat/` | virtual/dang-nhap.php | Logout handler, sau đó redirect |
| `/tai-khoan/` | virtual/tai-khoan.php | |
| `/portal/` | virtual/portal.php | Legacy |
| `/portal/dashboard/` | — | Redirect → `/portal/dashboard-2/` |
| `/portal/dashboard-2/` | virtual/dashboard-2.php | Portal chính |
| `/portal/flower-bio/` | virtual/flower-bio.php | |
| `/portal/webcam/` | virtual/portal.php | |
| `/portal/tinh-trang-vuon/` | virtual/portal.php | |
| `/portal/nhat-ky-cham-soc/` | virtual/portal.php | |
| `/portal/chat-luong-an-toan/` | virtual/portal.php | |
| `/portal/tro-ly-ai/` | virtual/portal.php | Cho phép guest (không cần login) |
| `/portal/kho-nong-cu/` | virtual/portal.php | Legacy |
| `/portal/kho-nong-cu-2/` | virtual/kho-nong-cu-2.php | |
| `/portal/vat-tu-thiet-bi-moi/` | virtual/vat-tu-thiet-bi-moi.php | |
| `/portal/hang-xom/` | virtual/portal.php | |
| `/portal/ban-be/` | — | Redirect → `/portal/hang-xom/` |
| `/portal/chia-se-khu-vuon/` | virtual/portal.php | |
| `/faq/` | virtual/faq.php | |

**Quan trọng:** Sau khi thêm/xóa virtual page slug, phải flush rewrite bằng cách vào WP Admin → Settings → Permalinks → Save, hoặc gọi `flush_rewrite_rules()`.

---

## Auth Flow

```
User → /signup/register.php → POST aitrongcay_consultation_submit
                             → redirect /onboarding/ (WP virtual page, tạo account)
                             → POST aitrongcay_register_submit
                             → redirect /dang-nhap/?auth_status=register-success

User → /auth/login.php → POST aitrongcay_login_submit
                       → redirect /portal/dashboard-2/
```

**Các `admin_post` actions chính:**

| Action | Handler | Ai gọi |
|--------|---------|--------|
| `aitrongcay_consultation_submit` | `aitrongcay_handle_consultation_submission` | signup/register.php |
| `aitrongcay_register_submit` | `aitrongcay_handle_register_submission` | /onboarding/ virtual page |
| `aitrongcay_login_submit` | `aitrongcay_handle_login_submission` | auth/login.php |
| `aitrongcay_account_update` | `aitrongcay_handle_account_update` | /tai-khoan/ |
| `aitrongcay_password_reset_request` | `aitrongcay_handle_password_reset_request` | /dang-nhap/ |
| `aitrongcay_blynk_control_direct` | `aitrongcay_blynk_control_direct_submit` | Portal |
| `aitrongcay_init_rack` | `aitrongcay_init_rack_for_current_user_action` | Portal |
| `aitrongcay_upload_photo_submit` | `aitrongcay_upload_photo_attachment_submit` | Portal |
| `aitrongcay_friend_toggle_share` … (6 friend actions) | … | Portal /hang-xom/ |

Tất cả form PHP đều dùng `wp_nonce_field()` / `check_admin_referer()`. **Không được bỏ qua nonce.**

---

## Database Tables

### Garden / Social (10 bảng)

| Tên bảng | Chức năng |
|----------|-----------|
| `wp_aitr_friendships` | Kết bạn / hàng xóm |
| `wp_aitr_garden_members` | Thành viên khu vườn (nhiều người theo dõi 1 vườn) |
| `wp_aitr_garden_notes` | Ghi chú / care log |
| `wp_aitr_gardens` | Hồ sơ khu vườn |
| `wp_aitr_garden_pots` | Các khay trồng cụ thể |
| `wp_aitr_garden_tools` | Nông cụ / vật tư theo vườn |
| `wp_aitr_garden_racks` | Rack vật lý (tối đa 6 khoang, 12 khay) |
| `wp_aitr_garden_rack_slots` | Slot / khay trong rack |
| `wp_aitr_garden_rack_inventory_events` | Lịch sử kho rack |
| `wp_aitr_garden_rack_assignments` | Phân công rack cho khách hàng |

### Plant / Onboarding (14 bảng)

| Tên bảng | Chức năng |
|----------|-----------|
| `wp_aitr_plants` | Danh mục cây |
| `wp_aitr_supplies` | Danh mục vật tư |
| `wp_aitr_plant_supplies` | Liên kết cây ↔ vật tư |
| `wp_aitr_plant_sop_steps` | Quy trình SOP |
| `wp_aitr_plant_public_content` | Nội dung công khai |
| `wp_aitr_plant_environment_profiles` | Hồ sơ môi trường |
| `wp_aitr_plant_growth_stages` | Giai đoạn sinh trưởng |
| `wp_aitr_plant_nutrition_profiles` | Hồ sơ dinh dưỡng |
| `wp_aitr_plant_checklists` | Checklist chăm sóc |
| `wp_aitr_plant_health_issues` | Vấn đề sức khoẻ |
| `wp_aitr_plant_alert_rules` | Quy tắc cảnh báo |
| `wp_aitr_plant_workflows` | Workflow |
| `wp_aitr_plant_protocol_topics` | Chủ đề protocol |
| `wp_aitr_plant_robot_tasks` | Tasks cho robot |

**Install function:** `aitrongcay_install_social_tables()` + `aitrongcay_install_onboarding_tables()` — dùng `dbDelta()`.

---

## Admin Pages (WP Admin → Appearance)

| Slug | Tên | File |
|------|-----|------|
| `aitrongcay-homepage-content` | Homepage content | inc/homepage-settings.php |
| `aitrongcay-garden-device-mapping` | Mapping thiết bị khu vườn | inc/portal-device-mapping-admin.php |
| `aitrongcay-garden-profile-tools` | Hồ sơ & vật tư khu vườn | inc/portal-device-mapping-admin.php |
| `aitrongcay-rack-inventory` | Kho rack | inc/portal-device-mapping-admin.php |

---

## Blynk / IoT Integration

- Blynk base URL: `https://blynk.cloud/external/api`
- Config lưu trong WP option: `aitrongcay_garden_device_configs` (array, key = `garden_key`)
- Mỗi garden có: `token`, `vpins` (V0–V40+), `devices`, `pots`, `pot_tokens`
- Token chung dùng cho: sensor (temp/hum/soil), pump, đèn, van
- `__shared__` / `shared-token` trong `pot_tokens` = dùng token chung của garden

**Rack model:** 1 rack → tối đa 6 khoang → mỗi khoang 2 khay (A/B) → slot code: `C01-T1`, `C01-T2`, v.v.

### Per-garden Rack Monitor Config (triển khai 2026-05-13)

Sensor monitor config của từng khách hàng được lưu riêng theo `garden_key`:

- **Option key pattern:** `aitrongcay_rack_cfg_{garden_key}` (ví dụ: `aitrongcay_rack_cfg_p001`)
- **Fallback:** nếu chưa có option per-garden, tự động đọc global `aitrongcay_rack_monitor_configs`
- **Transient cache key:** `aitr_t_{garden_key}_r{rack_index}_t{tray_index}` (TTL 5s)
- **Function:** `aitrongcay_get_rack_monitor_configs(string $garden_key = ''): array` trong `inc/tray-config.php`

**AJAX actions (đều yêu cầu nonce `aitrongcay_portal_actions` + garden_key):**

| Action | Xác thực | Mô tả |
|--------|----------|-------|
| `aitrongcay_tray_sensors` | owner/viewer hoặc admin | Đọc sensor từ Blynk |
| `aitrongcay_tray_control` | owner/viewer hoặc admin | Điều khiển thiết bị |
| `aitrongcay_tray_config_save` | admin only | Lưu config per-garden |

**Authorization:** dùng `aitrongcay_user_can_view_garden(string $garden_key, int $user_id): bool` (hàm có sẵn trong `functions.php:2359`). Admin (`manage_options`) bypass mọi check.

**Sau khi deploy production:** Admin phải vào từng `/portal/dashboard-2/?garden={garden_key}`, click ⚙️ Settings, nhập Blynk token đúng cho từng khách hàng rồi Save → tạo option `aitrongcay_rack_cfg_{garden_key}` cho khách đó.

**JS:** `AITR_GARDEN_KEY` (đã có sẵn trong dashboard-2.php) được gửi kèm trong mọi AJAX call sensor/control/save.

---

## WP Config (localhost override)

`wp-config.php` có 2 dòng thêm vào để override domain trong DB:

```php
define('WP_HOME', 'http://localhost/aitrongcay');
define('WP_SITEURL', 'http://localhost/aitrongcay');
```

Không được xóa khi develop local. Khi deploy production thì xóa 2 dòng này.

---

## Assets: Directory Junctions (Windows)

Vì file PHP ở `/auth/`, `/signup/` reference `../assets/css/styles.css` nhưng CSS nằm trong theme:

```
aitrongcay/assets/css/    → junction → wp-content/themes/aitrongcay/assets/css/
aitrongcay/assets/images/ → junction → wp-content/themes/aitrongcay/assets/images/
```

Tạo lại nếu mất:
```
mklink /J "d:\laragon\www\aitrongcay\assets\css" "d:\laragon\www\aitrongcay\wp-content\themes\aitrongcay\assets\css"
mklink /J "d:\laragon\www\aitrongcay\assets\images" "d:\laragon\www\aitrongcay\wp-content\themes\aitrongcay\assets\images"
```

---

## JavaScript conventions

### `assets/js/main.js` (dùng bởi auth/signup pages)
- Mobile nav toggle (`data-mobile-toggle` / `data-mobile-panel`)
- localStorage keys: `aitrongcay_auth_profile`, `aitrongcay_auth_session`, `aitrongcay_auth_flow`
- Có attribute `data-auth-form` trên form → JS intercept, **không** submit native. **Các form PHP (login.php, register.php) KHÔNG được gán attribute này** vì cần native POST đến admin-post.php.

### `wp-content/themes/aitrongcay/assets/js/main.js` (dùng bởi theme)
- Build mobile panel nav từ `window.aitrongcayTheme`
- Set active nav link theo URL hiện tại
- Emphasize brand name trong DOM text nodes

---

## Vấn đề đang tồn đọng

### Cần fix / chưa xong

| # | Mục | File | Mức độ |
|---|-----|------|--------|
| 1 | Nút Google login bị `disabled` cứng | `template-parts/virtual/dang-nhap.php:87` | Cao |
| 2 | FAQ virtual page chỉ redirect sang AI, không có nội dung | `template-parts/virtual/faq.php` | Thấp |
| 3 | Dynamic data `tai-khoan.php` một phần hardcode | `template-parts/virtual/tai-khoan.php` | Trung bình |

### Chờ làm (roadmap gần)

| # | Mục | Ghi chú |
|---|-----|---------|
| 4 | "Hàng xóm" garden sharing (Option C) | Cho phép bạn bè xem sensor data của nhau qua `wp_aitr_friendships` — user đã xác nhận sẽ làm sau |

### Cần dọn dẹp (cleanup)

| File | Lý do |
|------|-------|
| `functions.php:435` | HLS URL ngrok hardcode: `https://aitrongcay.ngrok.app/tung-01/P-001/index.m3u8` → cần vào WP option |
| `functions.php:504` | Email hardcode: `return 'tung@pccc.vn'` → cần vào WP option |
| `wp-content/themes/aitrongcay/tmp_aitr_map_rack3_devices.php` | Script tạm, chứa Blynk token thật |
| `wp-content/themes/aitrongcay/aitr_r3_apply.php` | Script tạm |
| `wp-content/themes/aitrongcay/functions.php.bak-20260421-0707` | Backup cũ (~355KB) |
| `aitr_debug_*.php`, `aitr_forensic_scan.php`, `backfill_*.php` (root) | Debug scripts |
| `leafy_import_*.json`, `verify_leafy_*.json` (root) | Import data tạm |
| `jho.txt` (root) | File rác |

---

## Quy ước code

- Tất cả PHP dùng `declare(strict_types=1)`
- Function prefix: `aitrongcay_`
- Sanitize input: `sanitize_text_field()`, `sanitize_textarea_field()`, `esc_url_raw()`, v.v.
- Output escape: `esc_html()`, `esc_attr()`, `esc_url()`
- WordPress URL: dùng `home_url()`, `admin_url()` — không dùng absolute URL `https://aitrongcay.com`
- Không dùng `split()` (deprecated PHP 7+) — dùng `explode()`
- Form validation: nonce check đầu tiên, sau đó sanitize, sau đó xử lý

---

## Quy ước virtual page

PHP template bắt đầu bằng:
```php
if (! defined('ABSPATH')) { exit; }
```

Template wrapper load bởi `inc/virtual-pages.php` khi slug khớp. Global `$post` là WP_Post stub (ID=0, type='page') được inject để tránh PHP 8 warnings từ `body_class()`.

---

## Môi trường phát triển

- **Server:** Laragon (Apache + PHP 8.1 + MySQL)
- **DB name:** `nhaitpx1_wp01` | user: `root` | pass: (trống)
- **WP Admin:** `http://localhost/aitrongcay/wp-admin/`
- **Email test:** Dùng Laragon MailHog hoặc WP Mail SMTP với SMTP local
