const mobileToggle = document.querySelector('[data-mobile-toggle]');
const mobilePanel = document.querySelector('[data-mobile-panel]');

if (mobileToggle && mobilePanel) {
  if (!mobilePanel.dataset.initialized) {
    const prefix = window.location.pathname.includes('/portal/') || window.location.pathname.includes('/auth/') || window.location.pathname.includes('/signup/') ? '../' : '';
    mobilePanel.innerHTML = `
      <div class="mobile-panel-inner">
        <a href="${prefix}index.html">Trang chủ</a>
        <a href="${prefix}how-it-works.html">Cách hoạt động</a>
        <a href="${prefix}packages.html">Chợ quê</a>
        <a href="${prefix}food-safety.html">An toàn thực phẩm</a>
        <a href="${prefix}faq.html">FAQ</a>
      </div>`;
    mobilePanel.dataset.initialized = 'true';
  }
  mobileToggle.addEventListener('click', () => {
    const opened = mobilePanel.style.display === 'block';
    mobilePanel.style.display = opened ? 'none' : 'block';
  });
}

const currentPath = window.location.pathname.split('/').pop() || 'index.html';
document.querySelectorAll('.nav-menu a').forEach(link => {
  const href = link.getAttribute('href') || '';
  if (href.endsWith(currentPath)) link.classList.add('active');
});

document.querySelectorAll('[data-current-year]').forEach(el => {
  el.textContent = new Date().getFullYear();
});

const storage = {
  read(key, fallback = null) {
    try {
      const raw = localStorage.getItem(key);
      return raw ? JSON.parse(raw) : fallback;
    } catch {
      return fallback;
    }
  },
  write(key, value) {
    try {
      localStorage.setItem(key, JSON.stringify(value));
    } catch {}
  }
};

const defaultProfile = {
  fullName: 'Gia đình Minh Anh',
  email: 'anhchi@email.com',
  phone: '0983 660 988',
  familySize: '3–4 người',
  goal: 'Rau sạch mỗi ngày',
  package: 'Family',
  focus: 'Muốn có webcam, care log rõ và rau sạch ổn định cho cả nhà.',
  gardenName: 'Vườn nhà Minh Anh',
  notifyWindow: '18:00–20:00',
  plantingFocus: 'Rau ăn lá',
  sharedWith: 'Bố mẹ, bé Bin',
  expectation: 'Mỗi tối mở portal xem vườn, cuối tuần xem timelapse cùng cả nhà.',
  onboardingCompleted: false,
  source: 'local'
};

const authProfileKey = 'aitrongcay.authProfile';
const authSessionKey = 'aitrongcay.authSession';
const authFlowKey = 'aitrongcay.authFlow';
const socialStateKey = 'aitrongcay.socialState.v2';

const normalizePhone = value => (value || '').replace(/\D+/g, '');
const getProfile = () => ({ ...defaultProfile, ...(storage.read(authProfileKey, {}) || {}) });
const setProfile = (patch) => {
  const next = { ...getProfile(), ...patch };
  storage.write(authProfileKey, next);
  return next;
};
const setSession = (patch) => {
  const current = storage.read(authSessionKey, {}) || {};
  const next = { ...current, ...patch, updatedAt: new Date().toISOString() };
  storage.write(authSessionKey, next);
  return next;
};
const setFlow = (patch) => {
  const current = storage.read(authFlowKey, {}) || {};
  storage.write(authFlowKey, { ...current, ...patch, updatedAt: new Date().toISOString() });
};

const createInitialSocialState = () => {
  const profile = getProfile();
  return {
    version: 2,
    activeGardenId: 'garden-home',
    friends: [
      { id: 'friend-lan', name: 'Chị Lan', email: 'lan@example.com', status: 'accepted', lastSeen: 'Vừa xem webcam sáng nay', note: 'Thích xem timelapse cuối tuần' },
      { id: 'friend-minh', name: 'Anh Minh', email: 'minh@example.com', status: 'accepted', lastSeen: 'Mở care log hôm qua', note: 'Hay xem chỉ số realtime' },
      { id: 'friend-binh', name: 'Bé Bin', email: 'bin@example.com', status: 'accepted', lastSeen: 'Thả tim cho ảnh snapshot', note: 'Người xem nhỏ tuổi nhất trong nhà' },
      { id: 'friend-ngoc', name: 'Chị Ngọc', email: 'ngoc@example.com', status: 'pending_received', from: 'Chị Ngọc', sentAt: '09:20 hôm nay' },
      { id: 'friend-khanh', name: 'Anh Khánh', email: 'khanh@example.com', status: 'pending_sent', sentAt: 'Tối qua' }
    ],
    gardens: [
      {
        id: 'garden-home',
        name: profile.gardenName || defaultProfile.gardenName,
        packageName: profile.package || defaultProfile.package,
        ownerName: profile.fullName || defaultProfile.fullName,
        role: 'owner',
        status: 'active',
        healthScore: 92,
        lotCode: 'LOT-0328-A12',
        cameraLabel: 'Cam 01',
        cropFocus: profile.plantingFocus || defaultProfile.plantingFocus,
        notifyWindow: profile.notifyWindow || defaultProfile.notifyWindow,
        heroSummary: 'Môi trường ổn định, cây đang lên đều và cả nhà có thể theo dõi rất yên tâm.',
        members: [
          { id: 'me', name: profile.fullName || defaultProfile.fullName, role: 'owner', status: 'active', tag: 'Chủ vườn', accent: 'forest' },
          { id: 'friend-lan', name: 'Chị Lan', role: 'co_owner', status: 'active', tag: 'Đồng sở hữu', accent: 'gold' },
          { id: 'friend-binh', name: 'Bé Bin', role: 'viewer', status: 'active', tag: 'Chỉ xem', accent: 'sky' },
          { id: 'friend-minh', name: 'Anh Minh', role: 'viewer', status: 'invited', invitedAt: '10:15 hôm nay', tag: 'Đang chờ', accent: 'sand' }
        ],
        inviteInbox: []
      },
      {
        id: 'garden-ba-ngoai',
        name: 'Vườn bà ngoại',
        packageName: 'Family',
        ownerName: 'Chị Lan',
        role: 'viewer',
        status: 'active',
        healthScore: 88,
        lotCode: 'LOT-0401-LAN',
        cameraLabel: 'Cam 02',
        cropFocus: 'Rau gia đình',
        notifyWindow: '07:00–08:00',
        heroSummary: 'Khu vườn được share cho anh/chị để theo dõi cùng gia đình, hiện trạng khá ổn.',
        members: [
          { id: 'friend-lan', name: 'Chị Lan', role: 'owner', status: 'active', tag: 'Chủ vườn', accent: 'forest' },
          { id: 'me', name: profile.fullName || defaultProfile.fullName, role: 'viewer', status: 'active', tag: 'Chỉ xem', accent: 'sky' }
        ],
        inviteInbox: []
      },
      {
        id: 'garden-san-thuong',
        name: 'Vườn sân thượng nhà Minh',
        packageName: 'Mini',
        ownerName: 'Anh Minh',
        role: 'co_owner',
        status: 'active',
        healthScore: 90,
        lotCode: 'LOT-0402-MINH',
        cameraLabel: 'Cam 03',
        cropFocus: 'Rau thơm và cải non',
        notifyWindow: '20:00–21:00',
        heroSummary: 'Khu vườn share này cho phép anh/chị cùng điều khiển và theo dõi như đồng sở hữu.',
        members: [
          { id: 'friend-minh', name: 'Anh Minh', role: 'owner', status: 'active', tag: 'Chủ vườn', accent: 'forest' },
          { id: 'me', name: profile.fullName || defaultProfile.fullName, role: 'co_owner', status: 'active', tag: 'Đồng sở hữu', accent: 'gold' }
        ],
        inviteInbox: []
      },
      {
        id: 'garden-pending',
        name: 'Vườn thử nghiệm của chị Ngọc',
        packageName: 'Premium',
        ownerName: 'Chị Ngọc',
        role: 'viewer',
        status: 'invited',
        healthScore: 0,
        lotCode: 'LOT-0403-NGOC',
        cameraLabel: 'Cam 04',
        cropFocus: 'Rau mầm',
        notifyWindow: '18:30–19:30',
        heroSummary: 'Lời mời đang chờ phản hồi nên khu vườn này chưa xuất hiện trong portal chính.',
        members: [
          { id: 'friend-ngoc', name: 'Chị Ngọc', role: 'owner', status: 'active', tag: 'Chủ vườn', accent: 'forest' },
          { id: 'me', name: profile.fullName || defaultProfile.fullName, role: 'viewer', status: 'invited', invitedAt: '08:45 hôm nay', tag: 'Được mời', accent: 'sand' }
        ],
        inviteInbox: [
          { id: 'invite-garden-ngoc', fromName: 'Chị Ngọc', fromId: 'friend-ngoc', role: 'viewer', sentAt: '08:45 hôm nay', message: 'Mời anh/chị cùng xem khu vườn rau mầm mới.' }
        ]
      }
    ]
  };
};

const getSocialState = () => {
  const saved = storage.read(socialStateKey, null);
  if (saved && saved.version === 2) return saved;
  const initial = createInitialSocialState();
  storage.write(socialStateKey, initial);
  return initial;
};

const setSocialState = (updater) => {
  const current = getSocialState();
  const next = typeof updater === 'function' ? updater(current) : updater;
  storage.write(socialStateKey, next);
  return next;
};

const getRoleLabel = role => ({ owner: 'Chủ vườn', co_owner: 'Đồng sở hữu', viewer: 'Chỉ xem' }[role] || role);
const getRoleClass = role => ({ owner: 'forest', co_owner: 'gold', viewer: 'sky' }[role] || 'sand');
const getStatusLabel = status => ({ active: 'Đang tham gia', invited: 'Đang chờ phản hồi', declined: 'Đã từ chối', removed: 'Đã gỡ' }[status] || status);
const escapeHtml = (value = '') => String(value)
  .replace(/&/g, '&amp;')
  .replace(/</g, '&lt;')
  .replace(/>/g, '&gt;')
  .replace(/"/g, '&quot;')
  .replace(/'/g, '&#39;');

const getActiveGardens = state => (state.gardens || []).filter(garden => garden.status === 'active');
const getPendingGardenInvites = state => (state.gardens || []).filter(garden => garden.status === 'invited');
const getActiveGarden = (state = getSocialState()) => {
  const activeGardens = getActiveGardens(state);
  return activeGardens.find(garden => garden.id === state.activeGardenId) || activeGardens[0] || null;
};

const showFormResult = (form, message, type = 'success') => {
  let notice = form.querySelector('.form-result');
  if (!notice) {
    notice = document.createElement('div');
    notice.className = 'form-result';
    form.appendChild(notice);
  }
  notice.className = `form-result is-${type}`;
  notice.textContent = message;
};

const redirectSoon = (url) => {
  if (!url) return;
  window.setTimeout(() => {
    window.location.href = url;
  }, 900);
};

const formValue = (form, name) => (form.elements.namedItem(name)?.value || '').trim();

const prefillInputs = () => {
  const profile = getProfile();
  document.querySelectorAll('[data-prefill]').forEach(input => {
    const key = input.dataset.prefill;
    if (!key || input.value) return;
    if (profile[key]) input.value = profile[key];
  });
};

prefillInputs();

document.querySelectorAll('[data-fake-submit]').forEach(form => {
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    showFormResult(form, 'Đã ghi nhận thông tin của anh/chị. Em sẽ giữ lại để gửi gợi ý gói phù hợp thật gọn và dễ theo dõi.');
  });
});

const registerHandlers = {
  register(form) {
    const fullName = formValue(form, 'fullName');
    const phone = formValue(form, 'phone');
    const email = formValue(form, 'email');
    const familySize = formValue(form, 'familySize');
    const goal = formValue(form, 'goal');
    const packageName = formValue(form, 'package');
    const focus = formValue(form, 'focus');

    if (!fullName || !phone || !email) {
      showFormResult(form, 'Anh/chị vui lòng điền đủ họ tên, số điện thoại và email để em giữ hồ sơ tư vấn.', 'error');
      return;
    }

    const profile = setProfile({
      fullName,
      phone,
      email,
      familySize,
      goal,
      package: packageName,
      focus,
      source: 'register',
      onboardingCompleted: false
    });

    storage.write(socialStateKey, createInitialSocialState());
    setFlow({ step: 'registered', lastAction: 'register' });
    showFormResult(form, `Đã lưu hồ sơ cho ${profile.fullName}. Mời anh/chị sang bước onboarding để đặt tên khu vườn và chốt nhịp trải nghiệm.`, 'success');
    redirectSoon(form.dataset.redirect);
  },
  onboarding(form) {
    const gardenName = formValue(form, 'gardenName');
    const notifyWindow = formValue(form, 'notifyWindow');
    const plantingFocus = formValue(form, 'plantingFocus');
    const sharedWith = formValue(form, 'sharedWith');
    const expectation = formValue(form, 'expectation');

    if (!gardenName || !notifyWindow || !plantingFocus) {
      showFormResult(form, 'Anh/chị giúp em điền tên vườn, khung giờ nhận thông báo và ưu tiên gieo trồng để portal cá nhân hoá tốt hơn.', 'error');
      return;
    }

    const profile = setProfile({
      gardenName,
      notifyWindow,
      plantingFocus,
      sharedWith,
      expectation,
      onboardingCompleted: true,
      source: 'onboarding'
    });

    const nextState = setSocialState(state => ({
      ...state,
      gardens: state.gardens.map(garden => garden.id === 'garden-home'
        ? {
            ...garden,
            name: gardenName,
            cropFocus: plantingFocus,
            notifyWindow,
            members: garden.members.map(member => member.id === 'me'
              ? { ...member, name: profile.fullName || defaultProfile.fullName }
              : member)
          }
        : garden)
    }));
    if (!getActiveGarden(nextState)) {
      storage.write(socialStateKey, createInitialSocialState());
    }

    setFlow({ step: 'onboarding-complete', lastAction: 'onboarding' });
    showFormResult(form, `Đã hoàn tất onboarding cho ${profile.gardenName}. Tiếp theo anh/chị đăng nhập để vào portal với hồ sơ vừa cá nhân hoá.`, 'success');
    redirectSoon(form.dataset.redirect);
  },
  login(form) {
    const identity = formValue(form, 'identity');
    const password = formValue(form, 'password');
    const remember = Boolean(form.elements.namedItem('remember')?.checked);
    const profile = getProfile();

    if (!identity) {
      showFormResult(form, 'Anh/chị nhập email hoặc số điện thoại đã dùng khi đăng ký để em nhận diện đúng khu vườn.', 'error');
      return;
    }

    if (password.length < 6) {
      showFormResult(form, 'Anh/chị nhập mật khẩu từ 6 ký tự trở lên nhé.', 'error');
      return;
    }

    const identityMatches = [profile.email?.toLowerCase(), normalizePhone(profile.phone)]
      .filter(Boolean)
      .includes(identity.toLowerCase()) || normalizePhone(identity) === normalizePhone(profile.phone);

    if (!identityMatches) {
      showFormResult(form, 'Em chưa thấy hồ sơ khớp trong trình duyệt này. Anh/chị có thể đăng ký mới hoặc dùng đúng email / số điện thoại đã điền trước đó.', 'error');
      return;
    }

    setSession({
      loggedIn: true,
      remember,
      identity,
      fullName: profile.fullName,
      gardenName: profile.gardenName || defaultProfile.gardenName,
      onboardingCompleted: !!profile.onboardingCompleted
    });
    setFlow({ step: 'logged-in', lastAction: 'login' });

    const nextCopy = profile.onboardingCompleted
      ? `Đăng nhập thành công. Đang mở ${profile.gardenName || 'khu vườn của anh/chị'} trong portal...`
      : 'Đăng nhập thành công. Portal vẫn mở được ngay, và em sẽ nhắc anh/chị hoàn tất onboarding ở bước tiếp theo.';
    showFormResult(form, nextCopy, 'success');
    redirectSoon(form.dataset.redirect);
  }
};

document.querySelectorAll('[data-auth-form]').forEach(form => {
  const type = form.dataset.authForm;
  if (!registerHandlers[type]) return;
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    registerHandlers[type](form);
  });
});

document.querySelectorAll('[data-auth-provider="google"]').forEach(link => {
  link.addEventListener('click', (e) => {
    e.preventDefault();
    const page = window.location.pathname;
    const msg = page.includes('/login')
      ? 'Tạm thời anh/chị dùng email hoặc số điện thoại đã đăng ký để vào khu vườn nhé.'
      : 'Tạm thời anh/chị điền form này là đủ để bắt đầu hành trình nhé.';
    window.alert(msg);
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
  const panels = wrapper.querySelectorAll('[data-tab-panel]');
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

const updatePortalMeta = (garden) => {
  if (!garden) return;
  const roleLabel = getRoleLabel(garden.role);
  const activeMembers = (garden.members || []).filter(member => member.status === 'active');

  const setText = (selector, value) => {
    const nodes = document.querySelectorAll(selector);
    nodes.forEach(el => {
      if (value) el.textContent = value;
    });
  };

  setText('[data-portal-garden-label]', `${garden.name} • ${garden.packageName} plan`);
  setText('[data-portal-headline]', `Tổng quan ${garden.name}`);
  setText('[data-portal-garden-name]', garden.name);
  setText('[data-portal-role-label]', roleLabel);
  setText('[data-portal-owner-label]', `Chủ vườn: ${garden.ownerName}`);
  setText('[data-portal-members]', `${activeMembers.length} người`);
  setText('[data-portal-viewer-count]', `${activeMembers.length} người`);
  setText('[data-portal-camera-label]', `${garden.cameraLabel} • ${garden.name}`);
  setText('[data-portal-health-score]', `${garden.healthScore}/100`);
  setText('[data-portal-lot-code]', garden.lotCode);
  setText('[data-portal-crop-focus]', garden.cropFocus);
  setText('[data-portal-notify-window]', garden.notifyWindow);
  setText('[data-portal-sharing]', `${activeMembers.length} thành viên đang cùng theo dõi ${garden.name}. Vai trò hiện tại của anh/chị: ${roleLabel.toLowerCase()}.`);

  if (window.location.pathname.includes('/portal/dashboard-2.html')) {
    const session = storage.read(authSessionKey, {}) || {};
    const profile = getProfile();
    const activeName = session.fullName || profile.fullName || defaultProfile.fullName;
    const canControl = garden.role === 'owner' || garden.role === 'co_owner';
    setText('[data-portal-subline]', `Xin chào ${activeName}. Anh/chị đang xem ${garden.name}. Từ đây có thể mở webcam, theo dõi care log và chuyển nhanh giữa các khu vườn được share.`);
    setText('[data-portal-welcome-title]', `Chào mừng trở lại, ${activeName}`);
    setText('[data-portal-welcome-copy]', canControl
      ? `${garden.name} đang ở vai trò ${roleLabel.toLowerCase()}, nên anh/chị vừa theo dõi vừa có thể can thiệp ở những luồng phù hợp.`
      : `${garden.name} đang được chia sẻ ở chế độ chỉ xem, nên anh/chị có thể theo dõi đầy đủ nhưng không điều khiển thiết bị.`);
    setText('[data-portal-ambient]', `${garden.name} đang đi khá đều. ${garden.heroSummary}`);
    setText('[data-portal-hero-title]', `${garden.name} hôm nay trông rất ổn và rất dễ chịu để theo dõi`);
    setText('[data-portal-hero-copy]', `AI summary: ${garden.heroSummary} Trọng tâm hiện tại là ${garden.cropFocus.toLowerCase()}, nhịp nhắc phù hợp: ${garden.notifyWindow}.`);
    setText('[data-portal-activity-1]', `${activeName} vừa mở lại ${garden.name}`);
    setText('[data-portal-activity-2]', `${activeMembers.map(member => member.name).join(', ')} đang cùng theo dõi khu vườn này`);
    setText('[data-portal-activity-3]', `AI gardener đã chuẩn bị summary theo đúng nhịp ${garden.notifyWindow}`);
    setText('[data-portal-soft-note]', canControl
      ? `Anh/chị đang có quyền ${roleLabel.toLowerCase()}. Nếu cần, có thể chia sẻ thêm hoặc điều chỉnh vai trò thành viên từ trang chia sẻ.`
      : `Anh/chị đang ở vai trò chỉ xem. Cơ chế này giúp vẫn theo dõi được nhiều khu vườn share cùng lúc mà không làm rối quyền điều khiển.`);
  }
};

const renderPortalSidebar = () => {
  if (!window.location.pathname.includes('/portal/')) return;
  const sidebar = document.querySelector('.sidebar');
  if (!sidebar) return;
  const state = getSocialState();
  const activeGarden = getActiveGarden(state);
  const activeGardens = getActiveGardens(state);
  const pendingInvites = getPendingGardenInvites(state);
  const activePage = currentPath;

  sidebar.innerHTML = `
    <div class="logo" style="margin-bottom:20px"><span class="logo-badge">🌿</span><span>Ai trồng cây</span></div>
    <div class="notice portal-switcher-shell">
      <div class="portal-switcher-head">
        <strong data-portal-garden-label>${escapeHtml(activeGarden ? `${activeGarden.name} • ${activeGarden.packageName} plan` : 'Chưa có khu vườn nào')}</strong>
        <span>${activeGardens.length} khu vườn đang xem${pendingInvites.length ? ` • ${pendingInvites.length} lời mời chờ phản hồi` : ''}</span>
      </div>
      <label class="switcher-label" for="portalGardenSwitcher">Đổi khu vườn đang mở</label>
      <select id="portalGardenSwitcher" data-garden-switcher>
        ${activeGardens.map(garden => `<option value="${garden.id}" ${activeGarden && garden.id === activeGarden.id ? 'selected' : ''}>${escapeHtml(garden.name)} • ${escapeHtml(getRoleLabel(garden.role))}</option>`).join('')}
      </select>
    </div>
    <div class="sidebar-group">
      <h4>Khu vườn</h4>
      <a class="${activePage === 'dashboard.html' ? 'active' : ''}" href="dashboard.html">Tổng quan</a>
      <a class="${activePage === 'webcam.html' ? 'active' : ''}" href="webcam.html">Live webcam 24/7</a>
      <a class="${activePage === 'status.html' ? 'active' : ''}" href="status.html">Realtime status</a>
      <a class="${activePage === 'care-log.html' ? 'active' : ''}" href="care-log.html">Nhật ký chăm sóc</a>
      <a class="${activePage === 'quality-safety.html' ? 'active' : ''}" href="quality-safety.html">Quality & safety</a>
      <a class="${activePage === 'ai-gardener.html' ? 'active' : ''}" href="ai-gardener.html">AI gardener</a>
      <a class="${activePage === 'tools-warehouse.html' ? 'active' : ''}" href="tools-warehouse.html">Kho nông cụ</a>
    </div>
    <div class="sidebar-group">
      <h4>Kết nối</h4>
      <a class="${activePage === 'friends.html' ? 'active' : ''}" href="friends.html">Bạn bè</a>
      <a class="${activePage === 'share-garden.html' ? 'active' : ''}" href="share-garden.html">Chia sẻ khu vườn</a>
    </div>
    <div class="sidebar-group">
      <h4>Tài khoản</h4>
      <a href="../auth/login.html">Đăng nhập lại</a>
      <a href="../signup/onboarding.html">Chỉnh onboarding</a>
    </div>
    <div class="sidebar-group">
      <h4>Website</h4>
      <a href="../index.html">← Quay lại website</a>
    </div>`;

  const switcher = sidebar.querySelector('[data-garden-switcher]');
  if (switcher) {
    switcher.addEventListener('change', () => {
      const nextId = switcher.value;
      setSocialState(stateValue => ({ ...stateValue, activeGardenId: nextId }));
      window.location.reload();
    });
  }
};

const injectPortalSwitcherCard = () => {
  if (!window.location.pathname.includes('/portal/')) return;
  const main = document.querySelector('.portal-main');
  const topbar = main?.querySelector('.portal-topbar');
  if (!main || !topbar || main.querySelector('[data-portal-switcher-card]')) return;
  const state = getSocialState();
  const activeGarden = getActiveGarden(state);
  const activeGardens = getActiveGardens(state);
  if (!activeGarden) return;

  const wrapper = document.createElement('section');
  wrapper.className = 'portal-switcher-card';
  wrapper.dataset.portalSwitcherCard = 'true';
  wrapper.innerHTML = `
    <div>
      <span class="eyebrow">Nhiều khu vườn trong một portal</span>
      <h3 style="margin-top:12px">Anh/chị đang mở <span data-portal-garden-name>${escapeHtml(activeGarden.name)}</span></h3>
      <p class="subtle">Hiện account này xem được ${activeGardens.length} khu vườn đã active. Khi đổi ở đây, toàn bộ dashboard sẽ bám theo khu vườn đang chọn.</p>
    </div>
    <div class="portal-switcher-inline-meta">
      <span class="member-badge is-${getRoleClass(activeGarden.role)}" data-portal-role-label>${escapeHtml(getRoleLabel(activeGarden.role))}</span>
      <span class="chip" data-portal-owner-label>Chủ vườn: ${escapeHtml(activeGarden.ownerName)}</span>
      <span class="chip">${escapeHtml(activeGarden.cameraLabel)}</span>
      <span class="chip">${escapeHtml(activeGarden.lotCode)}</span>
    </div>`;
  topbar.insertAdjacentElement('afterend', wrapper);
};

const personalizePortal = () => {
  if (!window.location.pathname.includes('/portal/')) return;
  const state = getSocialState();
  const activeGarden = getActiveGarden(state);
  renderPortalSidebar();
  injectPortalSwitcherCard();
  updatePortalMeta(activeGarden);
};

const refreshPortal = () => {
  personalizePortal();
  renderFriendsPage();
  renderShareGardenPage();
};

const renderFriendsPage = () => {
  const page = document.querySelector('[data-friends-page]');
  if (!page) return;
  const state = getSocialState();
  const pendingReceived = state.friends.filter(friend => friend.status === 'pending_received');
  const pendingSent = state.friends.filter(friend => friend.status === 'pending_sent');
  const acceptedFriends = state.friends.filter(friend => friend.status === 'accepted');
  const gardenInvites = getPendingGardenInvites(state);
  const activeGarden = getActiveGarden(state);

  page.innerHTML = `
    <div class="portal-cards social-layout">
      <section class="portal-card span-5">
        <div class="section-head" style="margin-bottom:18px">
          <span class="eyebrow">Tìm bạn</span>
          <h3 style="margin-bottom:0">Gửi lời mời kết bạn</h3>
        </div>
        <form class="auth-form" data-friend-request-form>
          <div>
            <label for="friendName">Tên người muốn mời</label>
            <input id="friendName" name="friendName" placeholder="Ví dụ: Cô Hoa">
          </div>
          <div>
            <label for="friendContact">Email hoặc username</label>
            <input id="friendContact" name="friendContact" placeholder="cohoa@example.com">
          </div>
          <button class="btn btn-primary" type="submit">Gửi lời mời kết bạn</button>
          <p class="field-hint">Sau khi kết bạn, người đó mới có thể được mời vào ${escapeHtml(activeGarden ? activeGarden.name : 'khu vườn hiện tại')}.</p>
        </form>
      </section>
      <section class="portal-card span-7">
        <div class="section-head" style="margin-bottom:18px">
          <span class="eyebrow">Lời mời khu vườn</span>
          <h3 style="margin-bottom:0">Những khu vườn đang chờ anh/chị phản hồi</h3>
        </div>
        <div class="social-stack">
          ${gardenInvites.length ? gardenInvites.map(garden => {
            const invite = garden.inviteInbox?.[0];
            return `
              <article class="social-item highlight-item">
                <div class="social-item-main">
                  <div>
                    <strong>${escapeHtml(garden.name)}</strong>
                    <p>${escapeHtml(invite?.message || 'Một khu vườn đang mời anh/chị cùng theo dõi.')}</p>
                  </div>
                  <div class="social-meta-row">
                    <span class="member-badge is-${getRoleClass(garden.role)}">${escapeHtml(getRoleLabel(garden.role))}</span>
                    <span class="subtle">Chủ vườn: ${escapeHtml(garden.ownerName)} • ${escapeHtml(invite?.sentAt || '')}</span>
                  </div>
                </div>
                <div class="social-actions">
                  <button class="btn btn-primary" type="button" data-garden-invite-action="accept" data-garden-id="${garden.id}">Chấp nhận</button>
                  <button class="btn btn-secondary" type="button" data-garden-invite-action="decline" data-garden-id="${garden.id}">Từ chối</button>
                </div>
              </article>`;
          }).join('') : '<div class="empty-state">Hiện chưa có lời mời khu vườn nào đang chờ phản hồi.</div>'}
        </div>
      </section>
      <section class="portal-card span-6">
        <div class="section-head" style="margin-bottom:18px">
          <span class="eyebrow">Lời mời đã nhận</span>
          <h3 style="margin-bottom:0">Ai đang muốn kết bạn với anh/chị</h3>
        </div>
        <div class="social-stack">
          ${pendingReceived.length ? pendingReceived.map(friend => `
            <article class="social-item">
              <div class="social-item-main">
                <strong>${escapeHtml(friend.name)}</strong>
                <p>${escapeHtml(friend.from || friend.name)} đã gửi lời mời kết bạn.</p>
                <span class="subtle">${escapeHtml(friend.sentAt || '')}</span>
              </div>
              <div class="social-actions">
                <button class="btn btn-primary" type="button" data-friend-action="accept" data-friend-id="${friend.id}">Chấp nhận</button>
                <button class="btn btn-secondary" type="button" data-friend-action="reject" data-friend-id="${friend.id}">Từ chối</button>
              </div>
            </article>`).join('') : '<div class="empty-state">Không có lời mời kết bạn mới.</div>'}
        </div>
      </section>
      <section class="portal-card span-6">
        <div class="section-head" style="margin-bottom:18px">
          <span class="eyebrow">Bạn bè của tôi</span>
          <h3 style="margin-bottom:0">Danh sách đã sẵn để mời vào khu vườn</h3>
        </div>
        <div class="social-stack">
          ${acceptedFriends.map(friend => `
            <article class="social-item compact-item">
              <div class="social-item-main">
                <strong>${escapeHtml(friend.name)}</strong>
                <p>${escapeHtml(friend.note || 'Đã sẵn sàng để mời vào khu vườn.')}</p>
                <span class="subtle">${escapeHtml(friend.lastSeen || '')}</span>
              </div>
              <div class="social-actions stacked-actions">
                <a class="btn btn-secondary" href="share-garden.html">Mời vào khu vườn</a>
              </div>
            </article>`).join('')}
        </div>
      </section>
      <section class="portal-card span-12">
        <div class="section-head" style="margin-bottom:18px">
          <span class="eyebrow">Lời mời đã gửi</span>
          <h3 style="margin-bottom:0">Những kết nối đang chờ phản hồi</h3>
        </div>
        <div class="social-stack">
          ${pendingSent.length ? pendingSent.map(friend => `
            <article class="social-item compact-item">
              <div class="social-item-main">
                <strong>${escapeHtml(friend.name)}</strong>
                <p>Đã gửi lời mời kết bạn qua ${escapeHtml(friend.email || 'username')}.</p>
                <span class="subtle">${escapeHtml(friend.sentAt || '')}</span>
              </div>
              <div class="social-meta-row">
                <span class="member-badge is-sand">Đang chờ phản hồi</span>
              </div>
            </article>`).join('') : '<div class="empty-state">Không có lời mời kết bạn nào đang chờ.</div>'}
        </div>
      </section>
    </div>`;

  const friendForm = page.querySelector('[data-friend-request-form]');
  if (friendForm) {
    friendForm.addEventListener('submit', (event) => {
      event.preventDefault();
      const name = formValue(friendForm, 'friendName');
      const contact = formValue(friendForm, 'friendContact');
      if (!name || !contact) {
        showFormResult(friendForm, 'Anh/chị điền giúp em cả tên và email/username để lời mời rõ ràng hơn.', 'error');
        return;
      }
      setSocialState(stateValue => ({
        ...stateValue,
        friends: [...stateValue.friends, {
          id: `friend-${Date.now()}`,
          name,
          email: contact,
          status: 'pending_sent',
          sentAt: 'Vừa gửi'
        }]
      }));
      showFormResult(friendForm, `Đã gửi lời mời kết bạn tới ${name}. Khi họ chấp nhận, anh/chị có thể mời vào khu vườn ngay.`, 'success');
      friendForm.reset();
      renderFriendsPage();
    });
  }

  page.querySelectorAll('[data-friend-action]').forEach(button => {
    button.addEventListener('click', () => {
      const friendId = button.dataset.friendId;
      const action = button.dataset.friendAction;
      setSocialState(stateValue => ({
        ...stateValue,
        friends: stateValue.friends.flatMap(friend => {
          if (friend.id !== friendId) return [friend];
          if (action === 'accept') return [{ ...friend, status: 'accepted', lastSeen: 'Vừa kết nối', note: 'Kết nối mới được thêm vào danh sách bạn bè.' }];
          return [];
        })
      }));
      renderFriendsPage();
    });
  });

  page.querySelectorAll('[data-garden-invite-action]').forEach(button => {
    button.addEventListener('click', () => {
      const gardenId = button.dataset.gardenId;
      const action = button.dataset.gardenInviteAction;
      const nextState = setSocialState(stateValue => ({
        ...stateValue,
        activeGardenId: action === 'accept' ? gardenId : stateValue.activeGardenId,
        gardens: stateValue.gardens.map(garden => {
          if (garden.id !== gardenId) return garden;
          return {
            ...garden,
            status: action === 'accept' ? 'active' : 'declined',
            members: garden.members.map(member => member.id === 'me' ? { ...member, status: action === 'accept' ? 'active' : 'declined', tag: action === 'accept' ? getRoleLabel(garden.role) : 'Đã từ chối' } : member),
            inviteInbox: []
          };
        })
      }));
      if (action === 'accept') updatePortalMeta(getActiveGarden(nextState));
      refreshPortal();
    });
  });
};

const renderShareGardenPage = () => {
  const page = document.querySelector('[data-share-garden-page]');
  if (!page) return;
  const state = getSocialState();
  const activeGarden = getActiveGarden(state);
  if (!activeGarden) return;
  const acceptedFriends = state.friends.filter(friend => friend.status === 'accepted');
  const activeMembers = activeGarden.members.filter(member => member.status === 'active');
  const pendingMembers = activeGarden.members.filter(member => member.status === 'invited');
  const canManage = activeGarden.role === 'owner';
  const candidateFriends = acceptedFriends.filter(friend => !activeGarden.members.some(member => member.id === friend.id && ['active', 'invited'].includes(member.status)));

  page.innerHTML = `
    <div class="portal-cards social-layout">
      <section class="portal-card span-12 share-hero-card">
        <div>
          <span class="eyebrow">Chia sẻ khu vườn</span>
          <h2 style="margin-top:12px;margin-bottom:10px">${escapeHtml(activeGarden.name)}</h2>
          <p>${escapeHtml(activeGarden.name)} hiện có ${activeMembers.length} người đang tham gia. Vai trò của anh/chị: ${getRoleLabel(activeGarden.role).toLowerCase()}.</p>
        </div>
        <div class="portal-switcher-inline-meta">
          <span class="member-badge is-${getRoleClass(activeGarden.role)}">${escapeHtml(getRoleLabel(activeGarden.role))}</span>
          <span class="chip">Chủ vườn: ${escapeHtml(activeGarden.ownerName)}</span>
          <span class="chip">${activeMembers.length} thành viên active</span>
          <span class="chip">${pendingMembers.length} lời mời chờ phản hồi</span>
        </div>
      </section>
      <section class="portal-card span-7">
        <div class="section-head" style="margin-bottom:18px">
          <span class="eyebrow">Thành viên hiện tại</span>
          <h3 style="margin-bottom:0">Ai đang cùng theo dõi khu vườn này</h3>
        </div>
        <div class="social-stack">
          ${activeMembers.map(member => `
            <article class="social-item member-row">
              <div class="social-item-main">
                <div class="member-row-head">
                  <strong>${escapeHtml(member.name)}</strong>
                  <span class="member-badge is-${member.accent || getRoleClass(member.role)}">${escapeHtml(getRoleLabel(member.role))}</span>
                </div>
                <p>${member.id === 'me' ? 'Đây là vai trò của account hiện tại trong khu vườn này.' : member.role === 'viewer' ? 'Có thể xem dashboard, ảnh, log và trạng thái nhưng không điều khiển.' : 'Có thể cùng theo dõi và hỗ trợ thao tác phù hợp với quyền.'}</p>
              </div>
              <div class="social-actions ${canManage && member.role !== 'owner' ? '' : 'is-disabled'}">
                ${canManage && member.role !== 'owner' ? `
                  <button class="btn btn-secondary" type="button" data-member-role-toggle="${member.id}">${member.role === 'viewer' ? 'Nâng thành đồng sở hữu' : 'Đổi về chỉ xem'}</button>
                  <button class="btn btn-ghost" type="button" data-member-remove="${member.id}">Gỡ khỏi khu vườn</button>` : '<span class="subtle">Vai trò này không chỉnh ngay trên demo này.</span>'}
              </div>
            </article>`).join('')}
        </div>
      </section>
      <section class="portal-card span-5">
        <div class="section-head" style="margin-bottom:18px">
          <span class="eyebrow">Mời bạn vào khu vườn</span>
          <h3 style="margin-bottom:0">Gửi lời mời gọn, rõ, đúng quyền</h3>
        </div>
        <form class="auth-form" data-share-invite-form>
          <div>
            <label for="shareFriend">Chọn bạn bè</label>
            <select id="shareFriend" name="shareFriend" ${canManage ? '' : 'disabled'}>
              <option value="">Chọn một người bạn</option>
              ${candidateFriends.map(friend => `<option value="${friend.id}">${escapeHtml(friend.name)} • ${escapeHtml(friend.email || '')}</option>`).join('')}
            </select>
          </div>
          <div>
            <label for="shareRole">Vai trò khi vào vườn</label>
            <select id="shareRole" name="shareRole" ${canManage ? '' : 'disabled'}>
              <option value="co_owner">Đồng sở hữu</option>
              <option value="viewer">Chỉ xem</option>
            </select>
          </div>
          <div>
            <label for="shareNote">Lời nhắn</label>
            <textarea id="shareNote" name="shareNote" ${canManage ? '' : 'disabled'} placeholder="Ví dụ: Mời vào để cùng theo dõi mùa vụ tuần này."></textarea>
          </div>
          <button class="btn btn-primary" type="submit" ${canManage ? '' : 'disabled'}>${canManage ? 'Gửi lời mời' : 'Chỉ chủ vườn mới mời được'}</button>
          <p class="field-hint">${canManage ? 'Luồng v1 cho phép chủ vườn mời bạn bè vào với quyền đồng sở hữu hoặc chỉ xem.' : 'Anh/chị đang ở vai trò không phải chủ vườn, nên trang này chuyển sang chế độ xem và rà thành viên.'}</p>
        </form>
      </section>
      <section class="portal-card span-12">
        <div class="section-head" style="margin-bottom:18px">
          <span class="eyebrow">Lời mời đang chờ</span>
          <h3 style="margin-bottom:0">Theo dõi nhanh ai chưa phản hồi</h3>
        </div>
        <div class="social-stack">
          ${pendingMembers.length ? pendingMembers.map(member => `
            <article class="social-item compact-item">
              <div class="social-item-main">
                <div class="member-row-head">
                  <strong>${escapeHtml(member.name)}</strong>
                  <span class="member-badge is-sand">${escapeHtml(getRoleLabel(member.role))}</span>
                </div>
                <p>${escapeHtml(member.name)} đang được mời vào ${escapeHtml(activeGarden.name)}.</p>
                <span class="subtle">${escapeHtml(member.invitedAt || 'Vừa gửi')} • ${escapeHtml(getStatusLabel(member.status))}</span>
              </div>
              <div class="social-actions ${canManage ? '' : 'is-disabled'}">
                ${canManage ? `<button class="btn btn-ghost" type="button" data-pending-cancel="${member.id}">Hủy lời mời</button>` : '<span class="subtle">Chỉ chủ vườn mới hủy lời mời.</span>'}
              </div>
            </article>`).join('') : '<div class="empty-state">Hiện không có lời mời nào đang chờ ở khu vườn này.</div>'}
        </div>
      </section>
    </div>`;

  const form = page.querySelector('[data-share-invite-form]');
  if (form) {
    form.addEventListener('submit', (event) => {
      event.preventDefault();
      if (!canManage) return;
      const friendId = formValue(form, 'shareFriend');
      const role = formValue(form, 'shareRole') || 'viewer';
      const note = formValue(form, 'shareNote');
      if (!friendId) {
        showFormResult(form, 'Anh/chị chọn một người bạn trước khi gửi lời mời nhé.', 'error');
        return;
      }
      const friend = acceptedFriends.find(item => item.id === friendId);
      if (!friend) {
        showFormResult(form, 'Em không tìm thấy người bạn này trong danh sách hiện tại.', 'error');
        return;
      }
      setSocialState(stateValue => ({
        ...stateValue,
        gardens: stateValue.gardens.map(garden => garden.id === activeGarden.id
          ? {
              ...garden,
              members: [...garden.members, {
                id: friend.id,
                name: friend.name,
                role,
                status: 'invited',
                invitedAt: 'Vừa gửi',
                tag: 'Đang chờ',
                accent: 'sand',
                note
              }]
            }
          : garden)
      }));
      showFormResult(form, `Đã gửi lời mời cho ${friend.name} với quyền ${getRoleLabel(role).toLowerCase()}.`, 'success');
      form.reset();
      renderShareGardenPage();
    });
  }

  page.querySelectorAll('[data-member-role-toggle]').forEach(button => {
    button.addEventListener('click', () => {
      if (!canManage) return;
      const memberId = button.dataset.memberRoleToggle;
      setSocialState(stateValue => ({
        ...stateValue,
        gardens: stateValue.gardens.map(garden => garden.id === activeGarden.id
          ? {
              ...garden,
              members: garden.members.map(member => member.id === memberId
                ? { ...member, role: member.role === 'viewer' ? 'co_owner' : 'viewer', accent: getRoleClass(member.role === 'viewer' ? 'co_owner' : 'viewer') }
                : member)
            }
          : garden)
      }));
      renderShareGardenPage();
    });
  });

  page.querySelectorAll('[data-member-remove]').forEach(button => {
    button.addEventListener('click', () => {
      if (!canManage) return;
      const memberId = button.dataset.memberRemove;
      setSocialState(stateValue => ({
        ...stateValue,
        gardens: stateValue.gardens.map(garden => garden.id === activeGarden.id
          ? { ...garden, members: garden.members.filter(member => member.id !== memberId) }
          : garden)
      }));
      renderShareGardenPage();
    });
  });

  page.querySelectorAll('[data-pending-cancel]').forEach(button => {
    button.addEventListener('click', () => {
      if (!canManage) return;
      const memberId = button.dataset.pendingCancel;
      setSocialState(stateValue => ({
        ...stateValue,
        gardens: stateValue.gardens.map(garden => garden.id === activeGarden.id
          ? { ...garden, members: garden.members.filter(member => !(member.id === memberId && member.status === 'invited')) }
          : garden)
      }));
      renderShareGardenPage();
    });
  });
};

refreshPortal();
