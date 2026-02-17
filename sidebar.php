<?php
/**
 * Shared Sidebar Component
 * 
 * Set $activePage before including this file:
 *   'dashboard', 'facilities', 'documents', 'legal', 'visitors'
 * 
 * Set $baseUrl to the relative path to the Admin root:
 *   '' (for root files like dashboard.php)
 *   '../../' (for module files under modules/xxx/)
 */

if (session_status() === PHP_SESSION_NONE) session_start();

// ─── User data from session (set by auth.php after OTP verify) ───
$sessionUser = $_SESSION['user'] ?? [];
$userName    = $sessionUser['name'] ?? 'Guest';
$roleMap     = ['super_admin' => 'System Administrator', 'admin' => 'Admin', 'manager' => 'Manager', 'staff' => 'Staff'];
$userRole    = $roleMap[$sessionUser['role'] ?? ''] ?? ucwords(str_replace('_', ' ', $sessionUser['role'] ?? 'unknown'));
$userInitial = strtoupper(substr($sessionUser['first_name'] ?? $userName, 0, 1));
$userDept    = $sessionUser['department'] ?? '';
$userId      = $sessionUser['user_id'] ?? 0;

if (!isset($activePage)) $activePage = '';
if (!isset($baseUrl)) $baseUrl = '';

// Build URL helpers
$dash   = $baseUrl . 'dashboard.php';
$fac    = $baseUrl . 'modules/facilities/facilities.php';
$doc    = $baseUrl . 'modules/documents/documents.php';
$legal  = $baseUrl . 'modules/legal/legal.php';
$vis    = $baseUrl . 'modules/visitors/visitors.php';
$logo   = $baseUrl . 'assets/images/logo.png';

// Active state helpers
function sidebarBtnClass($page, $current) {
  if ($page === $current) {
    return 'w-full flex items-center justify-between px-3 py-2.5 rounded-xl bg-brand-primary text-white shadow transition-all duration-200 active:scale-[0.99] font-semibold text-[13px]';
  }
  return 'w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-gray-700 hover:bg-green-50 hover:text-brand-primary transition-all duration-200 hover:translate-x-1 active:translate-x-0 active:scale-[0.99] font-semibold text-[13px]';
}

function sidebarIconWrap($page, $current) {
  return ($page === $current) ? 'inline-flex w-8 h-8 rounded-lg bg-white/15 items-center justify-center text-sm flex-shrink-0' : 'inline-flex w-8 h-8 rounded-lg bg-emerald-50 items-center justify-center text-sm flex-shrink-0';
}

function sidebarArrowClass($page, $current) {
  $base = 'w-4 h-4 transition-transform duration-300 arrow';
  if ($page === $current) return $base . ' text-white rotate-180';
  return $base . ' text-emerald-400';
}

function submenuClass($page, $current) {
  return ($page === $current) ? 'submenu is-open mt-1' : 'submenu mt-1';
}

function subLinkClass($isActive = false) {
  if ($isActive) return 'flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-brand-primary bg-green-50 font-semibold transition-all duration-200';
  return 'flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-green-50 hover:text-brand-primary transition-all duration-200 hover:translate-x-1';
}

function subArrow($isActive = false) {
  $color = $isActive ? 'text-brand-primary' : 'text-gray-400 group-hover:text-brand-primary';
  return '<svg class="w-3.5 h-3.5 flex-shrink-0 ' . $color . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>';
}
?>

<!-- Overlay (mobile) -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black/30 hidden opacity-0 transition-opacity duration-300 z-40"></div>

<!-- SIDEBAR -->
<aside id="sidebar"
  class="fixed top-0 left-0 h-full w-72 bg-white border-r border-gray-100 shadow-sm z-50
         transform -translate-x-full md:translate-x-0 transition-transform duration-300">

  <div class="h-16 flex items-center px-3 border-b border-gray-100">
    <a href="<?= $dash ?>" class="flex items-center gap-2.5 w-full rounded-xl px-2 py-2 hover:bg-gray-100 active:bg-gray-200 transition group">
      <img src="<?= $logo ?>" alt="Logo" class="w-10 h-10 flex-shrink-0">
      <div class="leading-tight min-w-0">
        <div class="text-base font-extrabold text-gray-800 group-hover:text-brand-primary transition-colors truncate tracking-tight">MICROFINANCIAL</div>
        <div class="text-xs text-gray-400 font-bold uppercase tracking-widest group-hover:text-brand-primary transition-colors">ADMINISTRATIVE</div>
      </div>
    </a>
  </div>

  <div class="px-4 py-4 overflow-y-auto h-[calc(100%-4rem)] custom-scrollbar">
    <div class="text-xs font-bold text-gray-400 tracking-wider px-2">MAIN MENU</div>

    <!-- Dashboard -->
    <?php if ($activePage === 'dashboard'): ?>
    <a href="<?= $dash ?>" class="mt-3 flex items-center justify-between px-3 py-2.5 rounded-xl bg-brand-primary text-white shadow transition-all duration-200 active:scale-[0.99] text-[13px]">
      <span class="flex items-center gap-2 font-semibold">
        <span class="inline-flex w-8 h-8 rounded-lg bg-white/15 items-center justify-center text-sm">📊</span> Dashboard
      </span>
    </a>
    <?php else: ?>
    <a href="<?= $dash ?>" class="mt-3 flex items-center gap-2 px-3 py-2.5 rounded-xl text-gray-700 hover:bg-green-50 hover:text-brand-primary transition-all duration-200 hover:translate-x-1 active:scale-[0.99] font-semibold text-[13px]">
      <span class="inline-flex w-8 h-8 rounded-lg bg-emerald-50 items-center justify-center text-sm">📊</span> Dashboard
    </a>
    <?php endif; ?>

    <div class="text-xs font-bold text-gray-400 tracking-wider px-2 mt-6">MODULES</div>

    <!-- ═══ Facilities ═══ -->
    <button data-submenu="facilities-submenu" class="mt-3 <?= sidebarBtnClass('facilities', $activePage) ?>">
      <span class="flex items-center gap-2 min-w-0">
        <span class="<?= sidebarIconWrap('facilities', $activePage) ?>">🏢</span><span class="truncate">Facilities Reservation</span>
      </span>
      <svg class="<?= sidebarArrowClass('facilities', $activePage) ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
    </button>
    <div id="facilities-submenu" class="<?= submenuClass('facilities', $activePage) ?>">
      <div class="pl-4 pr-2 py-2 space-y-1 border-l-2 border-gray-100 ml-6">
        <a href="<?= $fac ?>#tab-monitoring" data-hash="tab-monitoring" class="sidebar-sublink <?= subLinkClass() ?>"><?= subArrow() ?> Room Booking & Calendar</a>
        <a href="<?= $fac ?>#tab-approved" data-hash="tab-approved" class="sidebar-sublink <?= subLinkClass() ?>"><?= subArrow() ?> Approved Bookings</a>
        <a href="<?= $fac ?>#tab-equipment" data-hash="tab-equipment" class="sidebar-sublink <?= subLinkClass() ?>"><?= subArrow() ?> Equipment Reservation</a>
        <a href="<?= $fac ?>#tab-maintenance" data-hash="tab-maintenance" class="sidebar-sublink <?= subLinkClass() ?>"><?= subArrow() ?> Maintenance Requests</a>
      </div>
    </div>

    <!-- ═══ Documents ═══ -->
    <button data-submenu="documents-submenu" class="mt-2 <?= sidebarBtnClass('documents', $activePage) ?>">
      <span class="flex items-center gap-2 min-w-0">
        <span class="<?= sidebarIconWrap('documents', $activePage) ?>">📄</span><span class="truncate">Document Management</span>
      </span>
      <svg class="<?= sidebarArrowClass('documents', $activePage) ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
    </button>
    <div id="documents-submenu" class="<?= submenuClass('documents', $activePage) ?>">
      <div class="pl-4 pr-2 py-2 space-y-1 border-l-2 border-gray-100 ml-6">
        <a href="<?= $doc ?>#tab-folders" data-hash="tab-folders" class="sidebar-sublink <?= subLinkClass() ?>"><?= subArrow() ?> Department Folders</a>
        <a href="<?= $doc ?>#tab-all" data-hash="tab-all" class="sidebar-sublink <?= subLinkClass() ?>"><?= subArrow() ?> All Documents</a>
        <a href="<?= $doc ?>#tab-secure-storage" data-hash="tab-secure-storage" class="sidebar-sublink <?= subLinkClass() ?>"><?= subArrow() ?> Secure Storage</a>
        <a href="<?= $doc ?>#tab-ocr" data-hash="tab-ocr" class="sidebar-sublink <?= subLinkClass() ?>"><?= subArrow() ?> OCR Scanning</a>
        <a href="<?= $doc ?>#tab-versions" data-hash="tab-versions" class="sidebar-sublink <?= subLinkClass() ?>"><?= subArrow() ?> Version Control</a>
        <a href="<?= $doc ?>#tab-archiving" data-hash="tab-archiving" class="sidebar-sublink <?= subLinkClass() ?>"><?= subArrow() ?> Archiving</a>
        <a href="<?= $doc ?>#tab-access-control" data-hash="tab-access-control" class="sidebar-sublink <?= subLinkClass() ?>"><?= subArrow() ?> Access Control</a>
      </div>
    </div>

    <!-- ═══ Legal ═══ -->
    <button data-submenu="legal-submenu" class="mt-2 <?= sidebarBtnClass('legal', $activePage) ?>">
      <span class="flex items-center gap-2 min-w-0">
        <span class="<?= sidebarIconWrap('legal', $activePage) ?>">⚖️</span><span class="truncate">Legal Management</span>
      </span>
      <svg class="<?= sidebarArrowClass('legal', $activePage) ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
    </button>
    <div id="legal-submenu" class="<?= submenuClass('legal', $activePage) ?>">
      <div class="pl-4 pr-2 py-2 space-y-1 border-l-2 border-gray-100 ml-6">
        <a href="<?= $legal ?>#tab-loans" data-hash="tab-loans" class="sidebar-sublink <?= subLinkClass() ?>"><?= subArrow() ?> Loan Documentation</a>
        <a href="<?= $legal ?>#tab-collateral" data-hash="tab-collateral" class="sidebar-sublink <?= subLinkClass() ?>"><?= subArrow() ?> Collateral & Security</a>
        <a href="<?= $legal ?>#tab-cases" data-hash="tab-cases" class="sidebar-sublink <?= subLinkClass() ?>"><?= subArrow() ?> Litigation & Recovery</a>
        <a href="<?= $legal ?>#tab-compliance" data-hash="tab-compliance" class="sidebar-sublink <?= subLinkClass() ?>"><?= subArrow() ?> Compliance & KYC</a>
        <a href="<?= $legal ?>#tab-governance" data-hash="tab-governance" class="sidebar-sublink <?= subLinkClass() ?>"><?= subArrow() ?> Corporate Governance</a>
        <a href="<?= $legal ?>#tab-contracts" data-hash="tab-contracts" class="sidebar-sublink <?= subLinkClass() ?>"><?= subArrow() ?> Contracts & Agreements</a>
        <a href="<?= $legal ?>#tab-permits" data-hash="tab-permits" class="sidebar-sublink <?= subLinkClass() ?>"><?= subArrow() ?> Permits & Licensing</a>
        <a href="<?= $legal ?>#tab-legal-calendar" data-hash="tab-legal-calendar" class="sidebar-sublink <?= subLinkClass() ?>"><?= subArrow() ?> Legal Calendar</a>
      </div>
    </div>

    <!-- ═══ Visitors ═══ -->
    <button data-submenu="visitors-submenu" class="mt-2 <?= sidebarBtnClass('visitors', $activePage) ?>">
      <span class="flex items-center gap-2 min-w-0">
        <span class="<?= sidebarIconWrap('visitors', $activePage) ?>">🧑‍💼</span><span class="truncate">Visitor Management</span>
      </span>
      <svg class="<?= sidebarArrowClass('visitors', $activePage) ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
    </button>
    <div id="visitors-submenu" class="<?= submenuClass('visitors', $activePage) ?>">
      <div class="pl-4 pr-2 py-2 space-y-1 border-l-2 border-gray-100 ml-6">
        <a href="<?= $vis ?>#tab-registration" data-hash="tab-registration" class="sidebar-sublink <?= subLinkClass() ?>"><?= subArrow() ?> Visitor Registration</a>
        <a href="<?= $vis ?>#tab-qr" data-hash="tab-qr" class="sidebar-sublink <?= subLinkClass() ?>"><?= subArrow() ?> QR Pass Generation</a>
        <a href="<?= $vis ?>#tab-logs" data-hash="tab-logs" class="sidebar-sublink <?= subLinkClass() ?>"><?= subArrow() ?> Visitor Logs</a>
        <a href="<?= $vis ?>#tab-analytics" data-hash="tab-analytics" class="sidebar-sublink <?= subLinkClass() ?>"><?= subArrow() ?> Visitor Analytics</a>
      </div>
    </div>

    <div class="mt-8 px-2">
      <div class="flex items-center gap-2 text-xs font-bold text-emerald-600">
        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
        SYSTEM ONLINE
      </div>
      <div class="text-[11px] text-gray-400 mt-2 leading-snug">
        Microfinancial &copy; 2026<br/>
        Management System I — Administrative
      </div>
    </div>

    
</aside>

<script>
  window.__mf_user = {
    name: <?= json_encode($userName) ?>,
    role: <?= json_encode($userRole) ?>,
    initial: <?= json_encode($userInitial) ?>,
    employee_id: <?= json_encode($sessionUser['employee_id'] ?? '') ?>,
    department: <?= json_encode($userDept) ?>,
    email: <?= json_encode($sessionUser['email'] ?? '') ?>
  };
</script>
