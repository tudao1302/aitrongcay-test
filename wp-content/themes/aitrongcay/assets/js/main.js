const siteConfig = window.aitrongcayTheme || {};
const rootUrl = siteConfig.rootUrl || '/';
const ajaxUrl = siteConfig.ajaxUrl || `${window.location.origin}/wp-admin/admin-ajax.php`;
const ajaxNonce = siteConfig.ajaxNonce || '';
const activeGardenKey = window.AITR_GARDEN_KEY || siteConfig.gardenKey || '';
const navItems = Array.isArray(siteConfig.nav) ? siteConfig.nav : [
  { label: 'Trang chủ', url: rootUrl },
  { label: 'Giới thiệu', url: `${rootUrl.replace(/\/$/, '')}/cach-hoat-dong/` },
  { label: 'Chợ quê', url: `${rootUrl.replace(/\/$/, '')}/cho-que/` },
];
const mobileActionItems = [
  { label: 'Đăng nhập', url: `${rootUrl.replace(/\/$/, '')}/dang-nhap/`, tone: 'plain' },
  { label: 'Xem trải nghiệm khu vườn', url: `${rootUrl.replace(/\/$/, '')}/portal/`, tone: 'primary' },
];
const gardenAssistantEnabled = !!siteConfig.gardenAssistantEnabled;

const mobileToggle = document.querySelector('[data-mobile-toggle]');
const mobilePanel = document.querySelector('[data-mobile-panel]');

if (mobileToggle && mobilePanel && !mobilePanel.dataset.initialized) {
  mobilePanel.innerHTML = `
    <div class="mobile-panel-inner">
      <div class="mobile-panel-links">
        ${navItems.map(item => `<a href="${item.url}">${item.label}</a>`).join('')}
      </div>
      <div class="mobile-panel-actions">
        ${mobileActionItems.map(item => `<a href="${item.url}" class="mobile-action-link ${item.tone}">${item.label}</a>`).join('')}
      </div>
    </div>`;
  mobilePanel.dataset.initialized = 'true';

  mobileToggle.addEventListener('click', () => {
    const opened = mobilePanel.style.display === 'block';
    mobilePanel.style.display = opened ? 'none' : 'block';
  });
}



const currentPath = window.location.pathname.replace(/\/$/, '') || '/';
document.querySelectorAll('.nav-menu a').forEach(link => {
  const href = link.getAttribute('href') || '';
  try {
    const linkPath = new URL(href, window.location.origin).pathname.replace(/\/$/, '') || '/';
    if (linkPath === currentPath) link.classList.add('active');
  } catch (error) {
    // ignore malformed URLs in prototype content
  }
});

document.querySelectorAll('[data-current-year]').forEach(el => {
  el.textContent = new Date().getFullYear();
});

const emphasizeBrandName = () => {
  const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, {
    acceptNode(node) {
      if (!node.nodeValue || !node.nodeValue.includes('Ai trồng cây')) return NodeFilter.FILTER_REJECT;
      const parent = node.parentElement;
      if (!parent) return NodeFilter.FILTER_REJECT;
      if (parent.closest('.brand-mark')) return NodeFilter.FILTER_REJECT;
      const tag = parent.tagName;
      if (['SCRIPT', 'STYLE', 'TEXTAREA', 'INPUT', 'OPTION'].includes(tag)) return NodeFilter.FILTER_REJECT;
      return NodeFilter.FILTER_ACCEPT;
    }
  });

  const targets = [];
  while (walker.nextNode()) targets.push(walker.currentNode);

  targets.forEach(node => {
    const text = node.nodeValue || '';
    if (!text.includes('Ai trồng cây')) return;
    const wrapper = document.createElement('span');
    wrapper.innerHTML = text.replaceAll('Ai trồng cây', '<span class="brand-mark brand-mark-inline">Ai trồng cây</span>');
    node.parentNode.replaceChild(wrapper, node);
  });
};

if (document.body) emphasizeBrandName();

const potCards = document.querySelectorAll('.pot-card');
potCards.forEach(card => {
  card.addEventListener('toggle', () => {
    if (!card.open) return;
    potCards.forEach(other => {
      if (other !== card) other.open = false;
    });
  });
});

const gardenAiForms = document.querySelectorAll('[data-garden-ai-form]');
let activeAiSessionId = Number(document.querySelector('[data-garden-ai-form]')?.getAttribute('data-session-id') || '0');
let aiSessionCache = [];
const renderAiSessionItems = (sessions = []) => {
  aiSessionCache = Array.isArray(sessions) ? sessions.slice() : [];
  document.querySelectorAll('[data-ai-session-list]').forEach(list => {
    list.innerHTML = sessions.map(session => {
      const id = Number(session.id || 0);
      const active = id === activeAiSessionId ? ' is-active' : '';
      let title = (session.last_user_message || '').trim();
      if (!title) title = 'Cuộc trò chuyện mới';
      else if (title.split(' ').length > 8) title = title.split(' ').slice(0, 8).join(' ') + '...';
      
      return `<button class="ai-agent-session-item${active}" type="button" data-ai-session-item data-session-id="${id}" data-session-title="${title.replace(/"/g, '&quot;')}"><strong style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block">${title}</strong></button>`;
    }).join('');
  });
};
const appendGardenAiMessage = (log, role, text) => {
  if (!log) return;
  const row = document.createElement('div');
  row.className = `garden-ai-message ${role}`;
  const bubble = document.createElement('div');
  bubble.className = 'garden-ai-bubble';
  bubble.textContent = text;
  row.appendChild(bubble);
  log.appendChild(row);
  log.scrollTop = log.scrollHeight;
};

const formatGardenAiLatency = (ms) => {
  const value = Number(ms || 0);
  if (!Number.isFinite(value) || value <= 0) return '';
  if (value < 1000) return `${Math.round(value)} ms`;
  return `${(value / 1000).toFixed(value >= 10000 ? 1 : 2)} giây`;
};

const setGardenAiFormsBusy = (busy, waitingText = 'Cindy đang trả lời, anh chờ em một chút nhé.') => {
  document.querySelectorAll('[data-garden-ai-form]').forEach(form => {
    const input = form.querySelector('[data-garden-ai-input]');
    const submit = form.querySelector('[data-garden-ai-submit]');
    const iconButtons = form.querySelectorAll('.ai-agent-design-icon, [data-ai-fill]');
    const meta = form.querySelector('[data-garden-ai-meta]');
    form.classList.toggle('is-waiting', !!busy);
    if (input) {
      input.disabled = !!busy;
      input.setAttribute('aria-disabled', busy ? 'true' : 'false');
    }
    iconButtons.forEach(button => {
      button.disabled = !!busy;
      button.setAttribute('aria-disabled', busy ? 'true' : 'false');
    });
    if (submit) {
      const defaultLabel = submit.getAttribute('data-default-label') || submit.textContent || 'Gửi';
      submit.setAttribute('data-default-label', defaultLabel);
      submit.disabled = !!busy;
      submit.classList.toggle('is-waiting', !!busy);
      submit.setAttribute('aria-busy', busy ? 'true' : 'false');
      submit.setAttribute('aria-label', busy ? waitingText : defaultLabel);
      submit.innerHTML = busy ? '<span class="ai-agent-send-spinner" aria-hidden="true"></span>' : defaultLabel;
    }
    if (meta && busy) meta.textContent = waitingText;
  });
};

const syncGardenAiWidgets = (messages, status, meta) => {
  document.querySelectorAll('[data-garden-ai-log]').forEach(log => {
    log.innerHTML = '';
    messages.forEach(item => appendGardenAiMessage(log, item.role || 'assistant', item.text || ''));
  });
  document.querySelectorAll('[data-garden-ai-status]').forEach(el => {
    el.textContent = status || 'Adapter-ready';
  });
  document.querySelectorAll('[data-garden-ai-meta]').forEach(el => {
    el.textContent = meta || 'Garden assistant session đang ở chế độ adapter-ready.';
  });
};

const loadGardenAiSessions = async () => {
  const body = new URLSearchParams({ action: 'aitrongcay_ai_list_sessions', nonce: ajaxNonce, garden_key: activeGardenKey });
  const response = await fetch(ajaxUrl, {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
    body,
  });
  const result = await response.json();
  if (!result.success) throw new Error(result.data?.message || 'Không tải được session.');
  renderAiSessionItems(result.data?.sessions || []);
  return result.data?.sessions || [];
};

const upsertActiveSessionPreview = (patch = {}) => {
  if (!activeAiSessionId) return;
  const nowIso = new Date().toISOString().slice(0, 19).replace('T', ' ');
  const next = Array.isArray(aiSessionCache) ? aiSessionCache.slice() : [];
  const index = next.findIndex(item => Number(item.id || 0) === activeAiSessionId);
  const merged = {
    id: activeAiSessionId,
    title: patch.title || 'Phiên chat',
    last_user_message: patch.last_user_message || next[index]?.last_user_message || '',
    last_message_at: patch.last_message_at || nowIso,
    updated_at: patch.updated_at || nowIso,
    ...((index >= 0 ? next[index] : {})),
    ...patch,
  };
  if (index >= 0) next.splice(index, 1);
  next.unshift(merged);
  renderAiSessionItems(next);
};

const loadGardenAiSessionDetail = async (sessionId) => {
  const body = new URLSearchParams({ action: 'aitrongcay_ai_load_session', nonce: ajaxNonce, session_id: String(sessionId) });
  const response = await fetch(ajaxUrl, {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
    body,
  });
  const result = await response.json();
  if (!result.success) throw new Error(result.data?.message || 'Không mở được session.');
  activeAiSessionId = Number(result.data?.session?.id || 0);
  document.querySelectorAll('[data-garden-ai-form]').forEach(form => form.setAttribute('data-session-id', String(activeAiSessionId || 0)));
  document.querySelectorAll('[data-ai-session-title]').forEach(el => { el.textContent = result.data?.session?.title || 'Phiên chat'; });
  syncGardenAiWidgets(result.data?.messages || [], 'Adapter-ready', 'Đã mở lại lịch sử phiên chat.');
  await loadGardenAiSessions();
};

const createGardenAiSession = async () => {
  const body = new URLSearchParams({ action: 'aitrongcay_ai_create_session', nonce: ajaxNonce, garden_key: activeGardenKey });
  const response = await fetch(ajaxUrl, {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
    body,
  });
  const result = await response.json();
  if (!result.success) throw new Error(result.data?.message || 'Không tạo được session mới.');
  activeAiSessionId = Number(result.data?.session?.id || 0);
  document.querySelectorAll('[data-garden-ai-form]').forEach(form => form.setAttribute('data-session-id', String(activeAiSessionId || 0)));
  document.querySelectorAll('[data-ai-session-title]').forEach(el => { el.textContent = result.data?.session?.title || 'Phiên chat mới'; });
  syncGardenAiWidgets([], 'Adapter-ready', 'Phiên chat mới đã sẵn sàng.');
  await loadGardenAiSessions();
};

const sendGardenAiMessage = async (message) => {
  if (!gardenAssistantEnabled) return;
  const body = new URLSearchParams({ action: 'aitrongcay_garden_assistant_chat', nonce: ajaxNonce, message, garden_key: activeGardenKey, session_id: String(activeAiSessionId || 0) });
  const response = await fetch(ajaxUrl, {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
    body,
  });
  const result = await response.json();
  if (!result.success) throw new Error(result.data?.message || 'Không gửi được tin nhắn cho Trợ lý AI.');
  activeAiSessionId = Number(result.data?.sessionId || activeAiSessionId || 0);
  document.querySelectorAll('[data-garden-ai-form]').forEach(form => form.setAttribute('data-session-id', String(activeAiSessionId || 0)));
  document.querySelectorAll('[data-ai-session-title]').forEach(el => {
    if (result.data?.sessionTitle) el.textContent = result.data.sessionTitle;
  });
  const latencyLabel = formatGardenAiLatency(result.data?.latencyMs || 0);
  const statusText = result.data?.agentStatus || '';
  syncGardenAiWidgets(
    result.data?.messages || [],
    result.data?.mode || 'Adapter-ready',
    latencyLabel ? `${statusText}${statusText ? ' · ' : ''}${latencyLabel}` : statusText
  );
  upsertActiveSessionPreview({ title: result.data?.sessionTitle || 'Phiên chat', last_user_message: message });
};

document.addEventListener('click', (event) => {
  const item = event.target.closest('[data-ai-session-item]');
  if (item) {
    const sessionId = Number(item.getAttribute('data-session-id') || '0');
    if (sessionId > 0) {
      loadGardenAiSessionDetail(sessionId).catch(error => {
        document.querySelectorAll('[data-garden-ai-meta]').forEach(el => { el.textContent = error.message || 'Không mở được session.'; });
      });
    }
    return;
  }
  const newBtn = event.target.closest('[data-ai-session-new]');
  if (newBtn) {
    createGardenAiSession().catch(error => {
      document.querySelectorAll('[data-garden-ai-meta]').forEach(el => { el.textContent = error.message || 'Không tạo được session mới.'; });
    });
  }
});

if (document.querySelector('[data-ai-session-list]')) {
  loadGardenAiSessions().catch(() => {});
}

document.querySelectorAll('[data-garden-ai-prompt]').forEach(button => {
  button.addEventListener('click', async () => {
    const message = (button.dataset.gardenAiPrompt || '').trim();
    if (!message) return;
    try {
      await sendGardenAiMessage(message);
    } catch (error) {
      document.querySelectorAll('[data-garden-ai-meta]').forEach(el => { el.textContent = error.message || 'Không gửi được tin nhắn.'; });
    }
  });
});

const createFirstPot = async (plantName, createdAt) => {
  const body = new URLSearchParams({ action: 'aitrongcay_create_first_pot', nonce: ajaxNonce, plant_name: plantName, created_at: createdAt, garden_key: activeGardenKey });
  const response = await fetch(ajaxUrl, {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
    body,
  });
  const result = await response.json();
  if (!result.success) throw new Error(result.data?.message || 'Không tạo được khay cây.');
  return result.data || {};
};

gardenAiForms.forEach(form => {
  const input = form.querySelector('[data-garden-ai-input]');
  const isOnboarding = form.hasAttribute('data-ai-onboarding-form');

  if (input) {
    input.addEventListener('keydown', (event) => {
      if (event.key !== 'Enter') return;
      if (event.altKey) return;
      event.preventDefault();
      form.requestSubmit();
    });
  }

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const input = form.querySelector('[data-garden-ai-input]');
    const createdAtInput = form.querySelector('[data-garden-pot-created-at]');
    const submit = form.querySelector('[data-garden-ai-submit]');
    const defaultSubmitText = submit ? (submit.getAttribute('data-default-label') || submit.textContent || 'Gửi') : 'Gửi';
    const message = (input?.value || '').trim();
    const createdAt = (createdAtInput?.value || '').trim();
    if (!message) return;
    if (isOnboarding && !createdAt) {
      document.querySelectorAll('[data-garden-ai-meta]').forEach(el => { el.textContent = 'Anh cần nhập ngày khởi tạo khay trước khi tạo mới.'; });
      createdAtInput?.focus();
      return;
    }
    if (input) {
      input.value = '';
      input.style.height = '';
    }
    if (submit) {
      submit.setAttribute('data-default-label', defaultSubmitText);
    }
    setGardenAiFormsBusy(true, isOnboarding ? 'Cindy đang tạo khay cho anh, mình chờ em một chút nhé.' : 'Cindy đang trả lời, anh chờ em một chút nhé.');
    try {
      appendGardenAiMessage(form.closest('[data-garden-ai-chat]')?.querySelector('[data-garden-ai-log]'), 'user', message);
      const startedAt = performance.now();
      if (isOnboarding) {
        const result = await createFirstPot(message, createdAt);
        const elapsed = performance.now() - startedAt;
        document.querySelectorAll('[data-garden-ai-log]').forEach(log => appendGardenAiMessage(log, 'assistant', result.message || 'Em đã tạo khay mới.'));
        document.querySelectorAll('[data-garden-ai-meta]').forEach(el => { el.textContent = `Cindy đã phản hồi sau ${formatGardenAiLatency(elapsed)}. Đang mở dashboard vườn...`; });
        console.info('[garden-ai] onboarding round-trip', { elapsedMs: Math.round(elapsed), messageLength: message.length, gardenKey: activeGardenKey || null });
        window.setTimeout(() => {
          window.location.href = result.redirect || `${rootUrl.replace(/\/$/, '')}/portal/dashboard-2/`;
        }, 900);
        return;
      }
      await sendGardenAiMessage(message);
      const elapsed = performance.now() - startedAt;
      document.querySelectorAll('[data-garden-ai-meta]').forEach(el => { el.textContent = `Cindy đã phản hồi sau ${formatGardenAiLatency(elapsed)}.`; });
      console.info('[garden-ai] chat round-trip', { elapsedMs: Math.round(elapsed), messageLength: message.length, sessionId: activeAiSessionId || 0, gardenKey: activeGardenKey || null });
    } catch (error) {
      if (input && !input.value) input.value = message;
      document.querySelectorAll('[data-garden-ai-meta]').forEach(el => { el.textContent = error.message || 'Không gửi được tin nhắn.'; });
    } finally {
      setGardenAiFormsBusy(false);
    }
  });
});

const noteAreas = document.querySelectorAll('[data-autosave-note]');
noteAreas.forEach(area => {
  const potCode = area.dataset.noteKey || area.id || 'default';
  const statusWrap = area.parentElement;
  const status = statusWrap?.querySelector('[data-note-status]');
  const saveBtn = statusWrap?.querySelector('[data-save-note]');
  let timer = null;
  let inFlight = false;
  let queuedValue = null;
  let lastSavedValue = area.value || '';

  const setStatus = (text, cls = '') => {
    if (!status) return;
    status.textContent = text;
    status.classList.remove('saving', 'saved', 'error');
    if (cls) status.classList.add(cls);
  };

  const pushSave = async (value) => {
    if (!activeGardenKey || !potCode) {
      setStatus('Thiếu thông tin khu vườn', 'error');
      return;
    }
    inFlight = true;
    setStatus('Đang lưu...', 'saving');
    try {
      const body = new URLSearchParams({
        action: 'aitrongcay_save_pot_note',
        nonce: ajaxNonce,
        garden_key: activeGardenKey,
        pot_code: potCode,
        note_text: value,
      });
      const response = await fetch(ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body,
      });
      const result = await response.json();
      if (!result?.success) throw new Error(result?.data?.message || 'Chưa lưu được');
      lastSavedValue = value;
      setStatus('Đã lưu vào nhật ký vườn', 'saved');
    } catch (err) {
      area.dataset.lastSavedValue = lastSavedValue;
      setStatus(err?.message || 'Chưa lưu được', 'error');
    } finally {
      inFlight = false;
      if (queuedValue !== null && queuedValue !== lastSavedValue) {
        const nextValue = queuedValue;
        queuedValue = null;
        pushSave(nextValue);
      }
    }
  };

  const scheduleSave = () => {
    const nextValue = area.value;
    if (nextValue === lastSavedValue) {
      setStatus('Đã lưu', 'saved');
      return;
    }
    if (timer) clearTimeout(timer);
    setStatus('Đang lưu...', 'saving');
    timer = setTimeout(() => {
      if (inFlight) {
        queuedValue = area.value;
        return;
      }
      pushSave(area.value);
    }, 500);
  };

  area.addEventListener('input', scheduleSave);

  if (saveBtn) {
    saveBtn.addEventListener('click', () => {
      if (timer) clearTimeout(timer);
      if (inFlight) {
        queuedValue = area.value;
        return;
      }
      pushSave(area.value);
    });
  }
});

const viewModeStates = [
  { key: 'private', label: 'Chế độ view riêng tư', icon: '🔒', cls: 'is-private' },
  { key: 'friend', label: 'Friend view', icon: '👥', cls: 'is-friend' },
  { key: 'public', label: 'Public view', icon: '🌍', cls: 'is-public' },
];

const renderViewModeToggle = (viewModeToggle, key) => {
  if (!viewModeToggle) return;
  const label = viewModeToggle.querySelector('.view-mode-label');
  const icon = viewModeToggle.querySelector('.view-mode-icon');
  const state = viewModeStates.find(item => item.key === key) || viewModeStates[0];
  viewModeStates.forEach(item => viewModeToggle.classList.remove(item.cls));
  viewModeToggle.classList.add(state.cls);
  viewModeToggle.dataset.state = state.key;
  if (label) label.textContent = state.label;
  if (icon) icon.textContent = state.icon;
};

document.querySelectorAll('[data-view-mode-toggle]').forEach(toggle => {
  renderViewModeToggle(toggle, toggle.dataset.state || 'private');
});

document.addEventListener('click', (event) => {
  const toggle = event.target.closest('[data-view-mode-toggle]');
  if (!toggle) return;
  const current = toggle.dataset.state || 'private';
  const currentIndex = viewModeStates.findIndex(item => item.key === current);
  const next = viewModeStates[(currentIndex + 1) % viewModeStates.length];
  renderViewModeToggle(toggle, next.key);
});

document.querySelectorAll('[data-share-market-post]').forEach(btn => {
  btn.addEventListener('click', async () => {
    const countNode = btn.querySelector('[data-share-count]');
    const current = Number(countNode?.textContent || 0);
    const url = window.location.href;
    const title = document.title;
    try {
      if (navigator.share) {
        await navigator.share({ title, url });
      } else if (navigator.clipboard) {
        await navigator.clipboard.writeText(url);
      }
      if (countNode) countNode.textContent = String(current + 1);
    } catch (err) {
      // no-op
    }
  });
});

const shelf = document.querySelector('[data-tool-shelf]');
if (shelf) {
  const items = Array.from(shelf.querySelectorAll('[data-shelf-item]'));
  const prev = document.querySelector('[data-shelf-prev]');
  const next = document.querySelector('[data-shelf-next]');
  let start = 0;
  const pageSize = 4;

  const renderShelf = () => {
    items.forEach((item, index) => {
      item.style.display = index >= start && index < start + pageSize ? '' : 'none';
    });
    if (prev) prev.disabled = start === 0;
    if (next) next.disabled = start + pageSize >= items.length;
  };

  prev?.addEventListener('click', () => {
    start = Math.max(0, start - pageSize);
    renderShelf();
  });
  next?.addEventListener('click', () => {
    if (start + pageSize < items.length) start += pageSize;
    renderShelf();
  });
  renderShelf();
}

document.querySelectorAll('[data-fake-submit]').forEach(form => {
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const title = form.dataset.successTitle || 'Đã ghi nhận thông tin của anh/chị.';
    const body = form.dataset.successBody || 'Đội ngũ Ai trồng cây sẽ liên hệ lại để tư vấn gói phù hợp trong thời gian sớm nhất.';
    const nextUrl = form.dataset.nextUrl || '';
    const nextLabel = form.dataset.nextLabel || 'Đi tiếp';
    const notice = form.querySelector('.form-result') || document.createElement('div');
    notice.className = 'notice form-result';
    notice.style.marginTop = '16px';
    notice.innerHTML = `<strong>${title}</strong><div style="margin-top:6px">${body}</div>${nextUrl ? `<div style="margin-top:10px"><a class="btn btn-secondary" href="${nextUrl}">${nextLabel}</a></div>` : ''}`;
    form.appendChild(notice);
  });
});

const now = new Date();
document.querySelectorAll('[data-now]').forEach(el => {
  el.textContent = now.toLocaleString('vi-VN', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: '2-digit' });
});

document.querySelectorAll('[data-accordion-item]').forEach(item => {
  const button = item.querySelector('[data-accordion-button]');
  if (!button) return;
  button.addEventListener('click', () => {
    item.classList.toggle('open');
  });
});

document.querySelectorAll('[data-tabs]').forEach(wrapper => {
  const buttons = wrapper.querySelectorAll('[data-tab-button]');
  const panels = wrapper.parentElement.querySelectorAll('[data-tab-panel]');
  buttons.forEach(button => {
    button.addEventListener('click', () => {
      const target = button.dataset.tabButton;
      buttons.forEach(btn => btn.classList.toggle('active', btn === button));
      panels.forEach(panel => panel.classList.toggle('active', panel.dataset.tabPanel === target));
    });
  });
});

document.querySelectorAll('[data-progress]').forEach(bar => {
  const value = Number(bar.dataset.progress || 0);
  requestAnimationFrame(() => {
    bar.style.width = `${value}%`;
  });
});

document.querySelectorAll('[data-rotating-text]').forEach(el => {
  const items = (el.dataset.rotatingText || '').split('|').map(x => x.trim()).filter(Boolean);
  if (!items.length) return;
  let index = 0;
  el.textContent = items[0];
  setInterval(() => {
    index = (index + 1) % items.length;
    el.textContent = items[index];
  }, 2400);
});

const livecamNotice = document.querySelector('[data-livecam-notice]');
const photoLibrary = document.querySelector('[data-photo-library]');
const selectedMarketPhotos = document.querySelector('[data-market-selected]');
const marketNotice = document.querySelector('[data-market-notice]');
const livecamVideo = document.querySelector('[data-livecam]');
const publicButton = document.querySelector('[data-public-livecam]');
const publicOffButton = document.querySelector('[data-unpublic-livecam]');
const createMarketButton = document.querySelector('[data-create-market-post]');
const marketComposeModal = document.querySelector('[data-market-compose-modal]');
const marketComposeForm = document.querySelector('[data-market-compose-form]');
const marketComposeOpeners = document.querySelectorAll('[data-open-market-compose]');
const marketComposeClosers = document.querySelectorAll('[data-close-market-compose]');
const marketComposeTitle = document.querySelector('[data-market-compose-title]');
const marketComposeContent = document.querySelector('[data-market-compose-content]');
const marketComposeFiles = document.querySelector('[data-market-compose-files]');
const marketComposePreview = document.querySelector('[data-market-compose-preview]');
const marketComposeNotice = document.querySelector('[data-market-compose-notice]');
const marketComposeSubmit = document.querySelector('[data-market-compose-submit]');
const marketComposeCategory = document.querySelector('[data-market-compose-category]');
const marketComposeOfferType = document.querySelector('[data-market-compose-offer-type]');
const marketComposeQuantity = document.querySelector('[data-market-compose-quantity]');
const marketComposeArea = document.querySelector('[data-market-compose-area]');
const marketComposeAvailability = document.querySelector('[data-market-compose-availability]');
const marketComposeContact = document.querySelector('[data-market-compose-contact]');
const marketEditModal = document.querySelector('[data-market-edit-modal]');
const marketEditForm = document.querySelector('[data-market-edit-form]');
const marketEditClosers = document.querySelectorAll('[data-close-market-edit]');
const marketEditPostId = document.querySelector('[data-market-edit-post-id]');
const marketEditTitle = document.querySelector('[data-market-edit-title]');
const marketEditContent = document.querySelector('[data-market-edit-content]');
const marketEditFiles = document.querySelector('[data-market-edit-files]');
const marketEditPreview = document.querySelector('[data-market-edit-preview]');
const marketEditNotice = document.querySelector('[data-market-edit-notice]');
const marketEditSubmit = document.querySelector('[data-market-edit-submit]');
const marketEditCurrentMedia = document.querySelector('[data-market-edit-current-media]');
const marketEditCardPreview = document.querySelector('[data-market-edit-card-preview]');
const marketTitleHint = document.querySelector('[data-market-title-hint]');
const marketContentHint = document.querySelector('[data-market-content-hint]');
const marketEditCategory = document.querySelector('[data-market-edit-category]');
const marketEditOfferType = document.querySelector('[data-market-edit-offer-type]');
const marketEditQuantity = document.querySelector('[data-market-edit-quantity]');
const marketEditArea = document.querySelector('[data-market-edit-area]');
const marketEditAvailability = document.querySelector('[data-market-edit-availability]');
const marketEditContact = document.querySelector('[data-market-edit-contact]');
let activeMarketEditButton = null;
let marketEditExistingGallery = [];

const renderSelectedMarketPhotos = () => {
  if (!selectedMarketPhotos) return;
  const selected = Array.from(document.querySelectorAll('[data-market-photo]:checked')).map(input => input.value);
  selectedMarketPhotos.innerHTML = selected.length ? selected.map(value => `<span class="chip">${value}</span>`).join('') : '<span class="chip">Chưa chọn ảnh nào</span>';
};
document.querySelectorAll('[data-market-photo]').forEach(input => input.addEventListener('change', renderSelectedMarketPhotos));
renderSelectedMarketPhotos();

const showNotice = (target, html) => {
  if (!target) return;
  target.style.display = 'block';
  target.innerHTML = html;
};

const showToast = (html) => {
  const host = document.body;
  if (!host) return;
  const stack = document.querySelector('[data-app-toast-stack]') || (() => {
    const el = document.createElement('div');
    el.className = 'app-toast-stack';
    el.dataset.appToastStack = 'true';
    document.body.appendChild(el);
    return el;
  })();
  const toast = document.createElement('div');
  toast.className = 'app-toast';
  toast.innerHTML = html;
  stack.appendChild(toast);
  window.setTimeout(() => {
    toast.remove();
    if (!stack.children.length) stack.remove();
  }, 4200);
};

const syncMarketModalBodyLock = () => {
  const shouldLock = (marketComposeModal && !marketComposeModal.hidden) || (marketEditModal && !marketEditModal.hidden);
  document.body.style.overflow = shouldLock ? 'hidden' : '';
};

const setMarketComposeOpen = (open) => {
  if (!marketComposeModal) return;
  marketComposeModal.hidden = !open;
  syncMarketModalBodyLock();
};

const setMarketEditOpen = (open) => {
  if (!marketEditModal) return;
  marketEditModal.hidden = !open;
  if (!open) {
    activeMarketEditButton = null;
    marketEditExistingGallery = [];
    if (marketEditForm) marketEditForm.reset();
    if (marketEditPreview) marketEditPreview.innerHTML = '';
    if (marketEditCurrentMedia) marketEditCurrentMedia.innerHTML = '';
    if (marketEditCardPreview) marketEditCardPreview.innerHTML = '';
    if (marketEditNotice) marketEditNotice.style.display = 'none';
  }
  syncMarketModalBodyLock();
};

const renderMarketFilePreview = (input, target, emptyText = 'Chưa chọn ảnh nào') => {
  if (!target || !input) return;
  const files = Array.from(input.files || []);
  if (!files.length) {
    target.innerHTML = `<div class="market-compose-preview-empty">${emptyText}</div>`;
    return;
  }
  target.innerHTML = files.map(file => {
    const url = URL.createObjectURL(file);
    return `<div class="market-compose-thumb"><img src="${url}" alt="${file.name}"><span>${file.name}</span></div>`;
  }).join('');
};

const renderMarketComposePreview = () => {
  renderMarketFilePreview(marketComposeFiles, marketComposePreview, 'Chưa chọn ảnh nào');
};

const trimMarketPreviewText = (value, max = 180) => {
  const text = (value || '').trim();
  return text.length > max ? `${text.slice(0, max)}...` : text;
};

const renderMarketEditHints = () => {
  if (marketTitleHint) {
    const len = (marketEditTitle?.value || '').trim().length;
    marketTitleHint.textContent = len ? `${len}/140` : '';
  }
  if (marketContentHint) {
    const len = (marketEditContent?.value || '').trim().length;
    marketContentHint.textContent = len ? `${len} ký tự` : '';
  }
};

const collectMarketStructuredFormData = (mode = 'edit') => {
  const source = mode === 'compose'
    ? {
        category: marketComposeCategory,
        offer_type: marketComposeOfferType,
        quantity: marketComposeQuantity,
        area: marketComposeArea,
        availability: marketComposeAvailability,
        contact_text: marketComposeContact,
      }
    : {
        category: marketEditCategory,
        offer_type: marketEditOfferType,
        quantity: marketEditQuantity,
        area: marketEditArea,
        availability: marketEditAvailability,
        contact_text: marketEditContact,
      };
  return {
    category: (source.category?.value || '').trim(),
    offer_type: (source.offer_type?.value || '').trim(),
    quantity: (source.quantity?.value || '').trim(),
    area: (source.area?.value || '').trim(),
    availability: (source.availability?.value || '').trim(),
    contact_text: (source.contact_text?.value || '').trim(),
  };
};

const marketStructuredSummaryLine = (data) => [data.offer_type, data.quantity, data.area].filter(Boolean).join(' • ');

const validateMarketPostInput = ({ title, content, structured }) => {
  if (!title || !content) return 'Anh/chị vui lòng nhập đủ tiêu đề và nội dung tin đăng.';
  if (title.length < 12) return 'Tiêu đề hơi ngắn. Anh/chị nên viết rõ hơn để người xem hiểu ngay tin đăng nói về gì.';
  if (content.length < 24) return 'Nội dung còn quá ngắn. Anh/chị nên thêm số lượng, khu vực hoặc cách liên hệ.';
  if (!structured.category) return 'Anh/chị nên chọn danh mục cho tin đăng để bài rõ ràng hơn.';
  if (!structured.offer_type) return 'Anh/chị nên chọn hình thức như Bán, Trao đổi hoặc Chia sẻ.';
  return '';
};

const renderMarketEditCardPreview = () => {
  if (!marketEditCardPreview) return;
  renderMarketEditHints();
  const title = (marketEditTitle?.value || '').trim() || 'Tiêu đề tin đăng sẽ hiện ở đây';
  const content = trimMarketPreviewText(marketEditContent?.value || '', 180) || 'Nội dung mô tả sẽ hiện ở đây để anh/chị xem trước card ngoài danh sách.';
  const structured = collectMarketStructuredFormData();
  const summaryLine = marketStructuredSummaryLine(structured);
  const firstNewFile = Array.from(marketEditFiles?.files || [])[0];
  const newPreviewUrl = firstNewFile ? URL.createObjectURL(firstNewFile) : '';
  const currentImage = newPreviewUrl || marketEditExistingGallery[0]?.url || activeMarketEditButton?.dataset.marketImage || '';
  marketEditCardPreview.innerHTML = `
    <div class="market-edit-preview-shell">
      <div class="small subtle" style="margin-bottom:10px">Bản xem trước</div>
      <article class="market-row-card market-row-card-preview">
        <div class="market-row-media media-frame media-frame-16x9">
          <img class="media-thumb media-fit-cover" src="${currentImage || '/wp-content/themes/aitrongcay/assets/images/market-harvest.svg'}" alt="Xem trước ảnh tin đăng">
        </div>
        <div class="market-row-text">
          <div class="market-card-topline"><span class="kicker">Bản xem trước</span><span class="small subtle">Ngay lúc này</span></div>
          ${summaryLine ? `<div class="market-card-meta"><span>${summaryLine}</span></div>` : ''}
          <h3>${title}</h3>
          <div class="market-card-meta">${structured.contact_text ? `<span>Liên hệ: ${structured.contact_text}</span>` : ''}</div>
          <div class="entry-content market-copy-clean"><p>${content}</p></div>
        </div>
      </article>
    </div>`;
};

const renderMarketExistingGallery = () => {
  if (!marketEditCurrentMedia) return;
  if (!marketEditExistingGallery.length) {
    marketEditCurrentMedia.innerHTML = '<div class="market-compose-preview-empty">Tin này hiện chưa có ảnh minh họa.</div>';
    return;
  }
  marketEditCurrentMedia.innerHTML = `
    <div class="market-edit-current-card">
      <div class="small subtle" style="margin-bottom:8px">Ảnh hiện tại — có thể xóa riêng từng ảnh hoặc đổi thứ tự</div>
      <div class="market-edit-gallery-grid">
        ${marketEditExistingGallery.map((item, index) => `
          <div class="market-edit-gallery-item" data-market-existing-id="${item.id}" draggable="true">
            <div class="market-edit-gallery-badge-row"><span class="chip ${index === 0 ? 'market-cover-chip' : ''}">${index === 0 ? 'Ảnh bìa' : `Ảnh ${index + 1}`}</span>${index !== 0 ? `<button type="button" class="btn btn-ghost" data-market-set-cover="${item.id}">Đặt làm ảnh bìa</button>` : ''}</div>
            <div class="media-frame media-frame-16x9"><img class="media-thumb media-fit-cover" src="${item.url}" alt="${item.title || 'Ảnh tin đăng'}"></div>
            <div class="market-edit-gallery-actions">
              <button type="button" class="btn btn-ghost" data-market-move-left="${item.id}" ${index === 0 ? 'disabled' : ''}>←</button>
              <button type="button" class="btn btn-ghost" data-market-move-right="${item.id}" ${index === marketEditExistingGallery.length - 1 ? 'disabled' : ''}>→</button>
              <button type="button" class="btn btn-ghost market-edit-remove-btn" data-market-remove-existing="${item.id}">Xóa</button>
            </div>
          </div>`).join('')}
      </div>
    </div>`;

  marketEditCurrentMedia.querySelectorAll('[data-market-remove-existing]').forEach(button => {
    button.addEventListener('click', () => {
      const id = Number(button.dataset.marketRemoveExisting || 0);
      marketEditExistingGallery = marketEditExistingGallery.filter(item => Number(item.id) !== id);
      renderMarketExistingGallery();
      renderMarketEditCardPreview();
    });
  });

  marketEditCurrentMedia.querySelectorAll('[data-market-set-cover]').forEach(button => {
    button.addEventListener('click', () => {
      const id = Number(button.dataset.marketSetCover || 0);
      const index = marketEditExistingGallery.findIndex(item => Number(item.id) === id);
      if (index > 0) {
        const [picked] = marketEditExistingGallery.splice(index, 1);
        marketEditExistingGallery.unshift(picked);
        renderMarketExistingGallery();
        renderMarketEditCardPreview();
      }
    });
  });

  let draggedId = null;
  marketEditCurrentMedia.querySelectorAll('[data-market-existing-id]').forEach(item => {
    item.addEventListener('dragstart', () => {
      draggedId = Number(item.dataset.marketExistingId || 0);
      item.classList.add('is-dragging');
    });
    item.addEventListener('dragend', () => {
      draggedId = null;
      item.classList.remove('is-dragging');
    });
    item.addEventListener('dragover', (event) => {
      event.preventDefault();
    });
    item.addEventListener('drop', () => {
      const targetId = Number(item.dataset.marketExistingId || 0);
      if (!draggedId || draggedId === targetId) return;
      const fromIndex = marketEditExistingGallery.findIndex(entry => Number(entry.id) === draggedId);
      const toIndex = marketEditExistingGallery.findIndex(entry => Number(entry.id) === targetId);
      if (fromIndex < 0 || toIndex < 0) return;
      const [moved] = marketEditExistingGallery.splice(fromIndex, 1);
      marketEditExistingGallery.splice(toIndex, 0, moved);
      renderMarketExistingGallery();
      renderMarketEditCardPreview();
    });
  });

  marketEditCurrentMedia.querySelectorAll('[data-market-move-left]').forEach(button => {
    button.addEventListener('click', () => {
      const id = Number(button.dataset.marketMoveLeft || 0);
      const index = marketEditExistingGallery.findIndex(item => Number(item.id) === id);
      if (index > 0) {
        [marketEditExistingGallery[index - 1], marketEditExistingGallery[index]] = [marketEditExistingGallery[index], marketEditExistingGallery[index - 1]];
        renderMarketExistingGallery();
        renderMarketEditCardPreview();
      }
    });
  });

  marketEditCurrentMedia.querySelectorAll('[data-market-move-right]').forEach(button => {
    button.addEventListener('click', () => {
      const id = Number(button.dataset.marketMoveRight || 0);
      const index = marketEditExistingGallery.findIndex(item => Number(item.id) === id);
      if (index >= 0 && index < marketEditExistingGallery.length - 1) {
        [marketEditExistingGallery[index + 1], marketEditExistingGallery[index]] = [marketEditExistingGallery[index], marketEditExistingGallery[index + 1]];
        renderMarketExistingGallery();
        renderMarketEditCardPreview();
      }
    });
  });
};

const renderMarketEditPreview = () => {
  renderMarketFilePreview(marketEditFiles, marketEditPreview, 'Chưa chọn ảnh mới');
  renderMarketEditCardPreview();
};

marketComposeOpeners.forEach(button => {
  button.addEventListener('click', () => setMarketComposeOpen(true));
});
if (marketComposeModal && new URLSearchParams(window.location.search).get('compose') === '1') {
  setMarketComposeOpen(true);
}
marketComposeClosers.forEach(button => {
  button.addEventListener('click', () => setMarketComposeOpen(false));
});
marketEditClosers.forEach(button => {
  button.addEventListener('click', () => setMarketEditOpen(false));
});
if (marketComposeFiles) {
  marketComposeFiles.addEventListener('change', renderMarketComposePreview);
}
if (marketEditFiles) {
  marketEditFiles.addEventListener('change', renderMarketEditPreview);
}
if (marketEditTitle) {
  marketEditTitle.addEventListener('input', renderMarketEditCardPreview);
}
if (marketEditContent) {
  marketEditContent.addEventListener('input', renderMarketEditCardPreview);
}
[marketEditCategory, marketEditOfferType, marketEditQuantity, marketEditArea, marketEditAvailability, marketEditContact].forEach((field) => {
  if (field) field.addEventListener('input', renderMarketEditCardPreview);
  if (field) field.addEventListener('change', renderMarketEditCardPreview);
});
window.addEventListener('keydown', (event) => {
  if (event.key === 'Escape' && marketComposeModal && !marketComposeModal.hidden) {
    setMarketComposeOpen(false);
  }
  if (event.key === 'Escape' && marketEditModal && !marketEditModal.hidden) {
    setMarketEditOpen(false);
  }
  if (event.key === 'Escape' && photoLightbox && !photoLightbox.hidden) {
    setPhotoLightboxOpen(false);
  }
});

const uploadMarketFiles = async (files) => {
  const uploadedIds = [];
  for (const file of files) {
    const body = new FormData();
    body.append('action', 'aitrongcay_upload_market_photo');
    body.append('nonce', ajaxNonce);
    body.append('market_photo', file);
    const response = await fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin', body });
    const result = await response.json();
    if (!result.success) throw new Error(result.data?.message || 'Không tải được ảnh lên.');
    uploadedIds.push(Number(result.data.id));
  }
  return uploadedIds;
};

if (marketComposeForm) {
  marketComposeForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    const title = (marketComposeTitle?.value || '').trim();
    const content = (marketComposeContent?.value || '').trim();
    const structured = collectMarketStructuredFormData('compose');
    const files = Array.from(marketComposeFiles?.files || []);
    const validationError = validateMarketPostInput({ title, content, structured });
    if (validationError) {
      showNotice(marketComposeNotice, validationError);
      return;
    }
    if (marketComposeSubmit) {
      marketComposeSubmit.disabled = true;
      marketComposeSubmit.textContent = 'Đang đăng tin...';
    }
    showNotice(marketComposeNotice, 'Đang đăng tin...');
    try {
      const photoIds = files.length ? await uploadMarketFiles(files) : [];
      const body = new URLSearchParams({ action: 'aitrongcay_create_market_post', nonce: ajaxNonce, title, content, ...structured });
      photoIds.forEach(id => body.append('photo_ids[]', String(id)));
      const response = await fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, body });
      const result = await response.json();
      if (!result.success) throw new Error(result.data?.message || 'Không tạo được tin Chợ quê.');
      showToast(`Đăng tin thành công. <a href="${result.data.url}">Xem tin mới</a>`);
      window.location.href = result.data.url;
    } catch (error) {
      showNotice(marketComposeNotice, error.message || 'Không đăng được tin mới.');
      if (marketComposeSubmit) {
        marketComposeSubmit.disabled = false;
        marketComposeSubmit.textContent = 'Đăng tin ngay';
      }
    }
  });
}

const captureButton = document.querySelector('[data-capture-photo]');
if (captureButton) {
  captureButton.addEventListener('click', async () => {
    try {
      const body = new URLSearchParams({ action: 'aitrongcay_capture_demo_photo', nonce: ajaxNonce });
      const response = await fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, body });
      const result = await response.json();
      if (!result.success) throw new Error(result.data?.message || 'Không lưu được ảnh demo.');
      showNotice(livecamNotice, 'Ảnh snapshot demo có nội dung đã được lưu vào kho ảnh. Đang cập nhật kho ảnh...');
      window.setTimeout(() => {
        window.location.href = `${window.location.pathname}?photo_added=${result.data.id}#photo-library`;
      }, 350);
    } catch (error) {
      showNotice(livecamNotice, error.message || 'Không chụp được ảnh.');
    }
  });
}

if (publicButton) {
  publicButton.addEventListener('click', async () => {
    try {
      const body = new URLSearchParams({ action: 'aitrongcay_create_public_livecam', nonce: ajaxNonce });
      const response = await fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, body });
      const result = await response.json();
      if (!result.success) throw new Error(result.data?.message || 'Không tạo được link public.');
      try { if (navigator.clipboard?.writeText) await navigator.clipboard.writeText(result.data.url); } catch {}
      showNotice(livecamNotice, `Đã bật public livecam. Link chia sẻ thật: <a href="${result.data.url}">${result.data.url}</a>`);
    } catch (error) {
      showNotice(livecamNotice, error.message || 'Không public được livecam.');
    }
  });
}

if (publicOffButton) {
  publicOffButton.addEventListener('click', async () => {
    try {
      const body = new URLSearchParams({ action: 'aitrongcay_disable_public_livecam', nonce: ajaxNonce });
      const response = await fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, body });
      const result = await response.json();
      if (!result.success) throw new Error(result.data?.message || 'Không tắt được public livecam.');
      showNotice(livecamNotice, 'Đã tắt public livecam. Link cũ không còn hiệu lực.');
    } catch (error) {
      showNotice(livecamNotice, error.message || 'Không tắt được public livecam.');
    }
  });
}

if (createMarketButton) {
  createMarketButton.addEventListener('click', async () => {
    const title = createMarketButton.dataset.marketTitle || '';
    const content = createMarketButton.dataset.marketContent || '';
    const photoIds = Array.from(document.querySelectorAll('[data-market-photo]:checked')).map(input => Number(input.dataset.photoId || 0)).filter(Boolean);
    try {
      const body = new URLSearchParams({ action: 'aitrongcay_create_market_post', nonce: ajaxNonce, title, content });
      photoIds.forEach(id => body.append('photo_ids[]', String(id)));
      const response = await fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, body });
      const result = await response.json();
      if (!result.success) throw new Error(result.data?.message || 'Không tạo được tin Chợ quê.');
      showNotice(marketNotice, `Đã tạo tin Chợ quê thật. <a href="${result.data.url}">Xem trong Chợ quê</a>`);
      showToast(`Đã đăng tin Chợ quê thành công. <a href="${result.data.url}">Xem bài đăng</a>`);
    } catch (error) {
      showNotice(marketNotice, error.message || 'Không tạo được tin Chợ quê.');
    }
  });
}

document.querySelectorAll('[data-delete-market-post]').forEach(button => {
  button.addEventListener('click', async () => {
    try {
      const body = new URLSearchParams({ action: 'aitrongcay_delete_market_post', nonce: ajaxNonce, post_id: button.dataset.deleteMarketPost || '' });
      const response = await fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, body });
      const result = await response.json();
      if (!result.success) throw new Error(result.data?.message || 'Không xóa được tin đăng.');
      const card = button.closest('[data-market-card]');
      if (card) card.remove();
    } catch (error) {
      alert(error.message || 'Không xóa được tin đăng.');
    }
  });
});

document.querySelectorAll('[data-open-market-zalo]').forEach(button => {
  button.addEventListener('click', async () => {
    const originalText = button.textContent;
    const isTextButton = button.classList.contains('btn') || button.textContent.trim() !== '';
    if (isTextButton) button.textContent = 'Đang mở Zalo...';
    button.disabled = true;
    try {
      const body = new URLSearchParams({
        action: 'aitrongcay_get_market_zalo_link',
        nonce: ajaxNonce,
        post_id: button.dataset.openMarketZalo || '',
      });
      const response = await fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, body });
      const result = await response.json();
      if (!result.success || !result.data?.url) throw new Error(result.data?.message || 'Chưa mở được Zalo.');
      window.location.href = result.data.url;
    } catch (error) {
      alert(error.message || 'Chưa mở được Zalo.');
    } finally {
      button.disabled = false;
      if (isTextButton) button.textContent = originalText;
    }
  });
});

document.querySelectorAll('[data-edit-market-post]').forEach(button => {
  button.addEventListener('click', () => {
    activeMarketEditButton = button;
    if (marketEditPostId) marketEditPostId.value = button.dataset.editMarketPost || '';
    if (marketEditTitle) marketEditTitle.value = button.dataset.marketTitle || '';
    if (marketEditContent) marketEditContent.value = button.dataset.marketContent || '';
    if (marketEditCategory) marketEditCategory.value = button.dataset.marketCategory || '';
    if (marketEditOfferType) marketEditOfferType.value = button.dataset.marketOfferType || '';
    if (marketEditQuantity) marketEditQuantity.value = button.dataset.marketQuantity || '';
    if (marketEditArea) marketEditArea.value = button.dataset.marketArea || '';
    if (marketEditAvailability) marketEditAvailability.value = button.dataset.marketAvailability || '';
    if (marketEditContact) marketEditContact.value = button.dataset.marketContact || '';
    try {
      marketEditExistingGallery = JSON.parse(button.dataset.marketGallery || '[]');
    } catch {
      marketEditExistingGallery = [];
    }
    renderMarketExistingGallery();
    if (marketEditNotice) marketEditNotice.style.display = 'none';
    renderMarketEditPreview();
    setMarketEditOpen(true);
  });
});

if (marketEditForm) {
  marketEditForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    const postId = (marketEditPostId?.value || '').trim();
    const title = (marketEditTitle?.value || '').trim();
    const content = (marketEditContent?.value || '').trim();
    const structured = collectMarketStructuredFormData();
    const files = Array.from(marketEditFiles?.files || []);
    const validationError = validateMarketPostInput({ title, content, structured });
    if (!postId || validationError) {
      showNotice(marketEditNotice, validationError || 'Thiếu thông tin để lưu tin đăng.');
      return;
    }
    if (marketEditSubmit) {
      marketEditSubmit.disabled = true;
      marketEditSubmit.textContent = 'Đang lưu thay đổi...';
    }
    showNotice(marketEditNotice, 'Đang lưu...');
    try {
      const photoIds = files.length ? await uploadMarketFiles(files) : [];
      const body = new URLSearchParams({ action: 'aitrongcay_update_market_post', nonce: ajaxNonce, post_id: postId, title, content, ...structured });
      marketEditExistingGallery.forEach(item => body.append('existing_photo_ids[]', String(item.id)));
      photoIds.forEach(id => body.append('photo_ids[]', String(id)));
      const response = await fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, body });
      const result = await response.json();
      if (!result.success) throw new Error(result.data?.message || 'Không sửa được tin đăng.');
      const card = activeMarketEditButton?.closest('[data-market-card]');
      const titleEl = card?.querySelector('[data-market-title-render]');
      const contentEl = card?.querySelector('[data-market-content-render]');
      const imageEl = card?.querySelector('.market-row-media img');
      const metaSpans = card?.querySelectorAll('.market-card-meta span');
      if (titleEl) titleEl.textContent = title;
      if (contentEl) contentEl.innerHTML = `<p>${(result.data?.content || content).trim().slice(0, 180)}${(result.data?.content || content).trim().length > 180 ? '...' : ''}</p>`;
      const summaryLine = result.data?.summaryLine || marketStructuredSummaryLine(structured);
      if (metaSpans && metaSpans[1] && summaryLine) metaSpans[1].textContent = summaryLine;
      if (activeMarketEditButton) {
        activeMarketEditButton.dataset.marketTitle = title;
        activeMarketEditButton.dataset.marketContent = content;
        activeMarketEditButton.dataset.marketGallery = JSON.stringify(result.data?.gallery || []);
        activeMarketEditButton.dataset.marketCategory = structured.category || '';
        activeMarketEditButton.dataset.marketOfferType = structured.offer_type || '';
        activeMarketEditButton.dataset.marketQuantity = structured.quantity || '';
        activeMarketEditButton.dataset.marketArea = structured.area || '';
        activeMarketEditButton.dataset.marketAvailability = structured.availability || '';
        activeMarketEditButton.dataset.marketContact = structured.contact_text || '';
      }
      if (result.data?.imageUrl && imageEl) {
        imageEl.src = result.data.imageUrl;
      }
      if (activeMarketEditButton) {
        activeMarketEditButton.dataset.marketImage = result.data?.imageUrl || '';
      }
      showToast('Đã lưu thay đổi cho tin đăng Chợ quê.');
      setMarketEditOpen(false);
    } catch (error) {
      showNotice(marketEditNotice, error.message || 'Không sửa được tin đăng.');
    } finally {
      if (marketEditSubmit) {
        marketEditSubmit.disabled = false;
        marketEditSubmit.textContent = 'Lưu thay đổi';
      }
    }
  });
}

document.querySelectorAll('[data-share-photo]').forEach(button => {
  button.addEventListener('click', async () => {
    const url = button.dataset.sharePhoto || '';
    try { if (navigator.clipboard?.writeText) await navigator.clipboard.writeText(url); } catch {}
    showNotice(livecamNotice, `Đã sao chép link ảnh: <a href="${url}">${url}</a>`);
  });
});

document.querySelectorAll('[data-delete-photo]').forEach(button => {
  button.addEventListener('click', async () => {
    const attachmentId = button.dataset.deletePhoto || '';
    if (!attachmentId) return;
    try {
      const body = new URLSearchParams({ action: 'aitrongcay_delete_photo_attachment', nonce: ajaxNonce, attachment_id: attachmentId });
      const response = await fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, body });
      const result = await response.json();
      if (!result.success) throw new Error(result.data?.message || 'Không xóa được ảnh.');
      button.closest('[data-photo-card]')?.remove();
      showNotice(livecamNotice, 'Đã xóa ảnh khỏi kho ảnh.');
    } catch (error) {
      showNotice(livecamNotice, error.message || 'Không xóa được ảnh.');
    }
  });
});

document.querySelectorAll('[data-rename-photo]').forEach(button => {
  button.addEventListener('click', async () => {
    const attachmentId = button.dataset.renamePhoto || '';
    const nextTitle = window.prompt('Đổi tên ảnh', button.closest('[data-photo-card]')?.querySelector('[data-photo-title]')?.textContent || '');
    if (!attachmentId || !nextTitle) return;
    try {
      const body = new URLSearchParams({ action: 'aitrongcay_rename_photo_attachment', nonce: ajaxNonce, attachment_id: attachmentId, title: nextTitle });
      const response = await fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, body });
      const result = await response.json();
      if (!result.success) throw new Error(result.data?.message || 'Không đổi tên được ảnh.');
      const titleEl = button.closest('[data-photo-card]')?.querySelector('[data-photo-title]');
      if (titleEl) titleEl.textContent = result.data.title;
      const checkbox = button.closest('[data-photo-card]')?.querySelector('[data-market-photo]');
      if (checkbox) checkbox.value = result.data.title;
      renderSelectedMarketPhotos();
      showNotice(livecamNotice, 'Đã đổi tên ảnh.');
    } catch (error) {
      showNotice(livecamNotice, error.message || 'Không đổi tên được ảnh.');
    }
  });
});

const photoLightbox = document.querySelector('[data-photo-lightbox]');
const photoLightboxImage = document.querySelector('[data-photo-lightbox-image]');
const photoLightboxTitle = document.querySelector('[data-photo-lightbox-title]');

const setPhotoLightboxOpen = (open) => {
  if (!photoLightbox) return;
  photoLightbox.hidden = !open;
  document.body.style.overflow = open ? 'hidden' : '';
};

document.querySelectorAll('[data-photo-zoom]').forEach(button => {
  button.addEventListener('click', () => {
    if (!photoLightboxImage || !photoLightboxTitle) return;
    photoLightboxImage.src = button.dataset.photoSrc || '';
    photoLightboxImage.alt = button.dataset.photoTitleFull || 'Ảnh khu vườn';
    photoLightboxTitle.textContent = button.dataset.photoTitleFull || 'Ảnh khu vườn';
    setPhotoLightboxOpen(true);
  });
});

document.querySelectorAll('[data-close-photo-lightbox]').forEach(button => {
  button.addEventListener('click', () => setPhotoLightboxOpen(false));
});

const uploadInput = document.querySelector('[data-photo-upload]');
if (uploadInput) {
  uploadInput.addEventListener('change', async () => {
    const file = uploadInput.files?.[0];
    if (!file) return;
    const formData = new FormData();
    formData.append('action', 'aitrongcay_upload_photo_attachment');
    formData.append('nonce', ajaxNonce);
    formData.append('photo', file);
    try {
      const response = await fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin', body: formData });
      const result = await response.json();
      if (!result.success) throw new Error(result.data?.message || 'Không upload được ảnh.');
      window.location.href = `${window.location.pathname}?photo_added=${result.data.id}#photo-library`;
    } catch (error) {
      showNotice(livecamNotice, error.message || 'Không upload được ảnh.');
    }
  });
}

document.querySelector('[data-refresh-library]')?.addEventListener('click', () => {
  window.location.href = `${window.location.pathname}#photo-library`;
});

const addedPhotoId = new URLSearchParams(window.location.search).get('photo_added');
if (addedPhotoId) {
  const addedCard = document.querySelector(`#photo-${addedPhotoId}`);
  if (addedCard) {
    window.setTimeout(() => {
      addedCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }, 250);
  }
}

document.querySelectorAll('[data-like-market-post]').forEach(button => {
  button.addEventListener('click', async () => {
    try {
      const body = new URLSearchParams({ action: 'aitrongcay_toggle_market_like', nonce: ajaxNonce, post_id: button.dataset.likeMarketPost || '' });
      const response = await fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, body });
      const result = await response.json();
      if (!result.success) throw new Error(result.data?.message || 'Không thao tác được.');
      const label = button.querySelector('[data-like-label]');
      const count = button.querySelector('[data-like-count]');
      if (label) label.textContent = result.data.liked ? 'Đã thích' : 'Thích';
      if (count) count.textContent = String(result.data.count);
    } catch (error) {
      alert(error.message || 'Không thao tác được.');
    }
  });
});
