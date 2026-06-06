/**
 * Modern Admin Panel JavaScript
 * Bootstrap 5.3.0 Only
 */

(function () {
  "use strict";

  // Initialize when DOM is ready
  document.addEventListener("DOMContentLoaded", function () {
    initSidebar();
    initActiveMenu();
    initThemeToggle();
    initSessionTimer();
    initMobileSidebar();
  });

  /**
   * Sidebar Toggle Functionality
   */
  function initSidebar() {
    const headerCollapse = document.getElementById("headerCollapse");
    const sidebar = document.querySelector(".left-sidebar");
    const darkOverlay = document.querySelector(".dark-transparent");

    if (headerCollapse) {
      headerCollapse.addEventListener("click", function () {
        sidebar.classList.toggle("show");
        if (darkOverlay) {
          darkOverlay.classList.toggle("show");
        }
      });
    }

    // Close sidebar when clicking overlay
    if (darkOverlay) {
      darkOverlay.addEventListener("click", function () {
        sidebar.classList.remove("show");
        darkOverlay.classList.remove("show");
      });
    }
  }

  /**
   * Active Menu State Detection
   */
  function initActiveMenu() {
    const currentUrl = window.location.href;
    const sidebarLinks = document.querySelectorAll("#sidebarnav .sidebar-link");

    sidebarLinks.forEach(function (link) {
      const linkUrl = link.getAttribute("href");

      // Check if current URL matches this link
      if (linkUrl && currentUrl.includes(linkUrl.split("/").pop())) {
        link.classList.add("active");

        // Expand parent menu if it's a submenu
        const parentCollapse = link.closest(".collapse");
        if (parentCollapse) {
          const bsCollapse = new bootstrap.Collapse(parentCollapse, {
            toggle: false,
          });
          bsCollapse.show();

          // Mark parent link as active
          const parentLink = parentCollapse.previousElementSibling;
          if (parentLink && parentLink.classList.contains("sidebar-link")) {
            parentLink.classList.add("active");
          }
        }
      }
    });

    // Handle menu item clicks
    sidebarLinks.forEach(function (link) {
      link.addEventListener("click", function (e) {
        // Remove active from all links in the same level
        const parent = this.closest("ul");
        if (parent) {
          parent.querySelectorAll(".sidebar-link").forEach(function (l) {
            l.classList.remove("active");
          });
        }

        // Add active to clicked link
        this.classList.add("active");
      });
    });
  }

  /**
   * Theme Toggle (Light/Dark)
   */
  function initThemeToggle() {
    const themeToggle = document.getElementById("themeToggle");
    const themeIcon = document.getElementById("themeIcon");
    const html = document.documentElement;

    // Check for saved theme preference or default to light
    const currentTheme = localStorage.getItem("theme") || "light";
    html.setAttribute("data-bs-theme", currentTheme);
    updateThemeIcon(currentTheme);

    if (themeToggle) {
      themeToggle.addEventListener("click", function () {
        const currentTheme = html.getAttribute("data-bs-theme");
        const newTheme = currentTheme === "dark" ? "light" : "dark";

        html.setAttribute("data-bs-theme", newTheme);
        localStorage.setItem("theme", newTheme);
        updateThemeIcon(newTheme);
      });
    }

    function updateThemeIcon(theme) {
      if (themeIcon) {
        themeIcon.className = theme === "dark" ? "ti ti-sun" : "ti ti-moon";
      }
    }
  }

  /**
   * Session Timer (15 minutes) - Persists across page reloads using server-side session
   */
  function initSessionTimer() {
    const sessionTimer = document.getElementById("sessionTimer");
    if (!sessionTimer) return;

    let timeLeft = 0;
    let clientServerOffset = 0;
    let lastServerTime = 0;

    // Calculate client-server time offset on first load
    if (typeof window.serverTime !== "undefined" && window.serverTime > 0) {
      lastServerTime = window.serverTime;
      clientServerOffset = window.serverTime - Math.floor(Date.now() / 1000);
    }

    // Initialize timeLeft from server data
    if (
      typeof window.sessionRemainingTime !== "undefined" &&
      window.sessionRemainingTime > 0
    ) {
      // Use server-side remaining time (most accurate)
      timeLeft = Math.max(0, window.sessionRemainingTime);
    } else if (
      typeof window.sessionLoginTime !== "undefined" &&
      window.sessionLoginTime > 0 &&
      typeof window.sessionDuration !== "undefined"
    ) {
      // Calculate from login time using server time
      const currentServerTime =
        Math.floor(Date.now() / 1000) + clientServerOffset;
      const loginTime = window.sessionLoginTime;
      const sessionDuration = window.sessionDuration;
      const elapsed = currentServerTime - loginTime;
      timeLeft = Math.max(0, sessionDuration - elapsed);
    } else {
      // Fallback: if no server data, don't show timer
      if (sessionTimer) {
        sessionTimer.textContent = "";
      }
      return;
    }

    let lastUpdateTime = Date.now();

    function updateTimer() {
      // Calculate elapsed time since last update
      const now = Date.now();
      const elapsed = Math.floor((now - lastUpdateTime) / 1000);
      lastUpdateTime = now;

      // Decrease timeLeft by elapsed seconds
      timeLeft = Math.max(0, timeLeft - elapsed);

      const minutes = Math.floor(timeLeft / 60);
      const seconds = timeLeft % 60;

      if (sessionTimer) {
        sessionTimer.textContent = `Session: ${minutes}:${seconds.toString().padStart(2, "0")}`;

        // Change color when less than 2 minutes remaining
        if (timeLeft < 120) {
          sessionTimer.classList.add("text-danger");
          sessionTimer.classList.add("fw-bold");
        } else {
          sessionTimer.classList.remove("text-danger");
          sessionTimer.classList.remove("fw-bold");
        }
      }

      if (timeLeft <= 0) {
        // Session expired - redirect to login
        alert("Your session has expired. Please login again.");
        window.location.href = "index.php";
        return;
      }
    }

    // Update timer every second
    const timerInterval = setInterval(updateTimer, 1000);
    updateTimer();

    // Sync with server on page visibility change
    document.addEventListener("visibilitychange", function () {
      if (
        !document.hidden &&
        typeof window.sessionRemainingTime !== "undefined" &&
        window.sessionRemainingTime >= 0
      ) {
        // Page is visible again - reload to sync with server
        // Or update timeLeft if we have fresh server data
        if (window.sessionRemainingTime !== undefined) {
          timeLeft = Math.max(0, window.sessionRemainingTime);
          lastUpdateTime = Date.now();
        }
      }
    });

    // Sync on window focus
    window.addEventListener("focus", function () {
      // Reload session data from server by refreshing the page data
      // For now, just update timeLeft if available
      if (
        typeof window.sessionRemainingTime !== "undefined" &&
        window.sessionRemainingTime >= 0
      ) {
        timeLeft = Math.max(0, window.sessionRemainingTime);
        lastUpdateTime = Date.now();
      }
    });
  }

  /**
   * Mobile Sidebar Toggle
   */
  function initMobileSidebar() {
    const mobileToggle = document.getElementById("mobileSidebarToggle");
    const sidebar = document.querySelector(".left-sidebar");
    const darkOverlay = document.querySelector(".dark-transparent");

    if (mobileToggle) {
      mobileToggle.addEventListener("click", function () {
        sidebar.classList.remove("show");
        if (darkOverlay) {
          darkOverlay.classList.remove("show");
        }
      });
    }
  }

  /**
   * Smooth Scroll for Anchor Links
   */
  document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
    anchor.addEventListener("click", function (e) {
      const href = this.getAttribute("href");
      if (href !== "#" && href.length > 1) {
        const target = document.querySelector(href);
        if (target) {
          e.preventDefault();
          target.scrollIntoView({
            behavior: "smooth",
            block: "start",
          });
        }
      }
    });
  });

  /**
   * Auto-hide Alerts
   */
  const alerts = document.querySelectorAll(".alert:not(.alert-permanent)");
  alerts.forEach(function (alert) {
    setTimeout(function () {
      const bsAlert = new bootstrap.Alert(alert);
      bsAlert.close();
    }, 5000);
  });

  /**
   * Form Validation Enhancement
   */
  const forms = document.querySelectorAll(".needs-validation");
  forms.forEach(function (form) {
    form.addEventListener(
      "submit",
      function (event) {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add("was-validated");
      },
      false,
    );
  });

  /**
   * Tooltip Initialization
   */
  const tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]'),
  );
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  /**
   * Popover Initialization
   */
  const popoverTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="popover"]'),
  );
  popoverTriggerList.map(function (popoverTriggerEl) {
    return new bootstrap.Popover(popoverTriggerEl);
  });

  /**
   * Loading State for Buttons
   */
  document.querySelectorAll("form").forEach(function (form) {
    form.addEventListener("submit", function () {
      const submitBtn = form.querySelector('button[type="submit"]');
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML =
          '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
      }
    });
  });

  document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".only-number").forEach(function (input) {
      input.addEventListener("input", function () {
        this.value = this.value.replace(/[^0-9]/g, "");
      });
    });

    document.querySelectorAll(".only-alphabet").forEach(function (input) {
      input.addEventListener("input", function () {
        this.value = this.value.replace(/[^a-zA-Z\s]/g, "");
      });
    });
  });
})();
