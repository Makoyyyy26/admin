<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>OTP Verification — Microfinancial Management System</title>

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
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="min-h-screen bg-brand-primary relative overflow-hidden">

  <!-- Floating Shapes Background -->
  <div class="absolute inset-0 z-0">
    <div class="shape w-72 h-72 top-[5%] left-[-5%] bg-white/5"></div>
    <div class="shape shape-2 w-96 h-96 bottom-[-20%] left-[15%] bg-white/5"></div>
    <div class="shape shape-3 w-80 h-80 top-[-15%] right-[-10%] bg-white/5"></div>
    <div class="shape shape-4 w-56 h-56 bottom-[5%] right-[10%] bg-white/5"></div>
    <div class="shape shape-5 w-48 h-48 top-[50%] left-[50%] -translate-x-1/2 -translate-y-1/2 bg-white/5"></div>
  </div>

  <div class="min-h-screen flex items-center justify-center relative z-10 p-4">
    <div class="w-full max-w-md bg-white/90 backdrop-blur-lg rounded-2xl shadow-2xl p-8">

      <!-- Header -->
      <div class="text-center mb-6">
        <div class="mx-auto w-16 h-16 bg-brand-background-main rounded-full flex items-center justify-center mb-4">
          <svg class="w-8 h-8 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
          </svg>
        </div>
        <h2 class="text-2xl font-bold text-brand-text-primary">OTP Verification</h2>
        <p class="text-brand-text-secondary mt-1 text-sm">
          A 6-digit code was sent to your email.<br/>
          Enter it below to complete your login.
        </p>
      </div>

      <!-- Timer -->
      <div class="text-center mb-6">
        <div id="timer-ring" class="relative inline-flex items-center justify-center">
          <svg class="w-20 h-20 transform -rotate-90" viewBox="0 0 80 80">
            <circle cx="40" cy="40" r="34" fill="none" stroke="#E5E7EB" stroke-width="6"></circle>
            <circle id="timer-circle" cx="40" cy="40" r="34" fill="none" stroke="#059669" stroke-width="6"
                    stroke-dasharray="213.63" stroke-dashoffset="0" stroke-linecap="round"
                    class="transition-all duration-1000 ease-linear"></circle>
          </svg>
          <span id="timer-text" class="absolute text-xl font-bold text-brand-primary">60</span>
        </div>
        <p id="timer-label" class="text-sm text-brand-text-secondary mt-2">Time remaining</p>
      </div>

      <!-- OTP Input Boxes -->
      <form id="otp-form" class="mb-6">
        <div id="otp-inputs" class="flex justify-center gap-3 mb-6">
          <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"
                 class="otp-box w-12 h-14 text-center text-2xl font-bold border-2 border-gray-300 rounded-xl
                        focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/30 outline-none
                        transition-all duration-200 bg-white" />
          <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"
                 class="otp-box w-12 h-14 text-center text-2xl font-bold border-2 border-gray-300 rounded-xl
                        focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/30 outline-none
                        transition-all duration-200 bg-white" />
          <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"
                 class="otp-box w-12 h-14 text-center text-2xl font-bold border-2 border-gray-300 rounded-xl
                        focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/30 outline-none
                        transition-all duration-200 bg-white" />
          <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"
                 class="otp-box w-12 h-14 text-center text-2xl font-bold border-2 border-gray-300 rounded-xl
                        focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/30 outline-none
                        transition-all duration-200 bg-white" />
          <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"
                 class="otp-box w-12 h-14 text-center text-2xl font-bold border-2 border-gray-300 rounded-xl
                        focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/30 outline-none
                        transition-all duration-200 bg-white" />
          <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"
                 class="otp-box w-12 h-14 text-center text-2xl font-bold border-2 border-gray-300 rounded-xl
                        focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/30 outline-none
                        transition-all duration-200 bg-white" />
        </div>

        <!-- Verify Button -->
        <button id="verify-btn" type="submit"
          class="w-full bg-brand-primary text-white font-bold py-3 px-4 rounded-lg
                 hover:bg-brand-primary-hover transition-all duration-300 shadow-lg
                 transform active:translate-y-0 active:scale-[0.99]">
          Verify OTP
        </button>
      </form>

      <!-- Resend / Back -->
      <div class="text-center space-y-3">
        <p class="text-sm text-brand-text-secondary">
          Didn't receive the code?
          <button id="resend-btn" type="button"
            class="text-brand-primary hover:text-brand-primary-hover font-semibold hover:underline transition-colors disabled:opacity-40 disabled:cursor-not-allowed disabled:no-underline"
            disabled>
            Resend OTP
          </button>
        </p>
        <p>
          <a href="login.php" class="text-sm text-gray-400 hover:text-gray-600 transition-colors">
            ← Back to Login
          </a>
        </p>
      </div>

      <div class="text-center mt-6 text-sm">
        <p class="text-gray-500">&copy; 2026 Microfinancial Management System. All Rights Reserved.</p>
      </div>
    </div>
  </div>

  <script>
  document.addEventListener("DOMContentLoaded", () => {

    // ─── Elements ───
    const otpBoxes   = document.querySelectorAll(".otp-box");
    const verifyBtn  = document.getElementById("verify-btn");
    const resendBtn  = document.getElementById("resend-btn");
    const timerText  = document.getElementById("timer-text");
    const timerCircle = document.getElementById("timer-circle");
    const timerLabel = document.getElementById("timer-label");

    const OTP_DURATION = 300; // seconds (5 minutes)
    const CIRCUMFERENCE = 2 * Math.PI * 34; // matches SVG r=34
    let countdown = OTP_DURATION;
    let timerInterval = null;
    let expired = false;

    // ─── OTP Input Handling ───
    otpBoxes.forEach((box, idx) => {
      box.addEventListener("input", (e) => {
        const val = e.target.value.replace(/[^0-9]/g, "");
        e.target.value = val;
        if (val && idx < otpBoxes.length - 1) {
          otpBoxes[idx + 1].focus();
        }
      });

      box.addEventListener("keydown", (e) => {
        if (e.key === "Backspace" && !box.value && idx > 0) {
          otpBoxes[idx - 1].focus();
        }
      });

      // Allow paste
      box.addEventListener("paste", (e) => {
        e.preventDefault();
        const pasted = (e.clipboardData || window.clipboardData).getData("text").replace(/[^0-9]/g, "");
        for (let i = 0; i < otpBoxes.length && i < pasted.length; i++) {
          otpBoxes[i].value = pasted[i];
        }
        const focusIdx = Math.min(pasted.length, otpBoxes.length - 1);
        otpBoxes[focusIdx].focus();
      });
    });

    // Focus first box
    otpBoxes[0]?.focus();

    // ─── Timer ───
    function startTimer(seconds) {
      countdown = seconds;
      expired = false;
      resendBtn.disabled = true;
      verifyBtn.disabled = false;
      verifyBtn.classList.remove("opacity-60", "cursor-not-allowed");

      updateTimerDisplay();

      clearInterval(timerInterval);
      timerInterval = setInterval(() => {
        countdown--;
        updateTimerDisplay();

        if (countdown <= 0) {
          clearInterval(timerInterval);
          onExpired();
        }
      }, 1000);
    }

    function updateTimerDisplay() {
      const mins = Math.floor(countdown / 60);
      const secs = countdown % 60;
      timerText.textContent = mins > 0 ? `${mins}:${secs.toString().padStart(2, '0')}` : secs;
      const offset = CIRCUMFERENCE - (countdown / OTP_DURATION) * CIRCUMFERENCE;
      timerCircle.style.strokeDashoffset = offset;

      // Color changes
      if (countdown <= 30) {
        timerCircle.style.stroke = "#EF4444";
        timerText.classList.remove("text-brand-primary");
        timerText.classList.add("text-red-500");
      } else {
        timerCircle.style.stroke = "#059669";
        timerText.classList.remove("text-red-500");
        timerText.classList.add("text-brand-primary");
      }
    }

    function onExpired() {
      expired = true;
      timerText.textContent = "0";
      timerLabel.textContent = "OTP Expired";
      timerLabel.classList.add("text-red-500", "font-semibold");
      timerLabel.classList.remove("text-brand-text-secondary");
      resendBtn.disabled = false;
      verifyBtn.disabled = true;
      verifyBtn.classList.add("opacity-60", "cursor-not-allowed");

      // Clear OTP boxes
      otpBoxes.forEach(b => { b.value = ""; b.disabled = true; });

      Swal.fire({
        icon: "warning",
        title: "OTP Expired",
        text: "Your OTP has expired. Please request a new one.",
        confirmButtonColor: "#059669",
        confirmButtonText: "Resend OTP"
      }).then((result) => {
        if (result.isConfirmed) resendOTP();
      });
    }

    // ─── Verify OTP ───
    document.getElementById("otp-form").addEventListener("submit", async (e) => {
      e.preventDefault();
      if (expired) return;

      const otp = Array.from(otpBoxes).map(b => b.value).join("");
      if (otp.length < 6) {
        Swal.fire({ icon: "error", title: "Incomplete", text: "Please enter all 6 digits.", confirmButtonColor: "#059669" });
        return;
      }

      verifyBtn.disabled = true;
      verifyBtn.textContent = "Verifying…";

      try {
        const res = await fetch("api/otp.php?action=verify", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ otp }),
        });
        const data = await res.json();

        if (data.success) {
          clearInterval(timerInterval);
          Swal.fire({
            icon: "success",
            title: "Verified!",
            text: "Login successful. Redirecting…",
            timer: 1500,
            showConfirmButton: false
          });
          setTimeout(() => { window.location.href = data.redirect || "dashboard.php"; }, 1600);
        } else if (data.expired) {
          onExpired();
        } else {
          Swal.fire({ icon: "error", title: "Invalid OTP", text: data.message, confirmButtonColor: "#059669" });
          verifyBtn.disabled = false;
          verifyBtn.textContent = "Verify OTP";
          // Clear boxes and refocus
          otpBoxes.forEach(b => b.value = "");
          otpBoxes[0].focus();
        }
      } catch (err) {
        Swal.fire({ icon: "error", title: "Error", text: "Connection error. Please try again.", confirmButtonColor: "#059669" });
        verifyBtn.disabled = false;
        verifyBtn.textContent = "Verify OTP";
      }
    });

    // ─── Resend OTP ───
    resendBtn.addEventListener("click", () => resendOTP());

    async function resendOTP() {
      resendBtn.disabled = true;
      resendBtn.textContent = "Sending…";

      try {
        const res = await fetch("api/otp.php?action=resend");
        const data = await res.json();

        if (data.success) {
          // Reset UI
          timerLabel.textContent = "Time remaining";
          timerLabel.classList.remove("text-red-500", "font-semibold");
          timerLabel.classList.add("text-brand-text-secondary");
          otpBoxes.forEach(b => { b.value = ""; b.disabled = false; });
          otpBoxes[0].focus();
          resendBtn.textContent = "Resend OTP";

          startTimer(data.expires_in || OTP_DURATION);

          if (data.mail_failed && data.fallback_otp) {
            Swal.fire({
              icon: 'info',
              title: 'OTP Code (Dev Mode)',
              html: `<p style="color:#6B7280;margin-bottom:12px;">Email could not be sent.<br>Your new verification code is:</p>
                     <div style="font-size:32px;font-weight:700;letter-spacing:8px;color:#059669;background:#F0FDF4;border:2px solid #A7F3D0;border-radius:12px;padding:16px;margin:8px 0;">${data.fallback_otp}</div>
                     <p style="color:#9CA3AF;font-size:12px;margin-top:8px;">Enter this code in the boxes above.</p>`,
              confirmButtonColor: '#059669',
              confirmButtonText: 'Got it'
            });
          } else {
            Swal.fire({
              icon: "success",
              title: "OTP Sent!",
              text: "A new code has been sent to your email.",
              timer: 2000,
              showConfirmButton: false
            });
          }
        } else {
          Swal.fire({ icon: "error", title: "Failed", text: data.message, confirmButtonColor: "#059669" });
          resendBtn.disabled = false;
          resendBtn.textContent = "Resend OTP";
        }
      } catch (err) {
        Swal.fire({ icon: "error", title: "Error", text: "Connection error. Please try again.", confirmButtonColor: "#059669" });
        resendBtn.disabled = false;
        resendBtn.textContent = "Resend OTP";
      }
    }

    // ─── Send OTP on page load, then sync timer ───
    async function initOTP() {
      try {
        // Send OTP email when the page loads (decoupled from login)
        const sendRes = await fetch("api/otp.php?action=send");
        const sendData = await sendRes.json();

        if (sendData.success) {
          startTimer(sendData.expires_in || OTP_DURATION);

          if (sendData.mail_failed && sendData.fallback_otp) {
            // SMTP is blocked — show OTP on screen as fallback
            Swal.fire({
              icon: 'info',
              title: 'OTP Code (Dev Mode)',
              html: `<p style="color:#6B7280;margin-bottom:12px;">Email could not be sent.<br>Your verification code is:</p>
                     <div style="font-size:32px;font-weight:700;letter-spacing:8px;color:#059669;background:#F0FDF4;border:2px solid #A7F3D0;border-radius:12px;padding:16px;margin:8px 0;">${sendData.fallback_otp}</div>
                     <p style="color:#9CA3AF;font-size:12px;margin-top:8px;">Enter this code in the boxes above.</p>`,
              confirmButtonColor: '#059669',
              confirmButtonText: 'Got it'
            });
          } else {
            Swal.fire({
              icon: 'success',
              title: 'OTP Sent!',
              text: 'A verification code has been sent to your email.',
              timer: 2000,
              showConfirmButton: false
            });
          }
        } else {
          // OTP send failed entirely
          const timeRes = await fetch("api/otp.php?action=time");
          const timeData = await timeRes.json();
          if (timeData.remaining > 0) {
            startTimer(timeData.remaining);
          } else {
            onExpired();
          }
          Swal.fire({
            icon: 'warning',
            title: 'Email Issue',
            text: sendData.message || 'Could not send OTP email. Try Resend OTP.',
            confirmButtonColor: '#059669'
          });
        }
      } catch {
        startTimer(OTP_DURATION);
      }
    }

    initOTP();
  });
  </script>
</body>
</html>
