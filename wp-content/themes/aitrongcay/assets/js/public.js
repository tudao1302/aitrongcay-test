const siteConfig = window.aitrongcayTheme || {};
const rootUrl = siteConfig.rootUrl || '/';
const navItems = Array.isArray(siteConfig.nav) ? siteConfig.nav : [
  { label: 'Trang chủ', url: rootUrl },
  { label: 'Giới thiệu', url: `${rootUrl.replace(/\/$/, '')}/cach-hoat-dong/` },
  { label: 'Chợ quê', url: `${rootUrl.replace(/\/$/, '')}/cho-que/` },
];
const mobileActionItems = [
  { label: 'Đăng nhập', url: `${rootUrl.replace(/\/$/, '')}/dang-nhap/`, tone: 'plain' },
  { label: 'Xem trải nghiệm khu vườn', url: `${rootUrl.replace(/\/$/, '')}/portal/`, tone: 'primary' },
];

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
  } catch (error) {}
});

document.querySelectorAll('[data-current-year]').forEach(el => {
  el.textContent = new Date().getFullYear();
});

const emphasizeBrandName = () => {
  if (!document.body) return;
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

emphasizeBrandName();
