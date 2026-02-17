<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Landing — Microfinancial Management System</title>

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

  <link rel="stylesheet" href="styles.css" />
</head>

<body class="bg-brand-background-main min-h-screen">

  <!-- Overlay (mobile) -->
  <div id="sidebar-overlay" class="fixed inset-0 bg-black/30 hidden opacity-0 transition-opacity duration-300 z-40"></div>

  <!-- SIDEBAR -->
  <aside id="sidebar"
    class="fixed top-0 left-0 h-full w-72 bg-white border-r border-gray-100 shadow-sm z-50
           transform -translate-x-full md:translate-x-0 transition-transform duration-300">

    <div class="h-16 flex items-center px-4 border-b border-gray-100">
      <a href="#"
        class="flex items-center gap-3 w-full rounded-xl px-2 py-2
               hover:bg-gray-100 active:bg-gray-200 transition group">
        <img src="assets/images/logo.png" alt="Logo" class="w-10 h-10">
        <div class="leading-tight">
          <div class="font-bold text-gray-800 group-hover:text-brand-primary transition-colors">
            Microfinancial ADMIN
          </div>
          <div class="text-[11px] text-gray-500 font-semibold uppercase group-hover:text-brand-primary transition-colors">
            ADMINISTRATIVE
          </div>
        </div>
      </a>
    </div>

    <!-- Sidebar content -->
    <div class="px-4 py-4 overflow-y-auto h-[calc(100%-4rem)] custom-scrollbar">
      <div class="text-xs font-bold text-gray-400 tracking-wider px-2">MAIN MENU</div>

      <a href="#"
        class="mt-3 flex items-center justify-between px-4 py-3 rounded-xl bg-brand-primary text-white shadow
               transition-all duration-200 active:scale-[0.99]">
        <span class="flex items-center gap-3 font-semibold">
          <span class="inline-flex w-9 h-9 rounded-lg bg-white/15 items-center justify-center">🏠</span>
          Sample 1
        </span>
      </a>

      <div class="text-xs font-bold text-gray-400 tracking-wider px-2 mt-6">TEAM MANAGEMENT</div>

      <!-- Dropdown Sample 2 -->
      <button id="leave-menu-btn"
        class="mt-3 w-full flex items-center justify-between px-4 py-3 rounded-xl
               text-gray-700 hover:bg-green-50 hover:text-brand-primary
               transition-all duration-200 hover:translate-x-1 active:translate-x-0 active:scale-[0.99] font-semibold">
        <span class="flex items-center gap-3">
          <span class="inline-flex w-9 h-9 rounded-lg bg-emerald-50 items-center justify-center">📅</span>
          Sample 2
        </span>
        <svg id="leave-arrow" class="w-4 h-4 text-emerald-400 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
      </button>

      <div id="leave-submenu" class="submenu mt-1">
        <div class="pl-4 pr-2 py-2 space-y-1 border-l-2 border-gray-100 ml-6">
          <a href="#" class="block px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-green-50 hover:text-brand-primary transition-all duration-200 hover:translate-x-1">Sample 3</a>
          <a href="#" class="block px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-green-50 hover:text-brand-primary transition-all duration-200 hover:translate-x-1">Sample 4</a>
        </div>
      </div>

      <div class="text-xs font-bold text-gray-400 tracking-wider px-2 mt-6">SYSTEM ADMIN</div>
      <a href="#"
        class="mt-3 flex items-center gap-3 px-4 py-3 rounded-xl text-gray-700
               hover:bg-green-50 hover:text-brand-primary
               transition-all duration-200 hover:translate-x-1 active:scale-[0.99] font-semibold">
        <span class="inline-flex w-9 h-9 rounded-lg bg-emerald-50 items-center justify-center">👥</span>
        Sample 5
      </a>

      <div class="mt-8 px-2">
        <div class="flex items-center gap-2 text-xs font-bold text-emerald-600">
          <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
          SYSTEM ONLINE
        </div>
        <div class="text-[11px] text-gray-400 mt-2 leading-snug">
          Microfinancial © 2026<br/>
          Management System I — Administrative
        </div>
      </div>
    </div>
  </aside>

  <!-- ✅ MAIN WRAPPER (header starts after sidebar width) -->
  <div class="md:pl-72">

    <!-- ✅ TOP HEADER (ONLY RIGHT SIDE AREA) -->
<header class="h-16 bg-white flex items-center justify-between px-4 sm:px-6 relative
               shadow-[0_2px_8px_rgba(0,0,0,0.06)]">

      
      <!-- ✅ BORDER COVER (removes the vertical line only in header height) -->
      <div class="hidden md:block absolute left-0 top-0 h-16 w-[2px] bg-white"></div>

      <div class="flex items-center gap-3">
        <button id="mobile-menu-btn"
          class="md:hidden w-10 h-10 rounded-xl hover:bg-gray-100 active:bg-gray-200 transition flex items-center justify-center">
          ☰
        </button>
      </div>

      <div class="flex items-center gap-3 sm:gap-5">
        <!-- Clock pill -->
        <span id="real-time-clock"
          class="text-xs font-bold text-gray-700 bg-gray-50 px-3 py-2 rounded-lg border border-gray-200">
          --:--:--
        </span>

        <!-- Bell -->
        <button class="w-10 h-10 rounded-xl hover:bg-gray-100 active:bg-gray-200 transition flex items-center justify-center relative">
          🔔
          <span class="absolute top-2 right-2 w-2.5 h-2.5 rounded-full bg-red-500 border-2 border-white"></span>
        </button>

        <div class="h-8 w-px bg-gray-200 hidden sm:block"></div>

        <!-- User Profile Dropdown -->
        <div class="relative">
          <button id="user-menu-button"
            class="flex items-center gap-3 focus:outline-none group rounded-xl px-2 py-2
                   hover:bg-gray-100 active:bg-gray-200 transition">
            <div class="w-10 h-10 rounded-full bg-white shadow group-hover:shadow-md transition-shadow overflow-hidden flex items-center justify-center border border-gray-100">
              <div class="w-full h-full flex items-center justify-center font-bold text-brand-primary bg-emerald-50">M</div>
            </div>
            <div class="hidden md:flex flex-col items-start text-left">
              <span class="text-sm font-bold text-gray-700 group-hover:text-brand-primary transition-colors">
                Manager Reyes
              </span>
              <span class="text-[10px] text-gray-500 font-medium uppercase group-hover:text-brand-primary transition-colors">
                Admin
              </span>
            </div>
            <svg class="w-4 h-4 text-gray-400 group-hover:text-brand-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </button>

          <div id="user-menu-dropdown"
            class="dropdown-panel hidden opacity-0 translate-y-2 scale-95 pointer-events-none
                   absolute right-0 mt-3 w-56 bg-white rounded-xl shadow-lg border border-gray-100
                   transition-all duration-200 z-50">
            <a href="#" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition">Profile</a>
            <a href="#" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition">Settings</a>
            <div class="h-px bg-gray-100"></div>
            <a href="#" class="block px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition">Logout</a>
          </div>
        </div>
      </div>
    </header>

    <main id="main-content" class="p-6">
      <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-6 text-gray-500">
        Center content intentionally left minimal (Header + Sidebar only).
      </div>
    </main>
  </div>

  <script src="app.js"></script>
</body>
</html>
