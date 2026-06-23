<?php
/**
 * Pump panel widget — nhúng vào dashboard-2.php hoặc bất kỳ trang portal nào.
 *
 * Yêu cầu biến $garden_key được định nghĩa trước khi get_template_part().
 * Ví dụ:
 *   $garden_key = 'p001';
 *   get_template_part('template-parts/site/pump-panel');
 *
 * Hoặc dùng set_query_var / global $garden_key trước khi include.
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! isset($garden_key)) {
    // phpcs:ignore WordPress.Security.NonceVerification
    $garden_key = sanitize_text_field((string) ($_GET['garden'] ?? ''));
}

if ($garden_key === '') {
    return;
}

$pump_rules   = function_exists('aitrongcay_get_pump_rules') ? aitrongcay_get_pump_rules($garden_key) : [];
$is_auto_on   = ! empty($pump_rules['enabled']);
$is_admin     = current_user_can('manage_options');
$pump_running = get_transient('aitr_pump_run_' . sanitize_key($garden_key));
$last_pump    = function_exists('aitrongcay_pump_last_completed_at') ? aitrongcay_pump_last_completed_at($garden_key) : null;
?>

<div class="pump-panel" id="pump-panel-<?= esc_attr($garden_key) ?>"
     data-garden="<?= esc_attr($garden_key) ?>">

    <div class="pump-panel__header">
        <span class="pump-panel__icon">💧</span>
        <h3 class="pump-panel__title">Bơm tưới</h3>
        <span class="pump-panel__status-badge <?= $pump_running ? 'pump-panel__status-badge--on' : 'pump-panel__status-badge--off' ?>">
            <?= $pump_running ? 'Đang bơm' : 'Đang nghỉ' ?>
        </span>
    </div>

    <div class="pump-panel__meta">
        <span class="pump-panel__meta-item">
            <span class="pump-panel__meta-label">Độ ẩm hiện tại</span>
            <span class="pump-panel__soil-value" id="pump-soil-<?= esc_attr($garden_key) ?>">—</span>
        </span>
        <span class="pump-panel__meta-item">
            <span class="pump-panel__meta-label">Tự động</span>
            <span class="pump-panel__auto-badge <?= $is_auto_on ? 'pump-panel__auto-badge--on' : 'pump-panel__auto-badge--off' ?>">
                <?= $is_auto_on ? 'BẬT' : 'TẮT' ?>
            </span>
            <?php if ($is_auto_on): ?>
            <span class="pump-panel__auto-detail">(bật khi &lt; <?= esc_html((string) $pump_rules['soil_threshold_low']) ?>%,
                mỗi lần <?= esc_html((string) $pump_rules['pump_duration_sec']) ?>s)</span>
            <?php endif; ?>
        </span>
        <?php if ($last_pump !== null): ?>
        <span class="pump-panel__meta-item">
            <span class="pump-panel__meta-label">Lần bơm cuối</span>
            <span><?= esc_html(wp_date('d/m H:i', strtotime($last_pump))) ?></span>
        </span>
        <?php endif; ?>
    </div>

    <!-- Nút điều khiển thủ công -->
    <div class="pump-panel__controls">
        <button class="pump-panel__btn pump-panel__btn--on"
                data-action="pump_on" data-garden="<?= esc_attr($garden_key) ?>">
            ⚡ Bật bơm
        </button>
        <button class="pump-panel__btn pump-panel__btn--off"
                data-action="pump_off" data-garden="<?= esc_attr($garden_key) ?>">
            ■ Tắt bơm
        </button>
        <?php if ($is_admin): ?>
        <a class="pump-panel__btn pump-panel__btn--settings"
           href="<?= esc_url(admin_url('admin.php?page=aitrongcay-auto-pump&garden_key=' . urlencode($garden_key))) ?>">
            ⚙ Cài đặt
        </a>
        <?php endif; ?>
    </div>

    <!-- Lịch sử (load qua JS) -->
    <details class="pump-panel__log-details">
        <summary>Xem lịch sử bơm</summary>
        <div class="pump-panel__log-body" id="pump-log-<?= esc_attr($garden_key) ?>">
            <p class="pump-panel__log-loading">Đang tải…</p>
        </div>
    </details>

</div><!-- .pump-panel -->

<?php
// Inline JS chỉ output 1 lần dù nhúng nhiều panel
static $pump_panel_js_done = false;
if (! $pump_panel_js_done):
    $pump_panel_js_done = true;
    $ajax_url = admin_url('admin-ajax.php');
    $nonce    = wp_create_nonce('aitrongcay_portal_actions');
?>
<script>
(function () {
    const AJAX = <?= wp_json_encode($ajax_url) ?>;
    const NONCE = <?= wp_json_encode($nonce) ?>;

    function post(action, data) {
        const fd = new FormData();
        fd.append('action', action);
        fd.append('nonce',  NONCE);
        Object.entries(data).forEach(([k, v]) => fd.append(k, v));
        return fetch(AJAX, { method: 'POST', body: fd }).then(r => r.json());
    }

    function refreshSoil(gardenKey) {
        post('aitrongcay_pump_status', { garden_key: gardenKey }).then(res => {
            if (! res.success) { return; }
            const el = document.getElementById('pump-soil-' + gardenKey);
            if (el) {
                el.textContent = res.data.soil !== null ? res.data.soil.toFixed(1) + '%' : '—';
                
                // Low moisture alert
                const threshold = res.data.soil_threshold_low || 40;
                let alertEl = document.getElementById('pump-alert-' + gardenKey);
                if (res.data.soil !== null && res.data.soil < threshold && !res.data.is_running) {
                    if (!alertEl) {
                        alertEl = document.createElement('div');
                        alertEl.id = 'pump-alert-' + gardenKey;
                        alertEl.className = 'pump-panel__alert';
                        el.closest('.pump-panel__meta').after(alertEl);
                    }
                    alertEl.innerHTML = `⚠️ Cảnh báo: Độ ẩm đất (${res.data.soil.toFixed(1)}%) đang thấp hơn mức tối thiểu (${threshold}%). Hãy kiểm tra bơm!`;
                } else if (alertEl) {
                    alertEl.remove();
                }
            }
            // Cập nhật badge đang chạy
            const panel = document.getElementById('pump-panel-' + gardenKey);
            if (panel) {
                const badge = panel.querySelector('.pump-panel__status-badge');
                if (badge) {
                    badge.textContent   = res.data.is_running ? 'Đang bơm' : 'Đang nghỉ';
                    badge.className     = 'pump-panel__status-badge pump-panel__status-badge--'
                        + (res.data.is_running ? 'on' : 'off');
                }
            }
        }).catch(() => {});
    }

    function loadLog(gardenKey, container) {
        container.innerHTML = '<p>Đang tải…</p>';
        post('aitrongcay_pump_log', { garden_key: gardenKey, limit: 15 }).then(res => {
            if (! res.success || ! res.data.logs.length) {
                container.innerHTML = '<p>Chưa có lịch sử.</p>';
                return;
            }
            let html = '<table class="pump-log-table"><thead><tr>'
                + '<th>Bật lúc</th><th>Tắt lúc</th><th>Loại</th>'
                + '<th>Độ ẩm trước</th><th>Thời gian</th><th>TT</th>'
                + '</tr></thead><tbody>';
            res.data.logs.forEach(r => {
                html += `<tr>
                    <td>${r.pump_on_at  ?? '—'}</td>
                    <td>${r.pump_off_at ?? '—'}</td>
                    <td>${r.triggered_by}</td>
                    <td>${r.soil_before !== null ? parseFloat(r.soil_before).toFixed(1) + '%' : '—'}</td>
                    <td>${r.duration_sec !== null ? r.duration_sec + 's' : '—'}</td>
                    <td class="${r.status === 'completed' ? 'tt-ok' : 'tt-warn'}">${r.status}</td>
                </tr>`;
            });
            html += '</tbody></table>';
            container.innerHTML = html;
        }).catch(() => { container.innerHTML = '<p>Lỗi tải log.</p>'; });
    }

    document.querySelectorAll('.pump-panel').forEach(panel => {
        const gk = panel.dataset.garden;
        // Load soil on open
        refreshSoil(gk);
        // Refresh soil mỗi 30s
        setInterval(() => refreshSoil(gk), 30000);

        // Log lazy-load khi mở <details>
        const details = panel.querySelector('.pump-panel__log-details');
        const logBody = panel.querySelector('.pump-panel__log-body');
        if (details && logBody) {
            details.addEventListener('toggle', () => {
                if (details.open) { loadLog(gk, logBody); }
            });
        }

        // Manual pump buttons
        panel.querySelectorAll('[data-action]').forEach(btn => {
            btn.addEventListener('click', () => {
                const state = btn.dataset.action === 'pump_on' ? 1 : 0;
                btn.disabled = true;
                post('aitrongcay_pump_manual', { garden_key: gk, state }).then(res => {
                    alert(res.data?.message ?? (res.success ? 'OK' : 'Lỗi'));
                    refreshSoil(gk);
                }).catch(() => alert('Lỗi kết nối.'))
                  .finally(() => { btn.disabled = false; });
            });
        });
    });
}());
</script>
<style>
.pump-panel {
    background: var(--surface-card, #1e2530);
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 16px;
}
.pump-panel__header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
}
.pump-panel__title { margin: 0; font-size: 1rem; font-weight: 600; }
.pump-panel__status-badge {
    margin-left: auto;
    padding: 2px 10px;
    border-radius: 999px;
    font-size: .75rem;
    font-weight: 700;
    text-transform: uppercase;
}
.pump-panel__status-badge--on  { background: #1b5e20; color: #a5d6a7; }
.pump-panel__status-badge--off { background: #263238; color: #90a4ae; }
.pump-panel__meta {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    font-size: .85rem;
    margin-bottom: 14px;
    color: var(--text-muted, #8a9bb0);
}
.pump-panel__meta-label { display: block; font-size: .7rem; text-transform: uppercase; opacity: .7; }
.pump-panel__auto-badge { font-weight: 700; }
.pump-panel__auto-badge--on  { color: #4caf50; }
.pump-panel__auto-badge--off { color: #ef9a9a; }
.pump-panel__auto-detail { font-size: .75rem; }
.pump-panel__controls { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
.pump-panel__btn {
    padding: 7px 16px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    font-size: .85rem;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.pump-panel__btn--on       { background: #1565c0; color: #fff; }
.pump-panel__btn--on:hover { background: #1976d2; }
.pump-panel__btn--off       { background: #b71c1c; color: #fff; }
.pump-panel__btn--off:hover { background: #c62828; }
.pump-panel__btn--settings  { background: #37474f; color: #cfd8dc; }
.pump-panel__btn:disabled { opacity: .5; cursor: not-allowed; }
.pump-panel__log-details { font-size: .85rem; }
.pump-panel__log-details summary { cursor: pointer; color: var(--text-muted, #8a9bb0); }
.pump-log-table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: .8rem; }
.pump-log-table th, .pump-log-table td { padding: 4px 8px; border-bottom: 1px solid rgba(255,255,255,.07); }
.tt-ok   { color: #81c784; font-weight: 600; }
.tt-warn { color: #ef9a9a; font-weight: 600; }
.pump-panel__alert {
    background: rgba(239, 83, 80, 0.1);
    color: #ef5350;
    padding: 10px 12px;
    border-radius: 8px;
    border: 1px solid rgba(239, 83, 80, 0.2);
    font-size: .85rem;
    margin-bottom: 14px;
    animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-5px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
<?php endif; ?>
