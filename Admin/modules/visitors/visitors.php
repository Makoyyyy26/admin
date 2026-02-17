<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Visitor Management — Microfinancial Admin</title>

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
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
  <style>
    .qr-scan-zone { border: 3px dashed #D1FAE5; border-radius: 16px; padding: 32px; text-align: center; transition: all 0.3s; }
    .qr-scan-zone.scanning { border-color: #059669; background: #F0FDF4; }
    .qr-scan-zone.found { border-color: #059669; background: #D1FAE5; }
    .qr-scan-zone.error { border-color: #DC2626; background: #FEE2E2; }
    .scan-result-card { background: white; border: 2px solid #D1FAE5; border-radius: 16px; padding: 24px; margin-top: 16px; }
    .visitor-pass { background: white; border: 2px solid #D1FAE5; border-radius: 16px; padding: 24px; display: inline-block; text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
    .visitor-pass .pass-header { font-weight: 700; color: #059669; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
    .pulse-dot { animation: pulse-anim 2s infinite; }
    @keyframes pulse-anim { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
    #scanner-video { width: 100%; max-width: 400px; border-radius: 12px; border: 3px solid #059669; }
  </style>
</head>

<body class="bg-brand-background-main min-h-screen font-[Inter,'Segoe_UI',system-ui,-apple-system,sans-serif]">

  <?php $activePage = 'visitors'; $baseUrl = '../../'; include '../../sidebar.php'; ?>

  <!-- MAIN WRAPPER -->
  <div class="md:pl-72">
    <!-- HEADER -->
    <header class="h-16 bg-white flex items-center justify-between px-4 sm:px-6 relative shadow-[0_2px_8px_rgba(0,0,0,0.06)]">
      <div class="hidden md:block absolute left-0 top-0 h-16 w-[2px] bg-white"></div>
      <div class="flex items-center gap-3">
        <button id="mobile-menu-btn" class="md:hidden w-10 h-10 rounded-xl hover:bg-gray-100 active:bg-gray-200 transition flex items-center justify-center"></button>
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
        <h1 class="page-title">Visitor Management</h1>
        <p class="page-subtitle">Register visitors, generate QR passes, scan for check-in/out, track logs, and view analytics</p>
      </div>

      <!-- STAT CARDS -->
      <div class="stats-grid animate-in delay-1">
        <div class="stat-card"><div class="stat-icon purple"><svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div><div class="stat-info"><div class="stat-value" id="stat-total">—</div><div class="stat-label">Total Visitors</div></div></div>
        <div class="stat-card"><div class="stat-icon green"><svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg></div><div class="stat-info"><div class="stat-value" id="stat-checkedin">—</div><div class="stat-label">Checked In Now</div></div></div>
        <div class="stat-card"><div class="stat-icon blue"><svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div><div class="stat-info"><div class="stat-value" id="stat-preregs">—</div><div class="stat-label">Pre-Registered</div></div></div>
        <div class="stat-card"><div class="stat-icon amber"><svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg></div><div class="stat-info"><div class="stat-value" id="stat-today">—</div><div class="stat-label">Today's Visits</div></div></div>
      </div>



      <!-- ═══════════════════════════════════════════════════ -->
      <!-- TAB: Registration                                   -->
      <!-- ═══════════════════════════════════════════════════ -->
      <div id="tab-registration" class="tab-content active animate-in delay-3">

        <!-- Sub-tabs -->
        <div class="sub-tabs" style="margin-bottom:20px">
          <button class="sub-tab active" data-subtab="registered" onclick="switchSubTab(this,'registered')">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></svg>
            Registered Visitors <span id="badge-registered" class="sub-tab-badge">0</span>
          </button>
          <button class="sub-tab" data-subtab="prereg" onclick="switchSubTab(this,'prereg')">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
            Pre-Registrations <span id="badge-prereg" class="sub-tab-badge badge-amber">0</span>
          </button>
        </div>

        <!-- Sub-tab: Registered Visitors -->
        <div id="subtab-registered" class="sub-tab-content active">
          <div class="card">
            <div class="card-header">
              <span class="card-title">Registered Visitors</span>
              <div style="display:flex;gap:8px">
                <button class="btn-export btn-export-pdf btn-export-sm" onclick="exportVisitors('pdf')">📄 PDF</button>
                <button class="btn-export btn-export-csv btn-export-sm" onclick="exportVisitors('csv')">📊 CSV</button>
              </div>
            </div>
            <div class="card-body">
              <table class="data-table">
                <thead><tr><th>Code</th><th>Visitor</th><th>Company</th><th>Contact</th><th>ID</th><th>Visits</th><th>QR</th><th>Actions</th></tr></thead>
                <tbody id="visitors-tbody"></tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Sub-tab: Pre-Registrations -->
        <div id="subtab-prereg" class="sub-tab-content">
          <div class="card">
            <div class="card-header">
              <span class="card-title">Upcoming Pre-Registrations</span>
              <div style="display:flex;gap:8px;align-items:center">
                <button class="btn-export btn-export-pdf btn-export-sm" onclick="exportPreregs('pdf')">📄 PDF</button>
                <button class="btn-export btn-export-csv btn-export-sm" onclick="exportPreregs('csv')">📊 CSV</button>
                <span id="prereg-count" style="font-size:12px;color:#6B7280">0 pending</span>
                <button class="btn btn-sm" style="background:#059669;color:#fff;border:none" onclick="openModal('modal-prereg')">+ Pre-Register</button>
              </div>
            </div>
            <div class="card-body">
              <table class="data-table">
                <thead><tr><th>Code</th><th>Visitor</th><th>Company</th><th>Purpose</th><th>Expected</th><th>Host</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody id="preregs-tbody"></tbody>
              </table>
            </div>
          </div>
        </div>

      </div>

      <!-- ═══════════════════════════════════════════════════ -->
      <!-- TAB: QR Pass & Scanner                              -->
      <!-- ═══════════════════════════════════════════════════ -->
      <div id="tab-qr" class="tab-content">
        <div class="grid-2">
          <!-- QR Pass Generator -->
          <div class="card">
            <div class="card-header"><span class="card-title">Generate QR Pass</span></div>
            <div class="card-body" style="padding:20px">
              <div class="form-control">
                <label>Select Visitor</label>
                <select id="qr-visitor-select" class="form-input">
                  <option value="">-- Select Visitor --</option>
                </select>
              </div>
              <button class="btn btn-primary btn-sm" style="margin-top:8px" onclick="generateVisitorQR()">Generate QR Pass</button>

              <div id="qr-preview" style="margin-top:24px;text-align:center;display:none">
                <div class="visitor-pass">
                  <div class="pass-header">VISITOR PASS</div>
                  <div style="font-size:11px;color:#6B7280;margin-bottom:4px">Microfinancial Management System</div>
                  <hr style="border-color:#D1FAE5;margin:8px 0">
                  <div id="qr-visitor-name" style="font-weight:700;font-size:16px;color:#1F2937"></div>
                  <div id="qr-visitor-company" style="font-size:13px;color:#6B7280;margin-bottom:12px"></div>
                  <img id="qr-code-img" src="" alt="QR" style="width:180px;height:180px;border-radius:8px;margin:0 auto;display:block">
                  <div id="qr-code-id" style="font-size:12px;color:#059669;margin-top:10px;font-weight:700;letter-spacing:1px"></div>
                  <div style="font-size:10px;color:#9CA3AF;margin-top:8px">Present this QR code at the front desk</div>
                </div>
                <button class="btn btn-outline btn-sm" style="margin-top:12px" onclick="printQR()">Print Pass</button>
              </div>
            </div>
          </div>

          <!-- QR Scanner -->
          <div class="card">
            <div class="card-header"><span class="card-title">Scan / Lookup Visitor</span></div>
            <div class="card-body" style="padding:20px">
              <!-- Manual Code Entry -->
              <div class="form-control" style="margin-bottom:16px">
                <label>Enter Visitor Code or Scan QR</label>
                <div style="display:flex;gap:8px">
                  <input type="text" id="scan-code-input" class="form-input" placeholder="e.g. VIS-001, PR-2026-001, VL-2026-0001" style="flex:1" onkeydown="if(event.key==='Enter')lookupVisitor()">
                  <button class="btn btn-primary btn-sm" onclick="lookupVisitor()">Lookup</button>
                </div>
              </div>

              <!-- Camera Scanner -->
              <div id="scanner-zone" class="qr-scan-zone">
                <div id="scanner-idle">
                  <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="1.5" style="margin:0 auto 12px"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                  <div style="font-weight:600;color:#1F2937;margin-bottom:4px">Camera QR Scanner</div>
                  <div style="font-size:13px;color:#9CA3AF;margin-bottom:16px">Point your camera at a visitor's QR code to scan it</div>
                  <button class="btn btn-primary btn-sm" onclick="startScanner()">Enable Camera</button>
                </div>
                <div id="scanner-active" style="display:none">
                  <video id="scanner-video" autoplay playsinline></video>
                  <canvas id="scanner-canvas" style="display:none"></canvas>
                  <div style="margin-top:12px;font-size:13px;color:#059669;font-weight:600">
                    <span class="pulse-dot" style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#059669;margin-right:6px"></span>
                    Scanning... Point camera at QR code
                  </div>
                  <button class="btn btn-outline btn-sm" style="margin-top:8px" onclick="stopScanner()">Stop Scanner</button>
                </div>
              </div>

              <!-- Scan Result -->
              <div id="scan-result" style="display:none"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════ -->
      <!-- TAB: Visitor Logs                                   -->
      <!-- ═══════════════════════════════════════════════════ -->
      <div id="tab-logs" class="tab-content">
        <div class="card">
          <div class="card-header">
            <span class="card-title">Visitor Logs</span>
            <div style="display:flex;gap:8px;align-items:center">
              <button class="btn-export btn-export-pdf btn-export-sm" onclick="exportLogs('pdf')">📄 PDF</button>
              <button class="btn-export btn-export-csv btn-export-sm" onclick="exportLogs('csv')">📊 CSV</button>
            </div>
          </div>
          <!-- Filter Bar -->
          <div style="padding:12px 20px;background:#F9FAFB;border-bottom:1px solid #E5E7EB;display:flex;flex-wrap:wrap;gap:10px;align-items:center">
            <input type="text" id="log-filter-search" class="form-input" placeholder="Search visitor name..." style="width:180px;padding:6px 12px;font-size:12px" oninput="renderLogs()">
            <select id="log-filter-status" class="form-input" style="width:auto;padding:6px 12px;font-size:12px" onchange="renderLogs()">
              <option value="">All Status</option>
              <option value="checked_in">Checked In</option>
              <option value="checked_out">Checked Out</option>
              <option value="pre_registered">Pre-Registered</option>
            </select>
            <select id="log-filter-purpose" class="form-input" style="width:auto;padding:6px 12px;font-size:12px" onchange="renderLogs()">
              <option value="">All Purpose</option>
              <option value="meeting">Meeting</option>
              <option value="delivery">Delivery</option>
              <option value="interview">Interview</option>
              <option value="inspection">Inspection</option>
              <option value="consultation">Consultation</option>
              <option value="maintenance">Maintenance</option>
              <option value="partnership">Partnership</option>
              <option value="educational">Educational</option>
              <option value="other">Other</option>
            </select>
            <select id="log-filter-dept" class="form-input" style="width:auto;padding:6px 12px;font-size:12px" onchange="renderLogs()">
              <option value="">All Departments</option>
              <option value="Executive Office">Executive Office</option>
              <option value="Human Resources">Human Resources</option>
              <option value="Finance Dept.">Finance Dept.</option>
              <option value="Legal Dept.">Legal Dept.</option>
              <option value="Compliance Dept.">Compliance Dept.</option>
              <option value="Operations">Operations</option>
              <option value="Credit Department">Credit Department</option>
            </select>
            <input type="date" id="log-filter-from" class="form-input" style="width:auto;padding:6px 12px;font-size:12px" onchange="renderLogs()" title="From date">
            <input type="date" id="log-filter-to" class="form-input" style="width:auto;padding:6px 12px;font-size:12px" onchange="renderLogs()" title="To date">
            <button class="btn btn-outline btn-sm" style="font-size:11px" onclick="clearLogFilters()">Clear</button>
            <span id="log-result-count" style="font-size:11px;color:#6B7280;margin-left:auto">0 records</span>
          </div>
          <div class="card-body">
            <table class="data-table">
              <thead><tr><th>Visit Code</th><th>Visitor</th><th>Company</th><th>Purpose</th><th>Check-In</th><th>Check-Out</th><th>Duration</th><th>Host</th><th>Status</th><th>Actions</th></tr></thead>
              <tbody id="logs-tbody"></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════ -->
      <!-- TAB: Analytics                                      -->
      <!-- ═══════════════════════════════════════════════════ -->
      <div id="tab-analytics" class="tab-content">
        <div class="stats-grid">
          <div class="stat-card"><div class="stat-icon green"><svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg></div><div class="stat-info"><div class="stat-value" id="stat-month">—</div><div class="stat-label">This Month</div></div></div>
          <div class="stat-card"><div class="stat-icon blue"><svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div><div class="stat-info"><div class="stat-value" id="stat-avg-dur">—</div><div class="stat-label">Avg Duration</div></div></div>
          <div class="stat-card"><div class="stat-icon amber"><svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></div><div class="stat-info"><div class="stat-value" id="stat-top-org">—</div><div class="stat-label">Top Visitor Org</div></div></div>
          <div class="stat-card"><div class="stat-icon purple"><svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div><div class="stat-info"><div class="stat-value" id="stat-upcoming">—</div><div class="stat-label">Upcoming Pre-Regs</div></div></div>
        </div>

        <div class="grid-2" style="margin-top:20px">
          <div class="card">
            <div class="card-header"><span class="card-title">Visits by Purpose</span></div>
            <div class="card-body" style="padding:20px;height:280px">
              <canvas id="chartVisitorPurpose"></canvas>
            </div>
          </div>
          <div class="card">
            <div class="card-header"><span class="card-title">Top Visitors</span></div>
            <div class="card-body" style="padding:16px">
              <div id="top-visitors-list" style="display:flex;flex-direction:column;gap:10px"></div>
            </div>
          </div>
        </div>

        <div class="card" style="margin-top:20px">
          <div class="card-header"><span class="card-title">Upcoming Pre-Registrations</span></div>
          <div class="card-body">
            <table class="data-table">
              <thead><tr><th>Code</th><th>Visitor</th><th>Company</th><th>Purpose</th><th>Expected Date</th><th>Host</th><th>Status</th></tr></thead>
              <tbody id="analytics-preregs-tbody"></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════ -->
      <!-- MODAL: Register Visitor                             -->
      <!-- ═══════════════════════════════════════════════════ -->
      <div id="modal-visitor" class="modal-overlay" onclick="if(event.target===this)closeModal('modal-visitor')">
        <div class="modal" style="max-width:640px">
          <div class="modal-header"><span class="modal-title">Register New Visitor</span><button class="modal-close" onclick="closeModal('modal-visitor')">&times;</button></div>
          <div class="modal-body">
            <div class="grid-2">
              <div class="form-control"><label>First Name <span style="color:red">*</span></label><input type="text" class="form-input" id="reg-first-name" placeholder="First name"></div>
              <div class="form-control"><label>Last Name <span style="color:red">*</span></label><input type="text" class="form-input" id="reg-last-name" placeholder="Last name"></div>
            </div>
            <div class="grid-2">
              <div class="form-control"><label>Email</label><input type="email" class="form-input" id="reg-email" placeholder="visitor@email.com"></div>
              <div class="form-control"><label>Phone</label><input type="tel" class="form-input" id="reg-phone" placeholder="09XX-XXX-XXXX"></div>
            </div>
            <div class="form-control"><label>Company / Organization</label><input type="text" class="form-input" id="reg-company" placeholder="Company name"></div>
            <div class="grid-2">
              <div class="form-control">
                <label>Valid ID Type</label>
                <select class="form-input" id="reg-id-type">
                  <option value="">-- Select --</option>
                  <option value="government_id">Government ID</option>
                  <option value="passport">Passport</option>
                  <option value="drivers_license">Driver's License</option>
                  <option value="company_id">Company ID</option>
                  <option value="other">Other</option>
                </select>
              </div>
              <div class="form-control"><label>ID Number</label><input type="text" class="form-input" id="reg-id-number" placeholder="ID number"></div>
            </div>
            <div class="grid-2">
              <div class="form-control"><label>Scheduled Visit Date</label><input type="date" class="form-input" id="reg-visit-date"></div>
              <div class="form-control"><label>Scheduled Visit Time</label><input type="time" class="form-input" id="reg-visit-time"></div>
            </div>
            <div class="form-control">
              <label>Approving Department</label>
              <select class="form-input" id="reg-department">
                <option value="">-- Select Department --</option>
                <option value="Executive Office">Executive Office</option>
                <option value="Human Resources">Human Resources</option>
                <option value="Finance Dept.">Finance Dept.</option>
                <option value="Legal Dept.">Legal Dept.</option>
                <option value="Compliance Dept.">Compliance Dept.</option>
                <option value="Operations">Operations</option>
                <option value="Credit Department">Credit Department</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modal-visitor')">Cancel</button>
            <button class="btn btn-primary" onclick="submitRegisterVisitor()">Register & Generate QR</button>
          </div>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════ -->
      <!-- MODAL: Pre-Register Visitor                         -->
      <!-- ═══════════════════════════════════════════════════ -->
      <div id="modal-prereg" class="modal-overlay" onclick="if(event.target===this)closeModal('modal-prereg')">
        <div class="modal" style="max-width:640px">
          <div class="modal-header"><span class="modal-title">Pre-Register Visitor</span><button class="modal-close" onclick="closeModal('modal-prereg')">&times;</button></div>
          <div class="modal-body">
            <div class="grid-2">
              <div class="form-control"><label>Full Name <span style="color:red">*</span></label><input type="text" class="form-input" id="prereg-name" placeholder="Expected visitor name"></div>
              <div class="form-control"><label>Company</label><input type="text" class="form-input" id="prereg-company" placeholder="Company name"></div>
            </div>
            <div class="grid-2">
              <div class="form-control"><label>Email</label><input type="email" class="form-input" id="prereg-email" placeholder="visitor@email.com"></div>
              <div class="form-control"><label>Phone</label><input type="tel" class="form-input" id="prereg-phone" placeholder="09XX-XXX-XXXX"></div>
            </div>
            <div class="grid-2">
              <div class="form-control"><label>Expected Date <span style="color:red">*</span></label><input type="date" class="form-input" id="prereg-date"></div>
              <div class="form-control"><label>Expected Time</label><input type="time" class="form-input" id="prereg-time"></div>
            </div>
            <div class="grid-2">
              <div class="form-control">
                <label>Purpose <span style="color:red">*</span></label>
                <select class="form-input" id="prereg-purpose">
                  <option value="meeting">Meeting</option>
                  <option value="delivery">Delivery</option>
                  <option value="interview">Interview</option>
                  <option value="inspection">Inspection</option>
                  <option value="consultation">Consultation</option>
                  <option value="maintenance">Maintenance</option>
                  <option value="partnership">Partnership</option>
                  <option value="educational">Educational</option>
                  <option value="other">Other</option>
                </select>
              </div>
              <div class="form-control">
                <label>Host (User ID) <span style="color:red">*</span></label>
                <input type="number" class="form-input" id="prereg-host" placeholder="Host user ID" value="1">
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modal-prereg')">Cancel</button>
            <button class="btn btn-primary" onclick="submitPreRegister()">Pre-Register</button>
          </div>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════ -->
      <!-- MODAL: Check-In Form                                -->
      <!-- ═══════════════════════════════════════════════════ -->
      <div id="modal-checkin" class="modal-overlay" onclick="if(event.target===this)closeModal('modal-checkin')">
        <div class="modal" style="max-width:560px">
          <div class="modal-header"><span class="modal-title">Check-In Visitor</span><button class="modal-close" onclick="closeModal('modal-checkin')">&times;</button></div>
          <div class="modal-body">
            <div style="background:#D1FAE5;padding:12px 16px;border-radius:10px;margin-bottom:16px;display:flex;align-items:center;gap:12px">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
              <div>
                <div id="checkin-visitor-name" style="font-weight:700;color:#065F46"></div>
                <div id="checkin-visitor-company" style="font-size:12px;color:#047857"></div>
              </div>
            </div>
            <input type="hidden" id="checkin-visitor-id">
            <div class="grid-2">
              <div class="form-control">
                <label>Purpose <span style="color:red">*</span></label>
                <select class="form-input" id="checkin-purpose" onchange="togglePurposeDetails('checkin')">
                  <option value="meeting">Meeting</option>
                  <option value="delivery">Delivery</option>
                  <option value="interview">Interview</option>
                  <option value="inspection">Inspection</option>
                  <option value="consultation">Consultation</option>
                  <option value="maintenance">Maintenance</option>
                  <option value="partnership">Partnership</option>
                  <option value="educational">Educational</option>
                  <option value="other">Other</option>
                </select>
              </div>
              <div class="form-control"><label>Badge Number</label><input type="text" class="form-input" id="checkin-badge" placeholder="e.g. B-001"></div>
            </div>
            <div class="grid-2">
              <div class="form-control"><label>Host Name</label><input type="text" class="form-input" id="checkin-host-name" placeholder="Person to visit"></div>
              <div class="form-control">
                <label>Host Department</label>
                <select class="form-input" id="checkin-host-dept">
                  <option>Executive Office</option>
                  <option>Human Resources</option>
                  <option>Finance Dept.</option>
                  <option>IT Department</option>
                  <option>Legal Dept.</option>
                  <option>Compliance Dept.</option>
                  <option>Operations</option>
                  <option>Credit Department</option>
                </select>
              </div>
            </div>
            <!-- Purpose Breakdown (dynamic) -->
            <div id="checkin-purpose-breakdown" style="display:none">
              <div id="checkin-partnership-fields" style="display:none">
                <div class="grid-2">
                  <div class="form-control"><label>Partner Organization</label><input type="text" class="form-input" id="checkin-partner-org" placeholder="Organization name"></div>
                  <div class="form-control">
                    <label>Partnership Type</label>
                    <select class="form-input" id="checkin-partner-type">
                      <option value="business">Business Partnership</option>
                      <option value="mou">MOU Discussion</option>
                      <option value="joint_venture">Joint Venture</option>
                      <option value="sponsorship">Sponsorship</option>
                      <option value="other">Other</option>
                    </select>
                  </div>
                </div>
              </div>
              <div id="checkin-educational-fields" style="display:none">
                <div class="grid-2">
                  <div class="form-control"><label>Institution</label><input type="text" class="form-input" id="checkin-institution" placeholder="School/University"></div>
                  <div class="form-control">
                    <label>Educational Purpose</label>
                    <select class="form-input" id="checkin-edu-type">
                      <option value="thesis">Thesis/Research</option>
                      <option value="ojt">OJT/Internship</option>
                      <option value="seminar">Seminar/Workshop</option>
                      <option value="benchmarking">Benchmarking</option>
                      <option value="field_study">Field Study</option>
                      <option value="other">Other</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
            <div class="form-control"><label>Purpose Details / Notes</label><textarea class="form-input" id="checkin-details" rows="2" placeholder="Additional details about the visit"></textarea></div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modal-checkin')">Cancel</button>
            <button class="btn btn-primary" onclick="submitCheckIn()">Check In Now</button>
          </div>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════ -->
      <!-- MODAL: Visitor Detail                               -->
      <!-- ═══════════════════════════════════════════════════ -->
      <div id="modal-visitor-detail" class="modal-overlay" onclick="if(event.target===this)closeModal('modal-visitor-detail')">
        <div class="modal" style="max-width:560px">
          <div class="modal-header"><span class="modal-title" id="detail-title">Visitor Details</span><button class="modal-close" onclick="closeModal('modal-visitor-detail')">&times;</button></div>
          <div class="modal-body" id="detail-body"></div>
          <div class="modal-footer"><button class="btn btn-outline" onclick="closeModal('modal-visitor-detail')">Close</button></div>
        </div>
      </div>

    </main>
  </div>

<script src="../../admin.js"></script>
<script src="../../export.js"></script>
<script>
/* ═══════════════════════════════════════════════════════
   VISITOR MANAGEMENT MODULE — Full API Integration
   ═══════════════════════════════════════════════════════ */

const API = '../../api/visitors.php';

// Data stores
let visitors = [], logs = [], preregs = [], stats = {};
let purposeChart = null;
let scannerStream = null;
let scannerInterval = null;

// ───── Purpose Breakdown Toggle ─────
function togglePurposeDetails(prefix) {
  const purpose = document.getElementById(prefix + '-purpose').value;
  const breakdown = document.getElementById(prefix + '-purpose-breakdown');
  if (!breakdown) return;
  const partnerFields = document.getElementById(prefix + '-partnership-fields');
  const eduFields = document.getElementById(prefix + '-educational-fields');
  breakdown.style.display = (purpose === 'partnership' || purpose === 'educational') ? 'block' : 'none';
  if (partnerFields) partnerFields.style.display = purpose === 'partnership' ? 'block' : 'none';
  if (eduFields) eduFields.style.display = purpose === 'educational' ? 'block' : 'none';
}

// ───── Helpers ─────
function esc(s) { const d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML; }
function fmtDate(d) { if (!d) return '—'; return new Date(d).toLocaleDateString('en-US', { year:'numeric', month:'short', day:'numeric' }); }
function fmtTime(d) { if (!d) return '—'; return new Date(d).toLocaleTimeString('en-US', { hour:'numeric', minute:'2-digit', hour12:true }); }
function fmtDateTime(d) { if (!d) return '—'; return fmtDate(d) + ' ' + fmtTime(d); }
function labelCase(s) { return (s || '').replace(/_/g,' ').replace(/\b\w/g, c => c.toUpperCase()); }

function statusBadge(status) {
  const map = {
    'checked_in': 'badge-green', 'checked_out': 'badge-gray', 'pre_registered': 'badge-blue',
    'cancelled': 'badge-red', 'no_show': 'badge-red', 'pending': 'badge-amber',
    'approved': 'badge-green', 'expired': 'badge-gray', 'rejected': 'badge-red'
  };
  return `<span class="badge ${map[status] || 'badge-gray'}">${labelCase(status)}</span>`;
}

function duration(checkIn, checkOut) {
  if (!checkIn) return '—';
  if (!checkOut) return '<span class="badge badge-green">Active</span>';
  const mins = Math.round((new Date(checkOut) - new Date(checkIn)) / 60000);
  if (mins < 60) return mins + 'm';
  const h = Math.floor(mins / 60);
  const m = mins % 60;
  return h + 'h ' + (m ? m + 'm' : '');
}

function qrUrl(code, size) {
  return 'https://api.qrserver.com/v1/create-qr-code/?data=' + encodeURIComponent(code) + '&size=' + (size||40) + 'x' + (size||40) + '&color=059669';
}

// ───── Data Loading ─────
async function loadData() {
  try {
    const [sRes, vRes, lRes, pRes] = await Promise.all([
      fetch(API + '?action=dashboard_stats'),
      fetch(API + '?action=list_visitors'),
      fetch(API + '?action=list_logs'),
      fetch(API + '?action=list_preregistrations')
    ]);
    stats = await sRes.json();
    visitors = (await vRes.json()).data || [];
    logs = (await lRes.json()).data || [];
    preregs = (await pRes.json()).data || [];

    renderStats();
    renderVisitors();
    renderPreregs();
    renderLogs();
    populateQRSelect();
    renderAnalytics();
  } catch(e) {
    console.error('Load error:', e);
    Swal.fire({ icon:'error', title:'Load Error', text:'Failed to load visitor data. Check API connection.', confirmButtonColor:'#059669' });
  }
}

// ───── Render Stats ─────
function renderStats() {
  document.getElementById('stat-total').textContent = stats.total_visitors ?? 0;
  document.getElementById('stat-checkedin').textContent = stats.checked_in_now ?? 0;
  document.getElementById('stat-preregs').textContent = stats.pending_preregs ?? 0;
  document.getElementById('stat-today').textContent = stats.today_visits ?? 0;
}

// ───── Render Visitors Table ─────
function renderVisitors() {
  const tbody = document.getElementById('visitors-tbody');
  updateBadges();
  if (!visitors.length) {
    tbody.innerHTML = '<tr><td colspan="8"><div class="empty-state" style="padding:30px"><div style="font-size:40px;margin-bottom:8px">👤</div><div style="font-weight:600">No visitors registered yet</div><div style="font-size:13px;color:#9CA3AF">Register a visitor using the button above.</div></div></td></tr>';
    return;
  }
  tbody.innerHTML = visitors.map(v => {
    const name = esc(v.first_name + ' ' + v.last_name);
    const hasActiveLog = logs.some(l => l.visitor_id == v.visitor_id && l.status === 'checked_in');
    return `<tr>
      <td style="font-weight:600;font-size:12px;color:#059669">${esc(v.visitor_code)}</td>
      <td style="font-weight:600">${name}</td>
      <td>${esc(v.company) || '<span style="color:#9CA3AF">—</span>'}</td>
      <td style="font-size:12px">${esc(v.phone) || esc(v.email) || '—'}</td>
      <td style="font-size:12px">${v.id_type ? labelCase(v.id_type) : '—'}</td>
      <td style="text-align:center"><span class="badge badge-blue">${v.visit_count || 0}</span></td>
      <td><img src="${qrUrl(v.visitor_code)}" alt="QR" style="width:32px;height:32px;border-radius:4px"></td>
      <td>
        <div style="display:flex;gap:4px">
          ${hasActiveLog ?
            `<button class="btn btn-outline btn-sm" style="color:#DC2626;border-color:#DC2626" onclick="checkOutVisitor(${v.visitor_id})" title="Check Out">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            </button>` :
            `<button class="btn btn-primary btn-sm" onclick="openCheckInModal(${v.visitor_id})" title="Check In">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
            </button>`
          }
          <button class="btn btn-outline btn-sm" onclick="viewVisitorDetail(${v.visitor_id})" title="View Details">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
      </td>
    </tr>`;
  }).join('');
}

// ───── Sub-tab Switching ─────
function switchSubTab(btn, tab) {
  document.querySelectorAll('.sub-tab').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.sub-tab-content').forEach(c => c.classList.remove('active'));
  btn.classList.add('active');
  const target = document.getElementById('subtab-' + tab);
  if (target) target.classList.add('active');
}

// ───── Update badge counts ─────
function updateBadges() {
  document.getElementById('badge-registered').textContent = visitors.length;
  const pendingCount = preregs.filter(p => p.status === 'pending').length;
  document.getElementById('badge-prereg').textContent = preregs.length;
  document.getElementById('prereg-count').textContent = pendingCount + ' pending';
}

// ───── Render Pre-Registrations ─────
function renderPreregs() {
  const tbody = document.getElementById('preregs-tbody');
  updateBadges();

  if (!preregs.length) {
    tbody.innerHTML = '<tr><td colspan="8"><div class="empty-state" style="padding:24px"><div style="font-size:36px;margin-bottom:8px">📋</div><div style="font-weight:600">No pre-registrations</div></div></td></tr>';
    return;
  }
  tbody.innerHTML = preregs.map(p => {
    let actions = `<div style="display:flex;gap:4px">`;
    if (p.status === 'pending') {
      actions += `
        <button class="btn btn-sm" style="background:#059669;color:#fff;border:none" onclick="approvePrereg('${esc(p.prereg_code)}')" title="Approve">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
        </button>
        <button class="btn btn-sm" style="background:#DC2626;color:#fff;border:none" onclick="rejectPrereg('${esc(p.prereg_code)}')" title="Reject">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>`;
    }
    actions += `
      <button class="btn btn-outline btn-sm" onclick="viewPreregDetail('${esc(p.prereg_code)}')" title="View">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
      </button>
    </div>`;
    return `<tr>
      <td style="font-weight:600;font-size:12px;color:#059669">${esc(p.prereg_code)}</td>
      <td style="font-weight:600">${esc(p.visitor_name)}</td>
      <td>${esc(p.visitor_company) || '—'}</td>
      <td>${labelCase(p.purpose)}</td>
      <td style="font-size:12px">${fmtDate(p.expected_date)}${p.expected_time ? '<br>' + p.expected_time : ''}</td>
      <td>${esc(p.host_name) || '—'}</td>
      <td>${statusBadge(p.status)}</td>
      <td>${actions}</td>
    </tr>`;
  }).join('');
}

// ───── Clear Log Filters ─────
function clearLogFilters() {
  ['log-filter-search','log-filter-status','log-filter-purpose','log-filter-dept','log-filter-from','log-filter-to'].forEach(id => document.getElementById(id).value = '');
  renderLogs();
}

// ───── Render Logs ─────
function renderLogs() {
  const tbody = document.getElementById('logs-tbody');
  const filterStatus = document.getElementById('log-filter-status')?.value || '';
  const filterPurpose = document.getElementById('log-filter-purpose')?.value || '';
  const filterDept = document.getElementById('log-filter-dept')?.value || '';
  const filterSearch = (document.getElementById('log-filter-search')?.value || '').toLowerCase().trim();
  const filterFrom = document.getElementById('log-filter-from')?.value || '';
  const filterTo = document.getElementById('log-filter-to')?.value || '';

  let filtered = logs;
  if (filterStatus) filtered = filtered.filter(l => l.status === filterStatus);
  if (filterPurpose) filtered = filtered.filter(l => l.purpose === filterPurpose);
  if (filterDept) filtered = filtered.filter(l => (l.host_department || '') === filterDept);
  if (filterSearch) filtered = filtered.filter(l => (l.visitor_name || '').toLowerCase().includes(filterSearch) || (l.company || '').toLowerCase().includes(filterSearch));
  if (filterFrom) filtered = filtered.filter(l => l.check_in_time && l.check_in_time.slice(0,10) >= filterFrom);
  if (filterTo) filtered = filtered.filter(l => l.check_in_time && l.check_in_time.slice(0,10) <= filterTo);

  const countEl = document.getElementById('log-result-count');
  if (countEl) countEl.textContent = filtered.length + ' records';

  if (!filtered.length) {
    tbody.innerHTML = '<tr><td colspan="10"><div class="empty-state" style="padding:30px"><div style="font-size:40px;margin-bottom:8px">📋</div><div style="font-weight:600">No visitor logs found</div></div></td></tr>';
    return;
  }

  tbody.innerHTML = filtered.map(l => `<tr>
    <td style="font-weight:600;font-size:12px">${esc(l.visit_code)}</td>
    <td><a href="javascript:void(0)" onclick="viewVisitorHistory(${l.visitor_id})" style="font-weight:600;color:#059669;text-decoration:none;cursor:pointer" title="Click to see all visits">${esc(l.visitor_name)}</a></td>
    <td>${esc(l.company) || '—'}</td>
    <td>${labelCase(l.purpose)}</td>
    <td style="font-size:12px">${fmtTime(l.check_in_time)}<br><span style="color:#9CA3AF">${fmtDate(l.check_in_time)}</span></td>
    <td style="font-size:12px">${l.check_out_time ? fmtTime(l.check_out_time) : '—'}</td>
    <td>${duration(l.check_in_time, l.check_out_time)}</td>
    <td style="font-size:12px">${esc(l.host_name) || '—'}${l.host_department ? '<br><span style="color:#9CA3AF">' + esc(l.host_department) + '</span>' : ''}</td>
    <td>${statusBadge(l.status)}</td>
    <td>
      ${l.status === 'checked_in' ?
        `<button class="btn btn-outline btn-sm" style="color:#DC2626;border-color:#DC2626" onclick="checkOutByLogId(${l.log_id})" title="Check Out">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </button>` :
        `<button class="btn btn-outline btn-sm" onclick="viewLogDetail(${l.log_id})" title="View">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        </button>`
      }
    </td>
  </tr>`).join('');
}

// ───── Populate QR Select ─────
function populateQRSelect() {
  const sel = document.getElementById('qr-visitor-select');
  sel.innerHTML = '<option value="">-- Select Visitor --</option>';
  visitors.forEach(v => {
    sel.innerHTML += `<option value="${v.visitor_code}|${esc(v.first_name + ' ' + v.last_name)}|${esc(v.company||'')}|${v.visit_count||0}">${esc(v.first_name + ' ' + v.last_name)} — ${esc(v.company||'No Company')}</option>`;
  });
}

// ───── QR Pass Generator ─────
function generateVisitorQR() {
  const sel = document.getElementById('qr-visitor-select');
  if (!sel.value) return Swal.fire({ icon:'warning', title:'No Visitor Selected', text:'Please select a visitor to generate a QR pass.', confirmButtonColor:'#059669' });
  const [code, name, company] = sel.value.split('|');
  document.getElementById('qr-visitor-name').textContent = name;
  document.getElementById('qr-visitor-company').textContent = company || 'No Company';
  document.getElementById('qr-code-img').src = qrUrl(code, 180);
  document.getElementById('qr-code-id').textContent = code;
  document.getElementById('qr-preview').style.display = 'block';
}

function printQR() {
  const pass = document.querySelector('.visitor-pass');
  if (!pass) return;
  const w = window.open('', '_blank', 'width=400,height=500');
  w.document.write('<html><head><title>Visitor Pass</title><style>body{display:flex;justify-content:center;align-items:center;min-height:100vh;font-family:Arial,sans-serif;}</style></head><body>');
  w.document.write(pass.outerHTML);
  w.document.write('</body></html>');
  w.document.close();
  w.print();
}

// ═══════════════════════════════════════════════════════
// QR SCANNER
// ═══════════════════════════════════════════════════════

async function startScanner() {
  try {
    const video = document.getElementById('scanner-video');
    scannerStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
    video.srcObject = scannerStream;
    document.getElementById('scanner-idle').style.display = 'none';
    document.getElementById('scanner-active').style.display = 'block';
    document.getElementById('scanner-zone').classList.add('scanning');

    // Poll for QR codes using canvas
    const canvas = document.getElementById('scanner-canvas');
    const ctx = canvas.getContext('2d');

    scannerInterval = setInterval(() => {
      if (video.readyState === video.HAVE_ENOUGH_DATA) {
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        // Note: For full QR decoding, a library like jsQR would be needed.
        // The manual code entry field serves as the primary scanner interface.
      }
    }, 500);

    Swal.fire({ icon:'info', title:'Camera Active', text:'Camera scanning is active. For best results, also use the manual code entry field above.', timer:3000, showConfirmButton:false, toast:true, position:'top-end' });
  } catch(e) {
    Swal.fire({ icon:'error', title:'Camera Error', text:'Could not access camera. Please use the manual code entry instead.\n\nError: ' + e.message, confirmButtonColor:'#059669' });
  }
}

function stopScanner() {
  if (scannerStream) {
    scannerStream.getTracks().forEach(t => t.stop());
    scannerStream = null;
  }
  if (scannerInterval) {
    clearInterval(scannerInterval);
    scannerInterval = null;
  }
  document.getElementById('scanner-idle').style.display = 'block';
  document.getElementById('scanner-active').style.display = 'none';
  document.getElementById('scanner-zone').classList.remove('scanning');
}

// ───── Manual Lookup ─────
async function lookupVisitor() {
  const code = document.getElementById('scan-code-input').value.trim();
  if (!code) return Swal.fire({ icon:'warning', title:'Enter a Code', text:'Please enter a visitor code, pre-registration code, or visit code.', confirmButtonColor:'#059669' });

  const zone = document.getElementById('scanner-zone');
  const resultDiv = document.getElementById('scan-result');

  try {
    zone.classList.add('scanning');
    const res = await fetch(API + '?action=lookup_visitor&code=' + encodeURIComponent(code));
    const data = await res.json();

    if (!data.found) {
      zone.classList.remove('scanning');
      zone.classList.add('error');
      setTimeout(() => zone.classList.remove('error'), 2000);
      resultDiv.style.display = 'block';
      resultDiv.innerHTML = `<div class="scan-result-card" style="border-color:#FCA5A5">
        <div style="display:flex;align-items:center;gap:12px;color:#DC2626">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
          <div>
            <div style="font-weight:700;font-size:15px">Not Found</div>
            <div style="font-size:13px">No visitor found with code: <strong>${esc(code)}</strong></div>
          </div>
        </div>
      </div>`;
      return;
    }

    zone.classList.remove('scanning');
    zone.classList.add('found');
    setTimeout(() => zone.classList.remove('found'), 2000);
    resultDiv.style.display = 'block';

    if (data.type === 'visitor') {
      const v = data.visitor;
      const name = v.first_name + ' ' + v.last_name;
      const activeLog = data.active_log;

      resultDiv.innerHTML = `<div class="scan-result-card">
        <div style="display:flex;align-items:center;gap:16px;margin-bottom:16px">
          <div style="width:56px;height:56px;border-radius:14px;background:#D1FAE5;display:flex;align-items:center;justify-content:center">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
          </div>
          <div>
            <div style="font-weight:700;font-size:16px;color:#1F2937">${esc(name)}</div>
            <div style="font-size:13px;color:#6B7280">${esc(v.company || 'No Company')} · ${esc(v.visitor_code)}</div>
            <div style="font-size:12px;color:#9CA3AF;margin-top:2px">${v.visit_count || 0} previous visits</div>
          </div>
        </div>
        ${activeLog ?
          `<div style="background:#FEF3C7;padding:12px 16px;border-radius:10px;margin-bottom:12px;font-size:13px;color:#92400E">
            <strong>Currently Checked In</strong> since ${fmtTime(activeLog.check_in_time)} · ${esc(activeLog.host_department || '')}
          </div>
          <button class="btn btn-outline btn-sm" style="color:#DC2626;border-color:#DC2626;width:100%" onclick="checkOutByLogId(${activeLog.log_id})">Check Out Now</button>` :
          `<button class="btn btn-primary" style="width:100%" onclick="openCheckInModal(${v.visitor_id})">Check In This Visitor</button>`
        }
      </div>`;

    } else if (data.type === 'prereg') {
      const p = data.prereg;
      resultDiv.innerHTML = `<div class="scan-result-card">
        <div style="display:flex;align-items:center;gap:16px;margin-bottom:16px">
          <div style="width:56px;height:56px;border-radius:14px;background:#DBEAFE;display:flex;align-items:center;justify-content:center">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#3B82F6" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          </div>
          <div>
            <div style="font-weight:700;font-size:16px;color:#1F2937">${esc(p.visitor_name)}</div>
            <div style="font-size:13px;color:#6B7280">${esc(p.visitor_company || 'No Company')} · ${esc(p.prereg_code)}</div>
            <div style="font-size:12px;color:#9CA3AF;margin-top:2px">Pre-registered · Expected ${fmtDate(p.expected_date)}${p.expected_time ? ' at ' + p.expected_time : ''}</div>
          </div>
        </div>
        <div style="background:#DBEAFE;padding:10px 14px;border-radius:10px;margin-bottom:12px;font-size:13px;color:#1E40AF">
          <strong>Purpose:</strong> ${labelCase(p.purpose)} · <strong>Host:</strong> ${esc(p.host_name || '—')}
        </div>
        <div style="font-size:12px;color:#6B7280;margin-bottom:12px">This visitor is pre-registered. Register them as a visitor first, then check them in.</div>
        <button class="btn btn-primary" style="width:100%" onclick="registerFromPrereg(${JSON.stringify(p).replace(/"/g,'&quot;')})">Register & Check In</button>
      </div>`;

    } else if (data.type === 'visit_log') {
      const l = data.log;
      resultDiv.innerHTML = `<div class="scan-result-card">
        <div style="display:flex;align-items:center;gap:16px;margin-bottom:16px">
          <div style="width:56px;height:56px;border-radius:14px;background:#D1FAE5;display:flex;align-items:center;justify-content:center">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          </div>
          <div>
            <div style="font-weight:700;font-size:16px;color:#1F2937">${esc(l.visitor_name)}</div>
            <div style="font-size:13px;color:#6B7280">${esc(l.company || '')} · ${esc(l.visit_code)}</div>
          </div>
        </div>
        <table style="width:100%;font-size:13px;border-collapse:collapse">
          <tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280">Status</td><td style="padding:8px 0">${statusBadge(l.status)}</td></tr>
          <tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280">Check-In</td><td style="padding:8px 0">${fmtDateTime(l.check_in_time)}</td></tr>
          <tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280">Check-Out</td><td style="padding:8px 0">${l.check_out_time ? fmtDateTime(l.check_out_time) : '—'}</td></tr>
          <tr><td style="padding:8px 0;font-weight:600;color:#6B7280">Duration</td><td style="padding:8px 0">${duration(l.check_in_time, l.check_out_time)}</td></tr>
        </table>
        ${l.status === 'checked_in' ? `<button class="btn btn-outline btn-sm" style="color:#DC2626;border-color:#DC2626;width:100%;margin-top:12px" onclick="checkOutByLogId(${l.log_id})">Check Out Now</button>` : ''}
      </div>`;
    }

  } catch(e) {
    console.error(e);
    zone.classList.remove('scanning');
    Swal.fire({ icon:'error', title:'Lookup Error', text:'Failed to lookup visitor: ' + e.message, confirmButtonColor:'#059669' });
  }
}

// ═══════════════════════════════════════════════════════
// FORM SUBMISSIONS
// ═══════════════════════════════════════════════════════

// Register Visitor
async function submitRegisterVisitor() {
  const firstName = document.getElementById('reg-first-name').value.trim();
  const lastName = document.getElementById('reg-last-name').value.trim();
  if (!firstName || !lastName) return Swal.fire({ icon:'warning', title:'Required Fields', text:'First name and last name are required.', confirmButtonColor:'#059669' });

  const data = {
    first_name: firstName,
    last_name: lastName,
    email: document.getElementById('reg-email').value.trim() || null,
    phone: document.getElementById('reg-phone').value.trim() || null,
    company: document.getElementById('reg-company').value.trim() || null,
    id_type: document.getElementById('reg-id-type').value || null,
    id_number: document.getElementById('reg-id-number').value.trim() || null
  };

  try {
    const res = await fetch(API + '?action=register_visitor', {
      method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data)
    });
    const result = await res.json();
    if (result.success) {
      closeModal('modal-visitor');
      // Clear form
      ['reg-first-name','reg-last-name','reg-email','reg-phone','reg-company','reg-id-number'].forEach(id => document.getElementById(id).value = '');
      document.getElementById('reg-id-type').value = '';

      await Swal.fire({
        icon: 'success', title: 'Visitor Registered!',
        html: `<div style="text-align:center">
          <div style="font-size:14px;margin-bottom:16px"><strong>${esc(firstName)} ${esc(lastName)}</strong> has been registered.</div>
          <div style="margin-bottom:8px"><img src="${qrUrl(result.visitor_code, 160)}" style="width:160px;height:160px;border-radius:8px;margin:0 auto"></div>
          <div style="font-weight:700;color:#059669;font-size:16px;letter-spacing:1px">${result.visitor_code}</div>
          <div style="font-size:12px;color:#9CA3AF;margin-top:4px">Save this QR code for the visitor pass</div>
        </div>`,
        confirmButtonColor: '#059669'
      });
      await loadData();
    } else {
      Swal.fire({ icon:'error', title:'Registration Failed', text: result.error || 'Unknown error', confirmButtonColor:'#059669' });
    }
  } catch(e) {
    Swal.fire({ icon:'error', title:'Error', text: e.message, confirmButtonColor:'#059669' });
  }
}

// Pre-Register
async function submitPreRegister() {
  const name = document.getElementById('prereg-name').value.trim();
  const date = document.getElementById('prereg-date').value;
  const purpose = document.getElementById('prereg-purpose').value;
  const host = document.getElementById('prereg-host').value;

  if (!name || !date || !purpose || !host) return Swal.fire({ icon:'warning', title:'Required Fields', text:'Name, date, purpose, and host are required.', confirmButtonColor:'#059669' });

  const data = {
    visitor_name: name,
    visitor_email: document.getElementById('prereg-email').value.trim() || null,
    visitor_phone: document.getElementById('prereg-phone').value.trim() || null,
    visitor_company: document.getElementById('prereg-company').value.trim() || null,
    expected_date: date,
    expected_time: document.getElementById('prereg-time').value || null,
    purpose: purpose,
    host_user_id: parseInt(host)
  };

  try {
    const res = await fetch(API + '?action=preregister', {
      method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data)
    });
    const result = await res.json();
    if (result.success) {
      closeModal('modal-prereg');
      ['prereg-name','prereg-company','prereg-email','prereg-phone','prereg-date','prereg-time'].forEach(id => document.getElementById(id).value = '');
      Swal.fire({ icon:'success', title:'Pre-Registered!', html:`Visitor <strong>${esc(name)}</strong> has been pre-registered.<br>Code: <strong>${result.prereg_code}</strong>`, confirmButtonColor:'#059669' });
      await loadData();
    } else {
      Swal.fire({ icon:'error', title:'Failed', text: result.error || 'Unknown error', confirmButtonColor:'#059669' });
    }
  } catch(e) {
    Swal.fire({ icon:'error', title:'Error', text: e.message, confirmButtonColor:'#059669' });
  }
}

// Check-In
function openCheckInModal(visitorId) {
  const v = visitors.find(x => x.visitor_id == visitorId);
  if (!v) return;
  document.getElementById('checkin-visitor-id').value = v.visitor_id;
  document.getElementById('checkin-visitor-name').textContent = v.first_name + ' ' + v.last_name;
  document.getElementById('checkin-visitor-company').textContent = v.company || 'No company';
  document.getElementById('checkin-badge').value = '';
  document.getElementById('checkin-host-name').value = '';
  document.getElementById('checkin-details').value = '';
  openModal('modal-checkin');
}

async function submitCheckIn() {
  const visitorId = document.getElementById('checkin-visitor-id').value;
  const purpose = document.getElementById('checkin-purpose').value;
  if (!visitorId || !purpose) return Swal.fire({ icon:'warning', title:'Required', text:'Purpose is required.', confirmButtonColor:'#059669' });

  // Build purpose details with breakdown
  let purposeDetails = document.getElementById('checkin-details').value.trim() || '';
  if (purpose === 'partnership') {
    const org = document.getElementById('checkin-partner-org')?.value?.trim() || '';
    const ptype = document.getElementById('checkin-partner-type')?.value || '';
    if (org || ptype) purposeDetails = `[Partnership] Org: ${org} | Type: ${ptype}${purposeDetails ? ' | Notes: ' + purposeDetails : ''}`;
  } else if (purpose === 'educational') {
    const inst = document.getElementById('checkin-institution')?.value?.trim() || '';
    const etype = document.getElementById('checkin-edu-type')?.value || '';
    if (inst || etype) purposeDetails = `[Educational] Institution: ${inst} | Type: ${etype}${purposeDetails ? ' | Notes: ' + purposeDetails : ''}`;
  }

  const data = {
    visitor_id: parseInt(visitorId),
    purpose: purpose,
    badge_number: document.getElementById('checkin-badge').value.trim() || null,
    host_name: document.getElementById('checkin-host-name').value.trim() || null,
    host_department: document.getElementById('checkin-host-dept').value,
    purpose_details: purposeDetails || null
  };

  try {
    const res = await fetch(API + '?action=check_in', {
      method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data)
    });
    const result = await res.json();
    if (result.success) {
      closeModal('modal-checkin');
      Swal.fire({ icon:'success', title:'Checked In!', html:`Visit code: <strong>${result.visit_code}</strong>`, timer:2000, showConfirmButton:false, toast:true, position:'top-end' });
      await loadData();
    } else {
      Swal.fire({ icon:'error', title:'Check-In Failed', text: result.error || 'Unknown error', confirmButtonColor:'#059669' });
    }
  } catch(e) {
    Swal.fire({ icon:'error', title:'Error', text: e.message, confirmButtonColor:'#059669' });
  }
}

// Check-Out
async function checkOutByLogId(logId) {
  const confirm = await Swal.fire({
    title: 'Check Out Visitor?', text: 'This will mark the visitor as checked out.',
    icon: 'question', showCancelButton: true,
    confirmButtonText: 'Yes, Check Out', confirmButtonColor: '#059669', cancelButtonColor: '#6B7280'
  });
  if (!confirm.isConfirmed) return;

  try {
    const res = await fetch(API + '?action=check_out', {
      method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ log_id: logId })
    });
    const result = await res.json();
    if (result.success) {
      Swal.fire({ icon:'success', title:'Checked Out!', timer:2000, showConfirmButton:false, toast:true, position:'top-end' });
      await loadData();
    }
  } catch(e) {
    Swal.fire({ icon:'error', title:'Error', text: e.message, confirmButtonColor:'#059669' });
  }
}

async function checkOutVisitor(visitorId) {
  const activeLog = logs.find(l => l.visitor_id == visitorId && l.status === 'checked_in');
  if (activeLog) {
    await checkOutByLogId(activeLog.log_id);
  }
}

// Register from Pre-Registration
async function registerFromPrereg(prereg) {
  // Parse prereg name into first/last
  const parts = (prereg.visitor_name || '').trim().split(/\s+/);
  const firstName = parts[0] || '';
  const lastName = parts.slice(1).join(' ') || firstName;

  const data = {
    first_name: firstName,
    last_name: lastName,
    email: prereg.visitor_email || null,
    phone: prereg.visitor_phone || null,
    company: prereg.visitor_company || null
  };

  try {
    const res = await fetch(API + '?action=register_visitor', {
      method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data)
    });
    const result = await res.json();
    if (result.success) {
      document.getElementById('scan-result').style.display = 'none';
      Swal.fire({ icon:'success', title:'Registered!', html:`<strong>${esc(prereg.visitor_name)}</strong> registered as <strong>${result.visitor_code}</strong>.<br>You can now check them in from the Registration tab.`, confirmButtonColor:'#059669' });
      await loadData();
    }
  } catch(e) {
    Swal.fire({ icon:'error', title:'Error', text: e.message, confirmButtonColor:'#059669' });
  }
}

// ═══════════════════════════════════════════════════════
// VIEW DETAILS
// ═══════════════════════════════════════════════════════

// Consolidated visit history — click visitor name in logs
function viewVisitorHistory(visitorId) {
  const v = visitors.find(x => x.visitor_id == visitorId);
  if (v) return viewVisitorDetail(v.visitor_id);
  // Fallback: show history from logs only
  const visitorLogs = logs.filter(l => l.visitor_id == visitorId);
  if (!visitorLogs.length) return;
  const name = visitorLogs[0].visitor_name || 'Visitor';
  document.getElementById('detail-title').textContent = name + ' — Visit History';
  document.getElementById('detail-body').innerHTML = `
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px">
      <div style="width:48px;height:48px;border-radius:12px;background:#D1FAE5;display:flex;align-items:center;justify-content:center">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
      </div>
      <div>
        <div style="font-weight:700;font-size:15px;color:#1F2937">${esc(name)}</div>
        <div style="font-size:12px;color:#6B7280">${esc(visitorLogs[0].company || 'No Company')} · ${visitorLogs.length} visit(s)</div>
      </div>
    </div>
    <div style="max-height:400px;overflow-y:auto">
      ${visitorLogs.map(l => `
        <div style="border:1px solid #E5E7EB;border-radius:10px;padding:12px 16px;margin-bottom:8px;font-size:12px">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
            <span style="font-weight:700;color:#1F2937">${esc(l.visit_code)}</span>
            ${statusBadge(l.status)}
          </div>
          <div style="color:#6B7280;margin-bottom:4px">
            <span style="font-weight:600">${labelCase(l.purpose)}</span>${l.host_department ? ' · ' + esc(l.host_department) : ''}${l.host_name ? ' · Host: ' + esc(l.host_name) : ''}
          </div>
          <div style="display:flex;gap:16px;margin-top:6px">
            <div style="flex:1;background:#F0FDF4;border-radius:6px;padding:6px 10px">
              <div style="font-size:10px;font-weight:700;color:#059669;text-transform:uppercase;letter-spacing:0.5px">Check In</div>
              <div style="font-weight:600;color:#1F2937;margin-top:2px">${fmtDateTime(l.check_in_time)}</div>
            </div>
            <div style="flex:1;background:${l.check_out_time ? '#FEF2F2' : '#F9FAFB'};border-radius:6px;padding:6px 10px">
              <div style="font-size:10px;font-weight:700;color:${l.check_out_time ? '#DC2626' : '#9CA3AF'};text-transform:uppercase;letter-spacing:0.5px">Check Out</div>
              <div style="font-weight:600;color:#1F2937;margin-top:2px">${l.check_out_time ? fmtDateTime(l.check_out_time) : '<span style="color:#9CA3AF;font-style:italic">Still inside</span>'}</div>
            </div>
          </div>
          <div style="margin-top:6px;text-align:right;font-size:11px;color:#6B7280">
            Duration: <span style="font-weight:600;color:#1F2937">${duration(l.check_in_time, l.check_out_time)}</span>
          </div>
        </div>
      `).join('')}
    </div>`;
  openModal('modal-visitor-detail');
}

function viewVisitorDetail(visitorId) {
  const v = visitors.find(x => x.visitor_id == visitorId);
  if (!v) return;
  const name = v.first_name + ' ' + v.last_name;
  const visitorLogs = logs.filter(l => l.visitor_id == visitorId);

  document.getElementById('detail-title').textContent = name;
  document.getElementById('detail-body').innerHTML = `
    <div style="text-align:center;margin-bottom:20px">
      <img src="${qrUrl(v.visitor_code, 140)}" style="width:140px;height:140px;border-radius:8px;margin-bottom:8px">
      <div style="font-weight:700;color:#059669;font-size:16px;letter-spacing:1px">${esc(v.visitor_code)}</div>
    </div>
    <table style="width:100%;font-size:13px;border-collapse:collapse">
      <tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280;width:130px">Name</td><td style="padding:8px 0">${esc(name)}</td></tr>
      <tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280">Company</td><td style="padding:8px 0">${esc(v.company) || '—'}</td></tr>
      <tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280">Email</td><td style="padding:8px 0">${esc(v.email) || '—'}</td></tr>
      <tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280">Phone</td><td style="padding:8px 0">${esc(v.phone) || '—'}</td></tr>
      <tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280">ID Type</td><td style="padding:8px 0">${v.id_type ? labelCase(v.id_type) : '—'}</td></tr>
      <tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280">ID Number</td><td style="padding:8px 0">${esc(v.id_number) || '—'}</td></tr>
      <tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280">Total Visits</td><td style="padding:8px 0"><span class="badge badge-blue">${v.visit_count || 0}</span></td></tr>
      <tr><td style="padding:8px 0;font-weight:600;color:#6B7280">Registered</td><td style="padding:8px 0">${fmtDate(v.created_at)}</td></tr>
    </table>
    ${visitorLogs.length ? `
      <div style="margin-top:16px;font-weight:700;font-size:13px;color:#1F2937;margin-bottom:8px">Visit History (${visitorLogs.length})</div>
      <div style="max-height:280px;overflow-y:auto">
        ${visitorLogs.map(l => `
          <div style="border:1px solid #E5E7EB;border-radius:10px;padding:12px 16px;margin-bottom:8px;font-size:12px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
              <span style="font-weight:700;color:#1F2937">${esc(l.visit_code)}</span>
              ${statusBadge(l.status)}
            </div>
            <div style="color:#6B7280;margin-bottom:4px">
              <span style="font-weight:600">${labelCase(l.purpose)}</span>${l.host_department ? ' · ' + esc(l.host_department) : ''}${l.host_name ? ' · Host: ' + esc(l.host_name) : ''}
            </div>
            <div style="display:flex;gap:16px;margin-top:6px">
              <div style="flex:1;background:#F0FDF4;border-radius:6px;padding:6px 10px">
                <div style="font-size:10px;font-weight:700;color:#059669;text-transform:uppercase;letter-spacing:0.5px">Check In</div>
                <div style="font-weight:600;color:#1F2937;margin-top:2px">${fmtDateTime(l.check_in_time)}</div>
              </div>
              <div style="flex:1;background:${l.check_out_time ? '#FEF2F2' : '#F9FAFB'};border-radius:6px;padding:6px 10px">
                <div style="font-size:10px;font-weight:700;color:${l.check_out_time ? '#DC2626' : '#9CA3AF'};text-transform:uppercase;letter-spacing:0.5px">Check Out</div>
                <div style="font-weight:600;color:#1F2937;margin-top:2px">${l.check_out_time ? fmtDateTime(l.check_out_time) : '<span style="color:#9CA3AF;font-style:italic">Still inside</span>'}</div>
              </div>
            </div>
            <div style="margin-top:6px;text-align:right;font-size:11px;color:#6B7280">
              Duration: <span style="font-weight:600;color:#1F2937">${duration(l.check_in_time, l.check_out_time)}</span>
            </div>
          </div>
        `).join('')}
      </div>
    ` : ''}`;
  openModal('modal-visitor-detail');
}

function viewPreregDetail(code) {
  const p = preregs.find(x => x.prereg_code === code);
  if (!p) return;
  document.getElementById('detail-title').textContent = 'Pre-Registration: ' + p.prereg_code;
  document.getElementById('detail-body').innerHTML = `
    <table style="width:100%;font-size:13px;border-collapse:collapse">
      <tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280;width:130px">Code</td><td style="padding:8px 0;font-weight:600;color:#059669">${esc(p.prereg_code)}</td></tr>
      <tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280">Visitor</td><td style="padding:8px 0;font-weight:600">${esc(p.visitor_name)}</td></tr>
      <tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280">Company</td><td style="padding:8px 0">${esc(p.visitor_company) || '—'}</td></tr>
      <tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280">Email</td><td style="padding:8px 0">${esc(p.visitor_email) || '—'}</td></tr>
      <tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280">Phone</td><td style="padding:8px 0">${esc(p.visitor_phone) || '—'}</td></tr>
      <tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280">Purpose</td><td style="padding:8px 0">${labelCase(p.purpose)}</td></tr>
      <tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280">Expected Date</td><td style="padding:8px 0">${fmtDate(p.expected_date)}${p.expected_time ? ' at ' + p.expected_time : ''}</td></tr>
      <tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280">Host</td><td style="padding:8px 0">${esc(p.host_name) || '—'}</td></tr>
      <tr><td style="padding:8px 0;font-weight:600;color:#6B7280">Status</td><td style="padding:8px 0">${statusBadge(p.status)}</td></tr>
    </table>`;
  openModal('modal-visitor-detail');
}

function viewLogDetail(logId) {
  const l = logs.find(x => x.log_id == logId);
  if (!l) return;
  document.getElementById('detail-title').textContent = 'Visit: ' + l.visit_code;
  document.getElementById('detail-body').innerHTML = `
    <table style="width:100%;font-size:13px;border-collapse:collapse">
      <tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280;width:130px">Visit Code</td><td style="padding:8px 0;font-weight:600;color:#059669">${esc(l.visit_code)}</td></tr>
      <tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280">Visitor</td><td style="padding:8px 0;font-weight:600">${esc(l.visitor_name)}</td></tr>
      <tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280">Company</td><td style="padding:8px 0">${esc(l.company) || '—'}</td></tr>
      <tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280">Purpose</td><td style="padding:8px 0">${labelCase(l.purpose)}</td></tr>
      <tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280">Host</td><td style="padding:8px 0">${esc(l.host_name || '—')}${l.host_department ? '<br><span style="color:#9CA3AF">' + esc(l.host_department) + '</span>' : ''}</td></tr>
      <tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280">Badge</td><td style="padding:8px 0">${esc(l.badge_number) || '—'}</td></tr>
      <tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280">Check-In</td><td style="padding:8px 0">${fmtDateTime(l.check_in_time)}</td></tr>
      <tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280">Check-Out</td><td style="padding:8px 0">${l.check_out_time ? fmtDateTime(l.check_out_time) : '—'}</td></tr>
      <tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280">Duration</td><td style="padding:8px 0">${duration(l.check_in_time, l.check_out_time)}</td></tr>
      <tr><td style="padding:8px 0;font-weight:600;color:#6B7280">Status</td><td style="padding:8px 0">${statusBadge(l.status)}</td></tr>
    </table>`;
  openModal('modal-visitor-detail');
}

// ═══════════════════════════════════════════════════════
// ANALYTICS
// ═══════════════════════════════════════════════════════

function renderAnalytics() {
  // Analytics stat cards
  document.getElementById('stat-month').textContent = stats.total_visits_month ?? 0;
  const avgMin = parseInt(stats.avg_duration_min) || 0;
  document.getElementById('stat-avg-dur').textContent = avgMin > 60 ? Math.floor(avgMin/60) + 'h ' + (avgMin%60) + 'm' : avgMin + 'm';
  document.getElementById('stat-top-org').textContent = stats.top_company || 'N/A';
  document.getElementById('stat-upcoming').textContent = stats.pending_preregs ?? 0;

  // Top visitors from visitor data
  const topList = document.getElementById('top-visitors-list');
  const sorted = [...visitors].sort((a,b) => (b.visit_count||0) - (a.visit_count||0)).slice(0, 5);
  if (sorted.length === 0) {
    topList.innerHTML = '<div class="empty-state" style="padding:20px"><div style="font-size:36px;margin-bottom:8px">👤</div><div style="font-weight:600">No visitor data</div></div>';
  } else {
    const badgeColors = ['badge-green','badge-blue','badge-amber','badge-purple','badge-gray'];
    topList.innerHTML = sorted.map((v, i) => `
      <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px;background:#F9FAFB;border-radius:10px">
        <div>
          <span style="font-weight:600;font-size:13px;color:#1F2937">${esc(v.first_name + ' ' + v.last_name)}</span>
          <br><span style="font-size:11px;color:#9CA3AF">${esc(v.company || 'No Company')}</span>
        </div>
        <span class="badge ${badgeColors[i] || 'badge-gray'}">${v.visit_count || 0} visits</span>
      </div>
    `).join('');
  }

  // Purpose chart from logs data
  const purposeCounts = {};
  logs.forEach(l => {
    const p = labelCase(l.purpose);
    purposeCounts[p] = (purposeCounts[p] || 0) + 1;
  });
  const labels = Object.keys(purposeCounts);
  const data = Object.values(purposeCounts);
  const colors = ['#8B5CF6','#059669','#3B82F6','#F59E0B','#14B8A6','#DC2626','#6B7280'];

  if (purposeChart) purposeChart.destroy();
  const canvas = document.getElementById('chartVisitorPurpose');
  if (canvas && labels.length > 0) {
    purposeChart = new Chart(canvas, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{ label: 'Visits', data: data, backgroundColor: labels.map((_, i) => colors[i % colors.length]), borderRadius: 6, barPercentage: 0.65 }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: { backgroundColor: '#1F2937', cornerRadius: 8, padding: 10 } },
        scales: { x: { grid: { display: false }, border: { display: false } }, y: { beginAtZero: true, grid: { color: '#F3F4F6' }, border: { display: false }, ticks: { stepSize: 1 } } }
      }
    });
  }

  // Upcoming pre-regs in analytics
  const tbody = document.getElementById('analytics-preregs-tbody');
  const upcoming = preregs.filter(p => p.status === 'pending' || p.status === 'approved');
  if (!upcoming.length) {
    tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state" style="padding:20px"><div style="font-weight:600">No upcoming pre-registrations</div></div></td></tr>';
  } else {
    tbody.innerHTML = upcoming.map(p => `<tr>
      <td style="font-weight:600;font-size:12px;color:#059669">${esc(p.prereg_code)}</td>
      <td style="font-weight:600">${esc(p.visitor_name)}</td>
      <td>${esc(p.visitor_company) || '—'}</td>
      <td>${labelCase(p.purpose)}</td>
      <td style="font-size:12px">${fmtDate(p.expected_date)}${p.expected_time ? ' at ' + p.expected_time : ''}</td>
      <td>${esc(p.host_name) || '—'}</td>
      <td>${statusBadge(p.status)}</td>
    </tr>`).join('');
  }
}

// ───── Approve Pre-Registration ─────
async function approvePrereg(code) {
  const result = await Swal.fire({
    title: 'Approve Pre-Registration?',
    text: `Approve ${code}? The visitor will be added to Registered Visitors.`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#059669',
    confirmButtonText: 'Yes, Approve',
    cancelButtonText: 'Cancel'
  });
  if (!result.isConfirmed) return;
  try {
    const res = await fetch(API + '?action=approve_prereg', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ prereg_code: code })
    });
    const data = await res.json();
    if (data.success) {
      Swal.fire({ icon:'success', title:'Approved!', text: data.message || 'Pre-registration approved and visitor registered.', timer:2000, showConfirmButton:false });
      await loadData();
    } else {
      Swal.fire({ icon:'error', title:'Error', text: data.error || 'Failed to approve.' });
    }
  } catch (e) {
    Swal.fire({ icon:'error', title:'Error', text: 'Network error.' });
  }
}

// ───── Reject Pre-Registration ─────
async function rejectPrereg(code) {
  const result = await Swal.fire({
    title: 'Reject Pre-Registration?',
    text: `Reject ${code}? This action cannot be undone.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#DC2626',
    confirmButtonText: 'Yes, Reject',
    cancelButtonText: 'Cancel'
  });
  if (!result.isConfirmed) return;
  try {
    const res = await fetch(API + '?action=reject_prereg', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ prereg_code: code })
    });
    const data = await res.json();
    if (data.success) {
      Swal.fire({ icon:'success', title:'Rejected', text:'Pre-registration rejected.', timer:2000, showConfirmButton:false });
      await loadData();
    } else {
      Swal.fire({ icon:'error', title:'Error', text: data.error || 'Failed to reject.' });
    }
  } catch (e) {
    Swal.fire({ icon:'error', title:'Error', text: 'Network error.' });
  }
}

// ───── Section Switching (hash-driven) ─────
function showSection(hash) {
  const sections = document.querySelectorAll('.tab-content');
  const id = hash ? hash.replace('#', '') : 'tab-registration';
  sections.forEach(s => s.classList.remove('active'));
  const target = document.getElementById(id);
  if (target) target.classList.add('active');
  else if (sections[0]) sections[0].classList.add('active');
  if (id === 'tab-analytics') renderAnalytics();
}
window.addEventListener('hashchange', () => showSection(location.hash));

// ───── Init ─────
(async () => {
  await loadData();
  showSection(location.hash);
})();

// ───── Export Functions ─────
function exportVisitors(format) {
  const headers = ['Code', 'Name', 'Company', 'Contact', 'ID Type', 'ID Number', 'Total Visits'];
  const rows = visitors.map(v => [
    v.visitor_code || '', v.name || (v.first_name || '') + ' ' + (v.last_name || ''),
    v.company || '', v.contact_number || v.phone || '', v.id_type || '',
    v.id_number || '', v.visit_count || v.total_visits || 0
  ]);
  format === 'csv' ? ExportHelper.exportCSV('Visitors_Registered', headers, rows)
    : ExportHelper.exportPDF('Visitors_Registered', 'Visitor Management — Registered Visitors', headers, rows, { subtitle: visitors.length + ' visitors' });
}

function exportPreregs(format) {
  const headers = ['Code', 'Visitor Name', 'Company', 'Purpose', 'Expected Date', 'Host', 'Status'];
  const rows = preregs.map(p => [
    p.prereg_code || p.code || '', p.visitor_name || p.name || '',
    p.company || '', p.purpose || '', p.expected_date || p.visit_date || '',
    p.host_name || p.host || '', p.status || ''
  ]);
  format === 'csv' ? ExportHelper.exportCSV('Visitors_PreRegistrations', headers, rows)
    : ExportHelper.exportPDF('Visitors_PreRegistrations', 'Visitor Management — Pre-Registrations', headers, rows, { subtitle: preregs.length + ' records' });
}

function exportLogs(format) {
  const headers = ['Visit Code', 'Visitor', 'Company', 'Purpose', 'Check-In', 'Check-Out', 'Duration', 'Host', 'Status'];
  const rows = logs.map(l => {
    let duration = '';
    if (l.check_in_time && l.check_out_time) {
      const diff = Math.abs(new Date(l.check_out_time) - new Date(l.check_in_time));
      const hrs = Math.floor(diff / 3600000);
      const mins = Math.floor((diff % 3600000) / 60000);
      duration = hrs > 0 ? hrs + 'h ' + mins + 'm' : mins + 'm';
    }
    return [
      l.log_code || l.visit_code || '', l.visitor_name || '',
      l.company || '', l.purpose || '', l.check_in_time || '',
      l.check_out_time || '', duration, l.host_name || l.host || '', l.status || ''
    ];
  });
  format === 'csv' ? ExportHelper.exportCSV('Visitors_Logs', headers, rows)
    : ExportHelper.exportPDF('Visitors_Logs', 'Visitor Management — Visitor Logs', headers, rows, { landscape: true, subtitle: logs.length + ' log entries' });
}
</script>
</body>
</html>
