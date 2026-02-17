<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login — Microfinancial Management System</title>

  <!-- Tailwind CDN -->
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

  <div class="min-h-screen flex relative z-10">

    <!-- Left Panel -->
    <section class="hidden lg:flex w-1/2 items-center justify-center p-12 text-white">
      <div class="flex flex-col items-center w-full py-12">
        <div class="text-center">
          <img src="assets/images/logo.png" alt="Microfinance Logo" class="w-28 h-28 mx-auto">
          <h1 class="text-4xl font-bold mt-4">Microfinancial Admin</h1>
          <p class="text-white/80">Management System I — Administrative</p>
        </div>

        <!-- Illustration Carousel -->
        <div class="relative w-full max-w-2xl h-96 my-6">
          <img src="assets/images/login/illustration-1.svg" alt="Illustration 1" class="login-svg absolute inset-0 w-full h-full object-contain">
          <img src="assets/images/login/illustration-2.svg" alt="Illustration 2" class="login-svg absolute inset-0 w-full h-full object-contain">
          <img src="assets/images/login/illustration-3.svg" alt="Illustration 3" class="login-svg absolute inset-0 w-full h-full object-contain">
          <img src="assets/images/login/illustration-4.svg" alt="Illustration 4" class="login-svg absolute inset-0 w-full h-full object-contain">
          <img src="assets/images/login/illustration-5.svg" alt="Illustration 5" class="login-svg absolute inset-0 w-full h-full object-contain">
        </div>

        <div class="text-center mt-4 max-w-xl">
          <p class="italic text-white/90 text-lg leading-relaxed">
            “The strength of the team is each individual member. The strength of each member is the team.”
          </p>
          <cite class="block text-right mt-2 text-white/60">- Phil Jackson</cite>
        </div>
      </div>
    </section>

    <!-- Right Panel: Login Card -->
    <section class="w-full lg:w-1/2 flex items-center justify-center p-8">
      <div class="w-full max-w-md bg-white/90 backdrop-blur-lg rounded-2xl shadow-2xl p-8">

        <div class="text-center mb-6">
          <h2 class="text-3xl font-bold text-brand-text-primary">Welcome Back!</h2>
          <p class="text-brand-text-secondary mt-1">Please enter your details to sign in.</p>
        </div>

        <form id="login-form">
          <!-- Username -->
          <div class="relative mb-4">
            <label class="block text-sm font-medium text-gray-700" for="username">Username</label>
            <div class="mt-1 relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
              </div>
              <input id="username" type="text" placeholder="Employee ID or Email"
                class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg shadow-sm
                       focus:outline-none focus:ring-2 focus:ring-brand-primary focus:border-brand-primary
                       transition-all duration-200"
                required />
            </div>
          </div>

          <!-- Password -->
          <div class="relative mb-4">
            <label class="block text-sm font-medium text-gray-700" for="password">Password</label>
            <div class="mt-1 relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 11c1.657 0 3 1.343 3 3v2a2 2 0 01-2 2H9a2 2 0 01-2-2v-2c0-1.657 1.343-3 3-3h2zm4-1V7a4 4 0 00-8 0v3h8z">
                  </path>
                </svg>
              </div>

              <input id="password" type="password" placeholder="Enter your password"
                class="w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg shadow-sm
                       focus:outline-none focus:ring-2 focus:ring-brand-primary focus:border-brand-primary
                       transition-all duration-200"
                required />

              <div id="password-toggle"
                   class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer select-none transition-transform duration-150">
                <!-- Eye Open -->
                <svg id="eye-open" class="h-5 w-5 text-gray-400 hover:text-brand-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>

                <!-- Eye Closed -->
                <svg id="eye-closed" class="h-5 w-5 text-gray-400 hover:text-brand-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.269-2.943-9.543-7a9.966 9.966 0 012.257-3.592m3.086-2.16A9.956 9.956 0 0112 5c4.478 0 8.269 2.943 9.543 7a9.97 9.97 0 01-4.043 5.197M15 12a3 3 0 00-4.5-2.598M9 12a3 3 0 004.5 2.598M3 3l18 18"></path>
                </svg>
              </div>
            </div>
          </div>

          <!-- Sign In -->
          <button id="sign-in-btn" type="submit" disabled
            class="w-full bg-brand-primary text-white font-bold py-3 px-4 rounded-lg
                   transition-all duration-300 shadow-lg
                   transform active:translate-y-0 active:scale-[0.99]
                   opacity-60 cursor-not-allowed">
            Sign In
          </button>

          <!-- Terms checkbox below button -->
          <div class="mt-4 flex items-start gap-3">
            <input id="terms-check" type="checkbox"
              class="mt-1 h-4 w-4 text-brand-primary border-gray-300 rounded focus:ring-brand-primary transition">
            <label for="terms-check" class="text-sm text-gray-700 leading-relaxed select-none">
              I agree to the
              <button id="terms-link" type="button"
                class="text-brand-primary hover:text-brand-primary-hover hover:underline transition-colors font-semibold">
                Terms and Conditions
              </button>
            </label>
          </div>
        </form>

        <div class="text-center mt-8 text-sm">
          <p class="text-gray-500">&copy; 2026 Microfinancial Management System. All Rights Reserved.</p>
        </div>
      </div>
    </section>
  </div>

  <!-- Terms Modal (EMPTY BODY) -->
  <div id="terms-modal" class="fixed inset-0 hidden z-50">
    <div id="terms-backdrop" class="absolute inset-0 bg-black/40 opacity-0 transition-opacity duration-200"></div>

    <div class="relative mx-auto mt-24 w-[92%] max-w-lg bg-white rounded-2xl shadow-2xl border border-gray-100
                opacity-0 scale-95 translate-y-2 transition-all duration-200"
         id="terms-panel">
      <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
        <div class="font-bold text-gray-800">Terms and Conditions</div>
        <button id="terms-close"
          class="w-9 h-9 rounded-xl hover:bg-gray-100 active:bg-gray-200 transition flex items-center justify-center">
          ✕
        </button>
      </div>

      <!-- TERMS & CONDITIONS CONTENT -->
      <div class="p-5 max-h-[60vh] overflow-y-auto text-sm text-gray-600 leading-relaxed space-y-4">
        <p class="font-semibold text-gray-800">Microfinancial Management System I — Terms of Use</p>
        <p>By accessing and using the Microfinancial Management System I ("the System"), you agree to comply with the following terms and conditions. These terms govern your use of the administrative platform and all associated modules.</p>

        <p class="font-semibold text-gray-700 mt-3">1. Authorized Access</p>
        <p>Access to this System is restricted to authorized employees and personnel of Microfinancial. You must use your assigned Employee ID or registered email and maintain the confidentiality of your login credentials. Sharing of accounts or credentials is strictly prohibited.</p>

        <p class="font-semibold text-gray-700 mt-3">2. Data Privacy &amp; Confidentiality</p>
        <p>In accordance with the <strong>Data Privacy Act of 2012 (Republic Act No. 10173)</strong> and the regulations of the National Privacy Commission (NPC), all personal data processed through this System shall be handled with the highest standards of confidentiality, integrity, and security. Unauthorized disclosure, copying, or distribution of any data accessed through this System is strictly prohibited and may result in disciplinary action and/or legal proceedings.</p>

        <p class="font-semibold text-gray-700 mt-3">3. Acceptable Use</p>
        <p>Users shall use the System solely for legitimate business purposes related to their job functions. Any misuse, including but not limited to unauthorized data modification, system tampering, or accessing modules beyond your assigned scope, is a violation of these terms.</p>

        <p class="font-semibold text-gray-700 mt-3">4. Audit &amp; Monitoring</p>
        <p>All activities within the System are logged for security and compliance purposes. The organization reserves the right to monitor, audit, and review user activity to ensure compliance with these terms, internal policies, and applicable laws.</p>

        <p class="font-semibold text-gray-700 mt-3">5. Intellectual Property</p>
        <p>The System, including its source code, design, documentation, and all associated materials, is the exclusive property of Microfinancial. Unauthorized reproduction, distribution, or reverse engineering of any component is strictly prohibited.</p>

        <p class="font-semibold text-gray-700 mt-3">6. Account Suspension</p>
        <p>Microfinancial reserves the right to suspend or terminate user access at any time, without prior notice, in the event of a security breach, policy violation, or as deemed necessary by the IT Department.</p>

        <p class="font-semibold text-gray-700 mt-3">7. Limitation of Liability</p>
        <p>Microfinancial shall not be liable for any indirect, incidental, or consequential damages arising from the use of or inability to access the System. The System is provided "as is" for internal administrative operations.</p>

        <p class="font-semibold text-gray-700 mt-3">8. Amendments</p>
        <p>These terms may be updated from time to time. Continued use of the System following any changes constitutes acceptance of the revised terms.</p>

        <p class="text-xs text-gray-400 mt-4 text-center">Last updated: January 2026 &bull; Microfinancial Management System I</p>
      </div>

      <div class="px-5 pb-5">
        <button id="terms-close-bottom"
          class="w-full bg-brand-primary hover:bg-brand-primary-hover text-white font-bold py-3 rounded-lg
                 transition-all duration-200 active:scale-[0.99]">
          Close
        </button>
      </div>
    </div>
  </div>

  <script src="app.js"></script>
</body>
</html>
