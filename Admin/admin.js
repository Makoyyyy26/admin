/**
 * MICROFINANCIAL MANAGEMENT SYSTEM I: ADMINISTRATIVE
 * Master JavaScript — Dashboard & All Modules
 * Works with Tailwind CSS UI (matching Microfinancial UI reference)
 */

/* ═══════════ AUTH GUARD ═══════════ */
(function authGuard() {
  const depth = (window.location.pathname.match(/modules\/[^/]+\//g) || []).length;
  const prefix = depth > 0 ? '../../' : '';

  fetch(prefix + 'api/auth.php?action=check', { credentials: 'same-origin' })
    .then(r => r.json())
    .then(d => {
      if (!d.authenticated) {
        sessionStorage.removeItem('mf_auth');
        window.location.href = prefix + 'login.php';
      }
    })
    .catch(() => {
      if (!sessionStorage.getItem('mf_auth')) {
        window.location.href = prefix + 'login.php';
      }
    });
})();

document.addEventListener("DOMContentLoaded", () => {

  // ───── Real-Time Clock ─────
  const clockEl = document.getElementById("real-time-clock");
  const updateClock = () => {
    if (!clockEl) return;
    clockEl.textContent = new Date().toLocaleTimeString("en-US", {
      hour12: true, hour: "2-digit", minute: "2-digit", second: "2-digit"
    });
  };
  if (clockEl) { updateClock(); setInterval(updateClock, 1000); }

  // ───── User Dropdown (Tailwind animated) ─────
  const userBtn = document.getElementById("user-menu-button");
  const userDropdown = document.getElementById("user-menu-dropdown");

  const openDropdown = () => {
    if (!userDropdown) return;
    userDropdown.classList.remove("hidden");
    requestAnimationFrame(() => {
      userDropdown.classList.remove("opacity-0", "translate-y-2", "scale-95", "pointer-events-none");
      userDropdown.classList.add("opacity-100", "translate-y-0", "scale-100", "pointer-events-auto");
    });
  };

  const closeDropdown = () => {
    if (!userDropdown) return;
    userDropdown.classList.add("opacity-0", "translate-y-2", "scale-95", "pointer-events-none");
    userDropdown.classList.remove("opacity-100", "translate-y-0", "scale-100", "pointer-events-auto");
    setTimeout(() => userDropdown.classList.add("hidden"), 200);
  };

  if (userBtn && userDropdown) {
    userBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      const isHidden = userDropdown.classList.contains("hidden");
      if (isHidden) openDropdown();
      else closeDropdown();
    });
    document.addEventListener("click", () => {
      if (userDropdown && !userDropdown.classList.contains("hidden")) closeDropdown();
    });
  }

  // ───── Mobile Sidebar Toggle + Overlay ─────
  const sidebar = document.getElementById("sidebar");
  const overlay = document.getElementById("sidebar-overlay");
  const mobileBtn = document.getElementById("mobile-menu-btn");

  const openSidebar = () => {
    if (!sidebar || !overlay) return;
    sidebar.classList.remove("-translate-x-full");
    overlay.classList.remove("hidden");
    requestAnimationFrame(() => overlay.classList.remove("opacity-0"));
  };

  const closeSidebar = () => {
    if (!sidebar || !overlay) return;
    sidebar.classList.add("-translate-x-full");
    overlay.classList.add("opacity-0");
    setTimeout(() => overlay.classList.add("hidden"), 300);
  };

  if (mobileBtn && sidebar && overlay) {
    mobileBtn.addEventListener("click", () => {
      const closed = sidebar.classList.contains("-translate-x-full");
      if (closed) openSidebar();
      else closeSidebar();
    });
    overlay.addEventListener("click", closeSidebar);
  }

  // ───── Sidebar Submenu Toggles ─────
  document.querySelectorAll("[data-submenu]").forEach(btn => {
    const submenu = document.getElementById(btn.dataset.submenu);
    const arrow = btn.querySelector(".arrow");
    if (!submenu) return;

    // Initialize arrow state if submenu is already open
    if (submenu.classList.contains("is-open") && arrow) {
      arrow.classList.add("rotate-180");
    }

    btn.addEventListener("click", (e) => {
      e.preventDefault();
      submenu.classList.toggle("is-open");
      if (arrow) arrow.classList.toggle("rotate-180");
    });
  });

  // ───── Sidebar Submodule Active State (hash-driven) ─────
  const ACTIVE_SUB = 'flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-brand-primary bg-green-50 font-semibold transition-all duration-200';
  const INACTIVE_SUB = 'flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-green-50 hover:text-brand-primary transition-all duration-200 hover:translate-x-1';

  function updateActiveSublink() {
    const hash = location.hash.replace('#', '') || '';
    const sublinks = document.querySelectorAll('.sidebar-sublink');
    // Default map: first sublink in each submenu is active when no hash or unknown hash
    const submenuDefaults = {};
    document.querySelectorAll('[id$="-submenu"]').forEach(sm => {
      const first = sm.querySelector('.sidebar-sublink');
      if (first) submenuDefaults[sm.id] = first;
    });

    let matched = false;
    sublinks.forEach(link => {
      const linkHash = link.getAttribute('data-hash') || '';
      const isActive = hash && linkHash === hash;
      if (isActive) matched = true;

      // Update link classes
      link.className = 'sidebar-sublink ' + (isActive ? ACTIVE_SUB : INACTIVE_SUB);

      // Update the arrow SVG color inside the link
      const svg = link.querySelector('svg');
      if (svg) {
        svg.classList.remove('text-brand-primary', 'text-gray-400', 'group-hover:text-brand-primary');
        svg.classList.add(isActive ? 'text-brand-primary' : 'text-gray-400');
      }
    });

    // If no hash matched but we're on a module page, highlight the first sublink of the open submenu
    if (!matched) {
      document.querySelectorAll('.submenu.is-open').forEach(sm => {
        const first = sm.querySelector('.sidebar-sublink');
        if (first) {
          first.className = 'sidebar-sublink ' + ACTIVE_SUB;
          const svg = first.querySelector('svg');
          if (svg) {
            svg.classList.remove('text-gray-400');
            svg.classList.add('text-brand-primary');
          }
        }
      });
    }
  }

  // Run on load and on hash change
  updateActiveSublink();
  window.addEventListener('hashchange', updateActiveSublink);

  // Click handler for sublinks — update active state immediately on click
  document.querySelectorAll('.sidebar-sublink').forEach(link => {
    link.addEventListener('click', () => {
      // Small delay to let hash change propagate, then update
      setTimeout(updateActiveSublink, 50);
    });
  });

  // ───── Modal Helpers ─────
  window.openModal = (id) => {
    const m = document.getElementById(id);
    if (m) { m.classList.add("open"); document.body.style.overflow = "hidden"; }
  };

  window.closeModal = (id) => {
    const m = document.getElementById(id);
    if (m) { m.classList.remove("open"); document.body.style.overflow = ""; }
  };

  document.querySelectorAll(".modal-overlay").forEach(m => {
    m.addEventListener("click", (e) => {
      if (e.target === m) { m.classList.remove("open"); document.body.style.overflow = ""; }
    });
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      document.querySelectorAll(".modal-overlay.open").forEach(m => {
        m.classList.remove("open");
        document.body.style.overflow = "";
      });
    }
  });

  // ───── API Helper ─────
  window.api = async (endpoint, params = {}) => {
    const url = new URL(endpoint, window.location.origin);
    Object.entries(params).forEach(([k, v]) => { if (v !== undefined) url.searchParams.set(k, v); });
    try {
      const res = await fetch(url.toString());
      return await res.json();
    } catch (err) {
      console.error("API Error:", err);
      return { error: err.message };
    }
  };

  window.apiPost = async (endpoint, body = {}) => {
    try {
      const res = await fetch(endpoint, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(body)
      });
      return await res.json();
    } catch (err) {
      console.error("API Error:", err);
      return { error: err.message };
    }
  };

  // ───── Format helpers ─────
  window.formatDate = (str) => {
    if (!str) return "—";
    return new Date(str).toLocaleDateString("en-US", { year: "numeric", month: "short", day: "numeric" });
  };

  window.formatDateTime = (str) => {
    if (!str) return "—";
    return new Date(str).toLocaleString("en-US", {
      year: "numeric", month: "short", day: "numeric",
      hour: "2-digit", minute: "2-digit"
    });
  };

  window.formatCurrency = (val) => {
    return new Intl.NumberFormat("en-PH", { style: "currency", currency: "PHP" }).format(val || 0);
  };

  window.statusBadge = (status) => {
    const map = {
      pending: "badge-amber", approved: "badge-green", rejected: "badge-red",
      cancelled: "badge-gray", completed: "badge-blue", active: "badge-green",
      archived: "badge-gray", draft: "badge-amber", deleted: "badge-red",
      processing: "badge-blue", failed: "badge-red", not_applicable: "badge-gray",
      open: "badge-amber", in_progress: "badge-blue", pending_review: "badge-purple",
      resolved: "badge-green", closed: "badge-gray", appealed: "badge-red",
      compliant: "badge-green", non_compliant: "badge-red", exempted: "badge-gray",
      expired: "badge-red", terminated: "badge-red", renewed: "badge-teal",
      under_review: "badge-purple", pre_registered: "badge-amber",
      checked_in: "badge-green", checked_out: "badge-gray", no_show: "badge-red",
      excellent: "badge-green", good: "badge-blue", fair: "badge-amber",
      needs_repair: "badge-red", retired: "badge-gray",
      available: "badge-green", occupied: "badge-blue", maintenance: "badge-amber",
    };
    const cls = map[status] || "badge-gray";
    const label = (status || "unknown").replace(/_/g, " ");
    return `<span class="badge ${cls}">${label}</span>`;
  };

  // ───── QR Code Generation ─────
  window.generateQR = (data, size = 200) => {
    return `https://api.qrserver.com/v1/create-qr-code/?data=${encodeURIComponent(data)}&size=${size}x${size}&color=059669`;
  };

  // ───── Notification Bell ─────
  const depth = (window.location.pathname.match(/modules\/[^/]+\//g) || []).length;
  const apiPrefix = depth > 0 ? '../../' : '';

  const bellBtn  = document.getElementById('notification-bell');
  const badge    = document.getElementById('notif-badge');
  let notifPanel = document.getElementById('notif-panel');

  // Build notification dropdown panel (once)
  if (bellBtn && !notifPanel) {
    notifPanel = document.createElement('div');
    notifPanel.id = 'notif-panel';
    notifPanel.className = 'hidden absolute right-0 mt-3 w-80 max-h-96 overflow-y-auto bg-white rounded-xl shadow-lg border border-gray-100 z-50';
    notifPanel.innerHTML = `
      <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
        <span class="text-sm font-bold text-gray-800">Notifications</span>
        <button id="mark-all-read" class="text-xs text-emerald-600 hover:underline font-medium">Mark all read</button>
      </div>
      <div id="notif-list" class="divide-y divide-gray-50"></div>
      <div id="notif-empty" class="hidden px-4 py-6 text-center text-sm text-gray-400">No notifications</div>`;
    bellBtn.parentElement.style.position = 'relative';
    bellBtn.parentElement.appendChild(notifPanel);
  }

  const moduleIcons = { facilities: '🏢', documents: '📄', legal: '⚖️', visitors: '👥', system: '🔔' };

  async function loadNotifications() {
    try {
      const res = await fetch(apiPrefix + 'api/notifications.php?action=list', { credentials: 'same-origin' });
      const json = await res.json();
      const items = json.data || [];
      const listEl  = document.getElementById('notif-list');
      const emptyEl = document.getElementById('notif-empty');
      if (!listEl) return;

      const unread = items.filter(n => !n.is_read).length;
      if (badge) badge.style.display = unread > 0 ? '' : 'none';

      if (items.length === 0) {
        listEl.innerHTML = '';
        if (emptyEl) emptyEl.classList.remove('hidden');
        return;
      }
      if (emptyEl) emptyEl.classList.add('hidden');

      listEl.innerHTML = items.map(n => {
        const icon = moduleIcons[n.module] || '🔔';
        const time = window.formatDateTime ? window.formatDateTime(n.created_at) : n.created_at;
        const bg = n.is_read ? '' : 'bg-emerald-50/40';
        return `<div class="px-4 py-3 hover:bg-gray-50 cursor-pointer transition ${bg}" data-nid="${n.notification_id}" data-link="${n.link || ''}">
          <div class="flex items-start gap-2">
            <span class="text-lg">${icon}</span>
            <div class="flex-1 min-w-0">
              <div class="text-sm font-semibold text-gray-800 ${n.is_read ? '' : 'text-emerald-700'}">${n.title}</div>
              <div class="text-xs text-gray-500 mt-0.5 line-clamp-2">${n.message}</div>
              <div class="text-[10px] text-gray-400 mt-1">${time}</div>
            </div>
            ${n.is_read ? '' : '<span class="mt-1 w-2 h-2 rounded-full bg-emerald-500 flex-shrink-0"></span>'}
          </div>
        </div>`;
      }).join('');

      // Click to mark read & navigate
      listEl.querySelectorAll('[data-nid]').forEach(el => {
        el.addEventListener('click', async () => {
          const nid = el.dataset.nid;
          await fetch(apiPrefix + 'api/notifications.php?action=mark_read', {
            method: 'POST', credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ notification_id: parseInt(nid) })
          });
          el.querySelector('.bg-emerald-500')?.remove();
          el.classList.remove('bg-emerald-50/40');
          loadNotifications();
          if (el.dataset.link) window.location.href = apiPrefix + el.dataset.link.replace(/^\//, '');
        });
      });
    } catch (err) { console.error('Notification load error:', err); }
  }

  if (bellBtn && notifPanel) {
    bellBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      const isHidden = notifPanel.classList.contains('hidden');
      notifPanel.classList.toggle('hidden');
      if (isHidden) loadNotifications();
    });

    document.addEventListener('click', (e) => {
      if (!notifPanel.contains(e.target) && e.target !== bellBtn) {
        notifPanel.classList.add('hidden');
      }
    });

    // Mark all read
    document.addEventListener('click', async (e) => {
      if (e.target.id === 'mark-all-read') {
        await fetch(apiPrefix + 'api/notifications.php?action=mark_all_read', {
          method: 'POST', credentials: 'same-origin',
          headers: { 'Content-Type': 'application/json' }
        });
        loadNotifications();
      }
    });

    // Initial badge check
    (async () => {
      try {
        const res = await fetch(apiPrefix + 'api/notifications.php?action=unread_count', { credentials: 'same-origin' });
        const json = await res.json();
        if (badge) badge.style.display = (json.cnt > 0) ? '' : 'none';
      } catch (_) {}
    })();
  }

  // ───── Profile Modal ─────
  window.openProfileModal = () => {
    let modal = document.getElementById('profile-modal');
    if (!modal) {
      modal = document.createElement('div');
      modal.id = 'profile-modal';
      modal.className = 'modal-overlay fixed inset-0 z-[9999] flex items-center justify-center bg-black/40';
      modal.innerHTML = `
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
          <div class="bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-6 text-white">
            <div class="flex items-center gap-4">
              <div id="prof-avatar" class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center text-2xl font-bold"></div>
              <div><div id="prof-name" class="text-lg font-bold"></div><div id="prof-role" class="text-sm opacity-90"></div></div>
            </div>
          </div>
          <div class="px-6 py-5 space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div><div class="text-xs text-gray-400 uppercase font-medium">Employee ID</div><div id="prof-eid" class="text-sm font-semibold text-gray-800 mt-1"></div></div>
              <div><div class="text-xs text-gray-400 uppercase font-medium">Department</div><div id="prof-dept" class="text-sm font-semibold text-gray-800 mt-1"></div></div>
              <div class="col-span-2"><div class="text-xs text-gray-400 uppercase font-medium">Email</div><div id="prof-email" class="text-sm font-semibold text-gray-800 mt-1"></div></div>
            </div>
          </div>
          <div class="px-6 py-4 border-t border-gray-100 flex justify-end">
            <button onclick="closeModal('profile-modal')" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm font-medium transition">Close</button>
          </div>
        </div>`;
      document.body.appendChild(modal);
      modal.addEventListener('click', (e) => { if (e.target === modal) closeModal('profile-modal'); });
    }

    // Populate from embedded data
    const u = window.__mf_user || {};
    document.getElementById('prof-avatar').textContent = u.initial || '?';
    document.getElementById('prof-name').textContent = u.name || 'Unknown';
    document.getElementById('prof-role').textContent = u.role || '';
    document.getElementById('prof-eid').textContent = u.employee_id || '—';
    document.getElementById('prof-dept').textContent = u.department || '—';
    document.getElementById('prof-email').textContent = u.email || '—';
    openModal('profile-modal');
  };

  // ───── Settings Modal ─────
  window.openSettingsModal = () => {
    let modal = document.getElementById('settings-modal');
    if (!modal) {
      modal = document.createElement('div');
      modal.id = 'settings-modal';
      modal.className = 'modal-overlay fixed inset-0 z-[9999] flex items-center justify-center bg-black/40';
      modal.innerHTML = `
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
          <div class="px-6 py-5 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-800">⚙️ Settings</h3>
          </div>
          <div class="px-6 py-5 space-y-5">
            <div class="flex items-center justify-between">
              <div><div class="text-sm font-semibold text-gray-700">Email Notifications</div><div class="text-xs text-gray-400">Receive alerts via email</div></div>
              <label class="relative inline-flex items-center cursor-pointer"><input type="checkbox" checked class="sr-only peer"><div class="w-10 h-5 bg-gray-200 peer-focus:ring-2 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-emerald-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all"></div></label>
            </div>
            <div class="flex items-center justify-between">
              <div><div class="text-sm font-semibold text-gray-700">Desktop Notifications</div><div class="text-xs text-gray-400">Browser push alerts</div></div>
              <label class="relative inline-flex items-center cursor-pointer"><input type="checkbox" class="sr-only peer"><div class="w-10 h-5 bg-gray-200 peer-focus:ring-2 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-emerald-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all"></div></label>
            </div>
            <div class="flex items-center justify-between">
              <div><div class="text-sm font-semibold text-gray-700">Compact View</div><div class="text-xs text-gray-400">Reduce spacing in tables</div></div>
              <label class="relative inline-flex items-center cursor-pointer"><input type="checkbox" class="sr-only peer"><div class="w-10 h-5 bg-gray-200 peer-focus:ring-2 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-emerald-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all"></div></label>
            </div>
          </div>
          <div class="px-6 py-4 border-t border-gray-100 flex justify-end">
            <button onclick="closeModal('settings-modal')" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm font-medium transition">Close</button>
          </div>
        </div>`;
      document.body.appendChild(modal);
      modal.addEventListener('click', (e) => { if (e.target === modal) closeModal('settings-modal'); });
    }
    openModal('settings-modal');
  };

  // ───── Logout Handler ─────
  document.querySelectorAll('.logout').forEach(link => {
    link.addEventListener('click', async (e) => {
      e.preventDefault();
      try {
        await fetch(apiPrefix + 'api/auth.php?action=logout', { credentials: 'same-origin' });
      } catch (_) {}
      sessionStorage.removeItem('mf_auth');
      window.location.href = apiPrefix + 'login.php';
    });
  });
});
