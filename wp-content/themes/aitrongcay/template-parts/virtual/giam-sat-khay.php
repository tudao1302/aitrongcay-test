<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

if (! is_user_logged_in()) {
    wp_safe_redirect(home_url('/dang-nhap/?redirect_to=' . rawurlencode(home_url('/portal/giam-sat-khoang/'))));
    exit;
}

$current_user = wp_get_current_user();
$garden_key   = function_exists('aitrongcay_resolve_active_garden_key') ? aitrongcay_resolve_active_garden_key($current_user) : '';
$trays        = function_exists('aitrongcay_get_tray_configs') ? aitrongcay_get_tray_configs($garden_key) : [];
$nonce        = wp_create_nonce('aitrongcay_portal_actions');
$ajax_url     = admin_url('admin-ajax.php');
$admin_url    = admin_url('admin.php?page=aitrongcay-unified-admin-beta');
?>
<style>
/* ── Rack Monitor layout ─────────────────────────────── */
.rack-monitor{padding:28px 0 48px}
.rack-monitor-header{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px;margin-bottom:32px}
.rack-monitor-header h1{margin:0;font-size:28px;font-weight:900;color:#fff}
.rack-monitor-header p{margin:6px 0 0;color:rgba(227,227,222,.62);font-size:14px}
.rack-monitor-badge{display:inline-flex;align-items:center;gap:8px;padding:8px 16px;border-radius:999px;background:rgba(111,219,168,.12);border:1px solid rgba(111,219,168,.24);color:#6fdba8;font-size:13px;font-weight:700}
.rack-monitor-badge::before{content:'';width:8px;height:8px;border-radius:50%;background:#6fdba8;animation:rack-pulse 2s infinite}
@keyframes rack-pulse{0%,100%{opacity:1}50%{opacity:.35}}

/* ── 3-lane grid ─────────────────────────────────────── */
.tray-lanes{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;align-items:start}
@media(max-width:1100px){.tray-lanes{grid-template-columns:1fr 1fr}}
@media(max-width:700px){.tray-lanes{grid-template-columns:1fr}}

/* ── Single tray card ────────────────────────────────── */
.tray-lane{background:rgba(26,28,25,.94);border-radius:28px;overflow:hidden;border:1px solid rgba(255,255,255,.06);box-shadow:0 18px 44px rgba(0,0,0,.22)}
.tray-lane-header{padding:20px 22px 16px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid rgba(255,255,255,.06)}
.tray-lane-title{font-size:18px;font-weight:900;color:#fff}
.tray-lane-subtitle{font-size:12px;color:rgba(227,227,222,.5);margin-top:3px}
.tray-status-dot{width:12px;height:12px;border-radius:50%;background:#555;flex-shrink:0;transition:.3s}
.tray-status-dot.online{background:#6fdba8;box-shadow:0 0 0 3px rgba(111,219,168,.2);animation:rack-pulse 2.2s infinite}
.tray-status-dot.error{background:#ff6b6b}

/* ── Webcam ──────────────────────────────────────────── */
.tray-webcam{position:relative;background:#0b120e;aspect-ratio:16/9;overflow:hidden}
.tray-webcam img,.tray-webcam iframe{width:100%;height:100%;display:block;border:none;object-fit:cover}
.tray-webcam-placeholder{width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;color:rgba(227,227,222,.32)}
.tray-webcam-placeholder span:first-child{font-size:40px}
.tray-webcam-placeholder span:last-child{font-size:12px}
.tray-webcam-overlay{position:absolute;top:10px;right:10px;padding:4px 10px;border-radius:999px;background:rgba(0,0,0,.62);color:#6fdba8;font-size:11px;font-weight:700;backdrop-filter:blur(8px)}

/* ── Sensor grid ─────────────────────────────────────── */
.tray-sensors{display:grid;grid-template-columns:1fr 1fr;gap:10px;padding:16px}
.sensor-card{background:rgba(41,43,39,.62);border-radius:16px;padding:14px;border:1px solid rgba(255,255,255,.04);position:relative;overflow:hidden}
.sensor-card::after{content:'';position:absolute;inset:0;border-radius:16px;opacity:0;transition:.4s;pointer-events:none}
.sensor-card.ok::after{background:rgba(111,219,168,.06);opacity:1}
.sensor-card.warn::after{background:rgba(255,182,76,.08);opacity:1}
.sensor-card.alert::after{background:rgba(255,107,107,.08);opacity:1}
.sensor-icon{font-size:22px;line-height:1;margin-bottom:6px}
.sensor-label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:rgba(227,227,222,.42);margin-bottom:4px}
.sensor-value{font-size:28px;font-weight:900;color:#fff;line-height:1;font-variant-numeric:tabular-nums}
.sensor-value.loading{animation:sensor-shimmer 1.2s infinite}
.sensor-unit{font-size:13px;font-weight:500;color:rgba(227,227,222,.5);margin-left:2px}
.sensor-status{font-size:10px;margin-top:5px;font-weight:600}
.sensor-status.ok{color:#6fdba8}
.sensor-status.warn{color:#ffb64c}
.sensor-status.alert{color:#ff6b6b}
@keyframes sensor-shimmer{0%,100%{opacity:.4}50%{opacity:1}}

/* ── Controls ────────────────────────────────────────── */
.tray-controls{display:flex;gap:10px;padding:0 16px 18px;flex-wrap:wrap}
.tray-ctrl-btn{flex:1;min-width:100px;display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:12px 14px;border-radius:14px;border:1px solid rgba(255,255,255,.08);background:rgba(51,53,50,.62);color:#e3e3de;font-size:13px;font-weight:700;cursor:pointer;transition:.22s;user-select:none}
.tray-ctrl-btn:hover{background:rgba(51,53,50,.88)}
.tray-ctrl-btn.active{background:linear-gradient(135deg,#31a375,#6fdba8);color:#062013;border-color:transparent}
.tray-ctrl-btn:disabled{opacity:.4;cursor:not-allowed}
.tray-ctrl-btn .ctrl-dot{width:8px;height:8px;border-radius:50%;background:currentColor;flex-shrink:0;transition:.22s}

/* ── Refresh meta ────────────────────────────────────── */
.tray-refresh-meta{padding:0 16px 14px;display:flex;justify-content:space-between;align-items:center}
.tray-last-updated{font-size:11px;color:rgba(227,227,222,.32)}
.tray-error-notice{padding:12px 14px;margin:0 16px 14px;border-radius:12px;background:rgba(255,107,107,.1);border:1px solid rgba(255,107,107,.2);color:#ff9999;font-size:12px}

/* ── Not configured ──────────────────────────────────── */
.tray-not-configured{padding:28px 22px;text-align:center;color:rgba(227,227,222,.42)}
.tray-not-configured a{color:#6fdba8;font-weight:700;text-decoration:none}

/* ── Alert Toast System ──────────────────────────────── */
.global-alert-container { position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; pointer-events: none; }
.global-alert-toast { background: rgba(255, 107, 107, 0.95); backdrop-filter: blur(10px); color: #fff; padding: 16px 24px; border-radius: 12px; font-weight: 700; box-shadow: 0 10px 30px rgba(255, 107, 107, 0.4); transform: translateX(120%); transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); display: flex; align-items: center; gap: 12px; border-left: 4px solid #c92a2a; }
.global-alert-toast.show { transform: translateX(0); }
.global-alert-toast .icon { font-size: 24px; animation: rack-pulse 1s infinite; }

/* ── Section wrapper ─────────────────────────────────── */
.rack-monitor .section-tight{padding-top:0!important}
</style>

<!-- Alert Container -->
<div class="global-alert-container" id="alert-container"></div>

<div class="section-tight rack-monitor">
  <div class="rack-monitor-header">
    <div>
      <h1>🌿 Rack Monitor</h1>
      <p>Giám sát & điều khiển 3 khoang trồng thủy canh theo thời gian thực</p>
    </div>
    <span class="rack-monitor-badge">Live · cập nhật 5 giây</span>
  </div>

  <div class="tray-lanes">
  <?php foreach ($trays as $i => $tray) :
      $has_token  = trim((string) ($tray['blynk_token'] ?? '')) !== '';
      $webcam_url = trim((string) ($tray['webcam_url'] ?? ''));
      $tray_name  = esc_html($tray['name'] ?: ('Khoang ' . ($i + 1)));
  ?>
  <div class="tray-lane" data-tray-lane="<?php echo $i; ?>">

    <!-- Header -->
    <div class="tray-lane-header">
      <div>
        <div class="tray-lane-title"><?php echo $tray_name; ?></div>
        <div class="tray-lane-subtitle">Luồng <?php echo $i + 1; ?> / 3</div>
      </div>
      <div class="tray-status-dot<?php echo $has_token ? '' : ' error'; ?>"
           data-tray-status-dot="<?php echo $i; ?>"
           title="<?php echo $has_token ? 'Đang kết nối...' : 'Chưa cấu hình token'; ?>"></div>
    </div>

    <!-- Webcam -->
    <div class="tray-webcam">
      <?php if ($webcam_url !== '') : ?>
        <?php if (str_contains($webcam_url, '.m3u8')) : ?>
          <?php
          // HLS — wrap in iframe pointing to a simple player page or use video directly
          // We output an HTML5 video tag; client JS will use hls.js if needed
          ?>
          <video data-tray-webcam-video="<?php echo $i; ?>"
                 src="<?php echo esc_url($webcam_url); ?>"
                 autoplay muted playsinline loop
                 style="width:100%;height:100%;object-fit:cover"></video>
        <?php elseif (str_contains($webcam_url, 'stream.html') || str_contains($webcam_url, 'webrtc')): ?>
          <!-- WebRTC via go2rtc or iframe-based player -->
          <iframe src="<?php echo esc_url($webcam_url); ?>" 
                  frameborder="0" 
                  scrolling="no" 
                  allowfullscreen 
                  style="width:100%;height:100%;object-fit:cover;pointer-events:none;"></iframe>
        <?php else : ?>
          <img data-tray-webcam-img="<?php echo $i; ?>"
               src="<?php echo esc_url($webcam_url); ?>"
               alt="Webcam khoang <?php echo $i + 1; ?>"
               loading="lazy">
        <?php endif; ?>
        <div class="tray-webcam-overlay">CAM <?php echo $i + 1; ?></div>
      <?php else : ?>
        <div class="tray-webcam-placeholder">
          <span>📷</span>
          <span>Webcam chưa cấu hình</span>
        </div>
      <?php endif; ?>
    </div>

    <?php if (! $has_token) : ?>
    <!-- Not configured -->
    <div class="tray-not-configured">
      <p>⚙️ Chưa cấu hình Blynk token cho khoang này.</p>
      <a href="<?php echo esc_url($admin_url); ?>">→ Vào admin để cấu hình</a>
    </div>

    <?php else : ?>
    <!-- Sensors -->
    <div class="tray-sensors" data-tray-sensors="<?php echo $i; ?>">
      <?php
      $sensor_defs = [
          ['key' => 'temp',  'icon' => '🌡️', 'label' => 'Nhiệt độ', 'unit' => '°C', 'ok' => [18, 28],  'warn' => [14, 34]],
          ['key' => 'hum',   'icon' => '💧', 'label' => 'Độ ẩm',    'unit' => '%',  'ok' => [50, 85],  'warn' => [35, 95]],
          ['key' => 'ph',    'icon' => '⚗️', 'label' => 'pH',        'unit' => '',   'ok' => [5.5, 7.0], 'warn' => [5.0, 7.5]],
          ['key' => 'ec',    'icon' => '🌱', 'label' => 'EC',        'unit' => 'mS', 'ok' => [0.8, 2.5], 'warn' => [0.4, 3.5]],
      ];
      foreach ($sensor_defs as $s) :
      ?>
      <div class="sensor-card" data-sensor-card="<?php echo esc_attr($s['key']); ?>"
           data-ok-min="<?php echo $s['ok'][0]; ?>" data-ok-max="<?php echo $s['ok'][1]; ?>"
           data-warn-min="<?php echo $s['warn'][0]; ?>" data-warn-max="<?php echo $s['warn'][1]; ?>">
        <div class="sensor-icon"><?php echo $s['icon']; ?></div>
        <div class="sensor-label"><?php echo esc_html($s['label']); ?></div>
        <div>
          <span class="sensor-value loading" data-sensor-value>--</span>
          <span class="sensor-unit"><?php echo esc_html($s['unit']); ?></span>
        </div>
        <div class="sensor-status" data-sensor-status></div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Controls -->
    <div class="tray-controls" data-tray-controls="<?php echo $i; ?>">
      <button class="tray-ctrl-btn"
              data-tray-ctrl="<?php echo $i; ?>" data-device="light" data-value="0"
              title="Bật / tắt đèn">
        <span class="ctrl-dot"></span>
        <span data-ctrl-label>💡 Đèn</span>
      </button>
      <button class="tray-ctrl-btn"
              data-tray-ctrl="<?php echo $i; ?>" data-device="pump" data-value="0"
              title="Bật / tắt máy bơm">
        <span class="ctrl-dot"></span>
        <span data-ctrl-label>🔄 Bơm</span>
      </button>
    </div>

    <!-- Refresh meta -->
    <div class="tray-refresh-meta">
      <span class="tray-last-updated" data-tray-updated="<?php echo $i; ?>">Đang tải...</span>
    </div>
    <?php endif; ?>

  </div><!-- .tray-lane -->
  <?php endforeach; ?>
  </div><!-- .tray-lanes -->

  <p style="margin-top:28px;font-size:12px;color:rgba(227,227,222,.32);text-align:center">
    Dữ liệu được proxy qua server — Blynk token không hiển thị trên trình duyệt. ·
    <a href="<?php echo esc_url($admin_url); ?>" style="color:#6fdba8">Cấu hình khoang</a>
  </p>
</div>

<script>
(function () {
  'use strict';

  var AJAX_URL   = <?php echo wp_json_encode($ajax_url); ?>;
  var NONCE      = <?php echo wp_json_encode($nonce); ?>;
  var GARDEN_KEY = <?php echo wp_json_encode($garden_key); ?>;
  var INTERVAL   = 5000; // ms between sensor polls

  // ── Alert System (Task 3.3) ──────────────────────────────────────────────
  var activeAlerts = new Set(); // To prevent spamming
  function triggerAlert(trayIndex, key, label, value) {
    var alertId = trayIndex + '-' + key;
    if (activeAlerts.has(alertId)) return;
    activeAlerts.add(alertId);

    var container = document.getElementById('alert-container');
    var toast = document.createElement('div');
    toast.className = 'global-alert-toast';
    toast.innerHTML = '<span class="icon">🚨</span><div><strong>CẢNH BÁO KHẨN CẤP (Luồng ' + (trayIndex + 1) + ')</strong><br><span style="font-size:13px;font-weight:normal;">Chỉ số <b>' + label + '</b> đang ở mức nguy hiểm: ' + value + '</span></div>';
    
    container.appendChild(toast);
    
    // Play a buzzer sound
    try {
      var audioCtx = new (window.AudioContext || window.webkitAudioContext)();
      var osc = audioCtx.createOscillator();
      osc.type = 'sawtooth';
      osc.frequency.setValueAtTime(800, audioCtx.currentTime);
      osc.connect(audioCtx.destination);
      osc.start();
      setTimeout(function() { osc.stop(); }, 500); // Buzz for 500ms
    } catch(e) {}

    // Show animation
    setTimeout(function() { toast.classList.add('show'); }, 50);

    // Auto remove after 10s and clear from active Set so it can trigger again if still bad later
    setTimeout(function() {
      toast.classList.remove('show');
      setTimeout(function() {
        if (toast.parentNode) toast.parentNode.removeChild(toast);
        activeAlerts.delete(alertId);
      }, 500);
    }, 10000);
  }

  // ── Sensor evaluation helpers ───────────────────────────────────────────
  function evalStatus(value, card) {
    if (value === null || isNaN(value)) return 'unknown';
    var okMin   = parseFloat(card.dataset.okMin   || '0');
    var okMax   = parseFloat(card.dataset.okMax   || '100');
    var warnMin = parseFloat(card.dataset.warnMin || '0');
    var warnMax = parseFloat(card.dataset.warnMax || '100');
    if (value >= okMin   && value <= okMax)   return 'ok';
    if (value >= warnMin && value <= warnMax) return 'warn';
    return 'alert';
  }

  var statusLabels = {
    temp:  { ok: 'Nhiệt độ lý tưởng', warn: 'Cần theo dõi', alert: 'Ngoài ngưỡng an toàn' },
    hum:   { ok: 'Độ ẩm tốt',          warn: 'Độ ẩm chú ý',  alert: 'Độ ẩm nguy hiểm' },
    ph:    { ok: 'pH cân bằng',         warn: 'pH cần điều chỉnh', alert: 'pH nguy hiểm cho cây' },
    ec:    { ok: 'Dinh dưỡng đủ',       warn: 'EC cần kiểm tra',   alert: 'EC bất thường' },
  };

  // ── Update one tray's sensor UI ─────────────────────────────────────────
  function applySensorData(trayIndex, data) {
    var lane = document.querySelector('[data-tray-lane="' + trayIndex + '"]');
    if (!lane) return;

    var dot        = lane.querySelector('[data-tray-status-dot]');
    var updatedEl  = lane.querySelector('[data-tray-updated]');
    var now        = new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

    // Status dot
    if (dot) {
      dot.classList.remove('online', 'error');
      if (data.error) {
        dot.classList.add('error');
        dot.title = data.error === 'no_token' ? 'Chưa cấu hình token' : 'Lỗi kết nối Blynk';
      } else {
        dot.classList.add('online');
        dot.title = 'Online · ' + now;
      }
    }

    if (updatedEl) {
      updatedEl.textContent = data.error ? '⚠ ' + data.error : 'Cập nhật lúc ' + now;
    }

    if (data.error) return;

    // Sensor cards
    var sensorKeys = ['temp', 'hum', 'ph', 'ec'];
    sensorKeys.forEach(function (key) {
      var card     = lane.querySelector('[data-sensor-card="' + key + '"]');
      var valueEl  = card ? card.querySelector('[data-sensor-value]') : null;
      var statusEl = card ? card.querySelector('[data-sensor-status]') : null;
      if (!card || !valueEl) return;

      var raw    = data[key];
      var value  = (raw !== null && raw !== undefined) ? parseFloat(raw) : null;
      var status = evalStatus(value, card);

      valueEl.classList.remove('loading');
      valueEl.textContent = value !== null ? (Number.isInteger(value) ? value : value.toFixed(1)) : '--';

      card.classList.remove('ok', 'warn', 'alert');
      if (status !== 'unknown') card.classList.add(status);

      if (statusEl) {
        statusEl.className = 'sensor-status ' + (status !== 'unknown' ? status : '');
        statusEl.textContent = (status !== 'unknown' && statusLabels[key])
          ? statusLabels[key][status] || ''
          : '';
      }
      
      // Kích hoạt Alert nếu vào ngưỡng nguy hiểm
      if (status === 'alert') {
        var lbl = card.querySelector('.sensor-label');
        triggerAlert(trayIndex, key, lbl ? lbl.textContent : key, value);
      }
    });

    // Update control buttons state
    ['light', 'pump'].forEach(function (device) {
      var btn = lane.querySelector('[data-tray-ctrl="' + trayIndex + '"][data-device="' + device + '"]');
      if (!btn) return;
      var currentVal = data[device];
      var isOn = currentVal !== null && currentVal !== undefined && parseInt(currentVal, 10) === 1;
      btn.dataset.value = isOn ? '1' : '0';
      btn.classList.toggle('active', isOn);
      var label = btn.querySelector('[data-ctrl-label]');
      if (label) {
        label.textContent = device === 'light'
          ? (isOn ? '💡 Đèn: BẬT' : '💡 Đèn: TẮT')
          : (isOn ? '🔄 Bơm: BẬT' : '🔄 Bơm: TẮT');
      }
    });
  }

  // ── Fetch sensors for one tray ──────────────────────────────────────────
  function fetchTray(trayIndex) {
    var body = new URLSearchParams({ action: 'aitrongcay_tray_sensors', nonce: NONCE, tray_index: trayIndex, garden_key: GARDEN_KEY });
    fetch(AJAX_URL, { method: 'POST', credentials: 'same-origin', body: body })
      .then(function (r) { return r.json(); })
      .then(function (result) {
        applySensorData(trayIndex, result.success ? (result.data || {}) : { error: result.data && result.data.message ? result.data.message : 'fetch_error' });
      })
      .catch(function () {
        applySensorData(trayIndex, { error: 'network_error' });
      });
  }

  // ── Device control ──────────────────────────────────────────────────────
  document.querySelectorAll('[data-tray-ctrl]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var trayIndex = parseInt(btn.dataset.trayCtrl, 10);
      var device    = btn.dataset.device;
      var current   = parseInt(btn.dataset.value || '0', 10);
      var next      = current === 1 ? 0 : 1;

      btn.disabled = true;
      var body = new URLSearchParams({
        action:     'aitrongcay_tray_control',
        nonce:      NONCE,
        tray_index: trayIndex,
        device:     device,
        value:      next,
        garden_key: GARDEN_KEY,
      });
      fetch(AJAX_URL, { method: 'POST', credentials: 'same-origin', body: body })
        .then(function (r) { return r.json(); })
        .then(function (result) {
          if (result.success) {
            btn.dataset.value = next;
            btn.classList.toggle('active', next === 1);
            var label = btn.querySelector('[data-ctrl-label]');
            if (label) {
              label.textContent = device === 'light'
                ? (next ? '💡 Đèn: BẬT' : '💡 Đèn: TẮT')
                : (next ? '🔄 Bơm: BẬT' : '🔄 Bơm: TẮT');
            }
          }
          btn.disabled = false;
        })
        .catch(function () { btn.disabled = false; });
    });
  });

  // ── MJPEG webcam auto-refresh (for static jpg snapshots) ───────────────
  document.querySelectorAll('[data-tray-webcam-img]').forEach(function (img) {
    var base = img.src.split('?')[0];
    // Refresh MJPEG / snapshot every 2s by busting cache
    setInterval(function () {
      var url = new URL(img.src, window.location.origin);
      url.searchParams.set('_t', Date.now());
      img.src = url.toString();
    }, 2000);
  });

  // ── HLS video streams: use hls.js if native not supported ──────────────
  document.querySelectorAll('[data-tray-webcam-video]').forEach(function (video) {
    if (video.canPlayType('application/vnd.apple.mpegurl')) return; // Safari native
    if (typeof Hls !== 'undefined' && Hls.isSupported()) {
      var hls = new Hls({ lowLatencyMode: true });
      hls.loadSource(video.src);
      hls.attachMedia(video);
    }
  });

  // ── Bootstrap: initial fetch + polling ─────────────────────────────────
  var trayCount = document.querySelectorAll('[data-tray-lane]').length;
  for (var i = 0; i < trayCount; i++) {
    (function (index) {
      var lane = document.querySelector('[data-tray-lane="' + index + '"]');
      if (!lane || !lane.querySelector('[data-tray-sensors]')) return; // skip unconfigured trays
      fetchTray(index);
      setInterval(function () { fetchTray(index); }, INTERVAL + index * 300); // stagger
    }(i));
  }

}());
</script>
<?php
// HLS.js CDN — only load when any tray has .m3u8 webcam
$needs_hlsjs = false;
foreach ($trays as $tray) {
    if (str_contains(trim((string) ($tray['webcam_url'] ?? '')), '.m3u8')) {
        $needs_hlsjs = true;
        break;
    }
}
if ($needs_hlsjs) : ?>
<script src="https://cdn.jsdelivr.net/npm/hls.js@1/dist/hls.min.js"></script>
<?php endif; ?>
