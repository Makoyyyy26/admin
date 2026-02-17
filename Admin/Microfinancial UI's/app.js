document.addEventListener("DOMContentLoaded", () => {
  // ---------------------------
  // Realtime Clock (Header)
  // ---------------------------
  const clockEl = document.getElementById("real-time-clock");
  const updateClock = () => {
    if (!clockEl) return;
    const now = new Date();
    clockEl.textContent = now.toLocaleTimeString("en-US", {
      hour12: true,
      hour: "2-digit",
      minute: "2-digit",
      second: "2-digit",
    });
  };
  if (clockEl) {
    updateClock();
    setInterval(updateClock, 1000);
  }

  // ---------------------------
  // User Dropdown (Header)
  // ---------------------------
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
      if (!userDropdown.classList.contains("hidden")) closeDropdown();
    });
  }

  // ---------------------------
  // Sidebar Dropdown Submenus
  // ---------------------------
  function setupDropdown(btnId, submenuId, arrowId) {
    const btn = document.getElementById(btnId);
    const submenu = document.getElementById(submenuId);
    const arrow = document.getElementById(arrowId);

    if (!btn || !submenu || !arrow) return;

    if (submenu.classList.contains("is-open")) arrow.classList.add("rotate-180");

    btn.addEventListener("click", () => {
      submenu.classList.toggle("is-open");
      arrow.classList.toggle("rotate-180");
    });
  }

  setupDropdown("leave-menu-btn", "leave-submenu", "leave-arrow");
  setupDropdown("claim-menu-btn", "claim-submenu", "claim-arrow");
  setupDropdown("reports-menu-btn", "reports-submenu", "reports-arrow");

  // ---------------------------
  // Mobile Sidebar Toggle + Overlay
  // ---------------------------
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

  // ---------------------------
  // Login: Password eye toggle
  // ---------------------------
  const pwd = document.getElementById("password");
  const toggle = document.getElementById("password-toggle");
  const eyeOpen = document.getElementById("eye-open");
  const eyeClosed = document.getElementById("eye-closed");

  if (pwd && toggle && eyeOpen && eyeClosed) {
    eyeClosed.classList.add("hidden");
    toggle.addEventListener("click", () => {
      const isPassword = pwd.getAttribute("type") === "password";
      pwd.setAttribute("type", isPassword ? "text" : "password");
      eyeOpen.classList.toggle("hidden");
      eyeClosed.classList.toggle("hidden");
      toggle.classList.add("scale-95");
      setTimeout(() => toggle.classList.remove("scale-95"), 120);
    });
  }

  // ---------------------------
  // Login: Terms checkbox enables Sign In
  // ---------------------------
  const termsCheck = document.getElementById("terms-check");
  const signInBtn = document.getElementById("sign-in-btn");
  const loginForm = document.getElementById("login-form");

  const setSignInEnabled = (enabled) => {
    if (!signInBtn) return;
    signInBtn.disabled = !enabled;

    if (enabled) {
      signInBtn.classList.remove("opacity-60", "cursor-not-allowed");
      signInBtn.classList.add("hover:bg-brand-primary-hover", "hover:shadow-xl", "hover:-translate-y-0.5");
    } else {
      signInBtn.classList.add("opacity-60", "cursor-not-allowed");
      signInBtn.classList.remove("hover:bg-brand-primary-hover", "hover:shadow-xl", "hover:-translate-y-0.5");
    }
  };

  if (termsCheck && signInBtn) {
    setSignInEnabled(termsCheck.checked);
    termsCheck.addEventListener("change", () => setSignInEnabled(termsCheck.checked));
  }

  if (loginForm) {
    loginForm.addEventListener("submit", (e) => {
      if (termsCheck && !termsCheck.checked) {
        e.preventDefault();
      }
    });
  }

  // ---------------------------
  // Login: Terms modal
  // ---------------------------
  const termsLink = document.getElementById("terms-link");
  const termsModal = document.getElementById("terms-modal");
  const termsBackdrop = document.getElementById("terms-backdrop");
  const termsPanel = document.getElementById("terms-panel");
  const termsClose = document.getElementById("terms-close");
  const termsCloseBottom = document.getElementById("terms-close-bottom");

  const openTerms = () => {
    if (!termsModal || !termsBackdrop || !termsPanel) return;
    termsModal.classList.remove("hidden");
    requestAnimationFrame(() => {
      termsBackdrop.classList.remove("opacity-0");
      termsPanel.classList.remove("opacity-0", "scale-95", "translate-y-2");
      termsPanel.classList.add("opacity-100", "scale-100", "translate-y-0");
    });
  };

  const closeTerms = () => {
    if (!termsModal || !termsBackdrop || !termsPanel) return;
    termsBackdrop.classList.add("opacity-0");
    termsPanel.classList.add("opacity-0", "scale-95", "translate-y-2");
    termsPanel.classList.remove("opacity-100", "scale-100", "translate-y-0");
    setTimeout(() => termsModal.classList.add("hidden"), 200);
  };

  if (termsLink) termsLink.addEventListener("click", openTerms);
  if (termsBackdrop) termsBackdrop.addEventListener("click", closeTerms);
  if (termsClose) termsClose.addEventListener("click", closeTerms);
  if (termsCloseBottom) termsCloseBottom.addEventListener("click", closeTerms);

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && termsModal && !termsModal.classList.contains("hidden")) {
      closeTerms();
    }
  });

  // ---------------------------
  // Login: Illustration carousel (fade)
  // ---------------------------
  const svgs = Array.from(document.querySelectorAll(".login-svg"));
  if (svgs.length > 1) {
    let i = 0;
    svgs.forEach((el, idx) => {
      el.classList.add("transition-opacity", "duration-700");
      el.style.opacity = idx === 0 ? "1" : "0";
    });

    setInterval(() => {
      svgs[i].style.opacity = "0";
      i = (i + 1) % svgs.length;
      svgs[i].style.opacity = "1";
    }, 4500);
  }
});
