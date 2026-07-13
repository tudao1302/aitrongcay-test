<?php
if (! defined('ABSPATH')) { exit; }
?>
<style>
.eco-top-bell {background: #252824; border: 1px solid rgba(255,255,255,0.05); color: #9da89f; width: 42px; height: 42px; border-radius: 50%; display: grid; place-items: center; cursor: pointer; position: relative; transition: .2s; flex-shrink: 0;}
.eco-top-bell:hover { background: #323531; color: #fff; }
.eco-top-bell[data-has-new="true"] { color: #f5a623; }
.eco-bell-dot { position: absolute; top: 8px; right: 10px; width: 8px; height: 8px; border-radius: 50%; background: #ff4757; display: none; box-shadow: 0 0 0 2px #121411; }
.eco-top-bell[data-has-new="true"] .eco-bell-dot { display: block; animation: ping 2s cubic-bezier(0, 0, 0.2, 1) infinite; }
@keyframes ping { 0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 71, 87, 0.7); } 70% { transform: scale(1.5); box-shadow: 0 0 0 6px rgba(255, 71, 87, 0); } 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 71, 87, 0); } }
.eco-noti-popup { position: absolute; top: 74px; right: 80px; width: 340px; background: rgba(26,28,25,.96); border: 1px solid rgba(255,255,255,.06); border-radius: 22px; padding: 0; box-shadow: 0 24px 52px rgba(0,0,0,.4); z-index: 1000; overflow: hidden; display: flex; flex-direction: column; max-height: 400px; text-align: left; }
.eco-noti-popup[hidden] { display: none; }
.eco-noti-header { display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; border-bottom: 1px solid rgba(255,255,255,.05); }
.eco-noti-header h4 { margin: 0; font-size: 16px; color: #fff; font-family: 'Manrope', sans-serif;}
.eco-noti-list { overflow-y: auto; flex: 1; padding: 8px; }
.eco-noti-list::-webkit-scrollbar { width: 4px; }
.eco-noti-list::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }
.eco-noti-item { padding: 12px; border-radius: 12px; transition: .2s; margin-bottom: 4px; cursor: pointer; text-decoration: none; display: block; color: #e3e3de; }
.eco-noti-item:hover { background: rgba(51,53,50,.56); }
.eco-noti-item.unread { background: rgba(111,219,168,.08); border-left: 3px solid #6fdba8; }
.eco-noti-title { font-weight: bold; font-size: 14px; margin-bottom: 4px; color: #fff; }
.eco-noti-body { font-size: 12px; color: #a9b5ab; line-height: 1.4; }
.eco-noti-time { font-size: 11px; color: #7a827b; margin-top: 6px; }
@media (max-width:820px) {
  .eco-noti-popup { position: fixed; top: 70px; right: 16px; left: 16px; width: calc(100vw - 32px); }
}
</style>
<button class="eco-top-bell" id="eco-notification-bell" data-has-new="false" title="Thông báo">
  <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
  <span class="eco-bell-dot"></span>
</button>
<div class="eco-noti-popup" id="eco-noti-popup" hidden>
  <div class="eco-noti-header">
    <h4>Thông báo</h4>
    <button type="button" id="eco-noti-mark-read" style="background:none;border:none;color:#6fdba8;cursor:pointer;font-size:12px;padding:0;">Đánh dấu đã đọc</button>
  </div>
  <div class="eco-noti-list" id="eco-noti-list">
     <div style="padding: 16px; text-align:center; color:#999; font-size: 13px;">Đang tải...</div>
  </div>
</div>
<script>
(function(){
  var notiTrigger = document.getElementById('eco-notification-bell');
  var notiPopup = document.getElementById('eco-noti-popup');

  function closeNoti(){ if(notiPopup) { notiPopup.hidden=true; } }

  if (notiTrigger && notiPopup) {
      notiTrigger.addEventListener('click', function(e){ e.preventDefault(); e.stopPropagation(); var open=notiPopup.hidden===false; notiPopup.hidden=open; });
  }

  document.addEventListener('click', function(e){ 
      if(notiPopup && !notiPopup.hidden && !notiPopup.contains(e.target) && e.target!==notiTrigger && !notiTrigger.contains(e.target)){ closeNoti(); } 
  });
  document.addEventListener('keydown', function(e){ if(e.key==='Escape') { closeNoti(); } });

  // Notification Polling Logic
  var ajaxUrl = "<?php echo esc_url(admin_url('admin-ajax.php')); ?>";
  if (typeof aitrongcayTheme !== 'undefined' && aitrongcayTheme.ajaxUrl) {
      ajaxUrl = aitrongcayTheme.ajaxUrl;
  }

  if (notiTrigger) {
      function fetchNotifications() {
          fetch(ajaxUrl + '?action=aitrongcay_get_notifications', {cache: 'no-store'})
              .then(r => r.json())
              .then(res => {
                  if (res.success && res.data) {
                      updateNotificationUI(res.data);
                  }
              });
      }

      function timeAgo(ts) {
          var seconds = Math.floor(Date.now()/1000) - ts;
          if (seconds < 60) return "Vừa xong";
          if (seconds < 3600) return Math.floor(seconds/60) + " phút trước";
          if (seconds < 86400) return Math.floor(seconds/3600) + " giờ trước";
          return Math.floor(seconds/86400) + " ngày trước";
      }

      function updateNotificationUI(data) {
          var count = data.unread_count || 0;
          if (count > 0) {
              notiTrigger.setAttribute('data-has-new', 'true');
          } else {
              notiTrigger.setAttribute('data-has-new', 'false');
          }
          
          var list = document.getElementById('eco-noti-list');
          if (!list) return;

          if (data.notifications && data.notifications.length > 0) {
              var html = '';
              data.notifications.forEach(n => {
                  var cls = n.read ? 'eco-noti-item' : 'eco-noti-item unread';
                  var href = n.link ? n.link : '#';
                  html += '<a href="'+href+'" class="'+cls+'">';
                  html += '<div class="eco-noti-title">'+n.title+'</div>';
                  html += '<div class="eco-noti-body">'+n.message+'</div>';
                  html += '<div class="eco-noti-time">'+timeAgo(n.time)+'</div>';
                  html += '</a>';
              });
              list.innerHTML = html;
          } else {
              list.innerHTML = '<div style="padding: 16px; text-align:center; color:#999; font-size: 13px;">Chưa có thông báo nào.</div>';
          }
      }

      var markReadBtn = document.getElementById('eco-noti-mark-read');
      if (markReadBtn) {
          markReadBtn.addEventListener('click', function(e) {
              e.preventDefault();
              e.stopPropagation();
              fetch(ajaxUrl + '?action=aitrongcay_mark_notifications_read')
                  .then(r => r.json())
                  .then(res => {
                      fetchNotifications();
                  });
          });
      }

      fetchNotifications();
      setInterval(fetchNotifications, 60000);
  }
})();
</script>
