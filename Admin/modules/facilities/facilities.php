<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Facilities Reservation — Microfinancial Admin</title>

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
  <style>
    @keyframes pulse-dot { 0%, 100% { opacity: 1; } 50% { opacity: 0.3; } }
  </style>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
</head>

<body class="bg-brand-background-main min-h-screen font-[Inter,'Segoe_UI',system-ui,-apple-system,sans-serif]">

  <?php $activePage = 'facilities'; $baseUrl = '../../'; include '../../sidebar.php'; ?>

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
            <div class="w-10 h-10 rounded-full bg-white shadow group-hover:shadow-md transition-shadow overflow-hidden flex items-center justify-center border border-gray-100">
              <div class="w-full h-full flex items-center justify-center font-bold text-brand-primary bg-emerald-50"><?= $userInitial ?></div>
            </div>
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
        <h1 class="page-title">Facilities Reservation</h1>
        <p class="page-subtitle">Room monitoring, bookings, VIP reservations, emergency meetings, equipment &amp; calendar scheduling</p>
      </div>

      <!-- STAT CARDS -->
      <div class="stats-grid animate-in delay-1">
        <div class="stat-card"><div class="stat-icon green">🏢</div><div class="stat-info"><div class="stat-value" id="stat-total">0</div><div class="stat-label">Total Facilities</div></div></div>
        <div class="stat-card"><div class="stat-icon blue">✅</div><div class="stat-info"><div class="stat-value" id="stat-available">0</div><div class="stat-label">Available Now</div></div></div>
        <div class="stat-card"><div class="stat-icon amber">⏳</div><div class="stat-info"><div class="stat-value" id="stat-pending">0</div><div class="stat-label">Pending Reservations</div></div></div>
        <div class="stat-card"><div class="stat-icon purple">⭐</div><div class="stat-info"><div class="stat-value" id="stat-vip">0</div><div class="stat-label">VIP Reservations</div></div></div>
        <div class="stat-card"><div class="stat-icon red">🚨</div><div class="stat-info"><div class="stat-value" id="stat-emergency">0</div><div class="stat-label">Emergency Meetings</div></div></div>
        <div class="stat-card"><div class="stat-icon amber">🔧</div><div class="stat-info"><div class="stat-value" id="stat-malfunction">0</div><div class="stat-label">Equipment Malfunction</div></div></div>
      </div>



      <!-- TOP-LEVEL SECTION TABS -->
      <div class="sub-tabs" id="section-tabs" style="margin-bottom:20px">
        <button class="sub-tab active" data-section="tab-monitoring" onclick="switchSection('tab-monitoring',this)">🏢 Room Booking & Calendar</button>
        <button class="sub-tab" data-section="tab-approved" onclick="switchSection('tab-approved',this)">✅ Approved Bookings</button>
        <button class="sub-tab" data-section="tab-equipment" onclick="switchSection('tab-equipment',this)">🔧 Equipment</button>
        <button class="sub-tab" data-section="tab-maintenance" onclick="switchSection('tab-maintenance',this)">🛠️ Maintenance</button>
      </div>

      <!-- ═══════════════════════════════════════════════════ -->
      <!-- TAB: Room Booking & Calendar (merged)               -->
      <!-- ═══════════════════════════════════════════════════ -->
      <div id="tab-monitoring" class="tab-content active animate-in delay-3">

        <!-- Sub-tabs: Rooms | Reservations | Calendar -->
        <div class="sub-tabs" id="booking-sub-tabs" style="margin-bottom:20px">
          <button class="sub-tab active" data-booking-tab="rooms-view" onclick="switchBookingTab('rooms-view',this)">🏢 Room Monitoring</button>
          <button class="sub-tab" data-booking-tab="reservations-view" onclick="switchBookingTab('reservations-view',this)">📋 Reservations</button>
          <button class="sub-tab" data-booking-tab="calendar-view" onclick="switchBookingTab('calendar-view',this)">📅 Calendar</button>
        </div>

        <!-- ── Sub-tab: Room Monitoring ── -->
        <div id="rooms-view" class="sub-tab-content active">
          <!-- Filter bar -->
          <div style="display:flex;gap:10px;margin-bottom:18px;flex-wrap:wrap;align-items:center">
            <span style="font-size:13px;font-weight:600;color:#4B5563">Filter:</span>
            <button class="btn btn-sm btn-outline room-filter active" data-filter="all" onclick="filterRooms('all',this)">All Rooms</button>
            <button class="btn btn-sm btn-outline room-filter" data-filter="available" onclick="filterRooms('available',this)">🟢 Available</button>
            <button class="btn btn-sm btn-outline room-filter" data-filter="occupied" onclick="filterRooms('occupied',this)">🔴 Occupied</button>
            <button class="btn btn-sm btn-outline room-filter" data-filter="pending" onclick="filterRooms('pending',this)">⏳ Pending</button>
            <button class="btn btn-sm btn-outline room-filter" data-filter="maintenance" onclick="filterRooms('maintenance',this)">🟡 Maintenance</button>
          </div>
          <!-- Room Cards Grid -->
          <div id="room-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:20px"></div>
        </div>

        <!-- ── Sub-tab: Reservations ── -->
        <div id="reservations-view" class="sub-tab-content">
          <div class="card">
            <div class="card-header">
              <span class="card-title">Pending & Upcoming Reservations</span>
              <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                <button class="btn-export btn-export-pdf btn-export-sm" onclick="exportReservations('pdf')" title="Export PDF">📄 PDF</button>
                <button class="btn-export btn-export-csv btn-export-sm" onclick="exportReservations('csv')" title="Export CSV">📊 CSV</button>
                <select id="res-filter-type" class="form-input" style="width:auto;padding:6px 12px;font-size:12px" onchange="filterReservations()">
                  <option value="">All Types</option>
                  <option value="regular">Regular</option>
                  <option value="vip">⭐ VIP</option>
                  <option value="emergency">🚨 Emergency</option>
                </select>
                <select id="res-filter-status" class="form-input" style="width:auto;padding:6px 12px;font-size:12px" onchange="filterReservations()">
                  <option value="">All Status</option>
                  <option value="pending">Pending</option>
                  <option value="approved">Approved</option>
                </select>
                <button class="btn btn-primary btn-sm" onclick="openReservationModal()">+ New Reservation</button>
              </div>
            </div>
            <div class="card-body">
              <table class="data-table" id="reservations-table">
                <thead><tr>
                  <th>Code</th><th>Facility</th><th>Event / Purpose</th><th>Type</th><th>Date &amp; Time</th>
                  <th>Budget</th><th>Equipment</th><th>Department</th><th>Status</th><th>Validated</th><th>Actions</th>
                </tr></thead>
                <tbody id="reservations-tbody"></tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- ── Sub-tab: Calendar ── -->
        <div id="calendar-view" class="sub-tab-content">
          <div class="card">
            <div class="card-header">
              <div style="display:flex;align-items:center;gap:12px">
                <button class="btn btn-outline btn-sm" onclick="changeMonth(-1)" id="cal-prev">◀</button>
                <span class="card-title" id="cal-month-label">📅 Loading...</span>
                <button class="btn btn-outline btn-sm" onclick="changeMonth(1)" id="cal-next">▶</button>
              </div>
              <div style="display:flex;gap:8px;align-items:center">
                <button class="btn btn-outline btn-sm" onclick="goToToday()">Today</button>
                <button class="btn btn-primary btn-sm" onclick="openReservationModal()">+ New Schedule</button>
              </div>
            </div>
            <div class="card-body" style="padding:20px">
              <!-- Legend -->
              <div style="display:flex;gap:16px;margin-bottom:16px;flex-wrap:wrap;font-size:12px;color:#4B5563">
                <span style="display:flex;align-items:center;gap:4px"><span style="width:10px;height:10px;border-radius:50%;background:#059669;display:inline-block"></span> Approved</span>
                <span style="display:flex;align-items:center;gap:4px"><span style="width:10px;height:10px;border-radius:50%;background:#F59E0B;display:inline-block"></span> Pending</span>
                <span style="display:flex;align-items:center;gap:4px"><span style="width:10px;height:10px;border-radius:50%;background:#7C3AED;display:inline-block"></span> VIP</span>
                <span style="display:flex;align-items:center;gap:4px"><span style="width:10px;height:10px;border-radius:50%;background:#DC2626;display:inline-block"></span> Emergency</span>
              </div>
              <!-- Calendar grid -->
              <div id="calendar-grid" style="display:grid;grid-template-columns:repeat(7,1fr);gap:4px;text-align:center;font-size:13px">
                <div style="font-weight:700;color:#6B7280;padding:8px">Sun</div>
                <div style="font-weight:700;color:#6B7280;padding:8px">Mon</div>
                <div style="font-weight:700;color:#6B7280;padding:8px">Tue</div>
                <div style="font-weight:700;color:#6B7280;padding:8px">Wed</div>
                <div style="font-weight:700;color:#6B7280;padding:8px">Thu</div>
                <div style="font-weight:700;color:#6B7280;padding:8px">Fri</div>
                <div style="font-weight:700;color:#6B7280;padding:8px">Sat</div>
              </div>
            </div>
          </div>
          <!-- Day Schedule Panel -->
          <div class="card" id="day-schedule-card" style="margin-top:20px;display:none">
            <div class="card-header">
              <span class="card-title" id="day-schedule-title">Schedule</span>
              <button class="btn btn-primary btn-sm" onclick="openReservationModal()">+ Add to this day</button>
            </div>
            <div class="card-body" id="day-schedule-body"></div>
          </div>
        </div>

      </div>

      <!-- ═══════════════════════════════════════════════════ -->
      <!-- TAB: Approved Bookings                              -->
      <!-- ═══════════════════════════════════════════════════ -->
      <div id="tab-approved" class="tab-content">
        <div class="card">
          <div class="card-header">
            <span class="card-title">✅ Approved Bookings</span>
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
              <button class="btn-export btn-export-pdf btn-export-sm" onclick="exportApprovedBookings('pdf')" title="Export PDF">📄 PDF</button>
              <button class="btn-export btn-export-csv btn-export-sm" onclick="exportApprovedBookings('csv')" title="Export CSV">📊 CSV</button>
              <select id="approved-filter-status" class="form-input" style="width:auto;padding:6px 12px;font-size:12px" onchange="renderApprovedBookings()">
                <option value="">All</option>
                <option value="ongoing">🔴 Ongoing (Occupied)</option>
                <option value="upcoming">⏳ Upcoming (Pending)</option>
                <option value="completed">✅ Completed</option>
              </select>
            </div>
          </div>
          <div class="card-body">
            <table class="data-table" id="approved-table">
              <thead><tr>
                <th>Code</th><th>Facility</th><th>Event / Purpose</th><th>Type</th>
                <th>Date &amp; Time</th><th>Department</th><th>Room Status</th><th>Actions</th>
              </tr></thead>
              <tbody id="approved-tbody"></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════ -->
      <!-- TAB: Equipment                                      -->
      <!-- ═══════════════════════════════════════════════════ -->
      <div id="tab-equipment" class="tab-content">
        <div class="card">
          <div class="card-header">
            <span class="card-title">Equipment Inventory</span>
            <div style="display:flex;gap:8px">
              <button class="btn-export btn-export-pdf btn-export-sm" onclick="exportEquipment('pdf')" title="Export PDF">📄 PDF</button>
              <button class="btn-export btn-export-csv btn-export-sm" onclick="exportEquipment('csv')" title="Export CSV">📊 CSV</button>
            </div>
          </div>
          <div class="card-body">
            <table class="data-table" id="equipment-table">
              <thead><tr><th>Equipment</th><th>Type</th><th>Location</th><th>Condition</th><th>Status</th></tr></thead>
              <tbody id="equipment-tbody"></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════ -->
      <!-- TAB: Maintenance                                    -->
      <!-- ═══════════════════════════════════════════════════ -->
      <div id="tab-maintenance" class="tab-content">

        <!-- Maintenance Stat Cards -->
        <div class="stats-grid" style="margin-bottom:20px">
          <div class="stat-card"><div class="stat-icon red">🔧</div><div class="stat-info"><div class="stat-value" id="stat-maint-open">0</div><div class="stat-label">Open Tickets</div></div></div>
          <div class="stat-card"><div class="stat-icon blue">⚙️</div><div class="stat-info"><div class="stat-value" id="stat-maint-progress">0</div><div class="stat-label">In Progress</div></div></div>
          <div class="stat-card"><div class="stat-icon amber">⚠️</div><div class="stat-info"><div class="stat-value" id="stat-maint-equipment">0</div><div class="stat-label">Equipment Malfunction</div></div></div>
          <div class="stat-card"><div class="stat-icon green">✅</div><div class="stat-info"><div class="stat-value" id="stat-maint-resolved">0</div><div class="stat-label">Resolved</div></div></div>
        </div>

        <div class="card">
          <div class="card-header">
            <span class="card-title">🛠️ Maintenance Requests</span>
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
              <button class="btn-export btn-export-pdf btn-export-sm" onclick="exportMaintenance('pdf')" title="Export PDF">📄 PDF</button>
              <button class="btn-export btn-export-csv btn-export-sm" onclick="exportMaintenance('csv')" title="Export CSV">📊 CSV</button>
              <select id="maint-filter-status" class="form-input" style="width:auto;padding:6px 12px;font-size:12px" onchange="renderMaintenance()">
                <option value="">All Status</option>
                <option value="open">🔴 Open</option>
                <option value="in_progress">🔵 In Progress</option>
                <option value="resolved">🟢 Resolved</option>
                <option value="closed">⚫ Closed</option>
              </select>
              <select id="maint-filter-type" class="form-input" style="width:auto;padding:6px 12px;font-size:12px" onchange="renderMaintenance()">
                <option value="">All Types</option>
                <option value="equipment">🔧 Equipment Malfunction</option>
                <option value="electrical">⚡ Electrical</option>
                <option value="plumbing">🚿 Plumbing</option>
                <option value="hvac">❄️ HVAC</option>
                <option value="structural">🏗️ Structural</option>
                <option value="cleaning">🧹 Cleaning</option>
                <option value="other">📋 Other</option>
              </select>
              <select id="maint-filter-priority" class="form-input" style="width:auto;padding:6px 12px;font-size:12px" onchange="renderMaintenance()">
                <option value="">All Priority</option>
                <option value="critical">🔴 Critical</option>
                <option value="high">🟠 High</option>
                <option value="medium">🟡 Medium</option>
                <option value="low">🟢 Low</option>
              </select>
              <button class="btn btn-primary btn-sm" onclick="openMaintenanceModal()">+ Report Malfunction</button>
            </div>
          </div>
          <div class="card-body" id="maintenance-body"></div>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════ -->
      <!-- MODAL: New Maintenance Request                      -->
      <!-- ═══════════════════════════════════════════════════ -->
      <div id="modal-maintenance" class="modal-overlay" onclick="if(event.target===this)closeModal('modal-maintenance')">
        <div class="modal" style="max-width:600px">
          <div class="modal-header">
            <span class="modal-title" id="modal-maint-title">🔧 Report Equipment Malfunction</span>
            <button class="modal-close" onclick="closeModal('modal-maintenance')">&times;</button>
          </div>
          <div class="modal-body">

            <div style="background:#FEF3C7;padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:13px;color:#92400E;border:1px solid #FDE68A">
              ⚠️ <strong>Report a malfunction or maintenance issue.</strong> Critical and high-priority issues will be flagged immediately. Equipment marked as malfunctioning will be taken offline until resolved.
            </div>

            <div class="form-control">
              <label>Issue Type</label>
              <select class="form-input" id="maint-issue-type" onchange="toggleEquipmentField()">
                <option value="equipment" selected>🔧 Equipment Malfunction</option>
                <option value="electrical">⚡ Electrical</option>
                <option value="plumbing">🚿 Plumbing</option>
                <option value="hvac">❄️ HVAC</option>
                <option value="structural">🏗️ Structural</option>
                <option value="cleaning">🧹 Cleaning</option>
                <option value="other">📋 Other</option>
              </select>
            </div>

            <div class="form-control">
              <label>Facility / Location</label>
              <select class="form-input" id="maint-facility" onchange="loadFacilityEquipment()">
                <!-- Populated dynamically -->
              </select>
            </div>

            <div class="form-control" id="maint-equipment-group">
              <label>Malfunctioning Equipment</label>
              <select class="form-input" id="maint-equipment">
                <option value="">— Select equipment —</option>
                <!-- Populated dynamically based on selected facility -->
              </select>
              <span style="font-size:11px;color:#9CA3AF;margin-top:4px;display:block">Select the specific equipment that is malfunctioning. It will be marked as unavailable until repaired.</span>
            </div>

            <div class="form-control">
              <label>Priority</label>
              <select class="form-input" id="maint-priority">
                <option value="low">🟢 Low — Minor issue, can wait</option>
                <option value="medium" selected>🟡 Medium — Should be fixed soon</option>
                <option value="high">🟠 High — Urgent, affects operations</option>
                <option value="critical">🔴 Critical — Immediate attention needed</option>
              </select>
            </div>

            <div class="form-control">
              <label>Description of Issue</label>
              <textarea class="form-input" id="maint-description" rows="4" placeholder="Describe the malfunction in detail: What happened? When did it start? Any error indicators or visible damage?"></textarea>
            </div>

            <div class="form-control">
              <label>Assign To (Optional)</label>
              <input type="text" class="form-input" id="maint-assigned" placeholder="Name of technician or maintenance team">
            </div>

            <div class="form-control" id="maint-facility-offline-group" style="display:none">
              <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                <input type="checkbox" id="maint-set-offline" style="accent-color:#DC2626;width:18px;height:18px">
                <span style="font-size:13px;font-weight:600;color:#991B1B">⚠️ Take facility offline (set to Maintenance mode)</span>
              </label>
              <span style="font-size:11px;color:#9CA3AF;margin-top:4px;display:block">This will prevent new bookings for this facility until the issue is resolved.</span>
            </div>

          </div>
          <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modal-maintenance')">Cancel</button>
            <button class="btn btn-primary" id="btn-submit-maintenance" onclick="submitMaintenance()">🔧 Submit Report</button>
          </div>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════ -->
      <!-- MODAL: Maintenance Detail / Update                  -->
      <!-- ═══════════════════════════════════════════════════ -->
      <div id="modal-maint-detail" class="modal-overlay" onclick="if(event.target===this)closeModal('modal-maint-detail')">
        <div class="modal" style="max-width:560px">
          <div class="modal-header">
            <span class="modal-title" id="modal-maint-detail-title">Maintenance Details</span>
            <button class="modal-close" onclick="closeModal('modal-maint-detail')">&times;</button>
          </div>
          <div class="modal-body" id="modal-maint-detail-body"></div>
          <div class="modal-footer" id="modal-maint-detail-footer">
            <button class="btn btn-outline" onclick="closeModal('modal-maint-detail')">Close</button>
          </div>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════ -->
      <!-- MODAL: New / Edit Reservation                       -->
      <!-- ═══════════════════════════════════════════════════ -->
      <div id="modal-reservation" class="modal-overlay" onclick="if(event.target===this)closeModal('modal-reservation')">
        <div class="modal" style="max-width:640px">
          <div class="modal-header">
            <span class="modal-title" id="modal-res-title">New Reservation</span>
            <button class="modal-close" onclick="closeModal('modal-reservation')">&times;</button>
          </div>
          <div class="modal-body">

            <!-- Reservation Type Banner -->
            <div id="res-type-banner" style="display:none;padding:10px 14px;border-radius:10px;margin-bottom:16px;font-size:13px;font-weight:600"></div>

            <div class="form-control">
              <label>Reservation Type</label>
              <div style="display:flex;gap:8px">
                <label style="flex:1;display:flex;align-items:center;gap:8px;padding:10px 14px;border:2px solid #E5E7EB;border-radius:10px;cursor:pointer;font-size:13px;transition:all 0.2s" class="type-option" data-type="regular">
                  <input type="radio" name="res-type" value="regular" checked style="accent-color:#059669"> Regular
                </label>
                <label style="flex:1;display:flex;align-items:center;gap:8px;padding:10px 14px;border:2px solid #E5E7EB;border-radius:10px;cursor:pointer;font-size:13px;transition:all 0.2s" class="type-option" data-type="vip">
                  <input type="radio" name="res-type" value="vip" style="accent-color:#7C3AED"> ⭐ VIP
                </label>
                <label style="flex:1;display:flex;align-items:center;gap:8px;padding:10px 14px;border:2px solid #E5E7EB;border-radius:10px;cursor:pointer;font-size:13px;transition:all 0.2s" class="type-option" data-type="emergency">
                  <input type="radio" name="res-type" value="emergency" style="accent-color:#DC2626"> 🚨 Emergency
                </label>
              </div>
            </div>

            <div class="form-control">
              <label>Facility / Room</label>
              <select class="form-input" id="res-facility">
                <!-- Populated dynamically from rooms data -->
              </select>
            </div>

            <div class="form-control">
              <label>Event Title</label>
              <input type="text" class="form-input" id="res-event" placeholder="Enter event title">
            </div>

            <div class="form-control">
              <label>Purpose / Description</label>
              <textarea class="form-input" id="res-purpose" rows="2" placeholder="Describe the meeting purpose, agenda, or objectives"></textarea>
            </div>

            <div class="form-control">
              <label>Priority</label>
              <select class="form-input" id="res-priority">
                <option value="low">Low</option>
                <option value="normal" selected>Normal</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
              </select>
            </div>

            <div class="grid-2">
              <div class="form-control">
                <label>Start Date &amp; Time</label>
                <input type="datetime-local" class="form-input" id="res-start">
              </div>
              <div class="form-control">
                <label>End Date &amp; Time</label>
                <input type="datetime-local" class="form-input" id="res-end">
              </div>
            </div>

            <div class="grid-2">
              <div class="form-control">
                <label>Department</label>
                <select class="form-input" id="res-dept">
                  <option>Executive Office</option>
                  <option>Human Resources</option>
                  <option>Credit Department</option>
                  <option>IT Department</option>
                  <option>Operations</option>
                  <option>Board of Directors</option>
                  <option>Finance</option>
                  <option>Marketing</option>
                </select>
              </div>
              <div class="form-control">
                <label>Expected Attendees</label>
                <input type="number" class="form-input" id="res-attendees" placeholder="Number of attendees">
              </div>
            </div>

            <div class="form-control">
              <label>Budget (₱)</label>
              <input type="number" class="form-input" id="res-budget" placeholder="0.00" step="0.01" min="0">
              <span style="font-size:11px;color:#9CA3AF;margin-top:4px;display:block">Enter the allocated budget for this event (catering, materials, etc.)</span>
            </div>

            <div class="form-control">
              <label>Equipment Needed</label>
              <div id="equipment-checkboxes" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:4px">
                <!-- Populated dynamically from equipment data -->
              </div>
            </div>

            <div class="form-control">
              <label>Special Requests</label>
              <textarea class="form-input" id="res-requests" rows="2" placeholder="Any special requirements (optional)"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modal-reservation')">Cancel</button>
            <button class="btn btn-primary" id="btn-submit-reservation" onclick="submitReservation()">Submit Reservation</button>
          </div>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════ -->
      <!-- MODAL: Event Detail                                 -->
      <!-- ═══════════════════════════════════════════════════ -->
      <div id="modal-day-detail" class="modal-overlay" onclick="if(event.target===this)closeModal('modal-day-detail')">
        <div class="modal" style="max-width:520px">
          <div class="modal-header">
            <span class="modal-title" id="modal-day-title">Schedule Details</span>
            <button class="modal-close" onclick="closeModal('modal-day-detail')">&times;</button>
          </div>
          <div class="modal-body" id="modal-day-body"></div>
          <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modal-day-detail')">Close</button>
          </div>
        </div>
      </div>

    </main>
  </div>

<script src="../../admin.js"></script>
<script src="../../export.js"></script>
<script>
/* ═══════════════════════════════════════════════════════
   FACILITIES MODULE JAVASCRIPT — All data from API
   ═══════════════════════════════════════════════════════ */

const API = '../../api/facilities.php';
let rooms = [], reservations = [], calendarEvents = [], equipment = [], maintenance = [], stats = {};

const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
const now = new Date();
let calYear = now.getFullYear(), calMonth = now.getMonth();

// ───── Helpers ─────
function formatMoney(v) {
  const n = parseFloat(v) || 0;
  return '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDate(str) {
  if (!str) return '';
  const d = new Date(str);
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function formatTime(str) {
  if (!str) return '';
  const d = new Date(str);
  return d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
}

function formatDateTime(start, end) {
  return formatDate(start) + '<br>' + formatTime(start) + ' – ' + formatTime(end);
}

function escHtml(s) {
  const el = document.createElement('div');
  el.textContent = s || '';
  return el.innerHTML;
}

// ───── Data Loading ─────
async function loadData() {
  try {
    const [roomRes, resRes, equipRes, maintRes, statsRes] = await Promise.all([
      fetch(API + '?action=room_status').then(r => r.json()),
      fetch(API + '?action=list_reservations').then(r => r.json()),
      fetch(API + '?action=list_equipment').then(r => r.json()),
      fetch(API + '?action=list_maintenance').then(r => r.json()),
      fetch(API + '?action=dashboard_stats').then(r => r.json()),
    ]);

    rooms = Array.isArray(roomRes) ? roomRes : (roomRes.data || []);
    reservations = Array.isArray(resRes) ? resRes : (resRes.data || []);
    equipment = Array.isArray(equipRes) ? equipRes : (equipRes.data || []);
    maintenance = Array.isArray(maintRes) ? maintRes : (maintRes.data || []);
    stats = statsRes.data || statsRes || {};

    renderStats();
    renderRooms();
    renderReservations();
    renderApprovedBookings();
    renderEquipment();
    renderMaintenance();
    populateFacilityDropdown();
    populateEquipmentCheckboxes();
    populateMaintenanceFacilityDropdown();
    await loadCalendarEvents();
  } catch (err) {
    console.error('Failed to load facilities data:', err);
  }
}

async function loadCalendarEvents() {
  try {
    const res = await fetch(API + '?action=calendar_events&month=' + (calMonth + 1) + '&year=' + calYear);
    const data = await res.json();
    calendarEvents = Array.isArray(data) ? data : (data.data || []);
    renderCalendar();
  } catch (err) {
    console.error('Failed to load calendar events:', err);
    calendarEvents = [];
    renderCalendar();
  }
}

// ───── Render Stats ─────
function renderStats() {
  document.getElementById('stat-total').textContent = stats.total_facilities ?? 0;
  document.getElementById('stat-available').textContent = stats.available_facilities ?? 0;
  document.getElementById('stat-pending').textContent = stats.pending_reservations ?? 0;

  // VIP: type=vip and status in (pending, approved)
  const vipCount = reservations.filter(r =>
    r.reservation_type === 'vip' && (r.status === 'pending' || r.status === 'approved')
  ).length;
  document.getElementById('stat-vip').textContent = vipCount;

  // Emergency: type=emergency and status=approved
  const emergencyCount = reservations.filter(r =>
    r.reservation_type === 'emergency' && r.status === 'approved'
  ).length;
  document.getElementById('stat-emergency').textContent = emergencyCount;

  // Malfunction: equipment maintenance tickets that are open or in_progress
  const malfunctionCount = maintenance.filter(m =>
    m.issue_type === 'equipment' && (m.status === 'open' || m.status === 'in_progress')
  ).length;
  document.getElementById('stat-malfunction').textContent = malfunctionCount;
}

// ───── Render Rooms ─────
function renderRooms() {
  const grid = document.getElementById('room-grid');
  if (!rooms.length) {
    grid.innerHTML = '<div class="empty-state" style="grid-column:1/-1"><div style="font-size:48px;margin-bottom:12px">🏢</div><div style="font-weight:600;color:#1F2937;margin-bottom:4px">No facilities found</div><div style="font-size:13px;color:#9CA3AF">Facilities will appear here once added to the system.</div></div>';
    return;
  }

  grid.innerHTML = rooms.map(room => {
    const isOccupied = room.is_currently_occupied == 1 || room.is_currently_occupied === true;
    const isMaint = room.status === 'maintenance';
    const hasUpcoming = (room.has_upcoming > 0) && !isOccupied;
    const displayStatus = isMaint ? 'maintenance' : (isOccupied ? 'occupied' : (hasUpcoming ? 'pending' : 'available'));

    const equipList = room.equipment_list
      ? (typeof room.equipment_list === 'string' ? room.equipment_list.split(',') : room.equipment_list)
      : [];
    const equipTags = equipList
      .filter(e => e.trim())
      .map(e => '<span style="background:#F3F4F6;padding:3px 8px;border-radius:6px;font-size:11px;color:#4B5563">' + escHtml(e.trim()) + '</span>')
      .join('');

    const safeName = escHtml(room.name).replace(/'/g, "\\'");
    const safeType = escHtml(room.type || '').replace(/'/g, "\\'");
    const fid = room.facility_id;

    let statusBadge = '', statusInfo = '', buttons = '';

    if (isMaint) {
      statusBadge = '<span class="badge badge-amber" style="font-size:12px">🟡 Maintenance</span>';
      statusInfo = '<div style="margin-top:8px;background:#FEF3C7;padding:8px 12px;border-radius:8px;font-size:12px;color:#92400E"><strong>Under maintenance</strong>' + (room.description ? ' — ' + escHtml(room.description) : '') + '</div>';
      buttons = '<button class="btn btn-outline btn-sm" disabled style="width:100%;opacity:0.5">Unavailable</button>';
    } else if (isOccupied) {
      statusBadge = '<span class="badge badge-red" style="font-size:12px">🔴 Occupied</span>';
      const curEvent = room.current_event || 'In use';
      const untilStr = room.occupied_until ? ' — until ' + formatTime(room.occupied_until) : '';
      statusInfo = '<div style="margin-top:8px;background:#FEE2E2;padding:8px 12px;border-radius:8px;font-size:12px;color:#991B1B"><strong>' + escHtml(curEvent) + '</strong>' + untilStr + '</div>';
      buttons = '<button class="btn btn-outline btn-sm" style="flex:1" onclick="openReservationModal(\'' + safeName + '\',\'' + safeType + '\',' + fid + ')">Schedule Later</button>'
        + '<button class="btn btn-outline btn-sm" onclick="openReservationModal(\'' + safeName + '\',\'' + safeType + '\',' + fid + ',\'emergency\')" title="Emergency Meeting">'
        + '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></button>';
    } else if (hasUpcoming) {
      statusBadge = '<span class="badge badge-amber" style="font-size:12px">⏳ Pending</span>';
      statusInfo = '<div style="margin-top:8px;background:#FEF3C7;padding:8px 12px;border-radius:8px;font-size:12px;color:#92400E"><strong>Upcoming reservation scheduled</strong></div>';
      buttons = '<button class="btn btn-primary btn-sm" style="flex:1" onclick="openReservationModal(\'' + safeName + '\',\'' + safeType + '\',' + fid + ')">Reserve</button>'
        + '<button class="btn btn-outline btn-sm" onclick="openReservationModal(\'' + safeName + '\',\'' + safeType + '\',' + fid + ',\'emergency\')" title="Emergency Meeting">'
        + '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></button>'
        + '<button class="btn btn-outline btn-sm" onclick="openReservationModal(\'' + safeName + '\',\'' + safeType + '\',' + fid + ',\'vip\')" title="VIP Reservation">'
        + '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="#7C3AED" stroke="#7C3AED" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></button>';
    } else {
      statusBadge = '<span class="badge badge-green" style="font-size:12px">🟢 Available</span>';
      buttons = '<button class="btn btn-primary btn-sm" style="flex:1" onclick="openReservationModal(\'' + safeName + '\',\'' + safeType + '\',' + fid + ')">Reserve</button>'
        + '<button class="btn btn-outline btn-sm" onclick="openReservationModal(\'' + safeName + '\',\'' + safeType + '\',' + fid + ',\'emergency\')" title="Emergency Meeting">'
        + '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></button>'
        + '<button class="btn btn-outline btn-sm" onclick="openReservationModal(\'' + safeName + '\',\'' + safeType + '\',' + fid + ',\'vip\')" title="VIP Reservation">'
        + '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="#7C3AED" stroke="#7C3AED" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></button>';
    }

    const todayBookings = room.today_bookings ?? 0;
    const availabilityIndicator = isMaint
      ? '<div style="margin-top:10px;display:flex;align-items:center;gap:6px;font-size:11px;color:#92400E"><span style="width:8px;height:8px;border-radius:50%;background:#F59E0B;display:inline-block"></span>Under maintenance — unavailable for booking</div>'
      : isOccupied
        ? '<div style="margin-top:10px;display:flex;align-items:center;gap:6px;font-size:11px;color:#991B1B"><span style="width:8px;height:8px;border-radius:50%;background:#EF4444;display:inline-block;animation:pulse-dot 2s infinite"></span>Currently occupied' + (room.occupied_until ? ' · Available after ' + formatTime(room.occupied_until) : '') + '</div>'
        : hasUpcoming
          ? '<div style="margin-top:10px;display:flex;align-items:center;gap:6px;font-size:11px;color:#92400E"><span style="width:8px;height:8px;border-radius:50%;background:#F59E0B;display:inline-block;animation:pulse-dot 2s infinite"></span>Pending — approved reservation upcoming · ' + todayBookings + ' booking(s) today</div>'
          : '<div style="margin-top:10px;display:flex;align-items:center;gap:6px;font-size:11px;color:#065F46"><span style="width:8px;height:8px;border-radius:50%;background:#059669;display:inline-block;animation:pulse-dot 2s infinite"></span>Available now · ' + todayBookings + ' booking(s) today</div>';

    return '<div class="card room-card" data-status="' + displayStatus + '" style="margin-bottom:0">'
      + '<div class="card-body padded">'
      + '<div style="display:flex;justify-content:space-between;align-items:flex-start">'
        + '<div>'
          + '<div style="font-size:15px;font-weight:700;color:#1F2937">' + escHtml(room.name) + '</div>'
          + '<div style="font-size:12px;color:#9CA3AF;margin-top:2px">' + (room.floor ? escHtml(room.floor) + ' · ' : '') + (room.capacity ? room.capacity + ' pax' : '') + '</div>'
        + '</div>'
        + statusBadge
      + '</div>'
      + statusInfo
      + (equipTags ? '<div style="margin-top:14px;display:flex;gap:6px;flex-wrap:wrap">' + equipTags + '</div>' : '')
      + '<div style="margin-top:14px;display:flex;gap:8px">' + buttons + '</div>'
      + availabilityIndicator
      + '</div></div>';
  }).join('');
}

// ───── Room Filter ─────
function filterRooms(status, btn) {
  document.querySelectorAll('.room-filter').forEach(b => {
    b.classList.remove('active');
    b.style.background = '';
    b.style.color = '';
    b.style.borderColor = '';
  });
  if (btn) {
    btn.classList.add('active');
    btn.style.background = '#059669';
    btn.style.color = '#fff';
    btn.style.borderColor = '#059669';
  }
  document.querySelectorAll('.room-card').forEach(card => {
    card.style.display = (status === 'all' || card.dataset.status === status) ? '' : 'none';
  });
}

// ───── Render Reservations ─────
function renderReservations() {
  const tbody = document.getElementById('reservations-tbody');
  const typeFilter = document.getElementById('res-filter-type').value;
  const statusFilter = document.getElementById('res-filter-status').value;

  let filtered = reservations;
  // Exclude completed/rejected/cancelled by default (remove history)
  if (!statusFilter) {
    filtered = filtered.filter(r => r.status === 'pending' || r.status === 'approved');
  }
  if (typeFilter) filtered = filtered.filter(r => r.reservation_type === typeFilter);
  if (statusFilter) filtered = filtered.filter(r => r.status === statusFilter);

  if (!filtered.length) {
    tbody.innerHTML = '<tr><td colspan="11" class="empty-state" style="padding:40px"><div style="font-size:36px;margin-bottom:8px">📋</div><div style="font-weight:600;color:#1F2937">No reservations found</div><div style="font-size:13px;color:#9CA3AF;margin-top:4px">Adjust filters or create a new reservation.</div></td></tr>';
    return;
  }

  tbody.innerHTML = filtered.map(r => {
    // Type badge
    const typeBadges = {
      vip: '<span class="badge badge-purple">⭐ VIP</span>',
      emergency: '<span class="badge badge-red">🚨 Emergency</span>',
      regular: '<span class="badge badge-gray">Regular</span>',
    };
    const typeBadge = typeBadges[r.reservation_type] || typeBadges.regular;

    // Status badge
    const statusBadges = {
      pending: '<span class="badge badge-amber">Pending</span>',
      approved: '<span class="badge badge-green">Approved</span>',
      rejected: '<span class="badge badge-red">Rejected</span>',
      completed: '<span class="badge badge-blue">Completed</span>',
      cancelled: '<span class="badge badge-gray">Cancelled</span>',
    };
    const statusBadge = statusBadges[r.status] || '<span class="badge badge-gray">' + escHtml(r.status) + '</span>';

    // Validated
    const validatedBadge = (r.is_validated == 1 || r.validated_by)
      ? '<span class="badge badge-green">✓ Yes</span>'
      : '<span class="badge badge-gray">✗ No</span>';

    // Equipment
    const equipRaw = r.equipment_needed || '';
    const equipDisplay = typeof equipRaw === 'string' ? equipRaw : (Array.isArray(equipRaw) ? equipRaw.join(', ') : '');

    // Actions
    let actions = '';
    if (r.status === 'pending') {
      actions = '<div style="display:flex;gap:4px">'
        + '<button class="btn btn-primary btn-sm" title="Approve" onclick="approveReservation(' + r.reservation_id + ')">'
        + '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></button>'
        + '<button class="btn btn-outline btn-sm" title="Validate" onclick="validateReservation(' + r.reservation_id + ')">'
        + '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></button>'
        + '</div>';
    } else {
      const evJson = JSON.stringify(r).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
      actions = '<button class="btn btn-outline btn-sm" title="View" onclick="showEventDetail(JSON.parse(this.dataset.ev))" data-ev="' + evJson + '">'
        + '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>';
    }

    return '<tr data-type="' + (r.reservation_type || 'regular') + '" data-status="' + (r.status || '') + '">'
      + '<td style="font-weight:600;font-size:12px">' + escHtml(r.reservation_code || '') + '</td>'
      + '<td style="font-weight:600">' + escHtml(r.facility_name || '') + '</td>'
      + '<td>' + escHtml(r.event_title || '') + (r.purpose ? '<br><span style="font-size:11px;color:#9CA3AF">' + escHtml(r.purpose) + '</span>' : '') + '</td>'
      + '<td>' + typeBadge + '</td>'
      + '<td style="font-size:12px">' + formatDateTime(r.start_datetime, r.end_datetime) + '</td>'
      + '<td>' + formatMoney(r.budget) + '</td>'
      + '<td><span style="font-size:11px">' + escHtml(equipDisplay || '—') + '</span></td>'
      + '<td>' + escHtml(r.department || '') + '</td>'
      + '<td>' + statusBadge + '</td>'
      + '<td>' + validatedBadge + '</td>'
      + '<td>' + actions + '</td>'
      + '</tr>';
  }).join('');
}

function filterReservations() {
  renderReservations();
}

// ───── Render Equipment ─────
function renderEquipment() {
  const tbody = document.getElementById('equipment-tbody');
  if (!equipment.length) {
    tbody.innerHTML = '<tr><td colspan="5" class="empty-state" style="padding:40px"><div style="font-size:36px;margin-bottom:8px">🖥️</div><div style="font-weight:600;color:#1F2937">No equipment found</div></td></tr>';
    return;
  }

  const condBadge = (c) => {
    const cl = (c || '').toLowerCase();
    if (cl === 'excellent' || cl === 'good') return '<span class="badge badge-green">' + escHtml(c) + '</span>';
    if (cl === 'fair') return '<span class="badge badge-amber">' + escHtml(c) + '</span>';
    if (cl === 'poor') return '<span class="badge badge-red">' + escHtml(c) + '</span>';
    return '<span class="badge badge-gray">' + escHtml(c) + '</span>';
  };

  const statBadge = (s) => {
    const sl = (s || '').toLowerCase();
    if (sl === 'available') return '<span class="badge badge-green">Available</span>';
    if (sl === 'in_use' || sl === 'in use') return '<span class="badge badge-blue">In Use</span>';
    if (sl === 'maintenance') return '<span class="badge badge-red">Maintenance</span>';
    if (sl === 'retired') return '<span class="badge badge-gray">Retired</span>';
    return '<span class="badge badge-gray">' + escHtml(s) + '</span>';
  };

  tbody.innerHTML = equipment.map(e =>
    '<tr>'
    + '<td style="font-weight:600">' + escHtml(e.name || e.equipment_name || '') + '</td>'
    + '<td>' + escHtml(e.type || e.equipment_type || '') + '</td>'
    + '<td>' + escHtml(e.facility_name || e.location || '') + '</td>'
    + '<td>' + condBadge(e.condition_status || e.condition || '') + '</td>'
    + '<td>' + statBadge(e.status || '') + '</td>'
    + '</tr>'
  ).join('');
}

// ───── Render Maintenance ─────
function renderMaintenance() {
  const body = document.getElementById('maintenance-body');
  const statusFilter = document.getElementById('maint-filter-status')?.value || '';
  const typeFilter = document.getElementById('maint-filter-type')?.value || '';
  const priorityFilter = document.getElementById('maint-filter-priority')?.value || '';

  // Update maintenance stat cards
  const openCount = maintenance.filter(m => m.status === 'open').length;
  const progressCount = maintenance.filter(m => m.status === 'in_progress').length;
  const equipCount = maintenance.filter(m => m.issue_type === 'equipment' && (m.status === 'open' || m.status === 'in_progress')).length;
  const resolvedCount = maintenance.filter(m => m.status === 'resolved' || m.status === 'closed').length;

  const el = (id) => document.getElementById(id);
  if (el('stat-maint-open')) el('stat-maint-open').textContent = openCount;
  if (el('stat-maint-progress')) el('stat-maint-progress').textContent = progressCount;
  if (el('stat-maint-equipment')) el('stat-maint-equipment').textContent = equipCount;
  if (el('stat-maint-resolved')) el('stat-maint-resolved').textContent = resolvedCount;

  let filtered = maintenance;
  if (statusFilter) filtered = filtered.filter(m => m.status === statusFilter);
  if (typeFilter) filtered = filtered.filter(m => m.issue_type === typeFilter);
  if (priorityFilter) filtered = filtered.filter(m => m.priority === priorityFilter);

  if (!filtered.length) {
    body.innerHTML = '<div class="empty-state"><div style="font-size:48px;margin-bottom:12px">🔧</div>'
      + '<div style="font-weight:600;color:#1F2937;margin-bottom:4px">No maintenance requests found</div>'
      + '<div style="font-size:13px;color:#9CA3AF">All facilities and equipment are operational. Click "Report Malfunction" to submit a new request.</div></div>';
    return;
  }

  const priBadge = (p) => {
    const pl = (p || '').toLowerCase();
    if (pl === 'critical') return '<span class="badge badge-red">🔴 Critical</span>';
    if (pl === 'high') return '<span class="badge badge-red">🟠 High</span>';
    if (pl === 'medium') return '<span class="badge badge-amber">🟡 Medium</span>';
    if (pl === 'low') return '<span class="badge badge-green">🟢 Low</span>';
    return '<span class="badge badge-gray">' + escHtml(p) + '</span>';
  };

  const stBadge = (s) => {
    const sl = (s || '').toLowerCase();
    if (sl === 'open') return '<span class="badge badge-red">Open</span>';
    if (sl === 'in_progress') return '<span class="badge badge-blue">In Progress</span>';
    if (sl === 'resolved') return '<span class="badge badge-green">Resolved</span>';
    if (sl === 'closed') return '<span class="badge badge-gray">Closed</span>';
    return '<span class="badge badge-gray">' + escHtml(s) + '</span>';
  };

  const issueIcon = (t) => {
    const icons = { equipment: '🔧', electrical: '⚡', plumbing: '🚿', hvac: '❄️', structural: '🏗️', cleaning: '🧹' };
    return (icons[t] || '📋') + ' ' + escHtml((t || 'other').replace(/_/g, ' '));
  };

  body.innerHTML = '<table class="data-table"><thead><tr>'
    + '<th>Ticket</th><th>Facility</th><th>Equipment</th><th>Issue Type</th><th>Description</th><th>Priority</th><th>Status</th><th>Assigned To</th><th>Reported By</th><th>Date</th><th>Actions</th>'
    + '</tr></thead><tbody>'
    + filtered.map(m => {
      const mId = m.maintenance_id;
      let actions = '';
      if (m.status === 'open') {
        actions = '<div style="display:flex;gap:4px">'
          + '<button class="btn btn-primary btn-sm" title="Start Work" onclick="updateMaintenanceStatus(' + mId + ',\'in_progress\')">'
          + '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="5 3 19 12 5 21 5 3"/></svg></button>'
          + '<button class="btn btn-outline btn-sm" title="View Details" onclick="showMaintenanceDetail(' + mId + ')">'
          + '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>'
          + '</div>';
      } else if (m.status === 'in_progress') {
        actions = '<div style="display:flex;gap:4px">'
          + '<button class="btn btn-primary btn-sm" title="Mark Resolved" onclick="resolveMaintenancePrompt(' + mId + ')">'
          + '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg></button>'
          + '<button class="btn btn-outline btn-sm" title="View Details" onclick="showMaintenanceDetail(' + mId + ')">'
          + '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>'
          + '</div>';
      } else {
        actions = '<button class="btn btn-outline btn-sm" title="View Details" onclick="showMaintenanceDetail(' + mId + ')">'
          + '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>';
      }

      return '<tr>'
        + '<td style="font-weight:600;font-size:12px">' + escHtml(m.ticket_number || '') + '</td>'
        + '<td style="font-weight:600">' + escHtml(m.facility_name || '') + '</td>'
        + '<td>' + (m.equipment_name ? '<span style="color:#991B1B;font-weight:600">⚠️ ' + escHtml(m.equipment_name) + '</span>' : '<span style="color:#9CA3AF">—</span>') + '</td>'
        + '<td style="font-size:12px">' + issueIcon(m.issue_type) + '</td>'
        + '<td style="max-width:200px"><div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="' + escHtml(m.description || '') + '">' + escHtml(m.description || '') + '</div></td>'
        + '<td>' + priBadge(m.priority) + '</td>'
        + '<td>' + stBadge(m.status) + '</td>'
        + '<td>' + escHtml(m.assigned_to || '—') + '</td>'
        + '<td style="font-size:12px">' + escHtml(m.reported_by_name || '') + '</td>'
        + '<td style="font-size:12px">' + formatDate(m.created_at || '') + '</td>'
        + '<td>' + actions + '</td>'
        + '</tr>';
    }).join('')
    + '</tbody></table>';
}

// ───── Maintenance Modal Helpers ─────
function toggleEquipmentField() {
  const issueType = document.getElementById('maint-issue-type').value;
  const equipGroup = document.getElementById('maint-equipment-group');
  const offlineGroup = document.getElementById('maint-facility-offline-group');
  equipGroup.style.display = issueType === 'equipment' ? '' : 'none';

  // Show "take offline" option for critical/high priority or equipment issues
  const priority = document.getElementById('maint-priority').value;
  offlineGroup.style.display = (priority === 'critical' || priority === 'high') ? '' : 'none';
}

function loadFacilityEquipment() {
  const facId = document.getElementById('maint-facility').value;
  const equipSel = document.getElementById('maint-equipment');
  const facEquip = equipment.filter(e => String(e.facility_id) === String(facId) || !e.facility_id);

  equipSel.innerHTML = '<option value="">— Select equipment —</option>'
    + facEquip.map(e => '<option value="' + e.equipment_id + '">' + escHtml(e.name || e.equipment_name || '') + ' (' + escHtml(e.condition_status || 'unknown') + ')</option>').join('');
}

function populateMaintenanceFacilityDropdown() {
  const sel = document.getElementById('maint-facility');
  if (!sel) return;
  sel.innerHTML = rooms.map(r => '<option value="' + r.facility_id + '">' + escHtml(r.name) + '</option>').join('');
  loadFacilityEquipment();
}

function openMaintenanceModal() {
  // Reset form
  document.getElementById('maint-issue-type').value = 'equipment';
  document.getElementById('maint-priority').value = 'medium';
  document.getElementById('maint-description').value = '';
  document.getElementById('maint-assigned').value = '';
  document.getElementById('maint-set-offline').checked = false;
  document.getElementById('modal-maint-title').textContent = '🔧 Report Equipment Malfunction';

  populateMaintenanceFacilityDropdown();
  toggleEquipmentField();
  openModal('modal-maintenance');
}

// Listen for priority change to toggle offline option
document.getElementById('maint-priority')?.addEventListener('change', function() {
  const offlineGroup = document.getElementById('maint-facility-offline-group');
  offlineGroup.style.display = (this.value === 'critical' || this.value === 'high') ? '' : 'none';
});

async function submitMaintenance() {
  const issueType = document.getElementById('maint-issue-type').value;
  const facilityId = document.getElementById('maint-facility').value;
  const equipmentId = document.getElementById('maint-equipment').value;
  const priority = document.getElementById('maint-priority').value;
  const description = document.getElementById('maint-description').value.trim();
  const assignedTo = document.getElementById('maint-assigned').value.trim();
  const setOffline = document.getElementById('maint-set-offline').checked;

  if (!description) {
    return Swal.fire({ icon: 'warning', title: 'Missing Field', text: 'Please describe the issue in detail.', confirmButtonColor: '#059669' });
  }

  if (issueType === 'equipment' && !equipmentId) {
    return Swal.fire({ icon: 'warning', title: 'Missing Field', text: 'Please select the malfunctioning equipment.', confirmButtonColor: '#059669' });
  }

  const priorityLabels = { low: '🟢 Low', medium: '🟡 Medium', high: '🟠 High', critical: '🔴 Critical' };
  const confirmResult = await Swal.fire({
    title: 'Submit Maintenance Report?',
    html: '<div style="text-align:left;font-size:13px;line-height:1.8">'
      + '<b>Type:</b> ' + escHtml(issueType.replace(/_/g, ' ')) + '<br>'
      + '<b>Priority:</b> ' + (priorityLabels[priority] || priority) + '<br>'
      + (equipmentId ? '<b>Equipment:</b> Will be marked as <span style="color:#DC2626;font-weight:700">Needs Repair</span><br>' : '')
      + (setOffline ? '<b>⚠️ Facility will be taken OFFLINE</b><br>' : '')
      + '</div>',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, Submit Report',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#DC2626',
    cancelButtonColor: '#6B7280'
  });
  if (!confirmResult.isConfirmed) return;

  try {
    const res = await fetch(API + '?action=create_maintenance', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        facility_id: facilityId,
        equipment_id: equipmentId || null,
        issue_type: issueType,
        priority: priority,
        description: description,
        assigned_to: assignedTo || null,
        set_facility_maintenance: setOffline
      })
    });
    const result = await res.json();

    if (result.success) {
      await Swal.fire({
        icon: 'success',
        title: 'Maintenance Report Submitted!',
        html: '<div style="text-align:left;font-size:14px;line-height:1.8">'
          + '<b>Ticket:</b> ' + escHtml(result.ticket_number || 'N/A') + '<br>'
          + '<b>Status:</b> Open — awaiting action<br>'
          + (equipmentId ? '<b>Equipment:</b> Marked as needs repair (taken offline)<br>' : '')
          + (setOffline ? '<b>Facility:</b> Set to maintenance mode<br>' : '')
          + '</div>',
        confirmButtonColor: '#059669'
      });
      closeModal('modal-maintenance');
      await loadData();
      // Switch to maintenance tab
      switchSection('tab-maintenance', document.querySelector('[data-section="tab-maintenance"]'));
    } else {
      Swal.fire({ icon: 'error', title: 'Error', text: result.message || 'Failed to submit report.', confirmButtonColor: '#059669' });
    }
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'Error', text: 'Network error. Please try again.', confirmButtonColor: '#059669' });
  }
}

// ───── Update Maintenance Status ─────
async function updateMaintenanceStatus(id, newStatus) {
  const labels = { in_progress: 'Start Work', resolved: 'Mark as Resolved', closed: 'Close Ticket' };
  const confirmResult = await Swal.fire({
    title: labels[newStatus] || 'Update Status',
    text: newStatus === 'in_progress'
      ? 'This will mark the ticket as In Progress. The maintenance team is now working on this issue.'
      : 'Update the maintenance ticket status.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes, ' + (labels[newStatus] || 'Update'),
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#059669',
    cancelButtonColor: '#6B7280'
  });
  if (!confirmResult.isConfirmed) return;

  try {
    const res = await fetch(API + '?action=update_maintenance', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ maintenance_id: id, status: newStatus })
    });
    const result = await res.json();

    if (result.success) {
      await Swal.fire({ icon: 'success', title: 'Updated!', text: 'Maintenance ticket status updated.', confirmButtonColor: '#059669' });
      await loadData();
    } else {
      Swal.fire({ icon: 'error', title: 'Error', text: result.message || 'Failed to update.', confirmButtonColor: '#059669' });
    }
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'Error', text: 'Network error.', confirmButtonColor: '#059669' });
  }
}

// ───── Resolve Maintenance with Notes ─────
async function resolveMaintenancePrompt(id) {
  const { value: notes } = await Swal.fire({
    title: '✅ Resolve Maintenance Ticket',
    html: '<div style="text-align:left;font-size:13px;margin-bottom:12px;color:#4B5563">Provide resolution details. The equipment will be restored to service and the facility will be set back to available.</div>',
    input: 'textarea',
    inputPlaceholder: 'Describe what was done to fix the issue...',
    inputAttributes: { rows: 4 },
    showCancelButton: true,
    confirmButtonText: '✅ Mark as Resolved',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#059669',
    cancelButtonColor: '#6B7280',
    inputValidator: (value) => {
      if (!value.trim()) return 'Please provide resolution notes.';
    }
  });

  if (!notes) return;

  try {
    const res = await fetch(API + '?action=update_maintenance', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ maintenance_id: id, status: 'resolved', resolution_notes: notes })
    });
    const result = await res.json();

    if (result.success) {
      await Swal.fire({
        icon: 'success',
        title: 'Resolved!',
        html: 'Maintenance ticket resolved.<br>Equipment restored to service. Facility set to available.',
        confirmButtonColor: '#059669'
      });
      await loadData();
    } else {
      Swal.fire({ icon: 'error', title: 'Error', text: result.message || 'Failed to resolve.', confirmButtonColor: '#059669' });
    }
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'Error', text: 'Network error.', confirmButtonColor: '#059669' });
  }
}

// ───── Show Maintenance Detail ─────
function showMaintenanceDetail(id) {
  const m = maintenance.find(x => x.maintenance_id == id);
  if (!m) return;

  const issueIcons = { equipment: '🔧', electrical: '⚡', plumbing: '🚿', hvac: '❄️', structural: '🏗️', cleaning: '🧹', other: '📋' };
  const priBg = { critical: '#FEE2E2', high: '#FFEDD5', medium: '#FEF3C7', low: '#D1FAE5' };
  const priColor = { critical: '#991B1B', high: '#9A3412', medium: '#92400E', low: '#065F46' };
  const stBg = { open: '#FEE2E2', in_progress: '#DBEAFE', resolved: '#D1FAE5', closed: '#F3F4F6' };
  const stColor = { open: '#991B1B', in_progress: '#1E40AF', resolved: '#065F46', closed: '#374151' };

  document.getElementById('modal-maint-detail-title').textContent = '🔧 ' + (m.ticket_number || 'Maintenance Detail');

  let html = '<div>'
    + '<div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap">'
      + '<span style="background:' + (priBg[m.priority] || '#F3F4F6') + ';color:' + (priColor[m.priority] || '#374151') + ';padding:6px 14px;border-radius:8px;font-size:12px;font-weight:700">'
        + (m.priority || 'medium').toUpperCase() + ' PRIORITY</span>'
      + '<span style="background:' + (stBg[m.status] || '#F3F4F6') + ';color:' + (stColor[m.status] || '#374151') + ';padding:6px 14px;border-radius:8px;font-size:12px;font-weight:700">'
        + (m.status || '').replace(/_/g, ' ').toUpperCase() + '</span>'
    + '</div>'
    + '<table style="width:100%;font-size:13px;border-collapse:collapse">'
      + '<tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280;width:140px">🎫 Ticket</td><td style="padding:8px 0;color:#1F2937;font-weight:600">' + escHtml(m.ticket_number || '') + '</td></tr>'
      + '<tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280">📍 Facility</td><td style="padding:8px 0;color:#1F2937">' + escHtml(m.facility_name || '') + '</td></tr>'
      + '<tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280">' + (issueIcons[m.issue_type] || '📋') + ' Issue Type</td><td style="padding:8px 0;color:#1F2937">' + escHtml((m.issue_type || '').replace(/_/g, ' ')) + '</td></tr>';

  if (m.equipment_name) {
    html += '<tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280">🔧 Equipment</td><td style="padding:8px 0;color:#DC2626;font-weight:600">⚠️ ' + escHtml(m.equipment_name) + ' (Malfunction)</td></tr>';
  }

  html += '<tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280;vertical-align:top">📝 Description</td><td style="padding:8px 0;color:#1F2937">' + escHtml(m.description || '') + '</td></tr>'
    + '<tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280">👤 Reported By</td><td style="padding:8px 0;color:#1F2937">' + escHtml(m.reported_by_name || '') + '</td></tr>'
    + '<tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280">🛠️ Assigned To</td><td style="padding:8px 0;color:#1F2937">' + escHtml(m.assigned_to || 'Unassigned') + '</td></tr>'
    + '<tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280">📅 Reported</td><td style="padding:8px 0;color:#1F2937">' + formatDate(m.created_at || '') + '</td></tr>';

  if (m.resolved_at) {
    html += '<tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280">✅ Resolved</td><td style="padding:8px 0;color:#065F46">' + formatDate(m.resolved_at) + '</td></tr>';
  }
  if (m.resolution_notes) {
    html += '<tr><td style="padding:8px 0;font-weight:600;color:#6B7280;vertical-align:top">📋 Resolution</td><td style="padding:8px 0;color:#065F46;font-weight:500">' + escHtml(m.resolution_notes) + '</td></tr>';
  }

  html += '</table></div>';

  document.getElementById('modal-maint-detail-body').innerHTML = html;

  // Update footer actions based on status
  const footer = document.getElementById('modal-maint-detail-footer');
  let footerHtml = '<button class="btn btn-outline" onclick="closeModal(\'modal-maint-detail\')">Close</button>';
  if (m.status === 'open') {
    footerHtml += ' <button class="btn btn-primary" onclick="closeModal(\'modal-maint-detail\');updateMaintenanceStatus(' + m.maintenance_id + ',\'in_progress\')">▶ Start Work</button>';
  } else if (m.status === 'in_progress') {
    footerHtml += ' <button class="btn btn-primary" onclick="closeModal(\'modal-maint-detail\');resolveMaintenancePrompt(' + m.maintenance_id + ')">✅ Mark Resolved</button>';
  }
  footer.innerHTML = footerHtml;

  openModal('modal-maint-detail');
}

// ───── Calendar ─────
function renderCalendar() {
  const grid = document.getElementById('calendar-grid');
  while (grid.children.length > 7) grid.removeChild(grid.lastChild);

  document.getElementById('cal-month-label').textContent = '📅 ' + monthNames[calMonth] + ' ' + calYear;

  const firstDay = new Date(calYear, calMonth, 1).getDay();
  const daysInMonth = new Date(calYear, calMonth + 1, 0).getDate();
  const todayStr = new Date().toISOString().split('T')[0];

  for (let i = 0; i < firstDay; i++) {
    const cell = document.createElement('div');
    cell.className = 'cal-day';
    cell.style.color = '#D1D5DB';
    grid.appendChild(cell);
  }

  for (let d = 1; d <= daysInMonth; d++) {
    const dateStr = calYear + '-' + String(calMonth + 1).padStart(2, '0') + '-' + String(d).padStart(2, '0');
    const dayEvents = calendarEvents.filter(e => {
      const eDate = e.date || (e.start_datetime ? e.start_datetime.split(' ')[0] : '') || '';
      return eDate === dateStr;
    });

    const cell = document.createElement('div');
    cell.className = 'cal-day' + (dayEvents.length ? ' has-event' : '');

    if (dateStr === todayStr) {
      cell.style.background = '#D1FAE5';
      cell.style.fontWeight = '800';
      cell.style.borderRadius = '10px';
    }

    cell.innerHTML = d;

    if (dayEvents.length) {
      let dots = '';
      dayEvents.forEach(ev => {
        const t = ev.reservation_type || ev.type || 'regular';
        const s = ev.status || '';
        const color = t === 'vip' ? '#7C3AED' : t === 'emergency' ? '#DC2626' : s === 'approved' ? '#059669' : '#F59E0B';
        dots += '<span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:' + color + ';margin:0 1px"></span>';
      });
      cell.innerHTML += '<div style="margin-top:4px;display:flex;justify-content:center;gap:2px">' + dots + '</div>';

      const tip = dayEvents.map(e => {
        const tStr = e.time || (formatTime(e.start_datetime) + ' – ' + formatTime(e.end_datetime));
        return tStr + ' — ' + (e.event || e.event_title || '');
      }).join('\n');
      cell.title = tip;
      cell.style.cursor = 'pointer';
      cell.addEventListener('click', () => showDaySchedule(dateStr, dayEvents));
    }

    grid.appendChild(cell);
  }
}

function changeMonth(dir) {
  calMonth += dir;
  if (calMonth > 11) { calMonth = 0; calYear++; }
  if (calMonth < 0) { calMonth = 11; calYear--; }
  document.getElementById('day-schedule-card').style.display = 'none';
  loadCalendarEvents();
}

function goToToday() {
  const n = new Date();
  calYear = n.getFullYear();
  calMonth = n.getMonth();
  loadCalendarEvents();
}

function showDaySchedule(dateStr, events) {
  const card = document.getElementById('day-schedule-card');
  const title = document.getElementById('day-schedule-title');
  const body = document.getElementById('day-schedule-body');

  const dateLabel = new Date(dateStr + 'T00:00:00').toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
  title.textContent = '📋 ' + dateLabel;

  let html = '<div style="padding:16px">';
  if (!events.length) {
    html += '<div class="empty-state" style="padding:24px"><div style="font-size:36px;margin-bottom:8px">📭</div><div style="font-weight:600">No schedules for this day</div></div>';
  } else {
    events.forEach(ev => {
      const t = ev.reservation_type || ev.type || 'regular';
      const typeColor = t === 'vip' ? '#7C3AED' : t === 'emergency' ? '#DC2626' : '#059669';
      const typeBg = t === 'vip' ? '#EDE9FE' : t === 'emergency' ? '#FEE2E2' : '#D1FAE5';
      const typeLabel = t === 'vip' ? '⭐ VIP' : t === 'emergency' ? '🚨 Emergency' : 'Regular';
      const st = ev.status || '';
      const statusBadge = st === 'approved' ? '<span class="badge badge-green">Approved</span>'
        : st === 'rejected' ? '<span class="badge badge-red">Rejected</span>'
        : '<span class="badge badge-amber">Pending</span>';
      const evTitle = ev.event || ev.event_title || '';
      const timeStr = ev.time || (formatTime(ev.start_datetime) + ' – ' + formatTime(ev.end_datetime));
      const facility = ev.facility || ev.facility_name || '';
      const dept = ev.dept || ev.department || '';
      const budget = ev.budget ?? 0;
      const equipStr = ev.equip || ev.equipment_needed || '';
      const equipDisplay = Array.isArray(equipStr) ? equipStr.join(', ') : (equipStr || '');

      const evJson = JSON.stringify(ev).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');

      html += '<div style="border:1px solid #E5E7EB;border-radius:12px;padding:16px;margin-bottom:12px;border-left:4px solid ' + typeColor + ';cursor:pointer;transition:all 0.2s"'
        + ' onmouseover="this.style.boxShadow=\'0 4px 12px rgba(0,0,0,0.08)\'" onmouseout="this.style.boxShadow=\'none\'"'
        + ' onclick="showEventDetail(JSON.parse(this.dataset.ev))" data-ev="' + evJson + '">'
        + '<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px">'
          + '<div>'
            + '<div style="font-weight:700;font-size:14px;color:#1F2937">' + escHtml(evTitle) + '</div>'
            + '<div style="font-size:12px;color:#6B7280;margin-top:2px">🕐 ' + escHtml(timeStr) + '</div>'
          + '</div>'
          + '<div style="display:flex;gap:6px;align-items:center">'
            + '<span style="background:' + typeBg + ';color:' + typeColor + ';padding:2px 8px;border-radius:6px;font-size:11px;font-weight:600">' + typeLabel + '</span>'
            + statusBadge
          + '</div>'
        + '</div>'
        + '<div style="display:flex;gap:16px;font-size:12px;color:#6B7280;flex-wrap:wrap">'
          + '<span>📍 ' + escHtml(facility) + '</span>'
          + '<span>🏢 ' + escHtml(dept) + '</span>'
          + '<span>💰 ' + formatMoney(budget) + '</span>'
        + '</div>'
        + (equipDisplay ? '<div style="font-size:12px;color:#6B7280;margin-top:6px">🔧 ' + escHtml(equipDisplay) + '</div>' : '')
        + '</div>';
    });
  }
  html += '</div>';
  body.innerHTML = html;
  card.style.display = '';
}

function showEventDetail(ev) {
  const t = ev.reservation_type || ev.type || 'regular';
  const typeLabel = t === 'vip' ? '⭐ VIP Reservation' : t === 'emergency' ? '🚨 Emergency Meeting' : 'Regular Reservation';
  const typeBg = t === 'vip' ? '#EDE9FE' : t === 'emergency' ? '#FEE2E2' : '#D1FAE5';
  const typeColor = t === 'vip' ? '#5B21B6' : t === 'emergency' ? '#991B1B' : '#065F46';

  const evTitle = ev.event || ev.event_title || 'Event Details';
  const facility = ev.facility || ev.facility_name || '';
  const timeStr = ev.time || (formatTime(ev.start_datetime) + ' – ' + formatTime(ev.end_datetime));
  const dateStr = ev.date || (ev.start_datetime ? ev.start_datetime.split(' ')[0] : '');
  const dept = ev.dept || ev.department || '';
  const purpose = ev.purpose || '';
  const budget = ev.budget ?? 0;
  const equipStr = ev.equip || ev.equipment_needed || '';
  const equipDisplay = Array.isArray(equipStr) ? equipStr.join(', ') : (equipStr || '');
  const st = ev.status || '';
  const statusBadge = st === 'approved' ? '<span class="badge badge-green">Approved</span>'
    : st === 'rejected' ? '<span class="badge badge-red">Rejected</span>'
    : st === 'completed' ? '<span class="badge badge-blue">Completed</span>'
    : '<span class="badge badge-amber">Pending</span>';

  document.getElementById('modal-day-title').textContent = evTitle;
  document.getElementById('modal-day-body').innerHTML =
    '<div>'
    + '<div style="background:' + typeBg + ';padding:10px 14px;border-radius:10px;font-size:13px;font-weight:600;color:' + typeColor + ';margin-bottom:16px">' + typeLabel + '</div>'
    + '<table style="width:100%;font-size:13px;border-collapse:collapse">'
      + '<tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280;width:140px">📍 Facility</td><td style="padding:8px 0;color:#1F2937">' + escHtml(facility) + '</td></tr>'
      + '<tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280">🕐 Schedule</td><td style="padding:8px 0;color:#1F2937">' + escHtml(dateStr) + '<br>' + escHtml(timeStr) + '</td></tr>'
      + '<tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280">🏢 Department</td><td style="padding:8px 0;color:#1F2937">' + escHtml(dept) + '</td></tr>'
      + '<tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280">📝 Purpose</td><td style="padding:8px 0;color:#1F2937">' + escHtml(purpose) + '</td></tr>'
      + '<tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280">💰 Budget</td><td style="padding:8px 0;color:#1F2937;font-weight:600">' + formatMoney(budget) + '</td></tr>'
      + '<tr style="border-bottom:1px solid #F3F4F6"><td style="padding:8px 0;font-weight:600;color:#6B7280">🔧 Equipment</td><td style="padding:8px 0;color:#1F2937">' + (escHtml(equipDisplay) || '—') + '</td></tr>'
      + '<tr><td style="padding:8px 0;font-weight:600;color:#6B7280">📊 Status</td><td style="padding:8px 0">' + statusBadge + '</td></tr>'
    + '</table>'
    + '</div>';
  openModal('modal-day-detail');
}

// ───── Populate Modal Dropdowns ─────
function populateFacilityDropdown() {
  const sel = document.getElementById('res-facility');
  sel.innerHTML = rooms
    .filter(r => r.status !== 'maintenance')
    .map(r => '<option value="' + r.facility_id + '">' + escHtml(r.name) + (r.capacity ? ' (' + r.capacity + ' pax)' : '') + '</option>')
    .join('');
}

function populateEquipmentCheckboxes() {
  const container = document.getElementById('equipment-checkboxes');
  const seen = new Set();
  const items = [];

  equipment.forEach(e => {
    const name = e.name || e.equipment_name || '';
    const key = name.toLowerCase().replace(/\s+/g, '_');
    if (key && !seen.has(key)) {
      seen.add(key);
      items.push({ key, name });
    }
  });

  // Fallback: derive from room equipment lists
  if (!items.length) {
    rooms.forEach(r => {
      const list = r.equipment_list ? (typeof r.equipment_list === 'string' ? r.equipment_list.split(',') : r.equipment_list) : [];
      list.forEach(e => {
        const name = e.trim();
        const key = name.toLowerCase().replace(/\s+/g, '_');
        if (key && !seen.has(key)) {
          seen.add(key);
          items.push({ key, name });
        }
      });
    });
  }

  container.innerHTML = items.map(i =>
    '<label style="display:flex;align-items:center;gap:6px;padding:6px 12px;border:1px solid #E5E7EB;border-radius:8px;font-size:12px;cursor:pointer;transition:all 0.2s" class="eq-label">'
    + '<input type="checkbox" name="equip" value="' + escHtml(i.key) + '" style="accent-color:#059669"> ' + escHtml(i.name)
    + '</label>'
  ).join('');

  container.querySelectorAll('.eq-label').forEach(label => {
    const cb = label.querySelector('input[type="checkbox"]');
    cb.addEventListener('change', () => {
      label.style.borderColor = cb.checked ? '#059669' : '#E5E7EB';
      label.style.background = cb.checked ? '#D1FAE5' : '#fff';
    });
  });
}

// ───── Reservation Modal ─────
function openReservationModal(roomName, roomType, facilityId, resType) {
  // Reset form fields
  document.getElementById('res-event').value = '';
  document.getElementById('res-purpose').value = '';
  document.getElementById('res-start').value = '';
  document.getElementById('res-end').value = '';
  document.getElementById('res-attendees').value = '';
  document.getElementById('res-budget').value = '';
  document.getElementById('res-requests').value = '';
  document.querySelectorAll('input[name="equip"]').forEach(cb => {
    cb.checked = false;
    const lbl = cb.closest('.eq-label');
    if (lbl) { lbl.style.borderColor = '#E5E7EB'; lbl.style.background = '#fff'; }
  });

  // Set type
  document.querySelectorAll('input[name="res-type"]').forEach(r => r.checked = r.value === (resType || 'regular'));
  if (facilityId) document.getElementById('res-facility').value = facilityId;
  updateResTypeBanner();

  const titles = { emergency: '🚨 Emergency Meeting Reservation', vip: '⭐ VIP Advance Reservation', regular: 'New Reservation' };
  document.getElementById('modal-res-title').textContent = titles[resType] || 'New Reservation';

  if (resType === 'emergency') {
    document.getElementById('res-priority').value = 'urgent';
  } else if (resType === 'vip') {
    document.getElementById('res-priority').value = 'high';
  } else {
    document.getElementById('res-priority').value = 'normal';
  }

  openModal('modal-reservation');
}

// Type radio change handler
document.querySelectorAll('input[name="res-type"]').forEach(radio => {
  radio.addEventListener('change', updateResTypeBanner);
});

function updateResTypeBanner() {
  const banner = document.getElementById('res-type-banner');
  const val = document.querySelector('input[name="res-type"]:checked')?.value;
  document.querySelectorAll('.type-option').forEach(opt => {
    opt.style.borderColor = opt.dataset.type === val
      ? (val === 'vip' ? '#7C3AED' : val === 'emergency' ? '#DC2626' : '#059669')
      : '#E5E7EB';
    opt.style.background = opt.dataset.type === val
      ? (val === 'vip' ? '#EDE9FE' : val === 'emergency' ? '#FEE2E2' : '#D1FAE5')
      : '#fff';
  });
  if (val === 'vip') {
    banner.style.display = 'block';
    banner.style.background = '#EDE9FE';
    banner.style.color = '#5B21B6';
    banner.textContent = '⭐ VIP reservations have priority and advance scheduling for important guests.';
  } else if (val === 'emergency') {
    banner.style.display = 'block';
    banner.style.background = '#FEE2E2';
    banner.style.color = '#991B1B';
    banner.textContent = '🚨 Emergency meetings are auto-approved and marked urgent.';
  } else {
    banner.style.display = 'none';
  }
}

async function submitReservation() {
  const resType = document.querySelector('input[name="res-type"]:checked')?.value || 'regular';
  const event = document.getElementById('res-event').value.trim();
  const purpose = document.getElementById('res-purpose').value.trim();
  const start = document.getElementById('res-start').value;
  const end = document.getElementById('res-end').value;

  if (!event) return Swal.fire({ icon: 'warning', title: 'Missing Field', text: 'Please enter an event title.', confirmButtonColor: '#059669' });
  if (!purpose) return Swal.fire({ icon: 'warning', title: 'Missing Field', text: 'Please enter the purpose / description.', confirmButtonColor: '#059669' });
  if (!start || !end) return Swal.fire({ icon: 'warning', title: 'Missing Field', text: 'Please set start and end date/time.', confirmButtonColor: '#059669' });
  if (new Date(end) <= new Date(start)) return Swal.fire({ icon: 'error', title: 'Invalid Time', text: 'End time must be after start time.', confirmButtonColor: '#059669' });

  const equip = [];
  document.querySelectorAll('input[name="equip"]:checked').forEach(cb => equip.push(cb.value));

  const data = {
    facility_id: document.getElementById('res-facility').value,
    event_title: event,
    purpose: purpose,
    reservation_type: resType,
    priority: document.getElementById('res-priority').value,
    start_datetime: start,
    end_datetime: end,
    department: document.getElementById('res-dept').value,
    attendees_count: document.getElementById('res-attendees').value || null,
    budget: document.getElementById('res-budget').value || 0,
    equipment_needed: equip,
    special_requests: document.getElementById('res-requests').value,
  };

  const typeLabel = { regular: 'Regular', vip: '⭐ VIP', emergency: '🚨 Emergency' }[resType];
  const confirmResult = await Swal.fire({
    title: 'Submit Reservation?',
    html: '<b>' + typeLabel + '</b> reservation for <b>"' + escHtml(event) + '"</b>',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes, Submit',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#059669',
    cancelButtonColor: '#6B7280'
  });
  if (!confirmResult.isConfirmed) return;

  try {
    const res = await fetch(API + '?action=create_reservation', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });
    const result = await res.json();

    if (result.success || result.reservation_code) {
      await Swal.fire({
        icon: 'success',
        title: 'Reservation Submitted!',
        html: '<div style="text-align:left;font-size:14px;line-height:1.8">'
          + '<b>Code:</b> ' + escHtml(result.reservation_code || 'N/A') + '<br>'
          + '<b>Type:</b> ' + typeLabel + '<br>'
          + '<b>Event:</b> ' + escHtml(event) + '<br>'
          + '<b>Budget:</b> ' + formatMoney(data.budget) + '<br>'
          + '<b>Equipment:</b> ' + (equip.length ? equip.join(', ') : 'None') + '<br>'
          + '<b>Status:</b> ' + (resType === 'emergency' ? 'Auto-approved ✓' : 'Pending approval')
          + '</div>',
        confirmButtonColor: '#059669'
      });
      closeModal('modal-reservation');
      await loadData();
    } else {
      Swal.fire({ icon: 'error', title: 'Error', text: result.message || 'Failed to create reservation.', confirmButtonColor: '#059669' });
    }
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'Error', text: 'Network error. Please try again.', confirmButtonColor: '#059669' });
  }
}

// ───── Approve Reservation ─────
async function approveReservation(id) {
  const confirmResult = await Swal.fire({
    title: 'Approve Reservation?',
    text: 'This will approve the reservation and notify the requestor.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes, Approve',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#059669',
    cancelButtonColor: '#6B7280'
  });
  if (!confirmResult.isConfirmed) return;

  try {
    const res = await fetch(API + '?action=update_status', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ reservation_id: id, status: 'approved', remarks: 'Approved by admin' })
    });
    const result = await res.json();

    if (result.success) {
      await Swal.fire({ icon: 'success', title: 'Approved!', text: 'Reservation has been approved. Check Room Monitoring for updated status.', confirmButtonColor: '#059669' });
      await loadData();
      // Auto-switch to Room Monitoring sub-tab so user sees occupied/pending change
      switchBookingTab('rooms-view', document.querySelector('[data-booking-tab="rooms-view"]'));
    } else {
      Swal.fire({ icon: 'error', title: 'Error', text: result.message || 'Failed to approve.', confirmButtonColor: '#059669' });
    }
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'Error', text: 'Network error. Please try again.', confirmButtonColor: '#059669' });
  }
}

// ───── Validate Reservation ─────
async function validateReservation(id) {
  const confirmResult = await Swal.fire({
    title: 'Validate Reservation?',
    text: 'This will mark the reservation as validated.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes, Validate',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#059669',
    cancelButtonColor: '#6B7280'
  });
  if (!confirmResult.isConfirmed) return;

  try {
    const res = await fetch(API + '?action=validate_reservation', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ reservation_id: id })
    });
    const result = await res.json();

    if (result.success) {
      await Swal.fire({ icon: 'success', title: 'Validated!', text: 'Reservation has been validated.', confirmButtonColor: '#059669' });
      await loadData();
    } else {
      Swal.fire({ icon: 'error', title: 'Error', text: result.message || 'Failed to validate.', confirmButtonColor: '#059669' });
    }
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'Error', text: 'Network error. Please try again.', confirmButtonColor: '#059669' });
  }
}

// ───── Render Approved Bookings ─────
function renderApprovedBookings() {
  const tbody = document.getElementById('approved-tbody');
  const filter = document.getElementById('approved-filter-status').value;
  const now = new Date();

  // Get approved + completed reservations
  let approved = reservations.filter(r => r.status === 'approved' || r.status === 'completed');

  // Determine room status for each reservation
  approved = approved.map(r => {
    const start = new Date(r.start_datetime);
    const end = new Date(r.end_datetime);
    let roomStatus = 'upcoming'; // pending
    if (r.status === 'completed') {
      roomStatus = 'completed';
    } else if (now >= start && now <= end) {
      roomStatus = 'ongoing'; // occupied
    } else if (now > end) {
      roomStatus = 'completed';
    }
    return { ...r, _roomStatus: roomStatus };
  });

  // Apply filter
  if (filter) {
    approved = approved.filter(r => r._roomStatus === filter);
  }

  // Sort: ongoing first, then upcoming, then completed
  const statusOrder = { ongoing: 0, upcoming: 1, completed: 2 };
  approved.sort((a, b) => (statusOrder[a._roomStatus] ?? 9) - (statusOrder[b._roomStatus] ?? 9));

  if (!approved.length) {
    tbody.innerHTML = '<tr><td colspan="8" class="empty-state" style="padding:40px"><div style="font-size:36px;margin-bottom:8px">✅</div><div style="font-weight:600;color:#1F2937">No approved bookings</div><div style="font-size:13px;color:#9CA3AF;margin-top:4px">Approved reservations will appear here.</div></td></tr>';
    return;
  }

  tbody.innerHTML = approved.map(r => {
    // Type badge
    const typeBadges = {
      vip: '<span class="badge badge-purple">⭐ VIP</span>',
      emergency: '<span class="badge badge-red">🚨 Emergency</span>',
      regular: '<span class="badge badge-gray">Regular</span>',
    };
    const typeBadge = typeBadges[r.reservation_type] || typeBadges.regular;

    // Room status badge
    let roomStatusBadge = '';
    if (r._roomStatus === 'ongoing') {
      roomStatusBadge = '<span class="badge badge-red">🔴 Occupied</span>';
    } else if (r._roomStatus === 'upcoming') {
      roomStatusBadge = '<span class="badge badge-amber">⏳ Pending</span>';
    } else {
      roomStatusBadge = '<span class="badge badge-green">🟢 Available</span>';
    }

    // Actions
    let actions = '';
    if (r._roomStatus === 'ongoing') {
      actions = '<button class="btn btn-primary btn-sm" title="Mark as Done" onclick="completeReservation(' + r.reservation_id + ')">'
        + '✓ Done</button>';
    } else if (r._roomStatus === 'upcoming') {
      const evJson = JSON.stringify(r).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
      actions = '<button class="btn btn-outline btn-sm" title="View" onclick="showEventDetail(JSON.parse(this.dataset.ev))" data-ev="' + evJson + '">'
        + '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>';
    } else {
      actions = '<span class="badge badge-blue">Completed</span>';
    }

    return '<tr>'
      + '<td style="font-weight:600;font-size:12px">' + escHtml(r.reservation_code || '') + '</td>'
      + '<td style="font-weight:600">' + escHtml(r.facility_name || '') + '</td>'
      + '<td>' + escHtml(r.event_title || '') + (r.purpose ? '<br><span style="font-size:11px;color:#9CA3AF">' + escHtml(r.purpose) + '</span>' : '') + '</td>'
      + '<td>' + typeBadge + '</td>'
      + '<td style="font-size:12px">' + formatDateTime(r.start_datetime, r.end_datetime) + '</td>'
      + '<td>' + escHtml(r.department || '') + '</td>'
      + '<td>' + roomStatusBadge + '</td>'
      + '<td>' + actions + '</td>'
      + '</tr>';
  }).join('');
}

// ───── Complete Reservation (Mark as Done) ─────
async function completeReservation(id) {
  const confirmResult = await Swal.fire({
    title: 'Mark as Complete?',
    text: 'This will mark the reservation as done and set the room back to available.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes, Complete',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#059669',
    cancelButtonColor: '#6B7280'
  });
  if (!confirmResult.isConfirmed) return;

  try {
    const res = await fetch(API + '?action=complete_reservation', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ reservation_id: id })
    });
    const result = await res.json();

    if (result.success) {
      await Swal.fire({ icon: 'success', title: 'Completed!', text: 'Reservation marked as done. Room is now available.', confirmButtonColor: '#059669' });
      await loadData();
    } else {
      Swal.fire({ icon: 'error', title: 'Error', text: result.message || 'Failed to complete reservation.', confirmButtonColor: '#059669' });
    }
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'Error', text: 'Network error. Please try again.', confirmButtonColor: '#059669' });
  }
}

// ───── Room filter button styling on load ─────
document.querySelectorAll('.room-filter').forEach(btn => {
  if (btn.classList.contains('active')) {
    btn.style.background = '#059669';
    btn.style.color = '#fff';
    btn.style.borderColor = '#059669';
  }
  btn.addEventListener('click', () => {
    document.querySelectorAll('.room-filter').forEach(b => {
      b.style.background = '';
      b.style.color = '';
      b.style.borderColor = '';
    });
    btn.style.background = '#059669';
    btn.style.color = '#fff';
    btn.style.borderColor = '#059669';
  });
});

// ───── Section Switching (hash-driven) ─────
function showSection(hash) {
  const sections = document.querySelectorAll('.tab-content');
  let id = hash ? hash.replace('#', '') : 'tab-monitoring';

  // Map old tab-calendar and tab-booking hashes to the merged tab-monitoring
  if (id === 'tab-calendar') {
    id = 'tab-monitoring';
    setTimeout(() => switchBookingTab('calendar-view', document.querySelector('[data-booking-tab="calendar-view"]')), 100);
  } else if (id === 'tab-booking') {
    id = 'tab-monitoring';
    setTimeout(() => switchBookingTab('reservations-view', document.querySelector('[data-booking-tab="reservations-view"]')), 100);
  } else if (id === 'tab-approved') {
    setTimeout(() => renderApprovedBookings(), 100);
  }

  sections.forEach(s => s.classList.remove('active'));
  const target = document.getElementById(id);
  if (target) target.classList.add('active');
  else if (sections[0]) sections[0].classList.add('active');

  // Sync the top-level section tab bar
  document.querySelectorAll('#section-tabs .sub-tab').forEach(t => t.classList.remove('active'));
  const activeTabBtn = document.querySelector('#section-tabs .sub-tab[data-section="' + id + '"]');
  if (activeTabBtn) activeTabBtn.classList.add('active');
}

// ───── Section Tab Switcher (top-level tab bar) ─────
function switchSection(sectionId, btn) {
  location.hash = '#' + sectionId;
}

// ───── Booking Sub-tab Switcher ─────
function switchBookingTab(tabId, btn) {
  // Toggle sub-tab buttons
  document.querySelectorAll('#booking-sub-tabs .sub-tab').forEach(t => t.classList.remove('active'));
  if (btn) btn.classList.add('active');

  // Toggle sub-tab content panels
  document.querySelectorAll('#tab-monitoring .sub-tab-content').forEach(p => p.classList.remove('active'));
  const panel = document.getElementById(tabId);
  if (panel) panel.classList.add('active');

  // Render calendar when switching to calendar view
  if (tabId === 'calendar-view') renderCalendar();
}

window.addEventListener('hashchange', () => showSection(location.hash));

// ───── Init ─────
loadData().then(() => {
  showSection(location.hash);
});

// ───── Export Functions ─────
function exportReservations(format) {
  const headers = ['Code', 'Facility', 'Event / Purpose', 'Type', 'Start Date', 'End Date', 'Budget', 'Department', 'Status', 'Validated'];
  const rows = reservations.map(r => [
    r.reservation_code || '', r.facility_name || '', r.title || r.purpose || '',
    r.reservation_type || 'regular', r.start_datetime || '', r.end_datetime || '',
    r.budget || '0', r.department || '', r.status || '', r.validated ? 'Yes' : 'No'
  ]);
  if (format === 'csv') {
    ExportHelper.exportCSV('Facilities_Reservations', headers, rows);
  } else {
    ExportHelper.exportPDF('Facilities_Reservations', 'Facilities — All Reservations', headers, rows, { landscape: true, subtitle: reservations.length + ' records' });
  }
}

function exportEquipment(format) {
  const headers = ['Equipment', 'Type', 'Location', 'Condition', 'Status'];
  const rows = equipment.map(e => [
    e.name || e.equipment_name || '', e.type || e.equipment_type || '',
    e.location || '', e.condition_status || e.condition || '', e.status || ''
  ]);
  if (format === 'csv') {
    ExportHelper.exportCSV('Facilities_Equipment', headers, rows);
  } else {
    ExportHelper.exportPDF('Facilities_Equipment', 'Facilities — Equipment Inventory', headers, rows, { subtitle: equipment.length + ' items' });
  }
}

function exportMaintenance(format) {
  const headers = ['Request ID', 'Facility', 'Issue', 'Priority', 'Status', 'Requested Date', 'Assigned To'];
  const rows = maintenance.map(m => [
    m.request_id || m.id || '', m.facility_name || '', m.issue || m.description || '',
    m.priority || '', m.status || '', m.created_at || m.request_date || '', m.assigned_to || ''
  ]);
  if (format === 'csv') {
    ExportHelper.exportCSV('Facilities_Maintenance', headers, rows);
  } else {
    ExportHelper.exportPDF('Facilities_Maintenance', 'Facilities — Maintenance Requests', headers, rows, { subtitle: maintenance.length + ' requests' });
  }
}

function exportApprovedBookings(format) {
  const now = new Date();
  const approved = reservations.filter(r => r.status === 'approved' || r.status === 'completed').map(r => {
    const start = new Date(r.start_datetime);
    const end   = new Date(r.end_datetime);
    let roomStat = 'Pending';
    if (r.status === 'completed' || now > end) roomStat = 'Available';
    else if (now >= start && now <= end) roomStat = 'Occupied';
    return { ...r, _roomLabel: roomStat };
  });
  const headers = ['Code', 'Facility', 'Event / Purpose', 'Type', 'Date & Time', 'Department', 'Room Status'];
  const rows = approved.map(r => [
    r.reservation_code || '', r.facility_name || '', r.event_title || r.purpose || '',
    r.reservation_type || 'regular',
    (r.start_datetime || '') + ' — ' + (r.end_datetime || ''),
    r.department || '', r._roomLabel
  ]);
  if (format === 'csv') {
    ExportHelper.exportCSV('Approved_Bookings', headers, rows);
  } else {
    ExportHelper.exportPDF('Approved_Bookings', 'Facilities — Approved Bookings', headers, rows, { landscape: true, subtitle: approved.length + ' bookings' });
  }
}
</script>
</body>
</html>
