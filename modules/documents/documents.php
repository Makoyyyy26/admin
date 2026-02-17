<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Document Management — Microfinancial Admin</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            "brand-primary": "#059669",
            "brand-primary-hover": "#047857",
            "brand-background-main": "#F0FDF4",
            "brand-border": "#D1FAE5",
            "brand-text-primary": "#1F2937",
            "brand-text-secondary": "#4B5563",
          }
        }
      }
    }
  </script>
  <link rel="stylesheet" href="../../admin.css" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
</head>

<body class="bg-brand-background-main min-h-screen font-[Inter,'Segoe_UI',system-ui,-apple-system,sans-serif]">

  <?php $activePage = 'documents'; $baseUrl = '../../'; include '../../sidebar.php'; ?>

  <!-- MAIN WRAPPER -->
  <div class="md:pl-72">
    <!-- HEADER -->
    <header class="h-16 bg-white flex items-center justify-between px-4 sm:px-6 relative shadow-[0_2px_8px_rgba(0,0,0,0.06)]">
      <div class="hidden md:block absolute left-0 top-0 h-16 w-[2px] bg-white"></div>
      <div class="flex items-center gap-3">
        <button id="mobile-menu-btn" class="md:hidden w-10 h-10 rounded-xl hover:bg-gray-100 active:bg-gray-200 transition flex items-center justify-center">☰</button>
      </div>
      <div class="flex items-center gap-3 sm:gap-5">
        <span id="real-time-clock" class="text-xs font-bold text-gray-700 bg-gray-50 px-3 py-2 rounded-lg border border-gray-200">--:--:--</span>
        <button id="notification-bell" class="w-10 h-10 rounded-xl hover:bg-gray-100 active:bg-gray-200 transition flex items-center justify-center relative">🔔<span id="notif-badge" class="absolute top-2 right-2 w-2.5 h-2.5 rounded-full bg-red-500 border-2 border-white"></span></button>
        <div class="h-8 w-px bg-gray-200 hidden sm:block"></div>
        <div class="relative">
          <button id="user-menu-button" class="flex items-center gap-3 focus:outline-none group rounded-xl px-2 py-2 hover:bg-gray-100 active:bg-gray-200 transition">
            <div class="w-10 h-10 rounded-full bg-white shadow group-hover:shadow-md transition-shadow overflow-hidden flex items-center justify-center border border-gray-100"><div class="w-full h-full flex items-center justify-center font-bold text-brand-primary bg-emerald-50"><?= $userInitial ?></div></div>
            <div class="hidden md:flex flex-col items-start text-left">
              <span class="text-sm font-bold text-gray-700 group-hover:text-brand-primary transition-colors"><?= htmlspecialchars($userName) ?></span>
              <span class="text-[10px] text-gray-500 font-medium uppercase group-hover:text-brand-primary transition-colors"><?= htmlspecialchars($userRole) ?></span>
            </div>
            <svg class="w-4 h-4 text-gray-400 group-hover:text-brand-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
          </button>
          <div id="user-menu-dropdown" class="dropdown-panel hidden opacity-0 translate-y-2 scale-95 pointer-events-none absolute right-0 mt-3 w-56 bg-white rounded-xl shadow-lg border border-gray-100 transition-all duration-200 z-50">
            <div class="px-4 py-3 border-b border-gray-100"><div class="text-sm font-bold text-gray-800"><?= htmlspecialchars($userName) ?></div><div class="text-xs text-gray-500"><?= htmlspecialchars($sessionUser['email'] ?? '') ?></div></div>
            <a href="#" onclick="openProfileModal(); return false;" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition">👤 &nbsp;My Profile</a>
            <a href="#" onclick="openSettingsModal(); return false;" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition">⚙️ &nbsp;Settings</a>
            <div class="h-px bg-gray-100"></div>
            <a href="#" class="block px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition rounded-b-xl logout">🚪 &nbsp;Logout</a>
          </div>
        </div>
      </div>
    </header>

    <!-- PAGE CONTENT -->
    <main class="p-6">

      <div class="animate-in">
        <h1 class="page-title">Document Management</h1>
        <p class="page-subtitle">Folder-based document organization with automatic 6-month archival and 3-year retention lifecycle. No deletion — view only.</p>
      </div>

      <!-- LIFECYCLE INFO BANNER -->
      <div class="animate-in delay-1" style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap">
        <div style="flex:1;min-width:220px;background:#D1FAE5;border:1px solid #A7F3D0;border-radius:12px;padding:12px 16px;display:flex;align-items:center;gap:10px">
          <span style="font-size:22px">🟢</span>
          <div><div style="font-weight:700;font-size:13px;color:#065F46">Active</div><div style="font-size:11px;color:#047857">Documents &lt; 6 months old</div></div>
        </div>
        <div style="flex:1;min-width:220px;background:#FEF3C7;border:1px solid #FDE68A;border-radius:12px;padding:12px 16px;display:flex;align-items:center;gap:10px">
          <span style="font-size:22px">📦</span>
          <div><div style="font-weight:700;font-size:13px;color:#92400E">Archived</div><div style="font-size:11px;color:#B45309">6 months – 3 years old</div></div>
        </div>
        <div style="flex:1;min-width:220px;background:#EDE9FE;border:1px solid #DDD6FE;border-radius:12px;padding:12px 16px;display:flex;align-items:center;gap:10px">
          <span style="font-size:22px">🔒</span>
          <div><div style="font-weight:700;font-size:13px;color:#5B21B6">Retained</div><div style="font-size:11px;color:#6D28D9">3+ years old · Permanent record</div></div>
        </div>
      </div>

      <!-- STAT CARDS -->
      <div class="stats-grid animate-in delay-1">
        <div class="stat-card"><div class="stat-icon blue">📄</div><div class="stat-info"><div class="stat-value" id="stat-total">—</div><div class="stat-label">Total Documents</div></div></div>
        <div class="stat-card"><div class="stat-icon green">🟢</div><div class="stat-info"><div class="stat-value" id="stat-active">—</div><div class="stat-label">Active (&lt;6 mo)</div></div></div>
        <div class="stat-card"><div class="stat-icon amber">📦</div><div class="stat-info"><div class="stat-value" id="stat-archived">—</div><div class="stat-label">Archived (6mo–3yr)</div></div></div>
        <div class="stat-card"><div class="stat-icon purple">🔒</div><div class="stat-info"><div class="stat-value" id="stat-retained">—</div><div class="stat-label">Retained (3yr+)</div></div></div>
        <div class="stat-card"><div class="stat-icon blue">🏢</div><div class="stat-info"><div class="stat-value" id="stat-depts">—</div><div class="stat-label">Departments</div></div></div>
      </div>



      <!-- ═══════════════════════════════════════════════════ -->
      <!-- TAB: Department Folders                              -->
      <!-- ═══════════════════════════════════════════════════ -->
      <div id="tab-folders" class="tab-content active animate-in delay-3">

        <!-- Search -->
        <div style="margin-bottom:20px;display:flex;gap:10px;align-items:center;flex-wrap:wrap">
          <div style="flex:1;min-width:260px;position:relative">
            <input type="text" id="folder-search" class="form-input" placeholder="🔍 Search documents by title, folder, or department..." oninput="filterFolderSearch(this.value)" style="padding-left:14px">
          </div>
          <span id="folder-summary" style="font-size:12px;color:#9CA3AF">Loading...</span>
        </div>

        <!-- Department Folder Grid -->
        <div id="dept-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;margin-bottom:20px">
        </div>

        <!-- Expanded Folder Panel -->
        <div id="folder-panel" class="card" style="display:none;margin-top:4px">
          <div class="card-header">
            <div style="display:flex;align-items:center;gap:10px">
              <button class="btn btn-outline btn-sm" onclick="closeFolderPanel()">← Back</button>
              <span class="card-title" id="folder-panel-title">📂 Department — Folder</span>
            </div>
            <span id="folder-panel-count" style="font-size:12px;color:#6B7280">0 documents</span>
          </div>
          <div class="card-body" id="folder-panel-body">
          </div>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════ -->
      <!-- TAB: All Documents                                   -->
      <!-- ═══════════════════════════════════════════════════ -->
      <div id="tab-all" class="tab-content">
        <div class="card">
          <div class="card-header">
            <span class="card-title">All Documents</span>
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
              <button class="btn-export btn-export-pdf btn-export-sm" onclick="exportAllDocs('pdf')" title="Export PDF">📄 PDF</button>
              <button class="btn-export btn-export-csv btn-export-sm" onclick="exportAllDocs('csv')" title="Export CSV">📊 CSV</button>
              <select id="filter-dept" class="form-input" style="width:auto;padding:6px 12px;font-size:12px" onchange="renderAllDocsTable()">
                <option value="">All Departments</option>
              </select>
              <select id="filter-lifecycle" class="form-input" style="width:auto;padding:6px 12px;font-size:12px" onchange="renderAllDocsTable()">
                <option value="">All Lifecycle</option>
                <option value="active">🟢 Active</option>
                <option value="archived">📦 Archived</option>
                <option value="retained">🔒 Retained</option>
              </select>
              <input type="text" id="filter-search" class="form-input" style="width:200px;padding:6px 12px;font-size:12px" placeholder="🔍 Search title..." oninput="renderAllDocsTable()">
            </div>
          </div>
          <div class="card-body">
            <table class="data-table" id="all-docs-table">
              <thead><tr>
                <th>Code</th><th>Title</th><th>📂 Folder</th><th>Department</th><th>Type</th>
                <th>Date Filed</th><th>Lifecycle</th><th>Actions</th>
              </tr></thead>
              <tbody id="all-docs-tbody">
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════ -->
      <!-- TAB: Archived (6 months – 3 years)                   -->
      <!-- ═══════════════════════════════════════════════════ -->
      <div id="tab-archived" class="tab-content">
        <div class="card" style="margin-bottom:16px;border-left:4px solid #F59E0B">
          <div class="card-body" style="padding:14px 20px;display:flex;align-items:center;gap:12px">
            <span style="font-size:24px">📦</span>
            <div>
              <div style="font-weight:700;color:#92400E">Archive Policy — 6 Month Automatic Archival</div>
              <div style="font-size:13px;color:#B45309">Documents older than 6 months are automatically archived. No deletion is allowed. All documents remain viewable for reference.</div>
            </div>
          </div>
        </div>
        <div class="card">
          <div class="card-header">
            <span class="card-title">📦 Archived Documents</span>
            <div style="display:flex;gap:8px;align-items:center">
              <button class="btn-export btn-export-pdf btn-export-sm" onclick="exportArchivedDocs('pdf')" title="Export PDF">📄 PDF</button>
              <button class="btn-export btn-export-csv btn-export-sm" onclick="exportArchivedDocs('csv')" title="Export CSV">📊 CSV</button>
              <span style="font-size:12px;color:#6B7280" id="archived-count">Loading...</span>
            </div>
          </div>
          <div class="card-body">
            <table class="data-table">
              <thead><tr><th>Code</th><th>Title</th><th>Department</th><th>Folder</th><th>Date Filed</th><th>Age</th><th>Actions</th></tr></thead>
              <tbody id="archived-tbody"></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════ -->
      <!-- TAB: Retained (3+ years)                             -->
      <!-- ═══════════════════════════════════════════════════ -->
      <div id="tab-retained" class="tab-content">
        <div class="card" style="margin-bottom:16px;border-left:4px solid #7C3AED">
          <div class="card-body" style="padding:14px 20px;display:flex;align-items:center;gap:12px">
            <span style="font-size:24px">🔒</span>
            <div>
              <div style="font-weight:700;color:#5B21B6">Retention Policy — 3 Year Permanent Retention</div>
              <div style="font-size:13px;color:#6D28D9">Documents older than 3 years enter permanent retention. They are preserved indefinitely as institutional records. No modification or deletion is allowed.</div>
            </div>
          </div>
        </div>
        <div class="card">
          <div class="card-header">
            <span class="card-title">🔒 Retained Documents</span>
            <div style="display:flex;gap:8px;align-items:center">
              <button class="btn-export btn-export-pdf btn-export-sm" onclick="exportRetainedDocs('pdf')" title="Export PDF">📄 PDF</button>
              <button class="btn-export btn-export-csv btn-export-sm" onclick="exportRetainedDocs('csv')" title="Export CSV">📊 CSV</button>
              <span style="font-size:12px;color:#6B7280" id="retained-count">Loading...</span>
            </div>
          </div>
          <div class="card-body">
            <table class="data-table">
              <thead><tr><th>Code</th><th>Title</th><th>Department</th><th>Folder</th><th>Date Filed</th><th>Age</th><th>Actions</th></tr></thead>
              <tbody id="retained-tbody"></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════ -->
      <!-- MODAL: View Document (No edit, no delete)            -->
      <!-- ═══════════════════════════════════════════════════ -->
      <div id="modal-view" class="modal-overlay" onclick="if(event.target===this)closeModal('modal-view')">
        <div class="modal" style="max-width:600px">
          <div class="modal-header" style="display:flex;align-items:center;justify-content:space-between">
            <span class="modal-title" id="modal-view-title">Document Details</span>
            <div style="display:flex;align-items:center;gap:8px">
              <button class="btn btn-export-pdf btn-export-sm" onclick="exportDocumentPDF()" title="Download PDF" style="margin:0;padding:5px 12px;font-size:12px">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:4px"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><polyline points="9 15 12 18 15 15"/></svg>
                PDF
              </button>
              <button class="modal-close" onclick="closeModal('modal-view')">&times;</button>
            </div>
          </div>
          <div class="modal-body" id="modal-view-body">
          </div>
          <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modal-view')">Close</button>
          </div>
        </div>
      </div>

    </main>
  </div>

<script src="../../admin.js"></script>
<script src="../../export.js"></script>
<script>
/* ═══════════════════════════════════════════════════════
   DOCUMENT MANAGEMENT MODULE — API-driven
   ═══════════════════════════════════════════════════════ */

const API = '../../api/documents.php';
let allDocuments = [];
let folders = [];
let stats = {};

// ───── Department Display Config ─────
const deptConfig = {
  'HR 1':      { icon: '👥', folder: 'Employee Records',           color: '#059669', bg: '#D1FAE5' },
  'HR 3':      { icon: '🎓', folder: 'Training & Development',     color: '#7C3AED', bg: '#EDE9FE' },
  'HR 4':      { icon: '📋', folder: 'Recruitment',                color: '#DC2626', bg: '#FEE2E2' },
  'Core 1':    { icon: '🏦', folder: 'Loan Processing',            color: '#D97706', bg: '#FEF3C7' },
  'Core 2':    { icon: '📊', folder: 'Collections & Disbursement', color: '#059669', bg: '#D1FAE5' },
  'Log 1':     { icon: '🚚', folder: 'Procurement & Fleet',        color: '#0891B2', bg: '#CFFAFE' },
  'Log 2':     { icon: '📦', folder: 'Warehouse & Equipment',      color: '#9333EA', bg: '#F3E8FF' },
  'Financial': { icon: '💵', folder: 'Financial Reports',          color: '#16A34A', bg: '#DCFCE7' },
};
const defaultDept = { icon: '📁', folder: 'Documents', color: '#6B7280', bg: '#F3F4F6' };

function getDeptInfo(deptId) {
  return deptConfig[deptId] || { ...defaultDept, folder: deptId || 'Documents' };
}

// ───── Lifecycle Computation (client-side from created_at) ─────
const SIX_MONTHS  = 6 * 30.44 * 24 * 60 * 60 * 1000;
const THREE_YEARS = 3 * 365.25 * 24 * 60 * 60 * 1000;

function getLifecycle(dateStr) {
  const age = Date.now() - new Date(dateStr).getTime();
  if (age >= THREE_YEARS) return { status: 'retained', label: '🔒 Retained', badge: 'badge-purple', color: '#7C3AED', bg: '#EDE9FE' };
  if (age >= SIX_MONTHS)  return { status: 'archived', label: '📦 Archived', badge: 'badge-amber',  color: '#D97706', bg: '#FEF3C7' };
  return { status: 'active', label: '🟢 Active', badge: 'badge-green', color: '#059669', bg: '#D1FAE5' };
}

function getAge(dateStr) {
  const diff = Date.now() - new Date(dateStr).getTime();
  const days = Math.floor(diff / 86400000);
  if (days < 0) return '0 days';
  if (days < 31) return days + ' days';
  const months = Math.floor(days / 30.44);
  if (months < 12) return months + ' month' + (months !== 1 ? 's' : '');
  const years = Math.floor(months / 12);
  const rem = months % 12;
  return years + 'yr' + (rem ? ' ' + rem + 'mo' : '');
}

function fmtDate(dateStr) {
  if (!dateStr) return '—';
  return new Date(dateStr).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

// ───── Document Field Accessors ─────
function getFileIcon(doc) {
  const raw = (doc.file_type || doc.file_name || doc.fileType || '').toLowerCase();
  if (raw.includes('pdf'))               return '📕';
  if (raw.includes('xls') || raw.includes('xlsx')) return '📗';
  if (raw.includes('doc') || raw.includes('docx')) return '📘';
  return '📄';
}

function getDocDate(doc)   { return doc.created_at || doc.date || ''; }
function getDocDept(doc)   { return doc.department || doc.dept || ''; }
function getDocCode(doc)   { return doc.document_code || doc.code || ''; }
function getDocType(doc)   { return doc.document_type || doc.type || ''; }
function getDocFolder(doc) { return doc.folder_name || doc.folder || getDeptInfo(getDocDept(doc)).folder; }
function getDocId(doc)     { return doc.document_id || doc.id; }

function getDocFileType(doc) {
  const ft = doc.file_type || doc.fileType || '';
  if (ft) return ft.replace(/^\./, '').toUpperCase();
  const fn = doc.file_name || '';
  const idx = fn.lastIndexOf('.');
  return idx >= 0 ? fn.substring(idx + 1).toUpperCase() : '';
}

function getDocFileSize(doc) {
  if (doc.file_size) {
    const bytes = parseInt(doc.file_size, 10);
    if (!isNaN(bytes)) {
      if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
      if (bytes >= 1024)    return (bytes / 1024).toFixed(0) + ' KB';
      return bytes + ' B';
    }
    return String(doc.file_size);
  }
  return doc.fileSize || '';
}

const viewSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';

// ═══════════════════════════════════════════════════════
// DATA LOADING — All from API
// ═══════════════════════════════════════════════════════

async function loadData() {
  try {
    const [sRes, dRes, fRes] = await Promise.all([
      fetch(API + '?action=dashboard_stats'),
      fetch(API + '?action=list_documents'),
      fetch(API + '?action=list_folders')
    ]);

    const sJson = await sRes.json();
    const dJson = await dRes.json();
    const fJson = await fRes.json();

    stats        = sJson.data || sJson || {};
    allDocuments = dJson.data || dJson || [];
    folders      = fJson.data || fJson || [];

    if (!Array.isArray(allDocuments)) allDocuments = [];

    renderStats();
    populateDeptFilter();
    renderDeptGrid();
    renderAllDocsTable();
    renderArchivedTable();
    renderRetainedTable();
  } catch (err) {
    console.error('Failed to load document data:', err);
    Swal.fire({
      icon: 'error',
      title: 'Load Error',
      text: 'Could not load document data. Please refresh the page.',
      confirmButtonColor: '#059669'
    });
  }
}

// ═══════════════════════════════════════════════════════
// RENDER STATS
// ═══════════════════════════════════════════════════════

function renderStats() {
  let activeCount = 0, archivedCount = 0, retainedCount = 0;
  const uniqueDepts = new Set();

  allDocuments.forEach(doc => {
    const lc = getLifecycle(getDocDate(doc));
    if (lc.status === 'active')   activeCount++;
    if (lc.status === 'archived') archivedCount++;
    if (lc.status === 'retained') retainedCount++;
    const dept = getDocDept(doc);
    if (dept) uniqueDepts.add(dept);
  });

  document.getElementById('stat-total').textContent    = allDocuments.length;
  document.getElementById('stat-active').textContent   = activeCount;
  document.getElementById('stat-archived').textContent = archivedCount;
  document.getElementById('stat-retained').textContent = retainedCount;
  document.getElementById('stat-depts').textContent    = uniqueDepts.size;
}

// ═══════════════════════════════════════════════════════
// DEPARTMENT FILTER DROPDOWN — populated from data
// ═══════════════════════════════════════════════════════

function populateDeptFilter() {
  const select = document.getElementById('filter-dept');
  const depts = [...new Set(allDocuments.map(d => getDocDept(d)).filter(Boolean))].sort();
  select.innerHTML = '<option value="">All Departments</option>';
  depts.forEach(dept => {
    const opt = document.createElement('option');
    opt.value = dept;
    opt.textContent = dept;
    select.appendChild(opt);
  });
}

// ═══════════════════════════════════════════════════════
// TAB 1 — DEPARTMENT FOLDERS
// ═══════════════════════════════════════════════════════

let deptList = [];

function renderDeptGrid() {
  const grid = document.getElementById('dept-grid');
  grid.innerHTML = '';

  // Build unique departments from fetched data
  const deptMap = {};
  allDocuments.forEach(doc => {
    const dept = getDocDept(doc);
    if (!dept) return;
    if (!deptMap[dept]) deptMap[dept] = [];
    deptMap[dept].push(doc);
  });

  deptList = Object.keys(deptMap).sort();

  document.getElementById('folder-summary').textContent =
    deptList.length + ' department folder' + (deptList.length !== 1 ? 's' : '') + ' · ' + allDocuments.length + ' documents';

  deptList.forEach(deptId => {
    const info = getDeptInfo(deptId);
    const docs = deptMap[deptId];
    const active   = docs.filter(d => getLifecycle(getDocDate(d)).status === 'active').length;
    const archived = docs.filter(d => getLifecycle(getDocDate(d)).status === 'archived').length;
    const retained = docs.filter(d => getLifecycle(getDocDate(d)).status === 'retained').length;

    const card = document.createElement('div');
    card.className = 'card dept-folder-card';
    card.dataset.dept = deptId;
    card.style.cssText = 'margin-bottom:0;cursor:pointer;transition:all 0.2s;border:2px solid transparent;';
    card.onmouseover = () => { card.style.borderColor = info.color; card.style.boxShadow = '0 6px 20px rgba(0,0,0,0.08)'; card.style.transform = 'translateY(-2px)'; };
    card.onmouseout  = () => { card.style.borderColor = 'transparent'; card.style.boxShadow = ''; card.style.transform = ''; };
    card.onclick = () => openFolderPanel(deptId);

    card.innerHTML = `
      <div class="card-body padded">
        <div style="display:flex;align-items:flex-start;gap:14px">
          <div style="width:52px;height:52px;border-radius:14px;background:${info.bg};display:flex;align-items:center;justify-content:center;font-size:26px;flex-shrink:0">${info.icon}</div>
          <div style="flex:1;min-width:0">
            <div style="font-size:15px;font-weight:800;color:#1F2937">${deptId}</div>
            <div style="font-size:12px;color:#6B7280;margin-top:2px">📂 ${info.folder}</div>
            <div style="font-size:12px;font-weight:600;color:${info.color};margin-top:6px">${docs.length} document${docs.length !== 1 ? 's' : ''}</div>
          </div>
        </div>
        <div style="display:flex;gap:8px;margin-top:14px;font-size:11px;flex-wrap:wrap">
          ${active   ? `<span style="background:#D1FAE5;color:#065F46;padding:2px 8px;border-radius:6px;font-weight:600">🟢 ${active} Active</span>` : ''}
          ${archived ? `<span style="background:#FEF3C7;color:#92400E;padding:2px 8px;border-radius:6px;font-weight:600">📦 ${archived} Archived</span>` : ''}
          ${retained ? `<span style="background:#EDE9FE;color:#5B21B6;padding:2px 8px;border-radius:6px;font-weight:600">🔒 ${retained} Retained</span>` : ''}
        </div>
      </div>`;
    grid.appendChild(card);
  });
}

function openFolderPanel(deptId) {
  const panel = document.getElementById('folder-panel');
  const info  = getDeptInfo(deptId);
  const docs  = allDocuments.filter(d => getDocDept(d) === deptId);

  document.getElementById('folder-panel-title').textContent = `📂 ${deptId} — ${info.folder}`;
  document.getElementById('folder-panel-count').textContent = `${docs.length} document${docs.length !== 1 ? 's' : ''}`;

  let html = '<div style="padding:12px">';
  if (docs.length === 0) {
    html += '<div class="empty-state" style="padding:30px"><div style="font-size:40px;margin-bottom:8px">📭</div><div style="font-weight:600">No documents in this folder</div></div>';
  } else {
    docs.sort((a, b) => new Date(getDocDate(b)) - new Date(getDocDate(a)));
    docs.forEach(doc => {
      const lc       = getLifecycle(getDocDate(doc));
      const docId    = getDocId(doc);
      const code     = getDocCode(doc);
      const title    = doc.title || '';
      const fileType = getDocFileType(doc);
      const fileSize = getDocFileSize(doc);
      const desc     = doc.description || '';
      const conf     = doc.confidentiality || '';
      const dateStr  = getDocDate(doc);

      html += `
        <div style="border:1px solid #E5E7EB;border-radius:12px;padding:16px;margin-bottom:10px;display:flex;gap:14px;align-items:flex-start;transition:all 0.2s;cursor:pointer;border-left:4px solid ${lc.color}"
             onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.06)'" onmouseout="this.style.boxShadow='none'"
             onclick="viewDocument(${docId})">
          <div style="width:44px;height:44px;border-radius:10px;background:${lc.bg};display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0">
            ${getFileIcon(doc)}
          </div>
          <div style="flex:1;min-width:0">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;flex-wrap:wrap">
              <div>
                <div style="font-weight:700;font-size:14px;color:#1F2937">${title}</div>
                <div style="font-size:11px;color:#9CA3AF;margin-top:2px">${code}${fileType ? ' · ' + fileType : ''}${fileSize ? ' · ' + fileSize : ''}</div>
              </div>
              <div style="display:flex;gap:6px;align-items:center;flex-shrink:0">
                <span class="badge ${lc.badge}" style="font-size:11px">${lc.label}</span>
              </div>
            </div>
            ${desc ? `<div style="font-size:12px;color:#6B7280;margin-top:6px;line-height:1.5">${desc}</div>` : ''}
            <div style="display:flex;gap:14px;margin-top:8px;font-size:11px;color:#9CA3AF;flex-wrap:wrap">
              <span>📅 ${fmtDate(dateStr)}</span>
              <span>⏱ ${getAge(dateStr)}</span>
              ${conf ? `<span>${conf === 'Restricted' ? '🔴' : conf === 'Internal' ? '🟡' : '🟢'} ${conf}</span>` : ''}
            </div>
          </div>
          <button class="btn btn-outline btn-sm" style="flex-shrink:0" onclick="event.stopPropagation();viewDocument(${docId})" title="View Document">${viewSvg}</button>
        </div>`;
    });
  }
  html += '</div>';
  document.getElementById('folder-panel-body').innerHTML = html;
  panel.style.display = '';
  panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function closeFolderPanel() {
  document.getElementById('folder-panel').style.display = 'none';
}

function filterFolderSearch(query) {
  const q = query.toLowerCase();
  const cards = document.querySelectorAll('.dept-folder-card');
  cards.forEach(card => {
    const deptId = card.dataset.dept;
    const info   = getDeptInfo(deptId);
    const docs   = allDocuments.filter(d => getDocDept(d) === deptId);
    const match  = !q
      || deptId.toLowerCase().includes(q)
      || info.folder.toLowerCase().includes(q)
      || docs.some(d => (d.title || '').toLowerCase().includes(q));
    card.style.display = match ? '' : 'none';
  });
}

// ═══════════════════════════════════════════════════════
// TAB 2 — ALL DOCUMENTS TABLE
// ═══════════════════════════════════════════════════════

function renderAllDocsTable() {
  const dept      = document.getElementById('filter-dept').value;
  const lifecycle = document.getElementById('filter-lifecycle').value;
  const search    = document.getElementById('filter-search').value.toLowerCase();

  const filtered = allDocuments.filter(doc => {
    if (dept && getDocDept(doc) !== dept) return false;
    if (lifecycle && getLifecycle(getDocDate(doc)).status !== lifecycle) return false;
    if (search) {
      const t = (doc.title || '').toLowerCase();
      const c = getDocCode(doc).toLowerCase();
      if (!t.includes(search) && !c.includes(search)) return false;
    }
    return true;
  });

  const tbody = document.getElementById('all-docs-tbody');
  if (filtered.length === 0) {
    tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:30px;color:#9CA3AF">No documents found</td></tr>';
    return;
  }

  tbody.innerHTML = filtered.map(doc => {
    const lc      = getLifecycle(getDocDate(doc));
    const docId   = getDocId(doc);
    const code    = getDocCode(doc);
    const title   = doc.title || '';
    const folder  = getDocFolder(doc);
    const docDept = getDocDept(doc);
    const type    = getDocType(doc);
    const dateStr = getDocDate(doc);

    return `<tr>
      <td style="font-weight:600;font-size:12px">${code}</td>
      <td style="font-weight:600">${title}</td>
      <td style="font-size:12px">📂 ${folder}</td>
      <td><span class="badge badge-blue" style="font-size:11px">${docDept}</span></td>
      <td style="font-size:12px">${type}</td>
      <td style="font-size:12px">${fmtDate(dateStr)}</td>
      <td><span class="badge ${lc.badge}" style="font-size:11px">${lc.label}</span></td>
      <td><button class="btn btn-outline btn-sm" onclick="viewDocument(${docId})" title="View">${viewSvg}</button></td>
    </tr>`;
  }).join('');
}

// ═══════════════════════════════════════════════════════
// TAB 3 — ARCHIVED DOCUMENTS (6 months – 3 years)
// ═══════════════════════════════════════════════════════

function renderArchivedTable() {
  const docs = allDocuments.filter(d => getLifecycle(getDocDate(d)).status === 'archived');
  document.getElementById('archived-count').textContent = docs.length + ' document' + (docs.length !== 1 ? 's' : '');

  const tbody = document.getElementById('archived-tbody');
  if (docs.length === 0) {
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:30px;color:#9CA3AF">No archived documents</td></tr>';
    return;
  }

  tbody.innerHTML = docs.map(doc => {
    const docId   = getDocId(doc);
    const code    = getDocCode(doc);
    const title   = doc.title || '';
    const docDept = getDocDept(doc);
    const folder  = getDocFolder(doc);
    const dateStr = getDocDate(doc);

    return `<tr>
      <td style="font-weight:600;font-size:12px">${code}</td>
      <td style="font-weight:600">${title}</td>
      <td><span class="badge badge-blue" style="font-size:11px">${docDept}</span></td>
      <td style="font-size:12px">📂 ${folder}</td>
      <td style="font-size:12px">${fmtDate(dateStr)}</td>
      <td style="font-size:12px;color:#D97706;font-weight:600">${getAge(dateStr)}</td>
      <td><button class="btn btn-outline btn-sm" onclick="viewDocument(${docId})" title="View Only">${viewSvg}</button></td>
    </tr>`;
  }).join('');
}

// ═══════════════════════════════════════════════════════
// TAB 4 — RETAINED DOCUMENTS (3+ years)
// ═══════════════════════════════════════════════════════

function renderRetainedTable() {
  const docs = allDocuments.filter(d => getLifecycle(getDocDate(d)).status === 'retained');
  document.getElementById('retained-count').textContent = docs.length + ' document' + (docs.length !== 1 ? 's' : '');

  const tbody = document.getElementById('retained-tbody');
  if (docs.length === 0) {
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:30px;color:#9CA3AF">No retained documents</td></tr>';
    return;
  }

  tbody.innerHTML = docs.map(doc => {
    const docId   = getDocId(doc);
    const code    = getDocCode(doc);
    const title   = doc.title || '';
    const docDept = getDocDept(doc);
    const folder  = getDocFolder(doc);
    const dateStr = getDocDate(doc);

    return `<tr>
      <td style="font-weight:600;font-size:12px">${code}</td>
      <td style="font-weight:600">${title}</td>
      <td><span class="badge badge-blue" style="font-size:11px">${docDept}</span></td>
      <td style="font-size:12px">📂 ${folder}</td>
      <td style="font-size:12px">${fmtDate(dateStr)}</td>
      <td style="font-size:12px;color:#7C3AED;font-weight:600">${getAge(dateStr)}</td>
      <td><button class="btn btn-outline btn-sm" onclick="viewDocument(${docId})" title="View Only">${viewSvg}</button></td>
    </tr>`;
  }).join('');
}

// ═══════════════════════════════════════════════════════
// VIEW DOCUMENT MODAL (View only — no edit, no delete)
// ═══════════════════════════════════════════════════════

function viewDocument(id) {
  const doc = allDocuments.find(d => getDocId(d) == id);
  if (!doc) return;

  const dateStr  = getDocDate(doc);
  const lc       = getLifecycle(dateStr);
  const docDept  = getDocDept(doc);
  const info     = getDeptInfo(docDept);
  const fileIcon = getFileIcon(doc);
  const code     = getDocCode(doc);
  const title    = doc.title || '';
  const fileType = getDocFileType(doc);
  const fileSize = getDocFileSize(doc);
  const folder   = getDocFolder(doc);
  const type     = getDocType(doc);
  const conf     = doc.confidentiality || '';
  const desc     = doc.description || '';

  let confBadge = '—';
  if (conf === 'Restricted')  confBadge = '<span class="badge badge-red">Restricted</span>';
  else if (conf === 'Internal') confBadge = '<span class="badge badge-amber">Internal</span>';
  else if (conf === 'Public')   confBadge = '<span class="badge badge-green">Public</span>';
  else if (conf) confBadge = `<span class="badge">${conf}</span>`;

  document.getElementById('modal-view-title').textContent = title;
  document.getElementById('modal-view-body').innerHTML = `
    <div>
      <!-- Lifecycle banner -->
      <div style="background:${lc.bg};padding:10px 14px;border-radius:10px;margin-bottom:16px;font-size:13px;font-weight:700;color:${lc.color};display:flex;justify-content:space-between;align-items:center">
        <span>${lc.label}</span>
        <span style="font-size:11px;font-weight:500">Filed ${getAge(dateStr)} ago</span>
      </div>

      <!-- File Info -->
      <div style="display:flex;align-items:center;gap:14px;padding:14px;background:#F9FAFB;border-radius:12px;margin-bottom:16px">
        <div style="font-size:36px">${fileIcon}</div>
        <div>
          <div style="font-weight:700;font-size:14px;color:#1F2937">${title}</div>
          <div style="font-size:12px;color:#6B7280;margin-top:2px">${code}${fileType ? ' · ' + fileType : ''}${fileSize ? ' · ' + fileSize : ''}</div>
        </div>
      </div>

      <!-- Details Table -->
      <table style="width:100%;font-size:13px;border-collapse:collapse">
        <tr style="border-bottom:1px solid #F3F4F6"><td style="padding:10px 0;font-weight:600;color:#6B7280;width:140px">🏢 Department</td><td style="padding:10px 0;color:#1F2937">${docDept}</td></tr>
        <tr style="border-bottom:1px solid #F3F4F6"><td style="padding:10px 0;font-weight:600;color:#6B7280">📂 Folder</td><td style="padding:10px 0;color:#1F2937">${folder}</td></tr>
        <tr style="border-bottom:1px solid #F3F4F6"><td style="padding:10px 0;font-weight:600;color:#6B7280">📋 Type</td><td style="padding:10px 0;color:#1F2937">${type || '—'}</td></tr>
        <tr style="border-bottom:1px solid #F3F4F6"><td style="padding:10px 0;font-weight:600;color:#6B7280">� Designated By</td><td style="padding:10px 0;color:#1F2937">${doc.designated_employee || doc.uploaded_by || doc.created_by || '—'}</td></tr>
        <tr style="border-bottom:1px solid #F3F4F6"><td style="padding:10px 0;font-weight:600;color:#6B7280">�📅 Date Filed</td><td style="padding:10px 0;color:#1F2937">${fmtDate(dateStr)}</td></tr>
        <tr style="border-bottom:1px solid #F3F4F6"><td style="padding:10px 0;font-weight:600;color:#6B7280">🔐 Confidentiality</td><td style="padding:10px 0">${confBadge}</td></tr>
        <tr style="border-bottom:1px solid #F3F4F6"><td style="padding:10px 0;font-weight:600;color:#6B7280">📊 Lifecycle</td><td style="padding:10px 0"><span class="badge ${lc.badge}">${lc.label}</span></td></tr>
        ${desc ? `<tr><td style="padding:10px 0;font-weight:600;color:#6B7280;vertical-align:top">📝 Description</td><td style="padding:10px 0;color:#1F2937;line-height:1.6">${desc}</td></tr>` : ''}
      </table>

      <!-- Lifecycle Timeline -->
      <div style="margin-top:20px;padding:16px;background:#F9FAFB;border-radius:12px">
        <div style="font-weight:700;font-size:13px;color:#1F2937;margin-bottom:12px">📐 Document Lifecycle Timeline</div>
        <div style="display:flex;align-items:center;gap:0;font-size:11px">
          <div style="text-align:center;flex:1">
            <div style="height:6px;background:${lc.status === 'active' || lc.status === 'archived' || lc.status === 'retained' ? '#059669' : '#E5E7EB'};border-radius:3px 0 0 3px"></div>
            <div style="margin-top:6px;font-weight:600;color:${lc.status === 'active' ? '#059669' : '#9CA3AF'}">🟢 Active</div>
            <div style="color:#9CA3AF">0–6 months</div>
          </div>
          <div style="text-align:center;flex:1">
            <div style="height:6px;background:${lc.status === 'archived' || lc.status === 'retained' ? '#D97706' : '#E5E7EB'}"></div>
            <div style="margin-top:6px;font-weight:600;color:${lc.status === 'archived' ? '#D97706' : '#9CA3AF'}">📦 Archive</div>
            <div style="color:#9CA3AF">6mo–3yr</div>
          </div>
          <div style="text-align:center;flex:1">
            <div style="height:6px;background:${lc.status === 'retained' ? '#7C3AED' : '#E5E7EB'};border-radius:0 3px 3px 0"></div>
            <div style="margin-top:6px;font-weight:600;color:${lc.status === 'retained' ? '#7C3AED' : '#9CA3AF'}">🔒 Retain</div>
            <div style="color:#9CA3AF">3yr+ forever</div>
          </div>
        </div>
      </div>

      <div style="margin-top:14px;padding:10px 14px;background:#FEF3C7;border-radius:10px;font-size:12px;color:#92400E;display:flex;align-items:center;gap:8px">
        ⚠️ <span>This document is <strong>view-only</strong>. No deletion or modification is allowed per retention policy.</span>
      </div>
    </div>`;
  currentViewDocId = id;
  openModal('modal-view');
}

// ───── Export Single Document as PDF ─────
function exportDocumentPDF() {
  const doc = allDocuments.find(d => getDocId(d) == currentViewDocId);
  if (!doc) return;

  const dateStr  = getDocDate(doc);
  const lc       = getLifecycle(dateStr);
  const docDept  = getDocDept(doc);
  const code     = getDocCode(doc);
  const title    = doc.title || 'Untitled Document';
  const folder   = getDocFolder(doc);
  const type     = getDocType(doc);
  const conf     = doc.confidentiality || 'N/A';
  const desc     = doc.description || '';
  const age      = getAge(dateStr);
  const fileType = getDocFileType(doc);
  const fileSize = getDocFileSize(doc);
  const dateFiled = fmtDate(dateStr);

  const { jsPDF } = window.jspdf;
  const pdf = new jsPDF('p', 'mm', 'a4');
  const W = pdf.internal.pageSize.getWidth();
  const pageH = pdf.internal.pageSize.getHeight();
  const brandGreen = [5, 150, 105];
  const margin = 14;
  let y = 0;

  // ——— Header Bar ———
  pdf.setFillColor(...brandGreen);
  pdf.rect(0, 0, W, 30, 'F');
  pdf.setTextColor(255, 255, 255);
  pdf.setFontSize(17);
  pdf.setFont('helvetica', 'bold');
  pdf.text('Document Detail Report', W / 2, 13, { align: 'center' });
  pdf.setFontSize(9);
  pdf.setFont('helvetica', 'normal');
  const genDate = new Date().toLocaleDateString('en-US', { year:'numeric', month:'long', day:'numeric', hour:'2-digit', minute:'2-digit' });
  pdf.text('Microfinancial Admin  |  Generated: ' + genDate, W / 2, 22, { align: 'center' });
  y = 38;

  // ——— Document Title + Code ———
  pdf.setTextColor(31, 41, 55);
  pdf.setFontSize(15);
  pdf.setFont('helvetica', 'bold');
  const titleLines = pdf.splitTextToSize(title, W - 28);
  pdf.text(titleLines, margin, y);
  y += titleLines.length * 7;
  pdf.setFontSize(10);
  pdf.setFont('helvetica', 'normal');
  pdf.setTextColor(107, 114, 128);
  const metaParts = [code, fileType, fileSize].filter(Boolean);
  pdf.text(metaParts.join('  ·  '), margin, y);
  y += 9;

  // ——— Lifecycle Status Badge (auto-width) ———
  const statusColors = { active: [5,150,105], archived: [217,119,6], retained: [124,58,237] };
  const sc = statusColors[lc.status] || [107,114,128];
  const badgeText = lc.label + '   ·   Filed ' + age + ' ago';
  pdf.setFontSize(9);
  pdf.setFont('helvetica', 'bold');
  const badgeW = pdf.getTextWidth(badgeText) + 10;
  pdf.setFillColor(...sc);
  pdf.roundedRect(margin, y - 5, badgeW, 8, 2, 2, 'F');
  pdf.setTextColor(255, 255, 255);
  pdf.text(badgeText, margin + 5, y);
  y += 12;

  // ——— Details Table ———
  const details = [
    ['Document Code', code || 'N/A'],
    ['Department', docDept || 'N/A'],
    ['Folder', folder || 'N/A'],
    ['Document Type', type || 'N/A'],
    ['File Format', fileType || 'N/A'],
    ['File Size', fileSize || 'N/A'],
    ['Date Filed', dateFiled],
    ['Document Age', age || 'N/A'],
    ['Confidentiality', conf],
    ['Lifecycle Stage', lc.label.replace(/[^\w\s]/g, '').trim()],
  ];

  pdf.autoTable({
    startY: y,
    head: [['Field', 'Value']],
    body: details,
    theme: 'striped',
    styles: { fontSize: 10, cellPadding: 4, lineColor: [229,231,235], lineWidth: 0.2 },
    headStyles: { fillColor: brandGreen, textColor: [255,255,255], fontStyle: 'bold', fontSize: 10 },
    columnStyles: {
      0: { fontStyle: 'bold', cellWidth: 50, textColor: [75,85,99] },
      1: { cellWidth: 'auto', textColor: [31,41,55] }
    },
    alternateRowStyles: { fillColor: [249,250,251] },
    margin: { left: margin, right: margin }
  });
  y = pdf.lastAutoTable.finalY + 10;

  // ——— Description ———
  if (desc) {
    pdf.setFontSize(12);
    pdf.setFont('helvetica', 'bold');
    pdf.setTextColor(31, 41, 55);
    pdf.text('Description', margin, y);
    y += 6;
    pdf.setFillColor(249, 250, 251);
    const descLines = pdf.splitTextToSize(desc, W - 32);
    const descH = descLines.length * 5 + 8;
    pdf.roundedRect(margin, y - 3, W - 28, descH, 2, 2, 'F');
    pdf.setFontSize(10);
    pdf.setFont('helvetica', 'normal');
    pdf.setTextColor(55, 65, 81);
    pdf.text(descLines, margin + 4, y + 2);
    y += descH + 6;
  }

  // ——— Lifecycle Timeline ———
  pdf.setFontSize(12);
  pdf.setFont('helvetica', 'bold');
  pdf.setTextColor(31, 41, 55);
  pdf.text('Document Lifecycle Timeline', margin, y);
  y += 8;

  const stages = [
    { label: 'Active',  range: '0 – 6 months',  color: [5,150,105],   key: 'active' },
    { label: 'Archive', range: '6 mo – 3 years', color: [217,119,6],   key: 'archived' },
    { label: 'Retain',  range: '3 years+',       color: [124,58,237],  key: 'retained' },
  ];
  const barW = (W - 28) / 3;
  const reached = lc.status === 'active' ? 1 : lc.status === 'archived' ? 2 : 3;
  stages.forEach((st, i) => {
    const x = margin + i * barW;
    const isReached = (i + 1) <= reached;
    pdf.setFillColor(...(isReached ? st.color : [229, 231, 235]));
    if (i === 0) pdf.roundedRect(x, y, barW - 2, 6, 3, 0, 'F');
    else if (i === 2) pdf.roundedRect(x, y, barW - 2, 6, 0, 3, 'F');
    else pdf.rect(x, y, barW - 2, 6, 'F');

    // Stage label
    pdf.setFontSize(9);
    pdf.setFont('helvetica', 'bold');
    pdf.setTextColor(...(st.key === lc.status ? st.color : [156,163,175]));
    const icon = st.key === 'active' ? 'Active' : st.key === 'archived' ? 'Archive' : 'Retain';
    pdf.text(icon, x + barW / 2 - 1, y + 14, { align: 'center' });
    // Current indicator
    if (st.key === lc.status) {
      pdf.setFontSize(7);
      pdf.text('(Current)', x + barW / 2 - 1, y + 19, { align: 'center' });
    }
    // Range label
    pdf.setFontSize(8);
    pdf.setFont('helvetica', 'normal');
    pdf.setTextColor(156, 163, 175);
    pdf.text(st.range, x + barW / 2 - 1, y + (st.key === lc.status ? 24 : 19), { align: 'center' });
  });
  y += (lc.status ? 30 : 26);

  // ——— Retention Notice ———
  pdf.setFillColor(254, 243, 199);
  pdf.roundedRect(margin, y, W - 28, 12, 2, 2, 'F');
  pdf.setFontSize(8);
  pdf.setFont('helvetica', 'normal');
  pdf.setTextColor(146, 64, 14);
  pdf.text('This document is view-only. No deletion or modification is allowed per retention policy.', margin + 4, y + 7);
  y += 18;

  // ——— Footer ———
  pdf.setDrawColor(229, 231, 235);
  pdf.line(margin, pageH - 16, W - margin, pageH - 16);
  pdf.setFontSize(8);
  pdf.setTextColor(156, 163, 175);
  pdf.setFont('helvetica', 'normal');
  pdf.text('Microfinancial Admin System', margin, pageH - 9);
  pdf.text('Confidential', W / 2, pageH - 9, { align: 'center' });
  pdf.text('Page 1 of 1', W - margin, pageH - 9, { align: 'right' });

  // ——— Save ———
  const safeName = title.replace(/[^a-zA-Z0-9]/g, '_').substring(0, 40);
  pdf.save(`${code || 'Document'}_${safeName}.pdf`);
}

// ───── Section Switching (hash-driven) ─────
function showSection(hash) {
  const sections = document.querySelectorAll('.tab-content');
  const id = hash ? hash.replace('#', '') : 'tab-folders';
  sections.forEach(s => s.classList.remove('active'));
  const target = document.getElementById(id);
  if (target) target.classList.add('active');
  else if (sections[0]) sections[0].classList.add('active');
  if (id === 'tab-all') renderAllDocsTable();
  if (id === 'tab-archived') renderArchivedTable();
  if (id === 'tab-retained') renderRetainedTable();
}
window.addEventListener('hashchange', () => showSection(location.hash));

// ───── Initialize on Load ─────
loadData().then(() => {
  showSection(location.hash);
});

// ───── Export Functions ─────
function exportAllDocs(format) {
  const headers = ['Code', 'Title', 'Folder', 'Department', 'Type', 'Date Filed', 'Lifecycle', 'Age'];
  const rows = allDocuments.map(doc => {
    const lc = getLifecycle(getDocDate(doc));
    return [
      getDocCode(doc), doc.title || '', getDocFolder(doc), getDocDept(doc),
      getDocType(doc), fmtDate(getDocDate(doc)), lc.status, getAge(getDocDate(doc))
    ];
  });
  if (format === 'csv') {
    ExportHelper.exportCSV('Documents_All', headers, rows);
  } else {
    ExportHelper.exportPDF('Documents_All', 'Document Management — All Documents', headers, rows, { landscape: true, subtitle: allDocuments.length + ' documents' });
  }
}

function exportArchivedDocs(format) {
  const docs = allDocuments.filter(d => getLifecycle(getDocDate(d)).status === 'archived');
  const headers = ['Code', 'Title', 'Department', 'Folder', 'Date Filed', 'Age'];
  const rows = docs.map(doc => [
    getDocCode(doc), doc.title || '', getDocDept(doc), getDocFolder(doc),
    fmtDate(getDocDate(doc)), getAge(getDocDate(doc))
  ]);
  if (format === 'csv') {
    ExportHelper.exportCSV('Documents_Archived', headers, rows);
  } else {
    ExportHelper.exportPDF('Documents_Archived', 'Document Management — Archived Documents', headers, rows, { subtitle: docs.length + ' documents' });
  }
}

function exportRetainedDocs(format) {
  const docs = allDocuments.filter(d => getLifecycle(getDocDate(d)).status === 'retained');
  const headers = ['Code', 'Title', 'Department', 'Folder', 'Date Filed', 'Age'];
  const rows = docs.map(doc => [
    getDocCode(doc), doc.title || '', getDocDept(doc), getDocFolder(doc),
    fmtDate(getDocDate(doc)), getAge(getDocDate(doc))
  ]);
  if (format === 'csv') {
    ExportHelper.exportCSV('Documents_Retained', headers, rows);
  } else {
    ExportHelper.exportPDF('Documents_Retained', 'Document Management — Retained Documents', headers, rows, { subtitle: docs.length + ' documents' });
  }
}
</script>
</body>
</html>
