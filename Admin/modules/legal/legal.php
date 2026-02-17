<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Legal Management — Microfinancial Admin</title>

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
  <style>
    .secure-text { letter-spacing: 2px; font-family: monospace; }
    .doc-secured .doc-title-text { filter: blur(5px); user-select: none; transition: filter 0.3s; }
    .doc-secured.revealed .doc-title-text { filter: none; user-select: auto; }
    .eye-toggle { cursor: pointer; opacity: 0.5; transition: opacity 0.2s; }
    .eye-toggle:hover { opacity: 1; }
    .contract-paper { background: #FFFEF7; border: 1px solid #E5E7EB; border-radius: 12px; padding: 32px; font-family: 'Georgia', serif; line-height: 1.8; white-space: pre-wrap; max-height: 500px; overflow-y: auto; }
    .contract-paper .signature-block { margin-top: 24px; padding-top: 16px; border-top: 1px dashed #D1D5DB; }
    .attorney-seal { display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(135deg, #F0FDF4, #D1FAE5); border: 2px solid #059669; border-radius: 10px; padding: 8px 16px; font-size: 12px; font-weight: 600; color: #065F46; }
  </style>
</head>

<body class="bg-brand-background-main min-h-screen font-[Inter,'Segoe_UI',system-ui,-apple-system,sans-serif]">

  <?php $activePage = 'legal'; $baseUrl = '../../'; include '../../sidebar.php'; ?>

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
        <h1 class="page-title">Legal Management</h1>
        <p class="page-subtitle">Loan documentation, collateral management, litigation &amp; recovery, regulatory compliance, and corporate governance</p>
      </div>

      <!-- STAT CARDS -->
      <div class="stats-grid animate-in delay-1">
        <div class="stat-card"><div class="stat-icon green">📄</div><div class="stat-info"><div class="stat-value" id="stat-loans">0</div><div class="stat-label">Loan Contracts</div></div></div>
        <div class="stat-card"><div class="stat-icon blue">🏠</div><div class="stat-info"><div class="stat-value" id="stat-collaterals">0</div><div class="stat-label">Collaterals</div></div></div>
        <div class="stat-card"><div class="stat-icon amber">⚖️</div><div class="stat-info"><div class="stat-value" id="stat-cases">0</div><div class="stat-label">Active Cases</div></div></div>
        <div class="stat-card"><div class="stat-icon purple">✅</div><div class="stat-info"><div class="stat-value" id="stat-compliance">0</div><div class="stat-label">Compliance Items</div></div></div>
        <div class="stat-card"><div class="stat-icon red">📋</div><div class="stat-info"><div class="stat-value" id="stat-resolutions">0</div><div class="stat-label">Board Resolutions</div></div></div>
      </div>



      <!-- ==================== TAB 1: LOAN DOCS ==================== -->
      <div id="tab-loans" class="tab-content active animate-in delay-3">
        <div class="card">
          <div class="card-header">
            <span class="card-title">Loan Documentation</span>
            <div style="display:flex;gap:8px">
              <button class="btn-export btn-export-pdf btn-export-sm" onclick="exportLoans('pdf')">📄 PDF</button>
              <button class="btn-export btn-export-csv btn-export-sm" onclick="exportLoans('csv')">📊 CSV</button>
            </div>
          </div>
          <div class="card-body">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Code</th>
                  <th>Borrower</th>
                  <th>Amount</th>
                  <th>Interest</th>
                  <th>Term</th>
                  <th>Security Type</th>
                  <th>Status</th>
                  <th>Attorney</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="loans-tbody">
                <tr><td colspan="9" class="text-center text-gray-400 py-8">Loading...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ==================== TAB 2: COLLATERAL ==================== -->
      <div id="tab-collateral" class="tab-content">
        <div class="card">
          <div class="card-header">
            <span class="card-title">Collateral Registry</span>
            <div style="display:flex;gap:8px">
              <button class="btn-export btn-export-pdf btn-export-sm" onclick="exportCollaterals('pdf')">📄 PDF</button>
              <button class="btn-export btn-export-csv btn-export-sm" onclick="exportCollaterals('csv')">📊 CSV</button>
            </div>
          </div>
          <div class="card-body">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Code</th>
                  <th>Borrower</th>
                  <th>Type</th>
                  <th>Description</th>
                  <th>Appraised Value</th>
                  <th>Lien Status</th>
                  <th>Insurance Expiry</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="collaterals-tbody">
                <tr><td colspan="8" class="text-center text-gray-400 py-8">Loading...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ==================== TAB 3: LITIGATION ==================== -->
      <div id="tab-cases" class="tab-content">
        <!-- Demand Letters -->
        <div class="card" style="margin-bottom:20px">
          <div class="card-header">
            <span class="card-title">📨 Demand Letters</span>
            <div style="display:flex;gap:8px">
              <button class="btn-export btn-export-pdf btn-export-sm" onclick="exportDemands('pdf')">📄 PDF</button>
              <button class="btn-export btn-export-csv btn-export-sm" onclick="exportDemands('csv')">📊 CSV</button>
            </div>
          </div>
          <div class="card-body">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Code</th>
                  <th>Borrower</th>
                  <th>Amount</th>
                  <th>Type</th>
                  <th>Status</th>
                  <th>Sent Date</th>
                  <th>Response Deadline</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="demands-tbody">
                <tr><td colspan="8" class="text-center text-gray-400 py-8">Loading...</td></tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Legal Cases -->
        <div class="card">
          <div class="card-header">
            <span class="card-title">⚖️ Legal Cases</span>
            <div style="display:flex;gap:8px">
              <button class="btn-export btn-export-pdf btn-export-sm" onclick="exportCases('pdf')">📄 PDF</button>
              <button class="btn-export btn-export-csv btn-export-sm" onclick="exportCases('csv')">📊 CSV</button>
            </div>
          </div>
          <div class="card-body">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Case #</th>
                  <th>Title</th>
                  <th>Type</th>
                  <th>Priority</th>
                  <th>Status</th>
                  <th>Opposing Party</th>
                  <th>Financial Impact</th>
                  <th>Assigned</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="cases-tbody">
                <tr><td colspan="9" class="text-center text-gray-400 py-8">Loading...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ==================== TAB 4: COMPLIANCE ==================== -->
      <div id="tab-compliance" class="tab-content">
        <!-- KYC Records -->
        <div class="card" style="margin-bottom:20px">
          <div class="card-header">
            <span class="card-title">🔍 KYC Records</span>
            <div style="display:flex;gap:8px">
              <button class="btn-export btn-export-pdf btn-export-sm" onclick="exportKYC('pdf')">📄 PDF</button>
              <button class="btn-export btn-export-csv btn-export-sm" onclick="exportKYC('csv')">📊 CSV</button>
            </div>
          </div>
          <div class="card-body">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Code</th>
                  <th>Client</th>
                  <th>Type</th>
                  <th>ID Type</th>
                  <th>Risk Rating</th>
                  <th>Verification Status</th>
                  <th>Next Review</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="kyc-tbody">
                <tr><td colspan="8" class="text-center text-gray-400 py-8">Loading...</td></tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Regulatory Compliance -->
        <div class="card">
          <div class="card-header">
            <span class="card-title">📑 Regulatory Compliance</span>
            <div style="display:flex;gap:8px">
              <button class="btn-export btn-export-pdf btn-export-sm" onclick="exportCompliance('pdf')">📄 PDF</button>
              <button class="btn-export btn-export-csv btn-export-sm" onclick="exportCompliance('csv')">📊 CSV</button>
            </div>
          </div>
          <div class="card-body">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Code</th>
                  <th>Requirement</th>
                  <th>Body</th>
                  <th>Status</th>
                  <th>Deadline</th>
                  <th>Assigned</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="compliance-tbody">
                <tr><td colspan="7" class="text-center text-gray-400 py-8">Loading...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ==================== TAB 5: GOVERNANCE ==================== -->
      <div id="tab-governance" class="tab-content">
        <!-- Board Resolutions -->
        <div class="card" style="margin-bottom:20px">
          <div class="card-header">
            <span class="card-title">🏛️ Board Resolutions</span>
            <div style="display:flex;gap:8px">
              <button class="btn-export btn-export-pdf btn-export-sm" onclick="exportResolutions('pdf')">📄 PDF</button>
              <button class="btn-export btn-export-csv btn-export-sm" onclick="exportResolutions('csv')">📊 CSV</button>
            </div>
          </div>
          <div class="card-body">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Code</th>
                  <th>Title</th>
                  <th>Type</th>
                  <th>Meeting Date</th>
                  <th>Meeting Type</th>
                  <th>Votes (F/A/Ab)</th>
                  <th>Passed</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="resolutions-tbody">
                <tr><td colspan="9" class="text-center text-gray-400 py-8">Loading...</td></tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Power of Attorney -->
        <div class="card">
          <div class="card-header">
            <span class="card-title">📜 Power of Attorney</span>
            <div style="display:flex;gap:8px">
              <button class="btn-export btn-export-pdf btn-export-sm" onclick="exportPOA('pdf')">📄 PDF</button>
              <button class="btn-export btn-export-csv btn-export-sm" onclick="exportPOA('csv')">📊 CSV</button>
            </div>
          </div>
          <div class="card-body">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Code</th>
                  <th>Principal</th>
                  <th>Agent</th>
                  <th>Type</th>
                  <th>Scope</th>
                  <th>Effective Date</th>
                  <th>Expiry</th>
                  <th>Notarized</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="poa-tbody">
                <tr><td colspan="10" class="text-center text-gray-400 py-8">Loading...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ==================== TAB 6: CONTRACTS ==================== -->
      <div id="tab-contracts" class="tab-content">
        <!-- Contract Folder Cards -->
        <div id="contract-folders-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;margin-bottom:20px">
          <div class="card" style="cursor:pointer;border:2px solid transparent;transition:all 0.2s" onmouseover="this.style.borderColor='#059669';this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='transparent';this.style.transform=''" onclick="showContractFolder('employee')">
            <div class="card-body" style="padding:20px">
              <div style="display:flex;align-items:center;gap:14px">
                <div style="width:52px;height:52px;border-radius:14px;background:#D1FAE5;display:flex;align-items:center;justify-content:center;font-size:26px">👤</div>
                <div>
                  <div style="font-size:15px;font-weight:800;color:#1F2937">Employee Contracts</div>
                  <div style="font-size:12px;color:#6B7280;margin-top:2px">Employment agreements &amp; NDAs</div>
                  <div style="font-size:12px;font-weight:600;color:#059669;margin-top:4px" id="contract-count-employee">0 contracts</div>
                </div>
              </div>
            </div>
          </div>
          <div class="card" style="cursor:pointer;border:2px solid transparent;transition:all 0.2s" onmouseover="this.style.borderColor='#3B82F6';this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='transparent';this.style.transform=''" onclick="showContractFolder('vendor')">
            <div class="card-body" style="padding:20px">
              <div style="display:flex;align-items:center;gap:14px">
                <div style="width:52px;height:52px;border-radius:14px;background:#DBEAFE;display:flex;align-items:center;justify-content:center;font-size:26px">🏢</div>
                <div>
                  <div style="font-size:15px;font-weight:800;color:#1F2937">Vendor Contracts</div>
                  <div style="font-size:12px;color:#6B7280;margin-top:2px">Vendor, service &amp; lease agreements</div>
                  <div style="font-size:12px;font-weight:600;color:#3B82F6;margin-top:4px" id="contract-count-vendor">0 contracts</div>
                </div>
              </div>
            </div>
          </div>
          <div class="card" style="cursor:pointer;border:2px solid transparent;transition:all 0.2s" onmouseover="this.style.borderColor='#8B5CF6';this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='transparent';this.style.transform=''" onclick="showContractFolder('partnership')">
            <div class="card-body" style="padding:20px">
              <div style="display:flex;align-items:center;gap:14px">
                <div style="width:52px;height:52px;border-radius:14px;background:#EDE9FE;display:flex;align-items:center;justify-content:center;font-size:26px">🤝</div>
                <div>
                  <div style="font-size:15px;font-weight:800;color:#1F2937">Partnership Contracts</div>
                  <div style="font-size:12px;color:#6B7280;margin-top:2px">Partnership &amp; joint venture agreements</div>
                  <div style="font-size:12px;font-weight:600;color:#8B5CF6;margin-top:4px" id="contract-count-partnership">0 contracts</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Contract Folder Detail Panel -->
        <div id="contract-folder-panel" class="card" style="display:none">
          <div class="card-header">
            <div style="display:flex;align-items:center;gap:10px">
              <button class="btn btn-outline btn-sm" onclick="closeContractFolder()">← Back</button>
              <span class="card-title" id="contract-folder-title">Contracts</span>
            </div>
            <div style="display:flex;gap:8px">
              <button class="btn-export btn-export-pdf btn-export-sm" onclick="exportContracts('pdf')">📄 PDF</button>
              <button class="btn-export btn-export-csv btn-export-sm" onclick="exportContracts('csv')">📊 CSV</button>
            </div>
          </div>
          <div class="card-body">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Contract #</th>
                  <th>Title</th>
                  <th>Type</th>
                  <th>Party</th>
                  <th>Value</th>
                  <th>Start</th>
                  <th>End</th>
                  <th>Status</th>
                  <th>Assigned</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="contracts-tbody">
                <tr><td colspan="10" class="text-center text-gray-400 py-8">Select a folder above</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ==================== TAB 7: PERMITS ==================== -->
      <div id="tab-permits" class="tab-content">
        <div class="card">
          <div class="card-header">
            <span class="card-title">Permits &amp; Licenses</span>
            <div style="display:flex;gap:8px">
              <button class="btn-export btn-export-pdf btn-export-sm" onclick="exportPermits('pdf')">📄 PDF</button>
              <button class="btn-export btn-export-csv btn-export-sm" onclick="exportPermits('csv')">📊 CSV</button>
            </div>
          </div>
          <div class="card-body">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Code</th>
                  <th>Permit Name</th>
                  <th>Issuing Body</th>
                  <th>Type</th>
                  <th>Permit #</th>
                  <th>Issue Date</th>
                  <th>Expiry</th>
                  <th>Fee</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="permits-tbody">
                <tr><td colspan="10" class="text-center text-gray-400 py-8">Loading...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ==================== TAB 8: LEGAL CALENDAR ==================== -->
      <div id="tab-legal-calendar" class="tab-content">
        <div class="card">
          <div class="card-header">
            <div style="display:flex;align-items:center;gap:12px">
              <button class="btn btn-outline btn-sm" onclick="legalCalNav(-1)">‹</button>
              <span class="card-title" id="legal-cal-title" style="min-width:160px;text-align:center">—</span>
              <button class="btn btn-outline btn-sm" onclick="legalCalNav(1)">›</button>
              <button class="btn btn-outline btn-sm" onclick="legalCalToday()" style="font-size:11px">Today</button>
            </div>
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
              <span style="font-size:11px;display:flex;align-items:center;gap:4px"><span style="width:10px;height:10px;border-radius:50%;background:#EF4444;display:inline-block"></span>Cases</span>
              <span style="font-size:11px;display:flex;align-items:center;gap:4px"><span style="width:10px;height:10px;border-radius:50%;background:#3B82F6;display:inline-block"></span>Contracts</span>
              <span style="font-size:11px;display:flex;align-items:center;gap:4px"><span style="width:10px;height:10px;border-radius:50%;background:#059669;display:inline-block"></span>Compliance</span>
              <span style="font-size:11px;display:flex;align-items:center;gap:4px"><span style="width:10px;height:10px;border-radius:50%;background:#F59E0B;display:inline-block"></span>Permits</span>
            </div>
          </div>
          <div class="card-body" style="padding:10px">
            <div id="legal-calendar-grid" style="display:grid;grid-template-columns:repeat(7,1fr);gap:1px;background:#E5E7EB;border-radius:8px;overflow:hidden">
            </div>
          </div>
        </div>

        <!-- Day Detail Panel -->
        <div id="legal-day-panel" class="card" style="display:none;margin-top:16px">
          <div class="card-header">
            <span class="card-title" id="legal-day-title">—</span>
            <button class="btn btn-outline btn-sm" onclick="document.getElementById('legal-day-panel').style.display='none'">Close</button>
          </div>
          <div class="card-body" id="legal-day-body" style="padding:16px">
          </div>
        </div>
      </div>

      <!-- ==================== MODALS ==================== -->

      <!-- Modal: View Loan Contract -->
      <div id="modal-loan-contract" class="modal-overlay" onclick="if(event.target===this)closeModal('modal-loan-contract')">
        <div class="modal" style="max-width:800px">
          <div class="modal-header">
            <span class="modal-title">📄 Loan Contract</span>
            <button class="modal-close" onclick="closeModal('modal-loan-contract')">&times;</button>
          </div>
          <div class="modal-body" id="loan-contract-body"></div>
          <div class="modal-footer">
            <button class="btn btn-primary" id="btn-download-loan-pdf" style="display:none" onclick="exportLoanContractPDF()">📥 Download PDF</button>
            <button class="btn btn-outline" onclick="closeModal('modal-loan-contract')">Close</button>
          </div>
        </div>
      </div>

      <!-- Modal: View Collateral Details -->
      <div id="modal-collateral-detail" class="modal-overlay" onclick="if(event.target===this)closeModal('modal-collateral-detail')">
        <div class="modal" style="max-width:700px">
          <div class="modal-header">
            <span class="modal-title">🏠 Collateral Details</span>
            <button class="modal-close" onclick="closeModal('modal-collateral-detail')">&times;</button>
          </div>
          <div class="modal-body" id="collateral-detail-body"></div>
          <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modal-collateral-detail')">Close</button>
          </div>
        </div>
      </div>

      <!-- Modal: View Demand Letter -->
      <div id="modal-demand-detail" class="modal-overlay" onclick="if(event.target===this)closeModal('modal-demand-detail')">
        <div class="modal" style="max-width:750px">
          <div class="modal-header">
            <span class="modal-title">📨 Demand Letter</span>
            <button class="modal-close" onclick="closeModal('modal-demand-detail')">&times;</button>
          </div>
          <div class="modal-body" id="demand-detail-body"></div>
          <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modal-demand-detail')">Close</button>
          </div>
        </div>
      </div>

      <!-- Modal: View Legal Case -->
      <div id="modal-case-detail" class="modal-overlay" onclick="if(event.target===this)closeModal('modal-case-detail')">
        <div class="modal" style="max-width:750px">
          <div class="modal-header">
            <span class="modal-title">⚖️ Legal Case Details</span>
            <button class="modal-close" onclick="closeModal('modal-case-detail')">&times;</button>
          </div>
          <div class="modal-body" id="case-detail-body"></div>
          <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modal-case-detail')">Close</button>
          </div>
        </div>
      </div>

      <!-- Modal: View KYC -->
      <div id="modal-kyc-detail" class="modal-overlay" onclick="if(event.target===this)closeModal('modal-kyc-detail')">
        <div class="modal" style="max-width:700px">
          <div class="modal-header">
            <span class="modal-title">🔍 KYC Record Details</span>
            <button class="modal-close" onclick="closeModal('modal-kyc-detail')">&times;</button>
          </div>
          <div class="modal-body" id="kyc-detail-body"></div>
          <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modal-kyc-detail')">Close</button>
          </div>
        </div>
      </div>

      <!-- Modal: View Compliance -->
      <div id="modal-compliance-detail" class="modal-overlay" onclick="if(event.target===this)closeModal('modal-compliance-detail')">
        <div class="modal" style="max-width:700px">
          <div class="modal-header">
            <span class="modal-title">📑 Compliance Details</span>
            <button class="modal-close" onclick="closeModal('modal-compliance-detail')">&times;</button>
          </div>
          <div class="modal-body" id="compliance-detail-body"></div>
          <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modal-compliance-detail')">Close</button>
          </div>
        </div>
      </div>

      <!-- Modal: View Resolution -->
      <div id="modal-resolution-detail" class="modal-overlay" onclick="if(event.target===this)closeModal('modal-resolution-detail')">
        <div class="modal" style="max-width:800px">
          <div class="modal-header">
            <span class="modal-title">🏛️ Board Resolution Details</span>
            <button class="modal-close" onclick="closeModal('modal-resolution-detail')">&times;</button>
          </div>
          <div class="modal-body" id="resolution-detail-body"></div>
          <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modal-resolution-detail')">Close</button>
          </div>
        </div>
      </div>

      <!-- Modal: View POA -->
      <div id="modal-poa-detail" class="modal-overlay" onclick="if(event.target===this)closeModal('modal-poa-detail')">
        <div class="modal" style="max-width:700px">
          <div class="modal-header">
            <span class="modal-title">📜 Power of Attorney Details</span>
            <button class="modal-close" onclick="closeModal('modal-poa-detail')">&times;</button>
          </div>
          <div class="modal-body" id="poa-detail-body"></div>
          <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modal-poa-detail')">Close</button>
          </div>
        </div>
      </div>

      <!-- Modal: View Contract -->
      <div id="modal-contract-detail" class="modal-overlay" onclick="if(event.target===this)closeModal('modal-contract-detail')">
        <div class="modal" style="max-width:700px">
          <div class="modal-header">
            <span class="modal-title">📝 Contract Details</span>
            <button class="modal-close" onclick="closeModal('modal-contract-detail')">&times;</button>
          </div>
          <div class="modal-body" id="contract-detail-body"></div>
          <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modal-contract-detail')">Close</button>
          </div>
        </div>
      </div>

      <!-- Modal: View Permit -->
      <div id="modal-permit-detail" class="modal-overlay" onclick="if(event.target===this)closeModal('modal-permit-detail')">
        <div class="modal" style="max-width:700px">
          <div class="modal-header">
            <span class="modal-title">🪪 Permit Details</span>
            <button class="modal-close" onclick="closeModal('modal-permit-detail')">&times;</button>
          </div>
          <div class="modal-body" id="permit-detail-body"></div>
          <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modal-permit-detail')">Close</button>
          </div>
        </div>
      </div>

    </main>
  </div>

<script src="../../admin.js"></script>
<script src="../../export.js"></script>
<script>
const API = '../../api/legal.php';

// Data stores
let loans = [], collaterals = [], cases = [], demands = [], kyc = [], compliance = [],
    resolutions = [], poa = [], contracts = [], permits = [], stats = {};

// ===== Helpers =====
function money(v) {
  const n = parseFloat(v);
  if (isNaN(n)) return '₱0.00';
  return '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function fmtDate(d) {
  if (!d) return '—';
  const dt = new Date(d);
  if (isNaN(dt)) return d;
  return dt.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function esc(s) {
  if (s === null || s === undefined) return '';
  const d = document.createElement('div');
  d.textContent = String(s);
  return d.innerHTML;
}

function labelCase(s) {
  if (!s) return '';
  return s.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
}

// ===== Badge Helpers =====
function loanStatusBadge(s) {
  const map = {
    draft: 'badge badge-gray', pending_signature: 'badge badge-amber', signed: 'badge badge-blue',
    active: 'badge badge-green', defaulted: 'badge badge-red', paid: 'badge badge-purple', cancelled: 'badge badge-gray'
  };
  return '<span class="' + (map[s] || 'badge badge-gray') + '">' + labelCase(s) + '</span>';
}

function lienStatusBadge(s) {
  const map = { active: 'badge badge-green', released: 'badge badge-blue', foreclosed: 'badge badge-red', pending_release: 'badge badge-amber' };
  return '<span class="' + (map[s] || 'badge badge-gray') + '">' + labelCase(s) + '</span>';
}

function priorityBadge(p) {
  const map = { low: 'badge badge-gray', medium: 'badge badge-blue', high: 'badge badge-amber', critical: 'badge badge-red' };
  return '<span class="' + (map[p] || 'badge badge-gray') + '">' + labelCase(p) + '</span>';
}

function caseStatusBadge(s) {
  const map = {
    open: 'badge badge-amber', in_progress: 'badge badge-blue', pending_review: 'badge badge-amber',
    resolved: 'badge badge-green', closed: 'badge badge-gray', appealed: 'badge badge-purple'
  };
  return '<span class="' + (map[s] || 'badge badge-gray') + '">' + labelCase(s) + '</span>';
}

function demandStatusBadge(s) {
  const map = {
    draft: 'badge badge-gray', sent: 'badge badge-blue', received: 'badge badge-green',
    responded: 'badge badge-purple', expired: 'badge badge-red', escalated: 'badge badge-red'
  };
  return '<span class="' + (map[s] || 'badge badge-gray') + '">' + labelCase(s) + '</span>';
}

function riskBadge(r) {
  const map = { low: 'badge badge-green', medium: 'badge badge-amber', high: 'badge badge-red', pep: 'badge badge-purple' };
  return '<span class="' + (map[r] || 'badge badge-gray') + '">' + labelCase(r) + '</span>';
}

function kycStatusBadge(s) {
  const map = {
    pending: 'badge badge-amber', verified: 'badge badge-green', rejected: 'badge badge-red',
    expired: 'badge badge-gray', under_review: 'badge badge-blue'
  };
  return '<span class="' + (map[s] || 'badge badge-gray') + '">' + labelCase(s) + '</span>';
}

function complianceStatusBadge(s) {
  const map = {
    compliant: 'badge badge-green', non_compliant: 'badge badge-red', in_progress: 'badge badge-blue',
    pending_review: 'badge badge-amber', exempted: 'badge badge-gray'
  };
  return '<span class="' + (map[s] || 'badge badge-gray') + '">' + labelCase(s) + '</span>';
}

function resolutionStatusBadge(s) {
  const map = { draft: 'badge badge-gray', approved: 'badge badge-green', filed: 'badge badge-blue', superseded: 'badge badge-amber' };
  return '<span class="' + (map[s] || 'badge badge-gray') + '">' + labelCase(s) + '</span>';
}

function poaStatusBadge(s) {
  const map = { active: 'badge badge-green', expired: 'badge badge-gray', revoked: 'badge badge-red', superseded: 'badge badge-amber' };
  return '<span class="' + (map[s] || 'badge badge-gray') + '">' + labelCase(s) + '</span>';
}

function contractStatusBadge(s) {
  const map = {
    draft: 'badge badge-gray', active: 'badge badge-green', expired: 'badge badge-red',
    terminated: 'badge badge-red', renewed: 'badge badge-blue', under_review: 'badge badge-amber'
  };
  return '<span class="' + (map[s] || 'badge badge-gray') + '">' + labelCase(s) + '</span>';
}

function permitStatusBadge(s) {
  const map = {
    active: 'badge badge-green', expired: 'badge badge-red', pending_renewal: 'badge badge-amber',
    suspended: 'badge badge-amber', revoked: 'badge badge-red'
  };
  return '<span class="' + (map[s] || 'badge badge-gray') + '">' + labelCase(s) + '</span>';
}

function isExpiringSoon(dateStr) {
  if (!dateStr) return false;
  const exp = new Date(dateStr);
  const now = new Date();
  const diffDays = (exp - now) / (1000 * 60 * 60 * 24);
  return diffDays > 0 && diffDays <= 90;
}

function emptyRow(cols, msg) {
  return '<tr><td colspan="' + cols + '" class="text-center text-gray-400 py-8"><div class="empty-state"><div style="font-size:36px;margin-bottom:8px">📭</div><div>' + (msg || 'No records found') + '</div></div></td></tr>';
}

// ===== Data Loading =====
async function loadData() {
  try {
    const [sRes, lRes, cRes, csRes, dRes, kRes, cmRes, rRes, pRes, ctRes, pmRes] = await Promise.all([
      fetch(API + '?action=dashboard_stats'),
      fetch(API + '?action=list_loans'),
      fetch(API + '?action=list_collaterals'),
      fetch(API + '?action=list_cases'),
      fetch(API + '?action=list_demands'),
      fetch(API + '?action=list_kyc'),
      fetch(API + '?action=list_compliance'),
      fetch(API + '?action=list_resolutions'),
      fetch(API + '?action=list_poa'),
      fetch(API + '?action=list_contracts'),
      fetch(API + '?action=list_permits')
    ]);

    stats = await sRes.json();
    loans = (await lRes.json()).data || [];
    collaterals = (await cRes.json()).data || [];
    cases = (await csRes.json()).data || [];
    demands = (await dRes.json()).data || [];
    kyc = (await kRes.json()).data || [];
    compliance = (await cmRes.json()).data || [];
    resolutions = (await rRes.json()).data || [];
    poa = (await pRes.json()).data || [];
    contracts = (await ctRes.json()).data || [];
    permits = (await pmRes.json()).data || [];

    renderStats();
    renderLoans();
    renderCollaterals();
    renderDemands();
    renderCases();
    renderKyc();
    renderCompliance();
    renderResolutions();
    renderPoa();
    renderContracts();
    renderPermits();
  } catch (e) {
    console.error('Load error:', e);
    Swal.fire({ icon: 'error', title: 'Load Error', text: 'Failed to load legal data. Please refresh the page.', confirmButtonColor: '#059669' });
  }
}

// ===== Render: Stats =====
function renderStats() {
  document.getElementById('stat-loans').textContent = stats.total_loans || 0;
  document.getElementById('stat-collaterals').textContent = stats.total_collaterals || 0;
  document.getElementById('stat-cases').textContent = stats.active_cases || 0;
  document.getElementById('stat-compliance').textContent = stats.compliance_items || 0;
  document.getElementById('stat-resolutions').textContent = stats.total_resolutions || 0;
}

// ===== Render: Loans =====
function renderLoans() {
  const tb = document.getElementById('loans-tbody');
  if (!loans.length) { tb.innerHTML = emptyRow(9, 'No loan documents found'); return; }
  tb.innerHTML = loans.map(function(l, i) {
    return '<tr>' +
      '<td><span class="secure-text" style="font-size:11px">' + esc(l.loan_doc_code) + '</span></td>' +
      '<td style="font-weight:600">' + esc(l.borrower_name) + '</td>' +
      '<td>' + money(l.loan_amount) + '</td>' +
      '<td>' + parseFloat(l.interest_rate || 0).toFixed(2) + '%</td>' +
      '<td>' + (l.loan_term_months || 0) + ' mos</td>' +
      '<td>' + labelCase(l.security_type) + '</td>' +
      '<td>' + loanStatusBadge(l.status) + '</td>' +
      '<td>' + (l.attorney_name ? '<span class="attorney-seal">⚖️ ' + esc(l.attorney_name) + '</span>' : '<span class="text-gray-400">—</span>') + '</td>' +
      '<td><button class="btn btn-outline btn-sm" onclick="viewLoanContract(' + i + ')">View Contract</button></td>' +
    '</tr>';
  }).join('');
}

// ===== View: Loan Contract =====
let viewLoanContractIdx = null;

function viewLoanContract(idx) {
  const l = loans[idx];
  if (!l) return;
  viewLoanContractIdx = idx;
  document.getElementById('btn-download-loan-pdf').style.display = 'inline-flex';

  var html = '<div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:20px;flex-wrap:wrap;gap:12px">' +
    '<div>' +
      '<div style="font-weight:700;font-size:16px;color:#1F2937">' + esc(l.loan_doc_code) + '</div>' +
      '<div style="font-size:13px;color:#6B7280">Borrower: <strong>' + esc(l.borrower_name) + '</strong></div>' +
      (l.borrower_address ? '<div style="font-size:12px;color:#9CA3AF">' + esc(l.borrower_address) + '</div>' : '') +
    '</div>' +
    '<div style="text-align:right">' +
      loanStatusBadge(l.status) +
      '<div style="font-size:12px;color:#6B7280;margin-top:4px">Amount: <strong>' + money(l.loan_amount) + '</strong></div>' +
    '</div>' +
  '</div>';

  html += '<div class="grid-2" style="margin-bottom:16px">' +
    '<div style="background:#F9FAFB;padding:12px;border-radius:8px">' +
      '<div style="font-size:11px;color:#9CA3AF;text-transform:uppercase;font-weight:600">Loan Terms</div>' +
      '<div style="font-size:13px;margin-top:4px">Interest: <strong>' + parseFloat(l.interest_rate || 0).toFixed(2) + '%</strong> | Term: <strong>' + l.loan_term_months + ' months</strong></div>' +
      '<div style="font-size:13px">Repayment: <strong>' + labelCase(l.repayment_schedule) + '</strong> | Security: <strong>' + labelCase(l.security_type) + '</strong></div>' +
      '<div style="font-size:13px">Penalty Rate: <strong>' + parseFloat(l.penalty_rate || 0).toFixed(2) + '%/mo</strong></div>' +
      (l.purpose ? '<div style="font-size:13px">Purpose: <strong>' + esc(l.purpose) + '</strong></div>' : '') +
    '</div>' +
    '<div style="background:#F9FAFB;padding:12px;border-radius:8px">' +
      '<div style="font-size:11px;color:#9CA3AF;text-transform:uppercase;font-weight:600">Key Dates</div>' +
      '<div style="font-size:13px;margin-top:4px">Signed: <strong>' + fmtDate(l.signed_date) + '</strong></div>' +
      '<div style="font-size:13px">Effective: <strong>' + fmtDate(l.effective_date) + '</strong></div>' +
      '<div style="font-size:13px">Maturity: <strong>' + fmtDate(l.maturity_date) + '</strong></div>' +
    '</div>' +
  '</div>';

  if (l.contract_body) {
    html += '<div style="font-weight:600;font-size:13px;color:#1F2937;margin-bottom:8px">📄 Contract Body</div>' +
      '<div class="contract-paper">' + esc(l.contract_body) + '</div>';
  }

  if (l.disclosure_statement) {
    html += '<div style="font-weight:600;font-size:13px;color:#1F2937;margin:16px 0 8px">📋 Truth in Lending Disclosure</div>' +
      '<div class="contract-paper" style="max-height:200px">' + esc(l.disclosure_statement) + '</div>';
  }

  if (l.promissory_note) {
    html += '<div style="font-weight:600;font-size:13px;color:#1F2937;margin:16px 0 8px">📝 Promissory Note</div>' +
      '<div class="contract-paper" style="max-height:200px">' + esc(l.promissory_note) + '</div>';
  }

  if (l.attorney_name) {
    html += '<div style="margin-top:20px;padding:16px;background:linear-gradient(135deg,#F0FDF4,#D1FAE5);border-radius:12px;border:1px solid #A7F3D0">' +
      '<div style="font-weight:700;font-size:13px;color:#065F46;margin-bottom:8px">⚖️ Attorney Credentials</div>' +
      '<div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;font-size:12px">' +
        '<div><strong>Name:</strong> ' + esc(l.attorney_name) + '</div>' +
        '<div><strong>PRC No:</strong> ' + (esc(l.attorney_prc) || '—') + '</div>' +
        '<div><strong>PTR No:</strong> ' + (esc(l.attorney_ptr) || '—') + '</div>' +
        '<div><strong>IBP No:</strong> ' + (esc(l.attorney_ibp) || '—') + '</div>' +
        '<div><strong>Roll No:</strong> ' + (esc(l.attorney_roll) || '—') + '</div>' +
        '<div><strong>MCLE No:</strong> ' + (esc(l.attorney_mcle) || '—') + '</div>' +
      '</div>' +
    '</div>';
  }

  if (l.notary_name) {
    html += '<div style="margin-top:12px;padding:12px;background:#FFFBEB;border-radius:8px;border:1px solid #FDE68A;font-size:12px">' +
      '<strong>Notary:</strong> ' + esc(l.notary_name) + ' | <strong>Commission:</strong> ' + (esc(l.notary_commission) || '—') +
      ' | <strong>Doc No:</strong> ' + (esc(l.doc_series_no) || '—') + ' | <strong>Page:</strong> ' + (esc(l.doc_page_no) || '—') +
      ' | <strong>Book:</strong> ' + (esc(l.doc_book_no) || '—') +
    '</div>';
  }

  document.getElementById('loan-contract-body').innerHTML = html;
  openModal('modal-loan-contract');
}

// ===== Render: Collaterals =====
function renderCollaterals() {
  const tb = document.getElementById('collaterals-tbody');
  if (!collaterals.length) { tb.innerHTML = emptyRow(8, 'No collateral records found'); return; }
  tb.innerHTML = collaterals.map(function(c, i) {
    return '<tr>' +
      '<td><span class="secure-text" style="font-size:11px">' + esc(c.collateral_code) + '</span></td>' +
      '<td style="font-weight:600">' + esc(c.borrower_name) + '</td>' +
      '<td>' + labelCase(c.collateral_type) + '</td>' +
      '<td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">' + esc(c.description) + '</td>' +
      '<td>' + money(c.appraised_value) + '</td>' +
      '<td>' + lienStatusBadge(c.lien_status) + '</td>' +
      '<td>' + (c.insurance_expiry ? (isExpiringSoon(c.insurance_expiry) ? '<span class="badge badge-amber">⚠ ' + fmtDate(c.insurance_expiry) + '</span>' : fmtDate(c.insurance_expiry)) : '—') + '</td>' +
      '<td><button class="btn btn-outline btn-sm" onclick="viewCollateral(' + i + ')">View</button></td>' +
    '</tr>';
  }).join('');
}

// ===== View: Collateral =====
function viewCollateral(idx) {
  const c = collaterals[idx];
  if (!c) return;
  document.getElementById('collateral-detail-body').innerHTML =
    '<div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:16px;flex-wrap:wrap;gap:12px">' +
      '<div>' +
        '<div style="font-weight:700;font-size:16px;color:#1F2937">' + esc(c.collateral_code) + '</div>' +
        '<div style="font-size:13px;color:#6B7280">Borrower: <strong>' + esc(c.borrower_name) + '</strong></div>' +
      '</div>' +
      '<div>' + lienStatusBadge(c.lien_status) + '</div>' +
    '</div>' +
    '<div class="grid-2" style="gap:12px">' +
      '<div style="background:#F9FAFB;padding:12px;border-radius:8px">' +
        '<div style="font-size:11px;color:#9CA3AF;text-transform:uppercase;font-weight:600">Property Details</div>' +
        '<div style="font-size:13px;margin-top:4px"><strong>Type:</strong> ' + labelCase(c.collateral_type) + '</div>' +
        '<div style="font-size:13px"><strong>Description:</strong> ' + esc(c.description) + '</div>' +
        (c.serial_plate_no ? '<div style="font-size:13px"><strong>Serial/Plate:</strong> ' + esc(c.serial_plate_no) + '</div>' : '') +
        (c.title_deed_no ? '<div style="font-size:13px"><strong>Title/Deed:</strong> ' + esc(c.title_deed_no) + '</div>' : '') +
        (c.location_address ? '<div style="font-size:13px"><strong>Location:</strong> ' + esc(c.location_address) + '</div>' : '') +
      '</div>' +
      '<div style="background:#F9FAFB;padding:12px;border-radius:8px">' +
        '<div style="font-size:11px;color:#9CA3AF;text-transform:uppercase;font-weight:600">Valuation &amp; Lien</div>' +
        '<div style="font-size:13px;margin-top:4px"><strong>Appraised Value:</strong> ' + money(c.appraised_value) + '</div>' +
        (c.appraisal_date ? '<div style="font-size:13px"><strong>Appraisal Date:</strong> ' + fmtDate(c.appraisal_date) + '</div>' : '') +
        (c.appraiser_name ? '<div style="font-size:13px"><strong>Appraiser:</strong> ' + esc(c.appraiser_name) + '</div>' : '') +
        (c.lien_recorded_date ? '<div style="font-size:13px"><strong>Lien Recorded:</strong> ' + fmtDate(c.lien_recorded_date) + '</div>' : '') +
        (c.lien_registry_no ? '<div style="font-size:13px"><strong>Registry No:</strong> ' + esc(c.lien_registry_no) + '</div>' : '') +
      '</div>' +
    '</div>' +
    (c.insurance_policy ? '<div style="margin-top:12px;padding:12px;background:#EFF6FF;border-radius:8px;border:1px solid #BFDBFE;font-size:13px">' +
      '<strong>Insurance Policy:</strong> ' + esc(c.insurance_policy) + ' | <strong>Expiry:</strong> ' + fmtDate(c.insurance_expiry) +
      (isExpiringSoon(c.insurance_expiry) ? ' <span class="badge badge-amber" style="margin-left:8px">⚠ Expiring Soon</span>' : '') +
    '</div>' : '') +
    (c.notes ? '<div style="margin-top:12px;font-size:13px;color:#6B7280"><strong>Notes:</strong> ' + esc(c.notes) + '</div>' : '');
  openModal('modal-collateral-detail');
}

// ===== Render: Demands =====
function renderDemands() {
  const tb = document.getElementById('demands-tbody');
  if (!demands.length) { tb.innerHTML = emptyRow(8, 'No demand letters found'); return; }
  tb.innerHTML = demands.map(function(d, i) {
    return '<tr>' +
      '<td><span class="secure-text" style="font-size:11px">' + esc(d.demand_code) + '</span></td>' +
      '<td style="font-weight:600">' + esc(d.borrower_name) + '</td>' +
      '<td>' + money(d.amount_demanded) + '</td>' +
      '<td>' + labelCase(d.demand_type) + '</td>' +
      '<td>' + demandStatusBadge(d.status) + '</td>' +
      '<td>' + fmtDate(d.sent_date) + '</td>' +
      '<td>' + fmtDate(d.response_deadline) + '</td>' +
      '<td><button class="btn btn-outline btn-sm" onclick="viewDemand(' + i + ')">View</button></td>' +
    '</tr>';
  }).join('');
}

// ===== View: Demand =====
function viewDemand(idx) {
  const d = demands[idx];
  if (!d) return;
  document.getElementById('demand-detail-body').innerHTML =
    '<div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:16px;flex-wrap:wrap;gap:12px">' +
      '<div>' +
        '<div style="font-weight:700;font-size:16px;color:#1F2937">' + esc(d.demand_code) + '</div>' +
        '<div style="font-size:13px;color:#6B7280">Borrower: <strong>' + esc(d.borrower_name) + '</strong></div>' +
        (d.borrower_address ? '<div style="font-size:12px;color:#9CA3AF">' + esc(d.borrower_address) + '</div>' : '') +
      '</div>' +
      '<div style="text-align:right">' +
        demandStatusBadge(d.status) +
        '<div style="font-size:13px;color:#6B7280;margin-top:4px">Amount: <strong>' + money(d.amount_demanded) + '</strong></div>' +
      '</div>' +
    '</div>' +
    '<div class="grid-2" style="gap:12px;margin-bottom:16px">' +
      '<div style="background:#F9FAFB;padding:12px;border-radius:8px">' +
        '<div style="font-size:11px;color:#9CA3AF;text-transform:uppercase;font-weight:600">Letter Details</div>' +
        '<div style="font-size:13px;margin-top:4px"><strong>Type:</strong> ' + labelCase(d.demand_type) + '</div>' +
        (d.attorney_name ? '<div style="font-size:13px"><strong>Attorney:</strong> ' + esc(d.attorney_name) + '</div>' : '') +
        (d.sent_via ? '<div style="font-size:13px"><strong>Sent Via:</strong> ' + labelCase(d.sent_via) + '</div>' : '') +
      '</div>' +
      '<div style="background:#F9FAFB;padding:12px;border-radius:8px">' +
        '<div style="font-size:11px;color:#9CA3AF;text-transform:uppercase;font-weight:600">Timeline</div>' +
        '<div style="font-size:13px;margin-top:4px"><strong>Sent:</strong> ' + fmtDate(d.sent_date) + '</div>' +
        '<div style="font-size:13px"><strong>Received:</strong> ' + fmtDate(d.received_date) + '</div>' +
        '<div style="font-size:13px"><strong>Response Deadline:</strong> ' + fmtDate(d.response_deadline) + '</div>' +
      '</div>' +
    '</div>' +
    (d.borrower_responded ? '<div style="background:#F0FDF4;padding:12px;border-radius:8px;border:1px solid #A7F3D0;margin-bottom:12px">' +
      '<div style="font-size:12px;font-weight:600;color:#065F46">✅ Borrower Responded</div>' +
      (d.response_summary ? '<div style="font-size:13px;margin-top:4px">' + esc(d.response_summary) + '</div>' : '') +
    '</div>' : '') +
    (d.escalated_to_litigation ? '<div class="badge badge-red" style="margin-bottom:12px">⚠ Escalated to Litigation</div>' : '') +
    (d.letter_body ? '<div style="font-weight:600;font-size:13px;margin-bottom:8px">📄 Letter Body</div>' +
      '<div class="contract-paper">' + esc(d.letter_body) + '</div>' : '');
  openModal('modal-demand-detail');
}

// ===== Render: Cases =====
function renderCases() {
  const tb = document.getElementById('cases-tbody');
  if (!cases.length) { tb.innerHTML = emptyRow(9, 'No legal cases found'); return; }
  tb.innerHTML = cases.map(function(c, i) {
    return '<tr>' +
      '<td><span class="secure-text" style="font-size:11px">' + esc(c.case_number) + '</span></td>' +
      '<td style="font-weight:600;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">' + esc(c.title) + '</td>' +
      '<td>' + labelCase(c.case_type) + '</td>' +
      '<td>' + priorityBadge(c.priority) + '</td>' +
      '<td>' + caseStatusBadge(c.status) + '</td>' +
      '<td>' + (esc(c.opposing_party) || '—') + '</td>' +
      '<td>' + money(c.financial_impact) + '</td>' +
      '<td>' + (esc(c.assigned_name) || '—') + '</td>' +
      '<td><button class="btn btn-outline btn-sm" onclick="viewCase(' + i + ')">View</button></td>' +
    '</tr>';
  }).join('');
}

// ===== View: Case =====
function viewCase(idx) {
  const c = cases[idx];
  if (!c) return;
  document.getElementById('case-detail-body').innerHTML =
    '<div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:16px;flex-wrap:wrap;gap:12px">' +
      '<div>' +
        '<div style="font-weight:700;font-size:16px;color:#1F2937">' + esc(c.case_number) + '</div>' +
        '<div style="font-weight:600;font-size:14px;color:#374151;margin-top:2px">' + esc(c.title) + '</div>' +
      '</div>' +
      '<div style="display:flex;gap:8px;flex-wrap:wrap">' + priorityBadge(c.priority) + ' ' + caseStatusBadge(c.status) + '</div>' +
    '</div>' +
    '<div class="grid-2" style="gap:12px;margin-bottom:16px">' +
      '<div style="background:#F9FAFB;padding:12px;border-radius:8px">' +
        '<div style="font-size:11px;color:#9CA3AF;text-transform:uppercase;font-weight:600">Case Info</div>' +
        '<div style="font-size:13px;margin-top:4px"><strong>Type:</strong> ' + labelCase(c.case_type) + '</div>' +
        (c.opposing_party ? '<div style="font-size:13px"><strong>Opposing Party:</strong> ' + esc(c.opposing_party) + '</div>' : '') +
        (c.court_venue ? '<div style="font-size:13px"><strong>Court/Venue:</strong> ' + esc(c.court_venue) + '</div>' : '') +
        (c.assigned_lawyer ? '<div style="font-size:13px"><strong>Lawyer:</strong> ' + esc(c.assigned_lawyer) + '</div>' : '') +
        (c.department ? '<div style="font-size:13px"><strong>Department:</strong> ' + esc(c.department) + '</div>' : '') +
      '</div>' +
      '<div style="background:#F9FAFB;padding:12px;border-radius:8px">' +
        '<div style="font-size:11px;color:#9CA3AF;text-transform:uppercase;font-weight:600">Dates &amp; Impact</div>' +
        '<div style="font-size:13px;margin-top:4px"><strong>Filed:</strong> ' + fmtDate(c.filing_date) + '</div>' +
        '<div style="font-size:13px"><strong>Due:</strong> ' + fmtDate(c.due_date) + '</div>' +
        (c.resolution_date ? '<div style="font-size:13px"><strong>Resolved:</strong> ' + fmtDate(c.resolution_date) + '</div>' : '') +
        '<div style="font-size:13px"><strong>Financial Impact:</strong> ' + money(c.financial_impact) + '</div>' +
        (c.assigned_name ? '<div style="font-size:13px"><strong>Assigned To:</strong> ' + esc(c.assigned_name) + '</div>' : '') +
      '</div>' +
    '</div>' +
    '<div style="background:#F9FAFB;padding:12px;border-radius:8px;margin-bottom:12px">' +
      '<div style="font-size:11px;color:#9CA3AF;text-transform:uppercase;font-weight:600">Description</div>' +
      '<div style="font-size:13px;margin-top:4px;white-space:pre-wrap">' + esc(c.description) + '</div>' +
    '</div>' +
    (c.resolution_summary ? '<div style="background:#F0FDF4;padding:12px;border-radius:8px;border:1px solid #A7F3D0">' +
      '<div style="font-size:12px;font-weight:600;color:#065F46">📋 Resolution Summary</div>' +
      '<div style="font-size:13px;margin-top:4px;white-space:pre-wrap">' + esc(c.resolution_summary) + '</div>' +
    '</div>' : '');
  openModal('modal-case-detail');
}

// ===== Render: KYC =====
function renderKyc() {
  const tb = document.getElementById('kyc-tbody');
  if (!kyc.length) { tb.innerHTML = emptyRow(8, 'No KYC records found'); return; }
  tb.innerHTML = kyc.map(function(k, i) {
    return '<tr>' +
      '<td><span class="secure-text" style="font-size:11px">' + esc(k.kyc_code) + '</span></td>' +
      '<td style="font-weight:600">' + esc(k.client_name) + '</td>' +
      '<td>' + labelCase(k.client_type) + '</td>' +
      '<td>' + esc(k.id_type) + '</td>' +
      '<td>' + riskBadge(k.risk_rating) + '</td>' +
      '<td>' + kycStatusBadge(k.verification_status) + '</td>' +
      '<td>' + fmtDate(k.next_review_date) + '</td>' +
      '<td><button class="btn btn-outline btn-sm" onclick="viewKyc(' + i + ')">View</button></td>' +
    '</tr>';
  }).join('');
}

// ===== View: KYC =====
function viewKyc(idx) {
  const k = kyc[idx];
  if (!k) return;
  document.getElementById('kyc-detail-body').innerHTML =
    '<div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:16px;flex-wrap:wrap;gap:12px">' +
      '<div>' +
        '<div style="font-weight:700;font-size:16px;color:#1F2937">' + esc(k.kyc_code) + '</div>' +
        '<div style="font-size:13px;color:#6B7280">Client: <strong>' + esc(k.client_name) + '</strong></div>' +
      '</div>' +
      '<div style="display:flex;gap:8px;flex-wrap:wrap">' + riskBadge(k.risk_rating) + ' ' + kycStatusBadge(k.verification_status) + '</div>' +
    '</div>' +
    '<div class="grid-2" style="gap:12px;margin-bottom:16px">' +
      '<div style="background:#F9FAFB;padding:12px;border-radius:8px">' +
        '<div style="font-size:11px;color:#9CA3AF;text-transform:uppercase;font-weight:600">Identity</div>' +
        '<div style="font-size:13px;margin-top:4px"><strong>Client Type:</strong> ' + labelCase(k.client_type) + '</div>' +
        '<div style="font-size:13px"><strong>ID Type:</strong> ' + esc(k.id_type) + '</div>' +
        '<div style="font-size:13px" class="doc-secured" onclick="this.classList.toggle(\'revealed\')">' +
          '<strong>ID Number:</strong> <span class="doc-title-text">' + esc(k.id_number) + '</span> <span class="eye-toggle">👁</span>' +
        '</div>' +
        (k.id_expiry ? '<div style="font-size:13px"><strong>ID Expiry:</strong> ' + fmtDate(k.id_expiry) + '</div>' : '') +
        (k.tin ? '<div style="font-size:13px" class="doc-secured" onclick="this.classList.toggle(\'revealed\')"><strong>TIN:</strong> <span class="doc-title-text">' + esc(k.tin) + '</span> <span class="eye-toggle">👁</span></div>' : '') +
      '</div>' +
      '<div style="background:#F9FAFB;padding:12px;border-radius:8px">' +
        '<div style="font-size:11px;color:#9CA3AF;text-transform:uppercase;font-weight:600">Background</div>' +
        (k.address ? '<div style="font-size:13px;margin-top:4px"><strong>Address:</strong> ' + esc(k.address) + '</div>' : '') +
        (k.occupation ? '<div style="font-size:13px"><strong>Occupation:</strong> ' + esc(k.occupation) + '</div>' : '') +
        (k.source_of_funds ? '<div style="font-size:13px"><strong>Source of Funds:</strong> ' + esc(k.source_of_funds) + '</div>' : '') +
        '<div style="font-size:13px"><strong>Next Review:</strong> ' + fmtDate(k.next_review_date) + '</div>' +
        (k.verified_date ? '<div style="font-size:13px"><strong>Verified:</strong> ' + fmtDate(k.verified_date) + '</div>' : '') +
      '</div>' +
    '</div>' +
    '<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px">' +
      (k.aml_flag ? '<span class="badge badge-red">🚩 AML Flag</span>' : '<span class="badge badge-green">✅ AML Clear</span>') +
      (k.sanctions_checked ? ' <span class="badge badge-green">✅ Sanctions Checked</span>' : ' <span class="badge badge-gray">Sanctions Not Checked</span>') +
      (k.pep_checked ? ' <span class="badge badge-green">✅ PEP Checked</span>' : ' <span class="badge badge-gray">PEP Not Checked</span>') +
    '</div>' +
    (k.aml_notes ? '<div style="background:#FEF2F2;padding:12px;border-radius:8px;border:1px solid #FECACA;font-size:13px">' +
      '<strong>AML Notes:</strong> ' + esc(k.aml_notes) +
    '</div>' : '');
  openModal('modal-kyc-detail');
}

// ===== Render: Compliance =====
function renderCompliance() {
  const tb = document.getElementById('compliance-tbody');
  if (!compliance.length) { tb.innerHTML = emptyRow(7, 'No compliance records found'); return; }
  tb.innerHTML = compliance.map(function(c, i) {
    return '<tr>' +
      '<td><span class="secure-text" style="font-size:11px">' + esc(c.reference_code) + '</span></td>' +
      '<td style="font-weight:600;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">' + esc(c.requirement) + '</td>' +
      '<td>' + (esc(c.regulatory_body) || '—') + '</td>' +
      '<td>' + complianceStatusBadge(c.status) + '</td>' +
      '<td>' + fmtDate(c.deadline) + '</td>' +
      '<td>' + (esc(c.assigned_name) || '—') + '</td>' +
      '<td><button class="btn btn-outline btn-sm" onclick="viewCompliance(' + i + ')">View</button></td>' +
    '</tr>';
  }).join('');
}

// ===== View: Compliance =====
function viewCompliance(idx) {
  const c = compliance[idx];
  if (!c) return;
  document.getElementById('compliance-detail-body').innerHTML =
    '<div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:16px;flex-wrap:wrap;gap:12px">' +
      '<div>' +
        '<div style="font-weight:700;font-size:16px;color:#1F2937">' + esc(c.reference_code) + '</div>' +
        '<div style="font-weight:600;font-size:14px;color:#374151;margin-top:2px">' + esc(c.requirement) + '</div>' +
      '</div>' +
      '<div>' + complianceStatusBadge(c.status) + '</div>' +
    '</div>' +
    '<div class="grid-2" style="gap:12px;margin-bottom:16px">' +
      '<div style="background:#F9FAFB;padding:12px;border-radius:8px">' +
        '<div style="font-size:11px;color:#9CA3AF;text-transform:uppercase;font-weight:600">Details</div>' +
        '<div style="font-size:13px;margin-top:4px"><strong>Regulatory Body:</strong> ' + (esc(c.regulatory_body) || '—') + '</div>' +
        '<div style="font-size:13px"><strong>Category:</strong> ' + labelCase(c.category) + '</div>' +
        '<div style="font-size:13px"><strong>Risk Level:</strong> ' + priorityBadge(c.risk_level) + '</div>' +
        (c.assigned_name ? '<div style="font-size:13px"><strong>Assigned To:</strong> ' + esc(c.assigned_name) + '</div>' : '') +
      '</div>' +
      '<div style="background:#F9FAFB;padding:12px;border-radius:8px">' +
        '<div style="font-size:11px;color:#9CA3AF;text-transform:uppercase;font-weight:600">Timeline</div>' +
        '<div style="font-size:13px;margin-top:4px"><strong>Deadline:</strong> ' + fmtDate(c.deadline) + '</div>' +
        '<div style="font-size:13px"><strong>Last Reviewed:</strong> ' + fmtDate(c.last_reviewed) + '</div>' +
        '<div style="font-size:13px"><strong>Next Review:</strong> ' + fmtDate(c.next_review_date) + '</div>' +
      '</div>' +
    '</div>' +
    (c.description ? '<div style="background:#F9FAFB;padding:12px;border-radius:8px;margin-bottom:12px">' +
      '<div style="font-size:11px;color:#9CA3AF;text-transform:uppercase;font-weight:600">Description</div>' +
      '<div style="font-size:13px;margin-top:4px;white-space:pre-wrap">' + esc(c.description) + '</div>' +
    '</div>' : '') +
    (c.notes ? '<div style="font-size:13px;color:#6B7280"><strong>Notes:</strong> ' + esc(c.notes) + '</div>' : '');
  openModal('modal-compliance-detail');
}

// ===== Render: Resolutions =====
function renderResolutions() {
  const tb = document.getElementById('resolutions-tbody');
  if (!resolutions.length) { tb.innerHTML = emptyRow(9, 'No board resolutions found'); return; }
  tb.innerHTML = resolutions.map(function(r, i) {
    return '<tr>' +
      '<td><span class="secure-text" style="font-size:11px">' + esc(r.resolution_code) + '</span></td>' +
      '<td style="font-weight:600;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">' + esc(r.title) + '</td>' +
      '<td>' + labelCase(r.resolution_type) + '</td>' +
      '<td>' + fmtDate(r.meeting_date) + '</td>' +
      '<td>' + labelCase(r.meeting_type) + '</td>' +
      '<td><span style="font-size:12px;font-weight:600">' + (r.votes_for != null ? r.votes_for : 0) + '/<span style="color:#EF4444">' + (r.votes_against != null ? r.votes_against : 0) + '</span>/<span style="color:#9CA3AF">' + (r.votes_abstain != null ? r.votes_abstain : 0) + '</span></span></td>' +
      '<td>' + (r.passed ? '<span class="badge badge-green">✅ Yes</span>' : '<span class="badge badge-red">❌ No</span>') + '</td>' +
      '<td>' + resolutionStatusBadge(r.status) + '</td>' +
      '<td><button class="btn btn-outline btn-sm" onclick="viewResolution(' + i + ')">View</button></td>' +
    '</tr>';
  }).join('');
}

// ===== View: Resolution =====
function viewResolution(idx) {
  const r = resolutions[idx];
  if (!r) return;

  var attendeesHtml = '';
  if (r.attendees) {
    try {
      var list = typeof r.attendees === 'string' ? JSON.parse(r.attendees) : r.attendees;
      if (Array.isArray(list)) {
        attendeesHtml = '<div style="margin-top:8px"><strong>Attendees:</strong> ' + list.map(function(a) { return esc(a); }).join(', ') + '</div>';
      }
    } catch (e) {
      attendeesHtml = '<div style="margin-top:8px"><strong>Attendees:</strong> ' + esc(String(r.attendees)) + '</div>';
    }
  }

  document.getElementById('resolution-detail-body').innerHTML =
    '<div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:16px;flex-wrap:wrap;gap:12px">' +
      '<div>' +
        '<div style="font-weight:700;font-size:16px;color:#1F2937">' + esc(r.resolution_code) + '</div>' +
        '<div style="font-weight:600;font-size:14px;color:#374151;margin-top:2px">' + esc(r.title) + '</div>' +
      '</div>' +
      '<div style="display:flex;gap:8px;flex-wrap:wrap">' +
        (r.passed ? '<span class="badge badge-green">✅ Passed</span>' : '<span class="badge badge-red">❌ Not Passed</span>') + ' ' +
        resolutionStatusBadge(r.status) +
      '</div>' +
    '</div>' +
    '<div class="grid-2" style="gap:12px;margin-bottom:16px">' +
      '<div style="background:#F9FAFB;padding:12px;border-radius:8px">' +
        '<div style="font-size:11px;color:#9CA3AF;text-transform:uppercase;font-weight:600">Meeting Details</div>' +
        '<div style="font-size:13px;margin-top:4px"><strong>Type:</strong> ' + labelCase(r.resolution_type) + '</div>' +
        '<div style="font-size:13px"><strong>Meeting Date:</strong> ' + fmtDate(r.meeting_date) + '</div>' +
        '<div style="font-size:13px"><strong>Meeting Type:</strong> ' + labelCase(r.meeting_type) + '</div>' +
        '<div style="font-size:13px"><strong>Quorum:</strong> ' + (r.quorum_present ? '✅ Present' : '❌ No Quorum') + '</div>' +
        (r.effective_date ? '<div style="font-size:13px"><strong>Effective:</strong> ' + fmtDate(r.effective_date) + '</div>' : '') +
      '</div>' +
      '<div style="background:#F9FAFB;padding:12px;border-radius:8px">' +
        '<div style="font-size:11px;color:#9CA3AF;text-transform:uppercase;font-weight:600">Voting Results</div>' +
        '<div style="font-size:13px;margin-top:4px"><strong>For:</strong> <span style="color:#059669;font-weight:700">' + (r.votes_for != null ? r.votes_for : 0) + '</span></div>' +
        '<div style="font-size:13px"><strong>Against:</strong> <span style="color:#EF4444;font-weight:700">' + (r.votes_against != null ? r.votes_against : 0) + '</span></div>' +
        '<div style="font-size:13px"><strong>Abstain:</strong> <span style="color:#9CA3AF;font-weight:700">' + (r.votes_abstain != null ? r.votes_abstain : 0) + '</span></div>' +
        (r.secretary_name ? '<div style="font-size:13px;margin-top:4px"><strong>Secretary:</strong> ' + esc(r.secretary_name) + '</div>' : '') +
        (r.chairman_name ? '<div style="font-size:13px"><strong>Chairman:</strong> ' + esc(r.chairman_name) + '</div>' : '') +
      '</div>' +
    '</div>' +
    '<div style="font-size:13px;margin-bottom:12px">' + attendeesHtml + '</div>' +
    (r.resolution_text ? '<div style="font-weight:600;font-size:13px;margin-bottom:8px">📄 Resolution Text</div>' +
      '<div class="contract-paper">' + esc(r.resolution_text) + '</div>' : '') +
    (r.minutes_text ? '<div style="font-weight:600;font-size:13px;margin:16px 0 8px">📋 Meeting Minutes</div>' +
      '<div class="contract-paper" style="max-height:250px">' + esc(r.minutes_text) + '</div>' : '');
  openModal('modal-resolution-detail');
}

// ===== Render: POA =====
function renderPoa() {
  const tb = document.getElementById('poa-tbody');
  if (!poa.length) { tb.innerHTML = emptyRow(10, 'No power of attorney records found'); return; }
  tb.innerHTML = poa.map(function(p, i) {
    return '<tr>' +
      '<td><span class="secure-text" style="font-size:11px">' + esc(p.poa_code) + '</span></td>' +
      '<td style="font-weight:600">' + esc(p.principal_name) + '</td>' +
      '<td>' + esc(p.agent_name) + '</td>' +
      '<td>' + labelCase(p.poa_type) + '</td>' +
      '<td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">' + esc(p.scope) + '</td>' +
      '<td>' + fmtDate(p.effective_date) + '</td>' +
      '<td>' + fmtDate(p.expiry_date) + '</td>' +
      '<td>' + (p.notarized ? '<span class="badge badge-green">✅</span>' : '<span class="badge badge-gray">No</span>') + '</td>' +
      '<td>' + poaStatusBadge(p.status) + '</td>' +
      '<td><button class="btn btn-outline btn-sm" onclick="viewPoa(' + i + ')">View</button></td>' +
    '</tr>';
  }).join('');
}

// ===== View: POA =====
function viewPoa(idx) {
  const p = poa[idx];
  if (!p) return;
  document.getElementById('poa-detail-body').innerHTML =
    '<div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:16px;flex-wrap:wrap;gap:12px">' +
      '<div>' +
        '<div style="font-weight:700;font-size:16px;color:#1F2937">' + esc(p.poa_code) + '</div>' +
        '<div style="font-size:13px;color:#6B7280">' + labelCase(p.poa_type) + ' Power of Attorney</div>' +
      '</div>' +
      '<div style="display:flex;gap:8px;flex-wrap:wrap">' +
        (p.notarized ? '<span class="badge badge-green">✅ Notarized</span>' : '<span class="badge badge-gray">Not Notarized</span>') + ' ' +
        poaStatusBadge(p.status) +
      '</div>' +
    '</div>' +
    '<div class="grid-2" style="gap:12px;margin-bottom:16px">' +
      '<div style="background:#F9FAFB;padding:12px;border-radius:8px">' +
        '<div style="font-size:11px;color:#9CA3AF;text-transform:uppercase;font-weight:600">Principal (Grantor)</div>' +
        '<div style="font-size:13px;font-weight:600;margin-top:4px">' + esc(p.principal_name) + '</div>' +
        (p.principal_position ? '<div style="font-size:12px;color:#6B7280">' + esc(p.principal_position) + '</div>' : '') +
      '</div>' +
      '<div style="background:#F9FAFB;padding:12px;border-radius:8px">' +
        '<div style="font-size:11px;color:#9CA3AF;text-transform:uppercase;font-weight:600">Agent (Grantee)</div>' +
        '<div style="font-size:13px;font-weight:600;margin-top:4px">' + esc(p.agent_name) + '</div>' +
        (p.agent_position ? '<div style="font-size:12px;color:#6B7280">' + esc(p.agent_position) + '</div>' : '') +
      '</div>' +
    '</div>' +
    '<div style="background:#F9FAFB;padding:12px;border-radius:8px;margin-bottom:12px">' +
      '<div style="font-size:11px;color:#9CA3AF;text-transform:uppercase;font-weight:600">Scope of Authority</div>' +
      '<div style="font-size:13px;margin-top:4px;white-space:pre-wrap">' + esc(p.scope) + '</div>' +
    '</div>' +
    '<div class="grid-2" style="gap:12px;margin-bottom:12px">' +
      '<div style="font-size:13px"><strong>Effective:</strong> ' + fmtDate(p.effective_date) + '</div>' +
      '<div style="font-size:13px"><strong>Expiry:</strong> ' + fmtDate(p.expiry_date) + '</div>' +
    '</div>' +
    (p.notarized ? '<div style="background:#FFFBEB;padding:12px;border-radius:8px;border:1px solid #FDE68A;font-size:13px;margin-bottom:12px">' +
      '<strong>Notary:</strong> ' + (esc(p.notary_name) || '—') + ' | <strong>Date:</strong> ' + fmtDate(p.notary_date) +
    '</div>' : '') +
    (p.revoked_date ? '<div style="background:#FEF2F2;padding:12px;border-radius:8px;border:1px solid #FECACA;font-size:13px">' +
      '<strong>Revoked:</strong> ' + fmtDate(p.revoked_date) + (p.revoked_reason ? ' | <strong>Reason:</strong> ' + esc(p.revoked_reason) : '') +
    '</div>' : '');
  openModal('modal-poa-detail');
}

// ===== Render: Contracts =====
// Contract folder type mapping
const contractFolderMap = {
  'employee': { types: ['employment', 'nda'], title: '👤 Employee Contracts', color: '#059669' },
  'vendor':   { types: ['vendor', 'service', 'lease', 'loan'], title: '🏢 Vendor Contracts', color: '#3B82F6' },
  'partnership': { types: ['partnership', 'other'], title: '🤝 Partnership Contracts', color: '#8B5CF6' }
};
let activeContractFolder = null;

function updateContractFolderCounts() {
  const empCount = contracts.filter(c => contractFolderMap.employee.types.includes(c.contract_type)).length;
  const vendCount = contracts.filter(c => contractFolderMap.vendor.types.includes(c.contract_type)).length;
  const partCount = contracts.filter(c => contractFolderMap.partnership.types.includes(c.contract_type)).length;
  document.getElementById('contract-count-employee').textContent = empCount + ' contract' + (empCount !== 1 ? 's' : '');
  document.getElementById('contract-count-vendor').textContent = vendCount + ' contract' + (vendCount !== 1 ? 's' : '');
  document.getElementById('contract-count-partnership').textContent = partCount + ' contract' + (partCount !== 1 ? 's' : '');
}

function showContractFolder(folderKey) {
  activeContractFolder = folderKey;
  const folder = contractFolderMap[folderKey];
  if (!folder) return;
  document.getElementById('contract-folders-grid').style.display = 'none';
  document.getElementById('contract-folder-panel').style.display = 'block';
  document.getElementById('contract-folder-title').textContent = folder.title;
  renderContracts();
}

function closeContractFolder() {
  activeContractFolder = null;
  document.getElementById('contract-folders-grid').style.display = 'grid';
  document.getElementById('contract-folder-panel').style.display = 'none';
}

function renderContracts() {
  const tb = document.getElementById('contracts-tbody');
  updateContractFolderCounts();
  let filtered = contracts;
  if (activeContractFolder && contractFolderMap[activeContractFolder]) {
    filtered = contracts.filter(c => contractFolderMap[activeContractFolder].types.includes(c.contract_type));
  }
  if (!filtered.length) { tb.innerHTML = emptyRow(10, 'No contracts found in this folder'); return; }
  tb.innerHTML = filtered.map(function(c) {
    var idx = contracts.indexOf(c);
    return '<tr>' +
      '<td><span class="secure-text" style="font-size:11px">' + esc(c.contract_number) + '</span></td>' +
      '<td style="font-weight:600;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">' + esc(c.title) + '</td>' +
      '<td>' + labelCase(c.contract_type) + '</td>' +
      '<td>' + esc(c.party_name) + '</td>' +
      '<td>' + money(c.value) + '</td>' +
      '<td>' + fmtDate(c.start_date) + '</td>' +
      '<td>' + fmtDate(c.end_date) + '</td>' +
      '<td>' + contractStatusBadge(c.status) + '</td>' +
      '<td>' + (esc(c.assigned_name) || '—') + '</td>' +
      '<td><button class="btn btn-outline btn-sm" onclick="viewContract(' + idx + ')">View</button></td>' +
    '</tr>';
  }).join('');
}

// ===== View: Contract =====
function viewContract(idx) {
  const c = contracts[idx];
  if (!c) return;
  document.getElementById('contract-detail-body').innerHTML =
    '<div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:16px;flex-wrap:wrap;gap:12px">' +
      '<div>' +
        '<div style="font-weight:700;font-size:16px;color:#1F2937">' + esc(c.contract_number) + '</div>' +
        '<div style="font-weight:600;font-size:14px;color:#374151;margin-top:2px">' + esc(c.title) + '</div>' +
      '</div>' +
      '<div>' + contractStatusBadge(c.status) + '</div>' +
    '</div>' +
    '<div class="grid-2" style="gap:12px;margin-bottom:16px">' +
      '<div style="background:#F9FAFB;padding:12px;border-radius:8px">' +
        '<div style="font-size:11px;color:#9CA3AF;text-transform:uppercase;font-weight:600">Contract Info</div>' +
        '<div style="font-size:13px;margin-top:4px"><strong>Type:</strong> ' + labelCase(c.contract_type) + '</div>' +
        '<div style="font-size:13px"><strong>Party:</strong> ' + esc(c.party_name) + '</div>' +
        (c.party_contact ? '<div style="font-size:13px"><strong>Contact:</strong> ' + esc(c.party_contact) + '</div>' : '') +
        '<div style="font-size:13px"><strong>Value:</strong> ' + money(c.value) + ' ' + esc(c.currency || 'PHP') + '</div>' +
        '<div style="font-size:13px"><strong>Auto-Renew:</strong> ' + (c.auto_renew ? '✅ Yes' : 'No') + '</div>' +
      '</div>' +
      '<div style="background:#F9FAFB;padding:12px;border-radius:8px">' +
        '<div style="font-size:11px;color:#9CA3AF;text-transform:uppercase;font-weight:600">Duration</div>' +
        '<div style="font-size:13px;margin-top:4px"><strong>Start:</strong> ' + fmtDate(c.start_date) + '</div>' +
        '<div style="font-size:13px"><strong>End:</strong> ' + fmtDate(c.end_date) + '</div>' +
        (c.renewal_notice_days ? '<div style="font-size:13px"><strong>Renewal Notice:</strong> ' + c.renewal_notice_days + ' days</div>' : '') +
        (c.assigned_name ? '<div style="font-size:13px"><strong>Assigned To:</strong> ' + esc(c.assigned_name) + '</div>' : '') +
      '</div>' +
    '</div>' +
    (c.description ? '<div style="background:#F9FAFB;padding:12px;border-radius:8px">' +
      '<div style="font-size:11px;color:#9CA3AF;text-transform:uppercase;font-weight:600">Description</div>' +
      '<div style="font-size:13px;margin-top:4px;white-space:pre-wrap">' + esc(c.description) + '</div>' +
    '</div>' : '');
  openModal('modal-contract-detail');
}

// ===== Render: Permits =====
function renderPermits() {
  const tb = document.getElementById('permits-tbody');
  if (!permits.length) { tb.innerHTML = emptyRow(10, 'No permits found'); return; }
  tb.innerHTML = permits.map(function(p, i) {
    var expiring = isExpiringSoon(p.expiry_date);
    return '<tr' + (expiring ? ' style="background:#FFFBEB"' : '') + '>' +
      '<td><span class="secure-text" style="font-size:11px">' + esc(p.permit_code) + '</span></td>' +
      '<td style="font-weight:600">' + esc(p.permit_name) + '</td>' +
      '<td>' + esc(p.issuing_body) + '</td>' +
      '<td>' + labelCase(p.permit_type) + '</td>' +
      '<td>' + (esc(p.permit_number) || '—') + '</td>' +
      '<td>' + fmtDate(p.issue_date) + '</td>' +
      '<td>' + (expiring ? '<span class="badge badge-amber">⚠ ' + fmtDate(p.expiry_date) + '</span>' : fmtDate(p.expiry_date)) + '</td>' +
      '<td>' + money(p.renewal_fee) + '</td>' +
      '<td>' + permitStatusBadge(p.status) + '</td>' +
      '<td><button class="btn btn-outline btn-sm" onclick="viewPermit(' + i + ')">View</button></td>' +
    '</tr>';
  }).join('');
}

// ===== View: Permit =====
function viewPermit(idx) {
  const p = permits[idx];
  if (!p) return;
  document.getElementById('permit-detail-body').innerHTML =
    '<div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:16px;flex-wrap:wrap;gap:12px">' +
      '<div>' +
        '<div style="font-weight:700;font-size:16px;color:#1F2937">' + esc(p.permit_code) + '</div>' +
        '<div style="font-weight:600;font-size:14px;color:#374151;margin-top:2px">' + esc(p.permit_name) + '</div>' +
      '</div>' +
      '<div style="display:flex;gap:8px;flex-wrap:wrap">' +
        (isExpiringSoon(p.expiry_date) ? '<span class="badge badge-amber">⚠ Expiring Soon</span> ' : '') +
        permitStatusBadge(p.status) +
      '</div>' +
    '</div>' +
    '<div class="grid-2" style="gap:12px;margin-bottom:16px">' +
      '<div style="background:#F9FAFB;padding:12px;border-radius:8px">' +
        '<div style="font-size:11px;color:#9CA3AF;text-transform:uppercase;font-weight:600">Permit Info</div>' +
        '<div style="font-size:13px;margin-top:4px"><strong>Issuing Body:</strong> ' + esc(p.issuing_body) + '</div>' +
        '<div style="font-size:13px"><strong>Type:</strong> ' + labelCase(p.permit_type) + '</div>' +
        '<div style="font-size:13px"><strong>Permit #:</strong> ' + (esc(p.permit_number) || '—') + '</div>' +
        '<div style="font-size:13px"><strong>Renewal Fee:</strong> ' + money(p.renewal_fee) + '</div>' +
      '</div>' +
      '<div style="background:#F9FAFB;padding:12px;border-radius:8px">' +
        '<div style="font-size:11px;color:#9CA3AF;text-transform:uppercase;font-weight:600">Validity</div>' +
        '<div style="font-size:13px;margin-top:4px"><strong>Issued:</strong> ' + fmtDate(p.issue_date) + '</div>' +
        '<div style="font-size:13px"><strong>Expiry:</strong> ' + fmtDate(p.expiry_date) + '</div>' +
        (isExpiringSoon(p.expiry_date) ? '<div style="margin-top:8px;padding:6px 10px;background:#FEF3C7;border-radius:6px;font-size:12px;color:#92400E">⚠ This permit expires within 90 days. Please initiate renewal.</div>' : '') +
      '</div>' +
    '</div>' +
    (p.notes ? '<div style="font-size:13px;color:#6B7280"><strong>Notes:</strong> ' + esc(p.notes) + '</div>' : '');
  openModal('modal-permit-detail');
}

// ═══════════════════════════════════════════════════════
// LEGAL CALENDAR
// ═══════════════════════════════════════════════════════

let legalCalYear = new Date().getFullYear();
let legalCalMonth = new Date().getMonth();

function legalCalNav(dir) {
  legalCalMonth += dir;
  if (legalCalMonth > 11) { legalCalMonth = 0; legalCalYear++; }
  if (legalCalMonth < 0) { legalCalMonth = 11; legalCalYear--; }
  renderLegalCalendar();
}

function legalCalToday() {
  legalCalYear = new Date().getFullYear();
  legalCalMonth = new Date().getMonth();
  renderLegalCalendar();
}

function getLegalEvents() {
  const events = [];
  // Cases: filing dates, next hearing
  (cases || []).forEach(c => {
    if (c.filing_date) events.push({ date: c.filing_date.slice(0,10), type: 'case', color: '#EF4444', label: c.title || c.case_number, detail: c });
    if (c.next_hearing) events.push({ date: c.next_hearing.slice(0,10), type: 'case', color: '#EF4444', label: '🔔 Hearing: ' + (c.title || c.case_number), detail: c });
  });
  // Contracts: start/end dates
  (contracts || []).forEach(c => {
    if (c.start_date) events.push({ date: c.start_date.slice(0,10), type: 'contract', color: '#3B82F6', label: '📝 Start: ' + (c.title || c.contract_number), detail: c });
    if (c.end_date) events.push({ date: c.end_date.slice(0,10), type: 'contract', color: '#3B82F6', label: '📝 End: ' + (c.title || c.contract_number), detail: c });
  });
  // Compliance: deadlines
  (compliance || []).forEach(c => {
    if (c.deadline) events.push({ date: c.deadline.slice(0,10), type: 'compliance', color: '#059669', label: '✅ ' + (c.requirement || c.compliance_code), detail: c });
  });
  // Permits: expiry dates
  (permits || []).forEach(p => {
    if (p.expiry_date) events.push({ date: p.expiry_date.slice(0,10), type: 'permit', color: '#F59E0B', label: '📜 Expiry: ' + (p.permit_name || p.permit_code), detail: p });
  });
  // Demands: response deadlines
  (demands || []).forEach(d => {
    if (d.response_deadline) events.push({ date: d.response_deadline.slice(0,10), type: 'case', color: '#EF4444', label: '⚠ Demand deadline: ' + (d.borrower_name || d.demand_code), detail: d });
  });
  // Board resolutions: meeting dates
  (resolutions || []).forEach(r => {
    if (r.meeting_date) events.push({ date: r.meeting_date.slice(0,10), type: 'compliance', color: '#059669', label: '📋 Board: ' + (r.title || r.resolution_code), detail: r });
  });
  return events;
}

function renderLegalCalendar() {
  const grid = document.getElementById('legal-calendar-grid');
  const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
  document.getElementById('legal-cal-title').textContent = monthNames[legalCalMonth] + ' ' + legalCalYear;

  const events = getLegalEvents();
  const firstDay = new Date(legalCalYear, legalCalMonth, 1).getDay();
  const daysInMonth = new Date(legalCalYear, legalCalMonth + 1, 0).getDate();
  const today = new Date();
  const todayStr = today.getFullYear() + '-' + String(today.getMonth()+1).padStart(2,'0') + '-' + String(today.getDate()).padStart(2,'0');

  let html = '';
  // Day headers
  ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'].forEach(d => {
    html += '<div style="background:#F9FAFB;padding:8px;text-align:center;font-size:11px;font-weight:700;color:#6B7280">' + d + '</div>';
  });

  // Empty cells before first day
  for (let i = 0; i < firstDay; i++) {
    html += '<div style="background:white;padding:8px;min-height:80px"></div>';
  }

  // Day cells
  for (let day = 1; day <= daysInMonth; day++) {
    const dateStr = legalCalYear + '-' + String(legalCalMonth+1).padStart(2,'0') + '-' + String(day).padStart(2,'0');
    const dayEvents = events.filter(e => e.date === dateStr);
    const isToday = dateStr === todayStr;

    html += '<div style="background:white;padding:6px;min-height:80px;cursor:' + (dayEvents.length ? 'pointer' : 'default') + ';' + (isToday ? 'border:2px solid #059669;' : '') + '" onclick="showLegalDayEvents(\'' + dateStr + '\')">';
    html += '<div style="font-size:12px;font-weight:' + (isToday ? '800' : '600') + ';color:' + (isToday ? '#059669' : '#1F2937') + ';margin-bottom:4px">' + day + '</div>';
    // Show dots for events
    if (dayEvents.length > 0) {
      html += '<div style="display:flex;flex-wrap:wrap;gap:2px">';
      dayEvents.slice(0, 3).forEach(e => {
        html += '<div style="width:100%;font-size:9px;padding:1px 4px;border-radius:3px;background:' + e.color + '20;color:' + e.color + ';overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-weight:600">' + esc(e.label.substring(0, 25)) + '</div>';
      });
      if (dayEvents.length > 3) {
        html += '<div style="font-size:9px;color:#6B7280;font-weight:600">+' + (dayEvents.length - 3) + ' more</div>';
      }
      html += '</div>';
    }
    html += '</div>';
  }

  grid.innerHTML = html;
}

function showLegalDayEvents(dateStr) {
  const events = getLegalEvents().filter(e => e.date === dateStr);
  if (!events.length) return;

  const panel = document.getElementById('legal-day-panel');
  panel.style.display = 'block';
  document.getElementById('legal-day-title').textContent = '📅 ' + fmtDate(dateStr) + ' — ' + events.length + ' record(s)';

  let html = '';
  events.forEach(e => {
    const colorMap = { case: '#EF4444', contract: '#3B82F6', compliance: '#059669', permit: '#F59E0B' };
    const typeLabel = { case: 'Case/Litigation', contract: 'Contract', compliance: 'Compliance/Governance', permit: 'Permit/License' };
    html += '<div style="border:1px solid #E5E7EB;border-radius:10px;padding:12px 16px;margin-bottom:8px;border-left:4px solid ' + (colorMap[e.type] || '#6B7280') + '">';
    html += '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">';
    html += '<span style="font-weight:700;font-size:13px;color:#1F2937">' + esc(e.label) + '</span>';
    html += '<span class="badge" style="background:' + (colorMap[e.type] || '#6B7280') + '20;color:' + (colorMap[e.type] || '#6B7280') + ';font-size:10px">' + (typeLabel[e.type] || e.type) + '</span>';
    html += '</div>';
    // Show key details based on type
    const d = e.detail;
    if (e.type === 'case' && d) {
      html += '<div style="font-size:12px;color:#6B7280">';
      if (d.case_number) html += '<strong>#</strong> ' + esc(d.case_number) + ' · ';
      if (d.status) html += 'Status: ' + esc(d.status) + ' · ';
      if (d.opposing_party) html += 'vs. ' + esc(d.opposing_party);
      html += '</div>';
    } else if (e.type === 'contract' && d) {
      html += '<div style="font-size:12px;color:#6B7280">';
      if (d.contract_number) html += '<strong>#</strong> ' + esc(d.contract_number) + ' · ';
      if (d.party_name) html += 'Party: ' + esc(d.party_name) + ' · ';
      if (d.status) html += 'Status: ' + esc(d.status);
      html += '</div>';
    } else if (e.type === 'compliance' && d) {
      html += '<div style="font-size:12px;color:#6B7280">';
      if (d.regulatory_body) html += 'Body: ' + esc(d.regulatory_body) + ' · ';
      if (d.status) html += 'Status: ' + esc(d.status);
      if (d.resolution_code) html += '<strong>#</strong> ' + esc(d.resolution_code) + ' · Type: ' + esc(d.resolution_type || '');
      html += '</div>';
    } else if (e.type === 'permit' && d) {
      html += '<div style="font-size:12px;color:#6B7280">';
      if (d.issuing_body) html += 'Issuer: ' + esc(d.issuing_body) + ' · ';
      if (d.permit_number) html += 'Permit #' + esc(d.permit_number) + ' · ';
      if (d.status) html += 'Status: ' + esc(d.status);
      html += '</div>';
    }
    html += '</div>';
  });

  document.getElementById('legal-day-body').innerHTML = html;
  panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// ===== Section Switching (hash-driven) =====
function showSection(hash) {
  var sections = document.querySelectorAll('.tab-content');
  var id = hash ? hash.replace('#', '') : 'tab-loans';
  sections.forEach(function(s) { s.classList.remove('active'); });
  var target = document.getElementById(id);
  if (target) target.classList.add('active');
  else if (sections[0]) sections[0].classList.add('active');
  if (id === 'tab-legal-calendar') renderLegalCalendar();
}
window.addEventListener('hashchange', function() { showSection(location.hash); });

// ===== Init =====
document.addEventListener('DOMContentLoaded', function() {
  loadData().then(function() {
    showSection(location.hash);
  });
});

// ===== Export Functions =====
function exportLoans(format) {
  const headers = ['Code', 'Borrower', 'Amount', 'Interest', 'Term', 'Security Type', 'Status', 'Attorney'];
  const rows = loans.map(l => [
    l.loan_code || '', l.borrower_name || '', money(l.loan_amount), (l.interest_rate || '') + '%',
    l.loan_term || '', l.security_type || '', l.status || '', l.assigned_attorney || ''
  ]);
  format === 'csv' ? ExportHelper.exportCSV('Legal_Loans', headers, rows)
    : ExportHelper.exportPDF('Legal_Loans', 'Legal — Loan Documentation', headers, rows, { landscape: true, subtitle: loans.length + ' records' });
}

function exportCollaterals(format) {
  const headers = ['Code', 'Borrower', 'Type', 'Description', 'Appraised Value', 'Lien Status', 'Insurance Expiry'];
  const rows = collaterals.map(c => [
    c.collateral_code || '', c.borrower_name || '', c.collateral_type || '', c.description || '',
    money(c.appraised_value), c.lien_status || '', fmtDate(c.insurance_expiry)
  ]);
  format === 'csv' ? ExportHelper.exportCSV('Legal_Collaterals', headers, rows)
    : ExportHelper.exportPDF('Legal_Collaterals', 'Legal — Collateral Registry', headers, rows, { landscape: true, subtitle: collaterals.length + ' records' });
}

function exportDemands(format) {
  const headers = ['Code', 'Borrower', 'Amount', 'Type', 'Status', 'Sent Date', 'Response Deadline'];
  const rows = demands.map(d => [
    d.demand_code || '', d.borrower_name || '', money(d.demand_amount), d.demand_type || '',
    d.status || '', fmtDate(d.sent_date), fmtDate(d.response_deadline)
  ]);
  format === 'csv' ? ExportHelper.exportCSV('Legal_Demands', headers, rows)
    : ExportHelper.exportPDF('Legal_Demands', 'Legal — Demand Letters', headers, rows, { subtitle: demands.length + ' records' });
}

function exportCases(format) {
  const headers = ['Case #', 'Title', 'Type', 'Priority', 'Status', 'Opposing Party', 'Financial Impact', 'Assigned'];
  const rows = cases.map(c => [
    c.case_number || '', c.title || '', c.case_type || '', c.priority || '',
    c.status || '', c.opposing_party || '', money(c.financial_impact), c.assigned_attorney || ''
  ]);
  format === 'csv' ? ExportHelper.exportCSV('Legal_Cases', headers, rows)
    : ExportHelper.exportPDF('Legal_Cases', 'Legal — Legal Cases', headers, rows, { landscape: true, subtitle: cases.length + ' records' });
}

function exportKYC(format) {
  const headers = ['Code', 'Client', 'Type', 'ID Type', 'Risk Rating', 'Verification Status', 'Next Review'];
  const rows = kyc.map(k => [
    k.kyc_code || '', k.client_name || '', k.client_type || '', k.id_type || '',
    k.risk_rating || '', k.verification_status || '', fmtDate(k.next_review_date)
  ]);
  format === 'csv' ? ExportHelper.exportCSV('Legal_KYC', headers, rows)
    : ExportHelper.exportPDF('Legal_KYC', 'Legal — KYC Records', headers, rows, { subtitle: kyc.length + ' records' });
}

function exportCompliance(format) {
  const headers = ['Code', 'Requirement', 'Body', 'Status', 'Deadline', 'Assigned'];
  const rows = compliance.map(c => [
    c.compliance_code || '', c.requirement || '', c.regulatory_body || '',
    c.status || '', fmtDate(c.deadline), c.assigned_to || ''
  ]);
  format === 'csv' ? ExportHelper.exportCSV('Legal_Compliance', headers, rows)
    : ExportHelper.exportPDF('Legal_Compliance', 'Legal — Regulatory Compliance', headers, rows, { subtitle: compliance.length + ' records' });
}

function exportResolutions(format) {
  const headers = ['Code', 'Title', 'Type', 'Meeting Date', 'Meeting Type', 'Votes For', 'Votes Against', 'Abstain', 'Passed', 'Status'];
  const rows = resolutions.map(r => [
    r.resolution_code || '', r.title || '', r.resolution_type || '', fmtDate(r.meeting_date),
    r.meeting_type || '', r.votes_for || 0, r.votes_against || 0, r.votes_abstain || 0,
    r.passed ? 'Yes' : 'No', r.status || ''
  ]);
  format === 'csv' ? ExportHelper.exportCSV('Legal_Resolutions', headers, rows)
    : ExportHelper.exportPDF('Legal_Resolutions', 'Legal — Board Resolutions', headers, rows, { landscape: true, subtitle: resolutions.length + ' records' });
}

function exportPOA(format) {
  const headers = ['Code', 'Principal', 'Agent', 'Type', 'Scope', 'Effective Date', 'Expiry', 'Notarized', 'Status'];
  const rows = poa.map(p => [
    p.poa_code || '', p.principal_name || '', p.agent_name || '', p.poa_type || '',
    p.scope || '', fmtDate(p.effective_date), fmtDate(p.expiry_date),
    p.notarized ? 'Yes' : 'No', p.status || ''
  ]);
  format === 'csv' ? ExportHelper.exportCSV('Legal_POA', headers, rows)
    : ExportHelper.exportPDF('Legal_POA', 'Legal — Power of Attorney', headers, rows, { landscape: true, subtitle: poa.length + ' records' });
}

function exportContracts(format) {
  const headers = ['Contract #', 'Title', 'Type', 'Party', 'Value', 'Start', 'End', 'Status', 'Assigned'];
  const rows = contracts.map(c => [
    c.contract_number || '', c.title || '', c.contract_type || '', c.party_name || '',
    money(c.contract_value), fmtDate(c.start_date), fmtDate(c.end_date), c.status || '', c.assigned_to || ''
  ]);
  format === 'csv' ? ExportHelper.exportCSV('Legal_Contracts', headers, rows)
    : ExportHelper.exportPDF('Legal_Contracts', 'Legal — Contracts & Agreements', headers, rows, { landscape: true, subtitle: contracts.length + ' records' });
}

function exportPermits(format) {
  const headers = ['Code', 'Permit Name', 'Issuing Body', 'Type', 'Permit #', 'Issue Date', 'Expiry', 'Fee', 'Status'];
  const rows = permits.map(p => [
    p.permit_code || '', p.permit_name || '', p.issuing_body || '', p.permit_type || '',
    p.permit_number || '', fmtDate(p.issue_date), fmtDate(p.expiry_date), money(p.renewal_fee), p.status || ''
  ]);
  format === 'csv' ? ExportHelper.exportCSV('Legal_Permits', headers, rows)
    : ExportHelper.exportPDF('Legal_Permits', 'Legal — Permits & Licenses', headers, rows, { landscape: true, subtitle: permits.length + ' records' });
}

// ═══════════════════════════════════════════════════════
// LOAN CONTRACT PDF EXPORT
// ═══════════════════════════════════════════════════════
function exportLoanContractPDF() {
  const l = loans[viewLoanContractIdx];
  if (!l) { Swal.fire({ icon: 'warning', title: 'No Data', text: 'No loan contract selected.', confirmButtonColor: '#059669' }); return; }

  const { jsPDF } = window.jspdf;
  const doc = new jsPDF('p', 'mm', 'a4');
  const pageW = doc.internal.pageSize.getWidth();
  const pageH = doc.internal.pageSize.getHeight();
  const marginL = 20, marginR = 20, contentW = pageW - marginL - marginR;
  let y = 20;

  const brandColor = [5, 150, 105];       // #059669
  const darkColor  = [31, 41, 55];        // #1F2937
  const grayColor  = [107, 114, 128];     // #6B7280
  const lightGray  = [229, 231, 235];     // #E5E7EB

  function checkPage(needed) {
    if (y + needed > pageH - 25) {
      // Footer on current page
      drawFooter(doc);
      doc.addPage();
      y = 20;
    }
  }

  function drawFooter(d) {
    d.setDrawColor(...lightGray);
    d.line(marginL, pageH - 18, pageW - marginR, pageH - 18);
    d.setFont('helvetica', 'normal');
    d.setFontSize(8);
    d.setTextColor(...grayColor);
    d.text('Microfinancial Management System — Confidential', marginL, pageH - 12);
    d.text('Page ' + d.internal.getCurrentPageInfo().pageNumber, pageW - marginR, pageH - 12, { align: 'right' });
  }

  function fmtDatePdf(d) {
    if (!d) return '—';
    const dt = new Date(d);
    if (isNaN(dt)) return d;
    return dt.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
  }

  function moneyPdf(v) {
    const n = parseFloat(v);
    if (isNaN(n)) return '₱0.00';
    return '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  // ─── HEADER BANNER ───
  doc.setFillColor(...brandColor);
  doc.rect(0, 0, pageW, 40, 'F');

  doc.setFont('helvetica', 'bold');
  doc.setFontSize(22);
  doc.setTextColor(255, 255, 255);
  doc.text('LOAN AGREEMENT / CONTRACT', pageW / 2, 18, { align: 'center' });

  doc.setFontSize(10);
  doc.setFont('helvetica', 'normal');
  doc.text('Microfinancial Management System', pageW / 2, 27, { align: 'center' });
  doc.text('Reference: ' + (l.loan_doc_code || '—'), pageW / 2, 34, { align: 'center' });

  y = 50;

  // ─── DOCUMENT INFO ROW ───
  doc.setFillColor(240, 253, 244); // #F0FDF4
  doc.roundedRect(marginL, y, contentW, 16, 3, 3, 'F');
  doc.setFont('helvetica', 'normal');
  doc.setFontSize(9);
  doc.setTextColor(...grayColor);
  doc.text('Document Code: ' + (l.loan_doc_code || '—'), marginL + 6, y + 6);
  doc.text('Status: ' + (l.status ? l.status.replace(/_/g, ' ').toUpperCase() : '—'), marginL + 6, y + 12);
  doc.text('Generated: ' + new Date().toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' }), pageW - marginR - 6, y + 6, { align: 'right' });
  y += 22;

  // ─── BORROWER SECTION ───
  doc.setFont('helvetica', 'bold');
  doc.setFontSize(12);
  doc.setTextColor(...darkColor);
  doc.text('BORROWER INFORMATION', marginL, y);
  y += 2;
  doc.setDrawColor(...brandColor);
  doc.setLineWidth(0.8);
  doc.line(marginL, y, marginL + 60, y);
  y += 8;

  doc.setFont('helvetica', 'normal');
  doc.setFontSize(10);
  doc.setTextColor(...darkColor);
  doc.text('Name:', marginL, y);
  doc.setFont('helvetica', 'bold');
  doc.text(l.borrower_name || '—', marginL + 30, y);
  y += 6;

  if (l.borrower_address) {
    doc.setFont('helvetica', 'normal');
    doc.text('Address:', marginL, y);
    doc.setFont('helvetica', 'bold');
    const addrLines = doc.splitTextToSize(l.borrower_address, contentW - 35);
    doc.text(addrLines, marginL + 30, y);
    y += addrLines.length * 5 + 2;
  }
  y += 6;

  // ─── LOAN TERMS TABLE ───
  checkPage(50);
  doc.setFont('helvetica', 'bold');
  doc.setFontSize(12);
  doc.setTextColor(...darkColor);
  doc.text('LOAN TERMS', marginL, y);
  y += 2;
  doc.setDrawColor(...brandColor);
  doc.setLineWidth(0.8);
  doc.line(marginL, y, marginL + 40, y);
  y += 6;

  const termsData = [
    ['Loan Amount', moneyPdf(l.loan_amount)],
    ['Interest Rate', parseFloat(l.interest_rate || 0).toFixed(2) + '% per annum'],
    ['Loan Term', (l.loan_term_months || 0) + ' months'],
    ['Repayment Schedule', (l.repayment_schedule || '').replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())],
    ['Security Type', (l.security_type || '').replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())],
    ['Penalty Rate', parseFloat(l.penalty_rate || 0).toFixed(2) + '% per month (late payment)']
  ];
  if (l.purpose) termsData.push(['Purpose', l.purpose]);

  doc.autoTable({
    startY: y,
    head: [],
    body: termsData,
    theme: 'plain',
    margin: { left: marginL, right: marginR },
    styles: { fontSize: 10, cellPadding: { top: 3, bottom: 3, left: 6, right: 6 }, textColor: darkColor },
    columnStyles: {
      0: { fontStyle: 'bold', cellWidth: 50, textColor: grayColor },
      1: { fontStyle: 'normal' }
    },
    alternateRowStyles: { fillColor: [249, 250, 251] },
    didDrawPage: function() {}
  });
  y = doc.lastAutoTable.finalY + 8;

  // ─── KEY DATES TABLE ───
  checkPage(40);
  doc.setFont('helvetica', 'bold');
  doc.setFontSize(12);
  doc.setTextColor(...darkColor);
  doc.text('KEY DATES', marginL, y);
  y += 2;
  doc.setDrawColor(...brandColor);
  doc.setLineWidth(0.8);
  doc.line(marginL, y, marginL + 35, y);
  y += 6;

  const datesData = [
    ['Date Signed', fmtDatePdf(l.signed_date)],
    ['Effective Date', fmtDatePdf(l.effective_date)],
    ['Maturity Date', fmtDatePdf(l.maturity_date)]
  ];

  doc.autoTable({
    startY: y,
    head: [],
    body: datesData,
    theme: 'plain',
    margin: { left: marginL, right: marginR },
    styles: { fontSize: 10, cellPadding: { top: 3, bottom: 3, left: 6, right: 6 }, textColor: darkColor },
    columnStyles: {
      0: { fontStyle: 'bold', cellWidth: 50, textColor: grayColor },
      1: { fontStyle: 'normal' }
    },
    alternateRowStyles: { fillColor: [249, 250, 251] }
  });
  y = doc.lastAutoTable.finalY + 10;

  // ─── CONTRACT BODY ───
  if (l.contract_body) {
    checkPage(30);
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(12);
    doc.setTextColor(...darkColor);
    doc.text('CONTRACT BODY', marginL, y);
    y += 2;
    doc.setDrawColor(...brandColor);
    doc.setLineWidth(0.8);
    doc.line(marginL, y, marginL + 45, y);
    y += 8;

    doc.setFont('times', 'normal');
    doc.setFontSize(10);
    doc.setTextColor(...darkColor);
    const bodyLines = doc.splitTextToSize(l.contract_body, contentW - 10);
    const lineHeight = 5;

    for (let i = 0; i < bodyLines.length; i++) {
      checkPage(lineHeight + 2);
      doc.text(bodyLines[i], marginL + 5, y);
      y += lineHeight;
    }
    y += 8;
  }

  // ─── DISCLOSURE STATEMENT ───
  if (l.disclosure_statement) {
    checkPage(30);
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(11);
    doc.setTextColor(...darkColor);
    doc.text('TRUTH IN LENDING DISCLOSURE', marginL, y);
    y += 2;
    doc.setDrawColor(...brandColor);
    doc.setLineWidth(0.5);
    doc.line(marginL, y, marginL + 65, y);
    y += 8;

    doc.setFont('times', 'normal');
    doc.setFontSize(10);
    doc.setTextColor(...darkColor);
    const discLines = doc.splitTextToSize(l.disclosure_statement, contentW - 10);
    const lineHeight = 5;

    for (let i = 0; i < discLines.length; i++) {
      checkPage(lineHeight + 2);
      doc.text(discLines[i], marginL + 5, y);
      y += lineHeight;
    }
    y += 8;
  }

  // ─── PROMISSORY NOTE ───
  if (l.promissory_note) {
    checkPage(30);
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(11);
    doc.setTextColor(...darkColor);
    doc.text('PROMISSORY NOTE', marginL, y);
    y += 2;
    doc.setDrawColor(...brandColor);
    doc.setLineWidth(0.5);
    doc.line(marginL, y, marginL + 45, y);
    y += 8;

    doc.setFont('times', 'normal');
    doc.setFontSize(10);
    doc.setTextColor(...darkColor);
    const noteLines = doc.splitTextToSize(l.promissory_note, contentW - 10);
    const lineHeight = 5;

    for (let i = 0; i < noteLines.length; i++) {
      checkPage(lineHeight + 2);
      doc.text(noteLines[i], marginL + 5, y);
      y += lineHeight;
    }
    y += 8;
  }

  // ─── ATTORNEY CREDENTIALS & SIGNATURE ───
  if (l.attorney_name) {
    checkPage(80);

    doc.setFont('helvetica', 'bold');
    doc.setFontSize(12);
    doc.setTextColor(...darkColor);
    doc.text('ATTORNEY CREDENTIALS', marginL, y);
    y += 2;
    doc.setDrawColor(...brandColor);
    doc.setLineWidth(0.8);
    doc.line(marginL, y, marginL + 55, y);
    y += 4;

    // Green background box for attorney details
    const attyBoxH = 42;
    checkPage(attyBoxH + 10);
    doc.setFillColor(240, 253, 244); // #F0FDF4
    doc.setDrawColor(167, 243, 208); // #A7F3D0
    doc.setLineWidth(0.5);
    doc.roundedRect(marginL, y, contentW, attyBoxH, 3, 3, 'FD');

    y += 8;
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(10);
    doc.setTextColor(6, 95, 70);       // #065F46

    const col1X = marginL + 8;
    const col2X = marginL + contentW / 2 + 5;
    const rowH = 6;

    doc.setFont('helvetica', 'bold');
    doc.text('Name:', col1X, y);
    doc.setFont('helvetica', 'normal');
    doc.text(l.attorney_name || '—', col1X + 22, y);

    doc.setFont('helvetica', 'bold');
    doc.text('PRC No:', col2X, y);
    doc.setFont('helvetica', 'normal');
    doc.text(l.attorney_prc || '—', col2X + 22, y);
    y += rowH;

    doc.setFont('helvetica', 'bold');
    doc.text('PTR No:', col1X, y);
    doc.setFont('helvetica', 'normal');
    doc.text(l.attorney_ptr || '—', col1X + 22, y);

    doc.setFont('helvetica', 'bold');
    doc.text('IBP No:', col2X, y);
    doc.setFont('helvetica', 'normal');
    doc.text(l.attorney_ibp || '—', col2X + 22, y);
    y += rowH;

    doc.setFont('helvetica', 'bold');
    doc.text('Roll No:', col1X, y);
    doc.setFont('helvetica', 'normal');
    doc.text(l.attorney_roll || '—', col1X + 22, y);

    doc.setFont('helvetica', 'bold');
    doc.text('MCLE No:', col2X, y);
    doc.setFont('helvetica', 'normal');
    doc.text(l.attorney_mcle || '—', col2X + 25, y);
    y += rowH;

    y += (attyBoxH - 3 * rowH - 8) + 4;

    // ─── ATTORNEY SIGNATURE ───
    checkPage(50);
    y += 8;
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(10);
    doc.setTextColor(...darkColor);
    doc.text('Attorney Signature:', marginL, y);
    y += 4;

    if (l.attorney_signature) {
      try {
        let sigData = l.attorney_signature;
        if (!sigData.startsWith('data:')) {
          sigData = 'data:image/png;base64,' + sigData;
        }
        doc.addImage(sigData, 'PNG', marginL, y, 60, 25);
        y += 28;
      } catch (e) {
        console.warn('Could not add attorney signature image:', e);
        doc.setFont('helvetica', 'italic');
        doc.setFontSize(9);
        doc.setTextColor(...grayColor);
        doc.text('[Signature on file]', marginL, y + 8);
        y += 14;
      }
    } else {
      // Draw a signature line
      doc.setDrawColor(...lightGray);
      doc.setLineWidth(0.5);
      doc.line(marginL, y + 15, marginL + 70, y + 15);
      doc.setFont('helvetica', 'italic');
      doc.setFontSize(8);
      doc.setTextColor(...grayColor);
      doc.text('Signature Over Printed Name', marginL, y + 20);
      y += 24;
    }

    // Attorney printed name
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(10);
    doc.setTextColor(...darkColor);
    doc.text(l.attorney_name, marginL, y);
    y += 5;
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(8);
    doc.setTextColor(...grayColor);
    doc.text('Counsel', marginL, y);
    y += 10;
  }

  // ─── NOTARY SECTION ───
  if (l.notary_name) {
    checkPage(50);

    doc.setFont('helvetica', 'bold');
    doc.setFontSize(12);
    doc.setTextColor(...darkColor);
    doc.text('ACKNOWLEDGMENT / NOTARIAL CERTIFICATE', marginL, y);
    y += 2;
    doc.setDrawColor(...brandColor);
    doc.setLineWidth(0.8);
    doc.line(marginL, y, marginL + 80, y);
    y += 4;

    // Yellow background box for notarial section
    const notaryBoxH = 38;
    checkPage(notaryBoxH + 10);
    doc.setFillColor(255, 251, 235); // #FFFBEB
    doc.setDrawColor(253, 230, 138); // #FDE68A
    doc.setLineWidth(0.5);
    doc.roundedRect(marginL, y, contentW, notaryBoxH, 3, 3, 'FD');

    y += 8;
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(10);
    doc.setTextColor(146, 64, 14);     // #92400E

    const notCol1 = marginL + 8;
    const notCol2 = marginL + contentW / 2 + 5;
    const nRowH = 6;

    doc.setFont('helvetica', 'bold');
    doc.text('Notary Public:', notCol1, y);
    doc.setFont('helvetica', 'normal');
    doc.text(l.notary_name || '—', notCol1 + 35, y);

    doc.setFont('helvetica', 'bold');
    doc.text('Commission:', notCol2, y);
    doc.setFont('helvetica', 'normal');
    doc.text(l.notary_commission || '—', notCol2 + 30, y);
    y += nRowH;

    doc.setFont('helvetica', 'bold');
    doc.text('Doc No:', notCol1, y);
    doc.setFont('helvetica', 'normal');
    doc.text(l.doc_series_no || '—', notCol1 + 35, y);

    doc.setFont('helvetica', 'bold');
    doc.text('Page No:', notCol2, y);
    doc.setFont('helvetica', 'normal');
    doc.text(l.doc_page_no || '—', notCol2 + 30, y);
    y += nRowH;

    doc.setFont('helvetica', 'bold');
    doc.text('Book No:', notCol1, y);
    doc.setFont('helvetica', 'normal');
    doc.text(l.doc_book_no || '—', notCol1 + 35, y);
    y += nRowH;

    y += (notaryBoxH - 3 * nRowH - 8) + 6;
  }

  // ─── FOOTER ON LAST PAGE ───
  drawFooter(doc);

  // Save the file
  const filename = 'Loan_Contract_' + (l.loan_doc_code || 'DRAFT').replace(/[^A-Za-z0-9_-]/g, '_') + '.pdf';
  doc.save(filename);

  Swal.fire({
    icon: 'success',
    title: 'PDF Downloaded',
    text: 'Loan contract PDF has been saved as ' + filename,
    confirmButtonColor: '#059669',
    timer: 3000,
    showConfirmButton: false
  });
}
</script>
</body>
</html>
