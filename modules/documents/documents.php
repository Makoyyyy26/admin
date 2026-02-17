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
      <!-- TAB: Secure Storage                                  -->
      <!-- ═══════════════════════════════════════════════════ -->
      <div id="tab-secure-storage" class="tab-content">

        <!-- Secure Storage Stats -->
        <div class="stats-grid animate-in delay-1" style="margin-bottom:20px">
          <div class="stat-card"><div class="stat-icon green">🛡️</div><div class="stat-info"><div class="stat-value" id="ss-total">—</div><div class="stat-label">Total Files</div></div></div>
          <div class="stat-card"><div class="stat-icon blue">💾</div><div class="stat-info"><div class="stat-value" id="ss-size">—</div><div class="stat-label">Total Storage</div></div></div>
          <div class="stat-card"><div class="stat-icon red">🔴</div><div class="stat-info"><div class="stat-value" id="ss-restricted">—</div><div class="stat-label">Restricted</div></div></div>
          <div class="stat-card"><div class="stat-icon amber">🟡</div><div class="stat-info"><div class="stat-value" id="ss-confidential">—</div><div class="stat-label">Confidential</div></div></div>
          <div class="stat-card"><div class="stat-icon purple">🔒</div><div class="stat-info"><div class="stat-value" id="ss-encrypted">—</div><div class="stat-label">Encrypted</div></div></div>
        </div>

        <!-- Security Policy Banner -->
        <div class="card" style="margin-bottom:16px;border-left:4px solid #059669">
          <div class="card-body" style="padding:14px 20px;display:flex;align-items:center;gap:12px">
            <span style="font-size:24px">🛡️</span>
            <div>
              <div style="font-weight:700;color:#065F46">Secure Storage Policy</div>
              <div style="font-size:13px;color:#047857">All documents are stored with role-based access control. Restricted and Confidential documents have enhanced security with audit logging for every access event.</div>
            </div>
          </div>
        </div>

        <!-- Confidentiality Breakdown -->
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;margin-bottom:20px">
          <div onclick="filterSecureStorage('restricted')" style="cursor:pointer;background:#FEE2E2;border:1px solid #FECACA;border-radius:12px;padding:16px;text-align:center;transition:all 0.2s" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">
            <div style="font-size:28px;margin-bottom:6px">🔴</div>
            <div style="font-weight:800;font-size:20px;color:#991B1B" id="ss-restricted-card">—</div>
            <div style="font-size:12px;color:#B91C1C;font-weight:600">Restricted</div>
            <div style="font-size:10px;color:#DC2626;margin-top:4px">Highest security tier</div>
          </div>
          <div onclick="filterSecureStorage('confidential')" style="cursor:pointer;background:#FEF3C7;border:1px solid #FDE68A;border-radius:12px;padding:16px;text-align:center;transition:all 0.2s" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">
            <div style="font-size:28px;margin-bottom:6px">🟡</div>
            <div style="font-weight:800;font-size:20px;color:#92400E" id="ss-confidential-card">—</div>
            <div style="font-size:12px;color:#B45309;font-weight:600">Confidential</div>
            <div style="font-size:10px;color:#D97706;margin-top:4px">Need-to-know basis</div>
          </div>
          <div onclick="filterSecureStorage('internal')" style="cursor:pointer;background:#DBEAFE;border:1px solid #BFDBFE;border-radius:12px;padding:16px;text-align:center;transition:all 0.2s" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">
            <div style="font-size:28px;margin-bottom:6px">🔵</div>
            <div style="font-weight:800;font-size:20px;color:#1E40AF" id="ss-internal-card">—</div>
            <div style="font-size:12px;color:#1D4ED8;font-weight:600">Internal</div>
            <div style="font-size:10px;color:#2563EB;margin-top:4px">Employee access only</div>
          </div>
          <div onclick="filterSecureStorage('public')" style="cursor:pointer;background:#D1FAE5;border:1px solid #A7F3D0;border-radius:12px;padding:16px;text-align:center;transition:all 0.2s" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">
            <div style="font-size:28px;margin-bottom:6px">🟢</div>
            <div style="font-weight:800;font-size:20px;color:#065F46" id="ss-public-card">—</div>
            <div style="font-size:12px;color:#047857;font-weight:600">Public</div>
            <div style="font-size:10px;color:#059669;margin-top:4px">Unrestricted access</div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <span class="card-title">🛡️ Secure Documents Registry</span>
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
              <select id="ss-filter-conf" class="form-input" style="width:auto;padding:6px 12px;font-size:12px" onchange="renderSecureStorageTable()">
                <option value="">All Levels</option>
                <option value="restricted">🔴 Restricted</option>
                <option value="confidential">🟡 Confidential</option>
                <option value="internal">🔵 Internal</option>
                <option value="public">🟢 Public</option>
              </select>
              <input type="text" id="ss-search" class="form-input" style="width:200px;padding:6px 12px;font-size:12px" placeholder="🔍 Search..." oninput="renderSecureStorageTable()">
              <span style="font-size:12px;color:#6B7280" id="ss-table-count">Loading...</span>
            </div>
          </div>
          <div class="card-body">
            <table class="data-table">
              <thead><tr>
                <th>Code</th><th>Title</th><th>Department</th><th>Type</th><th>Size</th>
                <th>Security</th><th>Access Grants</th><th>Actions</th>
              </tr></thead>
              <tbody id="ss-tbody"></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════ -->
      <!-- TAB: OCR Scanning                                    -->
      <!-- ═══════════════════════════════════════════════════ -->
      <div id="tab-ocr" class="tab-content">

        <!-- OCR Stats -->
        <div class="stats-grid animate-in delay-1" style="margin-bottom:20px">
          <div class="stat-card"><div class="stat-icon green">✅</div><div class="stat-info"><div class="stat-value" id="ocr-completed">—</div><div class="stat-label">Completed Scans</div></div></div>
          <div class="stat-card"><div class="stat-icon amber">⏳</div><div class="stat-info"><div class="stat-value" id="ocr-pending">—</div><div class="stat-label">Pending</div></div></div>
          <div class="stat-card"><div class="stat-icon blue">⚙️</div><div class="stat-info"><div class="stat-value" id="ocr-processing">—</div><div class="stat-label">Processing</div></div></div>
          <div class="stat-card"><div class="stat-icon red">❌</div><div class="stat-info"><div class="stat-value" id="ocr-failed">—</div><div class="stat-label">Failed</div></div></div>
          <div class="stat-card"><div class="stat-icon purple">📋</div><div class="stat-info"><div class="stat-value" id="ocr-queue">—</div><div class="stat-label">In Queue</div></div></div>
        </div>

        <!-- OCR Info Banner -->
        <div class="card" style="margin-bottom:16px;border-left:4px solid #2563EB">
          <div class="card-body" style="padding:14px 20px;display:flex;align-items:center;gap:12px">
            <span style="font-size:24px">🔍</span>
            <div>
              <div style="font-weight:700;color:#1E40AF">OCR Scanning Engine</div>
              <div style="font-size:13px;color:#1D4ED8">Optical Character Recognition extracts searchable text from scanned documents and images. Queued documents are processed automatically with priority ordering.</div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <span class="card-title">🔍 OCR Document Queue</span>
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
              <select id="ocr-filter-status" class="form-input" style="width:auto;padding:6px 12px;font-size:12px" onchange="renderOcrTable()">
                <option value="">All Statuses</option>
                <option value="pending">⏳ Pending</option>
                <option value="processing">⚙️ Processing</option>
                <option value="completed">✅ Completed</option>
                <option value="failed">❌ Failed</option>
              </select>
              <input type="text" id="ocr-search" class="form-input" style="width:200px;padding:6px 12px;font-size:12px" placeholder="🔍 Search..." oninput="renderOcrTable()">
              <span style="font-size:12px;color:#6B7280" id="ocr-table-count">Loading...</span>
            </div>
          </div>
          <div class="card-body">
            <table class="data-table">
              <thead><tr>
                <th>Code</th><th>Title</th><th>File</th><th>Department</th>
                <th>OCR Status</th><th>Processed</th><th>Actions</th>
              </tr></thead>
              <tbody id="ocr-tbody"></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- OCR Text Preview Modal -->
      <div id="modal-ocr-text" class="modal-overlay" onclick="if(event.target===this)closeModal('modal-ocr-text')">
        <div class="modal" style="max-width:700px">
          <div class="modal-header">
            <span class="modal-title" id="ocr-modal-title">OCR Extracted Text</span>
            <button class="modal-close" onclick="closeModal('modal-ocr-text')">&times;</button>
          </div>
          <div class="modal-body" id="ocr-modal-body"></div>
          <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modal-ocr-text')">Close</button>
          </div>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════ -->
      <!-- TAB: Version Control                                 -->
      <!-- ═══════════════════════════════════════════════════ -->
      <div id="tab-versions" class="tab-content">

        <!-- Version Stats -->
        <div class="stats-grid animate-in delay-1" style="margin-bottom:20px">
          <div class="stat-card"><div class="stat-icon blue">🔄</div><div class="stat-info"><div class="stat-value" id="vc-total-versions">—</div><div class="stat-label">Total Versions</div></div></div>
          <div class="stat-card"><div class="stat-icon green">📄</div><div class="stat-info"><div class="stat-value" id="vc-docs-with-ver">—</div><div class="stat-label">Docs with Versions</div></div></div>
          <div class="stat-card"><div class="stat-icon amber">📊</div><div class="stat-info"><div class="stat-value" id="vc-avg-versions">—</div><div class="stat-label">Avg Versions/Doc</div></div></div>
          <div class="stat-card"><div class="stat-icon purple">🕐</div><div class="stat-info"><div class="stat-value" id="vc-latest-date">—</div><div class="stat-label">Last Version Created</div></div></div>
        </div>

        <!-- Version Control Banner -->
        <div class="card" style="margin-bottom:16px;border-left:4px solid #7C3AED">
          <div class="card-body" style="padding:14px 20px;display:flex;align-items:center;gap:12px">
            <span style="font-size:24px">🔄</span>
            <div>
              <div style="font-weight:700;color:#5B21B6">Document Version Control</div>
              <div style="font-size:13px;color:#6D28D9">Track every revision of your documents. Each version is permanently stored with change notes, contributor details, and timestamps. Previous versions are never overwritten.</div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <span class="card-title">🔄 Versioned Documents</span>
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
              <input type="text" id="vc-search" class="form-input" style="width:220px;padding:6px 12px;font-size:12px" placeholder="🔍 Search documents..." oninput="renderVersionTable()">
              <span style="font-size:12px;color:#6B7280" id="vc-table-count">Loading...</span>
            </div>
          </div>
          <div class="card-body">
            <table class="data-table">
              <thead><tr>
                <th>Code</th><th>Title</th><th>Department</th><th>Current Ver.</th>
                <th>Total Versions</th><th>Last Updated</th><th>Actions</th>
              </tr></thead>
              <tbody id="vc-tbody"></tbody>
            </table>
          </div>
        </div>

        <!-- Version History Panel -->
        <div id="version-history-panel" class="card" style="display:none;margin-top:16px">
          <div class="card-header">
            <div style="display:flex;align-items:center;gap:10px">
              <button class="btn btn-outline btn-sm" onclick="document.getElementById('version-history-panel').style.display='none'">← Back</button>
              <span class="card-title" id="vh-panel-title">Version History</span>
            </div>
            <span style="font-size:12px;color:#6B7280" id="vh-panel-count">0 versions</span>
          </div>
          <div class="card-body" id="vh-panel-body"></div>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════ -->
      <!-- TAB: Archiving                                       -->
      <!-- ═══════════════════════════════════════════════════ -->
      <div id="tab-archiving" class="tab-content">

        <!-- Archiving Stats -->
        <div class="stats-grid animate-in delay-1" style="margin-bottom:20px">
          <div class="stat-card"><div class="stat-icon green">🟢</div><div class="stat-info"><div class="stat-value" id="ar-active">—</div><div class="stat-label">Active Documents</div></div></div>
          <div class="stat-card"><div class="stat-icon amber">📦</div><div class="stat-info"><div class="stat-value" id="ar-archived">—</div><div class="stat-label">Archived (6mo–3yr)</div></div></div>
          <div class="stat-card"><div class="stat-icon purple">🔒</div><div class="stat-info"><div class="stat-value" id="ar-retained">—</div><div class="stat-label">Retained (3yr+)</div></div></div>
          <div class="stat-card"><div class="stat-icon red">⚠️</div><div class="stat-info"><div class="stat-value" id="ar-pending">—</div><div class="stat-label">Pending Archive</div></div></div>
        </div>

        <!-- Archive Policy Cards -->
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;margin-bottom:20px">
          <div class="card" style="border-left:4px solid #059669">
            <div class="card-body" style="padding:16px 20px">
              <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
                <span style="font-size:24px">🟢</span>
                <div style="font-weight:700;color:#065F46;font-size:15px">Active Phase</div>
              </div>
              <div style="font-size:13px;color:#047857;line-height:1.6">Documents less than <strong>6 months</strong> old remain fully active with all access permissions. Regular editing and sharing is enabled.</div>
            </div>
          </div>
          <div class="card" style="border-left:4px solid #D97706">
            <div class="card-body" style="padding:16px 20px">
              <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
                <span style="font-size:24px">📦</span>
                <div style="font-weight:700;color:#92400E;font-size:15px">Archive Phase</div>
              </div>
              <div style="font-size:13px;color:#B45309;line-height:1.6">Documents between <strong>6 months</strong> and <strong>3 years</strong> are automatically archived. View-only access with audit logging.</div>
            </div>
          </div>
          <div class="card" style="border-left:4px solid #7C3AED">
            <div class="card-body" style="padding:16px 20px">
              <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
                <span style="font-size:24px">🔒</span>
                <div style="font-weight:700;color:#5B21B6;font-size:15px">Retention Phase</div>
              </div>
              <div style="font-size:13px;color:#6D28D9;line-height:1.6">Documents older than <strong>3 years</strong> are permanently retained. No modification or deletion is allowed. Institutional records.</div>
            </div>
          </div>
        </div>

        <!-- Run Archive Cycle -->
        <div class="card" style="margin-bottom:16px">
          <div class="card-header">
            <span class="card-title">⚙️ Archive Lifecycle Engine</span>
            <button class="btn btn-primary" onclick="runArchiveCycle()" style="font-size:13px">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:4px"><path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/></svg>
              Run Archive Cycle
            </button>
          </div>
          <div class="card-body" style="padding:16px 20px">
            <div style="font-size:13px;color:#4B5563;line-height:1.6">
              Manually trigger the document lifecycle engine to archive active documents older than 6 months and retain archived documents older than 3 years. 
              This process runs automatically but can be triggered on demand.
            </div>
            <div style="margin-top:10px;font-size:12px;color:#9CA3AF" id="ar-last-run">Last run: —</div>
          </div>
        </div>

        <!-- Archive Timeline -->
        <div class="card">
          <div class="card-header">
            <span class="card-title">📊 Archive Timeline</span>
            <span style="font-size:12px;color:#6B7280">Monthly document lifecycle distribution</span>
          </div>
          <div class="card-body">
            <table class="data-table">
              <thead><tr>
                <th>Month</th><th>Total</th><th>🟢 Active</th><th>📦 Archived</th><th>🔒 Retained</th><th>Distribution</th>
              </tr></thead>
              <tbody id="ar-timeline-tbody"></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════ -->
      <!-- TAB: Access Control                                  -->
      <!-- ═══════════════════════════════════════════════════ -->
      <div id="tab-access-control" class="tab-content">

        <!-- Access Control Stats -->
        <div class="stats-grid animate-in delay-1" style="margin-bottom:20px">
          <div class="stat-card"><div class="stat-icon blue">🔑</div><div class="stat-info"><div class="stat-value" id="ac-total">—</div><div class="stat-label">Total Grants</div></div></div>
          <div class="stat-card"><div class="stat-icon green">✅</div><div class="stat-info"><div class="stat-value" id="ac-active">—</div><div class="stat-label">Active Grants</div></div></div>
          <div class="stat-card"><div class="stat-icon red">⏰</div><div class="stat-info"><div class="stat-value" id="ac-expired">—</div><div class="stat-label">Expired</div></div></div>
          <div class="stat-card"><div class="stat-icon amber">👥</div><div class="stat-info"><div class="stat-value" id="ac-users">—</div><div class="stat-label">Users with Access</div></div></div>
        </div>

        <!-- Access Control Banner -->
        <div class="card" style="margin-bottom:16px;border-left:4px solid #DC2626">
          <div class="card-body" style="padding:14px 20px;display:flex;align-items:center;gap:12px">
            <span style="font-size:24px">🔑</span>
            <div>
              <div style="font-weight:700;color:#991B1B">Document Access Control</div>
              <div style="font-size:13px;color:#DC2626">Manage who can view, download, edit, or administer documents. All access changes are logged in the audit trail. Expired grants are automatically revoked.</div>
            </div>
          </div>
        </div>

        <!-- Permission Level Summary -->
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px;margin-bottom:20px">
          <div style="background:#D1FAE5;border:1px solid #A7F3D0;border-radius:10px;padding:12px;text-align:center">
            <div style="font-size:20px">👁️</div>
            <div style="font-weight:800;font-size:18px;color:#065F46" id="ac-view-count">—</div>
            <div style="font-size:11px;color:#047857;font-weight:600">View Only</div>
          </div>
          <div style="background:#DBEAFE;border:1px solid #BFDBFE;border-radius:10px;padding:12px;text-align:center">
            <div style="font-size:20px">⬇️</div>
            <div style="font-weight:800;font-size:18px;color:#1E40AF" id="ac-download-count">—</div>
            <div style="font-size:11px;color:#1D4ED8;font-weight:600">Download</div>
          </div>
          <div style="background:#FEF3C7;border:1px solid #FDE68A;border-radius:10px;padding:12px;text-align:center">
            <div style="font-size:20px">✏️</div>
            <div style="font-weight:800;font-size:18px;color:#92400E" id="ac-edit-count">—</div>
            <div style="font-size:11px;color:#B45309;font-weight:600">Edit</div>
          </div>
          <div style="background:#FEE2E2;border:1px solid #FECACA;border-radius:10px;padding:12px;text-align:center">
            <div style="font-size:20px">⚙️</div>
            <div style="font-weight:800;font-size:18px;color:#991B1B" id="ac-admin-count">—</div>
            <div style="font-size:11px;color:#B91C1C;font-weight:600">Admin</div>
          </div>
        </div>

        <!-- Grant Access Button -->
        <div style="margin-bottom:16px;display:flex;gap:10px;align-items:center;flex-wrap:wrap">
          <button class="btn btn-primary" onclick="openGrantAccessModal()" style="font-size:13px">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:4px"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
            Grant Access
          </button>
        </div>

        <!-- Access Grants Table -->
        <div class="card">
          <div class="card-header">
            <span class="card-title">🔑 Access Grants</span>
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
              <select id="ac-filter-perm" class="form-input" style="width:auto;padding:6px 12px;font-size:12px" onchange="renderAccessTable()">
                <option value="">All Permissions</option>
                <option value="view">👁️ View</option>
                <option value="download">⬇️ Download</option>
                <option value="edit">✏️ Edit</option>
                <option value="admin">⚙️ Admin</option>
              </select>
              <input type="text" id="ac-search" class="form-input" style="width:200px;padding:6px 12px;font-size:12px" placeholder="🔍 Search..." oninput="renderAccessTable()">
              <span style="font-size:12px;color:#6B7280" id="ac-table-count">Loading...</span>
            </div>
          </div>
          <div class="card-body">
            <table class="data-table">
              <thead><tr>
                <th>Document</th><th>User</th><th>Department</th><th>Permission</th>
                <th>Granted By</th><th>Expires</th><th>Status</th><th>Actions</th>
              </tr></thead>
              <tbody id="ac-tbody"></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Grant Access Modal -->
      <div id="modal-grant-access" class="modal-overlay" onclick="if(event.target===this)closeModal('modal-grant-access')">
        <div class="modal" style="max-width:500px">
          <div class="modal-header">
            <span class="modal-title">🔑 Grant Document Access</span>
            <button class="modal-close" onclick="closeModal('modal-grant-access')">&times;</button>
          </div>
          <div class="modal-body">
            <div style="margin-bottom:14px">
              <label style="font-weight:600;font-size:13px;color:#374151;display:block;margin-bottom:6px">Document</label>
              <select id="ga-document" class="form-input" style="width:100%">
                <option value="">Select document...</option>
              </select>
            </div>
            <div style="margin-bottom:14px">
              <label style="font-weight:600;font-size:13px;color:#374151;display:block;margin-bottom:6px">User</label>
              <select id="ga-user" class="form-input" style="width:100%">
                <option value="">Select user...</option>
              </select>
            </div>
            <div style="margin-bottom:14px">
              <label style="font-weight:600;font-size:13px;color:#374151;display:block;margin-bottom:6px">Permission Level</label>
              <select id="ga-permission" class="form-input" style="width:100%">
                <option value="view">👁️ View — Can view document details</option>
                <option value="download">⬇️ Download — Can view and download</option>
                <option value="edit">✏️ Edit — Can view, download, and edit</option>
                <option value="admin">⚙️ Admin — Full control over document</option>
              </select>
            </div>
            <div style="margin-bottom:14px">
              <label style="font-weight:600;font-size:13px;color:#374151;display:block;margin-bottom:6px">Expiration Date <span style="color:#9CA3AF">(optional)</span></label>
              <input type="datetime-local" id="ga-expires" class="form-input" style="width:100%">
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modal-grant-access')">Cancel</button>
            <button class="btn btn-primary" onclick="submitGrantAccess()">Grant Access</button>
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
  if (id === 'tab-secure-storage') loadSecureStorage();
  if (id === 'tab-ocr') loadOcrScanning();
  if (id === 'tab-versions') loadVersionControl();
  if (id === 'tab-archiving') loadArchiving();
  if (id === 'tab-access-control') loadAccessControl();
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



// ═══════════════════════════════════════════════════════
// SUBMODULE: SECURE STORAGE
// ═══════════════════════════════════════════════════════

let secureDocuments = [];

async function loadSecureStorage() {
  try {
    const [statsRes, docsRes] = await Promise.all([
      fetch(API + '?action=secure_storage_stats'),
      fetch(API + '?action=list_secure_documents')
    ]);
    const statsJson = await statsRes.json();
    const docsJson = await docsRes.json();
    const s = statsJson.data || {};
    secureDocuments = docsJson.data || [];

    document.getElementById('ss-total').textContent = s.total_files || 0;
    document.getElementById('ss-size').textContent = formatBytes(s.total_size || 0);
    document.getElementById('ss-restricted').textContent = s.restricted || 0;
    document.getElementById('ss-confidential').textContent = s.confidential || 0;
    document.getElementById('ss-encrypted').textContent = s.encrypted || 0;
    document.getElementById('ss-restricted-card').textContent = s.restricted || 0;
    document.getElementById('ss-confidential-card').textContent = s.confidential || 0;
    document.getElementById('ss-internal-card').textContent = s.internal || 0;
    document.getElementById('ss-public-card').textContent = s.public || 0;

    renderSecureStorageTable();
  } catch (err) { console.error('Secure storage load error:', err); }
}

function formatBytes(bytes) {
  bytes = parseInt(bytes);
  if (!bytes || isNaN(bytes)) return '0 B';
  if (bytes >= 1073741824) return (bytes / 1073741824).toFixed(1) + ' GB';
  if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
  if (bytes >= 1024) return (bytes / 1024).toFixed(0) + ' KB';
  return bytes + ' B';
}

function filterSecureStorage(level) {
  document.getElementById('ss-filter-conf').value = level;
  renderSecureStorageTable();
}

function renderSecureStorageTable() {
  const confFilter = document.getElementById('ss-filter-conf').value;
  const search = document.getElementById('ss-search').value.toLowerCase();

  const filtered = secureDocuments.filter(doc => {
    if (confFilter && doc.confidentiality !== confFilter) return false;
    if (search) {
      const t = (doc.title || '').toLowerCase();
      const c = (doc.document_code || '').toLowerCase();
      if (!t.includes(search) && !c.includes(search)) return false;
    }
    return true;
  });

  document.getElementById('ss-table-count').textContent = filtered.length + ' document' + (filtered.length !== 1 ? 's' : '');

  const tbody = document.getElementById('ss-tbody');
  if (filtered.length === 0) {
    tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:30px;color:#9CA3AF">No documents found</td></tr>';
    return;
  }

  const confIcons = { restricted: '🔴', confidential: '🟡', internal: '🔵', public: '🟢' };
  const confBadges = { restricted: 'badge-red', confidential: 'badge-amber', internal: 'badge-blue', public: 'badge-green' };

  tbody.innerHTML = filtered.map(doc => {
    const conf = doc.confidentiality || 'internal';
    const icon = confIcons[conf] || '🔵';
    const badgeCls = confBadges[conf] || 'badge-blue';
    const isEncrypted = (conf === 'restricted' || conf === 'confidential');

    return `<tr>
      <td style="font-weight:600;font-size:12px">${doc.document_code || ''}</td>
      <td style="font-weight:600">${doc.title || ''}</td>
      <td><span class="badge badge-blue" style="font-size:11px">${doc.department || '—'}</span></td>
      <td style="font-size:12px">${(doc.file_type || '').toUpperCase()}</td>
      <td style="font-size:12px">${formatBytes(doc.file_size)}</td>
      <td><span class="badge ${badgeCls}" style="font-size:11px">${icon} ${conf.charAt(0).toUpperCase() + conf.slice(1)}</span></td>
      <td style="font-size:12px;text-align:center">
        ${isEncrypted ? '🔒' : '🔓'} ${doc.access_count || 0}
      </td>
      <td>
        <button class="btn btn-outline btn-sm" onclick="viewDocument(${doc.document_id})" title="View">${viewSvg}</button>
      </td>
    </tr>`;
  }).join('');
}

// ═══════════════════════════════════════════════════════
// SUBMODULE: OCR SCANNING
// ═══════════════════════════════════════════════════════

let ocrDocuments = [];

async function loadOcrScanning() {
  try {
    const [statsRes, docsRes] = await Promise.all([
      fetch(API + '?action=ocr_stats'),
      fetch(API + '?action=list_ocr_documents')
    ]);
    const statsJson = await statsRes.json();
    const docsJson = await docsRes.json();
    const s = statsJson.data || {};
    ocrDocuments = docsJson.data || [];

    document.getElementById('ocr-completed').textContent = s.total_scanned || 0;
    document.getElementById('ocr-pending').textContent = s.pending || 0;
    document.getElementById('ocr-processing').textContent = s.processing || 0;
    document.getElementById('ocr-failed').textContent = s.failed || 0;
    document.getElementById('ocr-queue').textContent = s.queue_count || 0;

    renderOcrTable();
  } catch (err) { console.error('OCR load error:', err); }
}

function renderOcrTable() {
  const statusFilter = document.getElementById('ocr-filter-status').value;
  const search = document.getElementById('ocr-search').value.toLowerCase();

  const filtered = ocrDocuments.filter(doc => {
    if (statusFilter && doc.ocr_status !== statusFilter) return false;
    if (search) {
      const t = (doc.title || '').toLowerCase();
      const c = (doc.document_code || '').toLowerCase();
      if (!t.includes(search) && !c.includes(search)) return false;
    }
    return true;
  });

  document.getElementById('ocr-table-count').textContent = filtered.length + ' document' + (filtered.length !== 1 ? 's' : '');

  const tbody = document.getElementById('ocr-tbody');
  if (filtered.length === 0) {
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:30px;color:#9CA3AF">No OCR documents found</td></tr>';
    return;
  }

  const statusIcons = { pending: '⏳', processing: '⚙️', completed: '✅', failed: '❌' };
  const statusBadges = { pending: 'badge-amber', processing: 'badge-blue', completed: 'badge-green', failed: 'badge-red' };

  tbody.innerHTML = filtered.map(doc => {
    const st = doc.ocr_status || 'pending';
    const icon = statusIcons[st] || '⏳';
    const badgeCls = statusBadges[st] || 'badge-gray';

    return `<tr>
      <td style="font-weight:600;font-size:12px">${doc.document_code || ''}</td>
      <td style="font-weight:600">${doc.title || ''}</td>
      <td style="font-size:12px">${doc.file_name || ''}</td>
      <td><span class="badge badge-blue" style="font-size:11px">${doc.department || '—'}</span></td>
      <td><span class="badge ${badgeCls}" style="font-size:11px">${icon} ${st.charAt(0).toUpperCase() + st.slice(1)}</span></td>
      <td style="font-size:12px">${doc.ocr_processed_at ? fmtDate(doc.ocr_processed_at) : '—'}</td>
      <td style="display:flex;gap:4px">
        ${st === 'completed' ? `<button class="btn btn-outline btn-sm" onclick="viewOcrText(${doc.document_id})" title="View OCR Text">📄</button>` : ''}
        ${(st === 'failed' || st === 'pending') ? `<button class="btn btn-outline btn-sm" onclick="queueOcr(${doc.document_id})" title="Queue for OCR">🔄</button>` : ''}
        <button class="btn btn-outline btn-sm" onclick="viewDocument(${doc.document_id})" title="View">${viewSvg}</button>
      </td>
    </tr>`;
  }).join('');
}

async function viewOcrText(docId) {
  try {
    const res = await fetch(API + '?action=view_ocr_text&document_id=' + docId);
    const json = await res.json();
    const doc = json.data;
    if (!doc) return;

    document.getElementById('ocr-modal-title').textContent = '📄 OCR Text — ' + (doc.title || doc.document_code);
    document.getElementById('ocr-modal-body').innerHTML = `
      <div style="margin-bottom:12px">
        <div style="display:flex;gap:12px;font-size:12px;color:#6B7280;margin-bottom:10px">
          <span><strong>Code:</strong> ${doc.document_code}</span>
          <span><strong>Status:</strong> ${doc.ocr_status}</span>
          ${doc.ocr_processed_at ? `<span><strong>Processed:</strong> ${fmtDate(doc.ocr_processed_at)}</span>` : ''}
        </div>
      </div>
      <div style="background:#F9FAFB;border:1px solid #E5E7EB;border-radius:10px;padding:16px;max-height:400px;overflow-y:auto;font-size:13px;line-height:1.8;white-space:pre-wrap;font-family:monospace;color:#1F2937">
        ${doc.ocr_text ? doc.ocr_text.replace(/</g, '&lt;').replace(/>/g, '&gt;') : '<em style="color:#9CA3AF">No OCR text extracted yet.</em>'}
      </div>`;
    openModal('modal-ocr-text');
  } catch (err) { console.error('View OCR text error:', err); }
}

async function queueOcr(docId) {
  const result = await Swal.fire({
    title: 'Queue for OCR?',
    text: 'Add this document to the OCR processing queue?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#059669',
    confirmButtonText: 'Queue'
  });
  if (!result.isConfirmed) return;

  try {
    const res = await fetch(API + '?action=queue_ocr', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ document_id: docId, priority: 'normal' })
    });
    const json = await res.json();
    if (json.success) {
      Swal.fire({ icon: 'success', title: 'Queued', text: 'Document added to OCR queue.', confirmButtonColor: '#059669', timer: 2000 });
      loadOcrScanning();
    }
  } catch (err) { console.error('Queue OCR error:', err); }
}

// ═══════════════════════════════════════════════════════
// SUBMODULE: VERSION CONTROL
// ═══════════════════════════════════════════════════════

let versionedDocuments = [];

async function loadVersionControl() {
  try {
    const [statsRes, docsRes] = await Promise.all([
      fetch(API + '?action=version_stats'),
      fetch(API + '?action=list_versioned_documents')
    ]);
    const statsJson = await statsRes.json();
    const docsJson = await docsRes.json();
    const s = statsJson.data || {};
    versionedDocuments = docsJson.data || [];

    document.getElementById('vc-total-versions').textContent = s.total_versions || 0;
    document.getElementById('vc-docs-with-ver').textContent = s.documents_with_versions || 0;
    document.getElementById('vc-avg-versions').textContent = s.avg_versions || '0';
    document.getElementById('vc-latest-date').textContent = s.latest_version_date ? fmtDate(s.latest_version_date) : '—';

    renderVersionTable();
  } catch (err) { console.error('Version control load error:', err); }
}

function renderVersionTable() {
  const search = document.getElementById('vc-search').value.toLowerCase();

  const filtered = versionedDocuments.filter(doc => {
    if (search) {
      const t = (doc.title || '').toLowerCase();
      const c = (doc.document_code || '').toLowerCase();
      if (!t.includes(search) && !c.includes(search)) return false;
    }
    return true;
  });

  document.getElementById('vc-table-count').textContent = filtered.length + ' document' + (filtered.length !== 1 ? 's' : '');

  const tbody = document.getElementById('vc-tbody');
  if (filtered.length === 0) {
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:30px;color:#9CA3AF">No documents found</td></tr>';
    return;
  }

  tbody.innerHTML = filtered.map(doc => {
    const verCount = parseInt(doc.version_count) || 0;
    const verBadge = verCount > 0 ? 'badge-purple' : 'badge-gray';

    return `<tr>
      <td style="font-weight:600;font-size:12px">${doc.document_code || ''}</td>
      <td style="font-weight:600">${doc.title || ''}</td>
      <td><span class="badge badge-blue" style="font-size:11px">${doc.department || '—'}</span></td>
      <td style="text-align:center;font-weight:700;color:#7C3AED">v${doc.current_version || 1}</td>
      <td style="text-align:center"><span class="badge ${verBadge}" style="font-size:11px">🔄 ${verCount}</span></td>
      <td style="font-size:12px">${doc.last_version_date ? fmtDate(doc.last_version_date) : fmtDate(doc.updated_at)}</td>
      <td style="display:flex;gap:4px">
        <button class="btn btn-outline btn-sm" onclick="showVersionHistory(${doc.document_id})" title="View History">🕐</button>
        <button class="btn btn-outline btn-sm" onclick="viewDocument(${doc.document_id})" title="View">${viewSvg}</button>
      </td>
    </tr>`;
  }).join('');
}

async function showVersionHistory(docId) {
  try {
    const res = await fetch(API + '?action=list_versions&document_id=' + docId);
    const json = await res.json();
    const versions = json.data || [];
    const doc = versionedDocuments.find(d => d.document_id == docId);

    const panel = document.getElementById('version-history-panel');
    document.getElementById('vh-panel-title').textContent = '🕐 Version History — ' + (doc ? doc.title : 'Document #' + docId);
    document.getElementById('vh-panel-count').textContent = versions.length + ' version' + (versions.length !== 1 ? 's' : '');

    let html = '<div style="padding:16px">';
    if (versions.length === 0) {
      html += '<div class="empty-state" style="padding:30px"><div style="font-size:40px;margin-bottom:8px">📭</div><div style="font-weight:600">No version history available</div><div style="font-size:12px;color:#9CA3AF;margin-top:4px">This document only has the original version.</div></div>';
    } else {
      versions.forEach((v, idx) => {
        const isCurrent = idx === 0;
        html += `
          <div style="border:1px solid ${isCurrent ? '#A7F3D0' : '#E5E7EB'};border-radius:12px;padding:16px;margin-bottom:10px;
                       ${isCurrent ? 'background:#F0FDF4;border-left:4px solid #059669' : 'border-left:4px solid #E5E7EB'}">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
              <div>
                <span style="font-weight:800;font-size:15px;color:${isCurrent ? '#059669' : '#374151'}">v${v.version_number}</span>
                ${isCurrent ? '<span class="badge badge-green" style="margin-left:8px;font-size:10px">CURRENT</span>' : ''}
              </div>
              <div style="font-size:12px;color:#6B7280">
                📅 ${fmtDate(v.created_at)} · 👤 ${v.uploaded_by_name || 'Unknown'}
              </div>
            </div>
            <div style="margin-top:8px;font-size:12px;color:#6B7280">
              <span>📎 ${v.file_name || '—'}</span>
              ${v.file_size ? ` · <span>${formatBytes(v.file_size)}</span>` : ''}
            </div>
            ${v.change_notes ? `<div style="margin-top:8px;font-size:13px;color:#374151;background:#F9FAFB;padding:10px 14px;border-radius:8px;line-height:1.6">💬 ${v.change_notes}</div>` : ''}
          </div>`;
      });
    }
    html += '</div>';
    document.getElementById('vh-panel-body').innerHTML = html;
    panel.style.display = '';
    panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  } catch (err) { console.error('Version history error:', err); }
}

// ═══════════════════════════════════════════════════════
// SUBMODULE: ARCHIVING
// ═══════════════════════════════════════════════════════

async function loadArchiving() {
  try {
    const [statsRes, timelineRes] = await Promise.all([
      fetch(API + '?action=archiving_stats'),
      fetch(API + '?action=list_archive_timeline')
    ]);
    const statsJson = await statsRes.json();
    const timelineJson = await timelineRes.json();
    const s = statsJson.data || {};
    const timeline = timelineJson.data || [];

    document.getElementById('ar-active').textContent = s.total_active || 0;
    document.getElementById('ar-archived').textContent = s.total_archived || 0;
    document.getElementById('ar-retained').textContent = s.total_retained || 0;
    document.getElementById('ar-pending').textContent = s.pending_archive || 0;

    if (s.oldest_document) {
      document.getElementById('ar-last-run').textContent = 'Oldest document: ' + fmtDate(s.oldest_document);
    }

    renderArchiveTimeline(timeline);
  } catch (err) { console.error('Archiving load error:', err); }
}

function renderArchiveTimeline(timeline) {
  const tbody = document.getElementById('ar-timeline-tbody');
  if (!timeline || timeline.length === 0) {
    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:30px;color:#9CA3AF">No timeline data available</td></tr>';
    return;
  }

  tbody.innerHTML = timeline.map(row => {
    const total = parseInt(row.total) || 1;
    const active = parseInt(row.active) || 0;
    const archived = parseInt(row.archived) || 0;
    const retained = parseInt(row.retained) || 0;
    const aPct = Math.round(active / total * 100);
    const arPct = Math.round(archived / total * 100);
    const rPct = Math.round(retained / total * 100);

    return `<tr>
      <td style="font-weight:600;font-size:13px">${row.month}</td>
      <td style="font-weight:700">${total}</td>
      <td><span class="badge badge-green" style="font-size:11px">🟢 ${active}</span></td>
      <td><span class="badge badge-amber" style="font-size:11px">📦 ${archived}</span></td>
      <td><span class="badge badge-purple" style="font-size:11px">🔒 ${retained}</span></td>
      <td>
        <div style="display:flex;height:8px;border-radius:4px;overflow:hidden;background:#F3F4F6;min-width:120px">
          ${aPct > 0 ? `<div style="width:${aPct}%;background:#059669" title="Active ${aPct}%"></div>` : ''}
          ${arPct > 0 ? `<div style="width:${arPct}%;background:#D97706" title="Archived ${arPct}%"></div>` : ''}
          ${rPct > 0 ? `<div style="width:${rPct}%;background:#7C3AED" title="Retained ${rPct}%"></div>` : ''}
        </div>
      </td>
    </tr>`;
  }).join('');
}

async function runArchiveCycle() {
  const result = await Swal.fire({
    title: 'Run Archive Cycle?',
    html: '<div style="font-size:14px;color:#4B5563">This will:<br>• Archive active documents older than 6 months<br>• Retain archived documents older than 3 years</div>',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#059669',
    confirmButtonText: 'Run Cycle',
    cancelButtonText: 'Cancel'
  });
  if (!result.isConfirmed) return;

  try {
    const res = await fetch(API + '?action=run_archive_cycle', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({})
    });
    const json = await res.json();
    if (json.success) {
      Swal.fire({
        icon: 'success',
        title: 'Archive Cycle Complete',
        html: `<div style="font-size:14px"><strong>${json.archived || 0}</strong> documents archived<br><strong>${json.retained || 0}</strong> documents retained</div>`,
        confirmButtonColor: '#059669'
      });
      loadArchiving();
      loadData(); // Refresh main data
    }
  } catch (err) { console.error('Archive cycle error:', err); }
}

// ═══════════════════════════════════════════════════════
// SUBMODULE: ACCESS CONTROL
// ═══════════════════════════════════════════════════════

let accessGrants = [];
let allUsers = [];

async function loadAccessControl() {
  try {
    const [statsRes, grantsRes, usersRes] = await Promise.all([
      fetch(API + '?action=access_control_stats'),
      fetch(API + '?action=list_access_grants'),
      fetch(API + '?action=list_users')
    ]);
    const statsJson = await statsRes.json();
    const grantsJson = await grantsRes.json();
    const usersJson = await usersRes.json();
    const s = statsJson.data || {};
    accessGrants = grantsJson.data || [];
    allUsers = usersJson.data || [];

    document.getElementById('ac-total').textContent = s.total_grants || 0;
    document.getElementById('ac-active').textContent = s.active_grants || 0;
    document.getElementById('ac-expired').textContent = s.expired_grants || 0;
    document.getElementById('ac-users').textContent = s.users_with_access || 0;
    document.getElementById('ac-view-count').textContent = s.view_only || 0;
    document.getElementById('ac-download-count').textContent = s.download || 0;
    document.getElementById('ac-edit-count').textContent = s.edit || 0;
    document.getElementById('ac-admin-count').textContent = s.admin || 0;

    renderAccessTable();
  } catch (err) { console.error('Access control load error:', err); }
}

function renderAccessTable() {
  const permFilter = document.getElementById('ac-filter-perm').value;
  const search = document.getElementById('ac-search').value.toLowerCase();

  const filtered = accessGrants.filter(g => {
    if (permFilter && g.permission !== permFilter) return false;
    if (search) {
      const n = (g.user_name || '').toLowerCase();
      const d = (g.document_title || '').toLowerCase();
      const c = (g.document_code || '').toLowerCase();
      if (!n.includes(search) && !d.includes(search) && !c.includes(search)) return false;
    }
    return true;
  });

  document.getElementById('ac-table-count').textContent = filtered.length + ' grant' + (filtered.length !== 1 ? 's' : '');

  const tbody = document.getElementById('ac-tbody');
  if (filtered.length === 0) {
    tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:30px;color:#9CA3AF">No access grants found</td></tr>';
    return;
  }

  const permIcons = { view: '👁️', download: '⬇️', edit: '✏️', admin: '⚙️' };
  const permBadges = { view: 'badge-green', download: 'badge-blue', edit: 'badge-amber', admin: 'badge-red' };

  tbody.innerHTML = filtered.map(g => {
    const perm = g.permission || 'view';
    const icon = permIcons[perm] || '👁️';
    const badgeCls = permBadges[perm] || 'badge-green';
    const isExpired = g.expires_at && new Date(g.expires_at) < new Date();

    return `<tr style="${isExpired ? 'opacity:0.6' : ''}">
      <td>
        <div style="font-weight:600;font-size:13px">${g.document_title || ''}</div>
        <div style="font-size:11px;color:#9CA3AF">${g.document_code || ''}</div>
      </td>
      <td style="font-weight:600">${g.user_name || ''}</td>
      <td><span class="badge badge-blue" style="font-size:11px">${g.user_department || '—'}</span></td>
      <td><span class="badge ${badgeCls}" style="font-size:11px">${icon} ${perm.charAt(0).toUpperCase() + perm.slice(1)}</span></td>
      <td style="font-size:12px">${g.granted_by_name || '—'}</td>
      <td style="font-size:12px">${g.expires_at ? fmtDate(g.expires_at) : '<span style="color:#059669">Never</span>'}</td>
      <td>
        ${isExpired
          ? '<span class="badge badge-red" style="font-size:10px">Expired</span>'
          : '<span class="badge badge-green" style="font-size:10px">Active</span>'}
      </td>
      <td>
        <button class="btn btn-outline btn-sm" style="color:#DC2626;border-color:#FCA5A5" onclick="revokeAccess(${g.access_id}, '${(g.user_name || '').replace(/'/g, "\\'")}', '${(g.document_title || '').replace(/'/g, "\\'")}')" title="Revoke">✕</button>
      </td>
    </tr>`;
  }).join('');
}

function openGrantAccessModal() {
  // Populate document dropdown
  const docSelect = document.getElementById('ga-document');
  docSelect.innerHTML = '<option value="">Select document...</option>';
  allDocuments.forEach(doc => {
    const opt = document.createElement('option');
    opt.value = getDocId(doc);
    opt.textContent = getDocCode(doc) + ' — ' + (doc.title || 'Untitled');
    docSelect.appendChild(opt);
  });

  // Populate user dropdown
  const userSelect = document.getElementById('ga-user');
  userSelect.innerHTML = '<option value="">Select user...</option>';
  allUsers.forEach(u => {
    const opt = document.createElement('option');
    opt.value = u.user_id;
    opt.textContent = u.first_name + ' ' + u.last_name + ' (' + (u.department || u.role) + ')';
    userSelect.appendChild(opt);
  });

  document.getElementById('ga-permission').value = 'view';
  document.getElementById('ga-expires').value = '';
  openModal('modal-grant-access');
}

async function submitGrantAccess() {
  const docId = document.getElementById('ga-document').value;
  const userId = document.getElementById('ga-user').value;
  const permission = document.getElementById('ga-permission').value;
  const expires = document.getElementById('ga-expires').value;

  if (!docId || !userId) {
    Swal.fire({ icon: 'warning', title: 'Missing Fields', text: 'Please select both a document and a user.', confirmButtonColor: '#059669' });
    return;
  }

  try {
    const res = await fetch(API + '?action=grant_access', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        document_id: parseInt(docId),
        user_id: parseInt(userId),
        permission: permission,
        expires_at: expires || null
      })
    });
    const json = await res.json();
    if (json.success) {
      closeModal('modal-grant-access');
      Swal.fire({ icon: 'success', title: 'Access Granted', text: 'Permission has been assigned.', confirmButtonColor: '#059669', timer: 2000 });
      loadAccessControl();
    }
  } catch (err) { console.error('Grant access error:', err); }
}

async function revokeAccess(accessId, userName, docTitle) {
  const result = await Swal.fire({
    title: 'Revoke Access?',
    html: `<div style="font-size:14px">Remove <strong>${userName}</strong>'s access to <strong>${docTitle}</strong>?</div>`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#DC2626',
    confirmButtonText: 'Revoke',
    cancelButtonText: 'Cancel'
  });
  if (!result.isConfirmed) return;

  try {
    const res = await fetch(API + '?action=revoke_access', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ access_id: accessId })
    });
    const json = await res.json();
    if (json.success) {
      Swal.fire({ icon: 'success', title: 'Revoked', text: 'Access has been removed.', confirmButtonColor: '#059669', timer: 2000 });
      loadAccessControl();
    }
  } catch (err) { console.error('Revoke access error:', err); }
}
</script>
</body>
</html>
