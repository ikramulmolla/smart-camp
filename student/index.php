<?php
session_start();
date_default_timezone_set('Asia/Kolkata');

$cookie_lifetime = 60 * 60 * 24 * 30; // 30 days

// -------------------------------------------------
// 1️⃣ Already logged in → redirect
// -------------------------------------------------
if (isset($_SESSION['roll'])) {
    header('Location: dashboard/');
    exit();
}

// -------------------------------------------------
// 2️⃣ Auto-login using cookie
// -------------------------------------------------
if (isset($_COOKIE['auto_login'])) {

    $cookieParts = explode('|', $_COOKIE['auto_login']);

    if (count($cookieParts) === 2) {
        [$cookieRoll, $cookieName] = $cookieParts;

        if (ctype_digit($cookieRoll) && strlen($cookieRoll) <= 11 && !empty($cookieName)) {

            session_regenerate_id(true);
            $_SESSION['roll'] = $cookieRoll;
            $_SESSION['name'] = htmlspecialchars($cookieName);
            $_SESSION['IPaddress'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
            $_SESSION['request_count'] = 0;

            header('Location: dashboard/');
            exit();
        }
    }

    // ❌ Invalid cookie → destroy it
    setcookie('auto_login', '', time() - 3600, '/');
}

// -------------------------------------------------
// 3️⃣ Manual login (Updated to use JSON)
// -------------------------------------------------
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $roll = trim($_POST['roll'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!preg_match('/^\d{1,11}$/', $roll)) {
        $error = "Roll Number must contain only digits (max 11).";
    } elseif ($password === '') {
        $error = "Password is required.";
    } else {

        // Read and decode the JSON file
        $json_file_path = __DIR__ . '/../students.json';
        
        if (file_exists($json_file_path)) {
            $json_data = file_get_contents($json_file_path);
            $students = json_decode($json_data, true);

            if (is_array($students)) {
                $login_successful = false;

                foreach ($students as $student) {
                    $storedRoll = (string)$student['roll'];
                    $storedPassword = (string)$student['password'];
                    $studentName = $student['name'];

                    if (hash_equals($storedRoll, $roll) && hash_equals($storedPassword, $password)) {

                        session_regenerate_id(true);

                        $_SESSION['roll'] = $roll;
                        $_SESSION['name'] = htmlspecialchars($studentName);
                        $_SESSION['IPaddress'] = $_SERVER['REMOTE_ADDR'];
                        $_SESSION['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
                        $_SESSION['request_count'] = 0;

                        // ✅ Set auto-login cookie
                        setcookie(
                            'auto_login',
                            $roll . '|' . $studentName,
                            time() + $cookie_lifetime,
                            '/',
                            '',
                            false,
                            true
                        );

                        header('Location: dashboard/');
                        exit();
                    }
                }
                
                if (!$login_successful) {
                    $error = "Invalid Roll Number or Password.";
                }
            } else {
                $error = "Error reading student database.";
            }
        } else {
            $error = "Student database not found.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Login | QR Attendance</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#eff6ff" id="meta-theme-color">
    <script>
        (function () {
            var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.setAttribute('data-theme', prefersDark ? 'dark' : 'light');
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-1: #eff6ff;
            --bg-2: #e0e7ff;
            --bg-3: #f3e8ff;
            --card-bg: rgba(255, 255, 255, 0.88);
            --card-border: rgba(255, 255, 255, 0.95);
            --shadow-color: rgba(59, 130, 246, 0.12);
            --text-strong: #1f2937;
            --text: #4b5563;
            --muted: #9ca3af;
            --line: rgba(15, 23, 42, 0.08);
            --surface: rgba(255, 255, 255, 0.95);
            --student: #3b82f6;
            --student-soft: rgba(59, 130, 246, 0.12);
            --logo-bg: rgba(255, 255, 255, 0.6);
            --ambient-opacity: 0.25;
            --toggle-bg: rgba(255, 255, 255, 0.8);
            --toggle-border: rgba(15, 23, 42, 0.1);
            --error-bg: rgba(254, 226, 226, 0.9);
            --error-text: #dc2626;
            --error-border: rgba(248, 113, 113, 0.4);
        }

        [data-theme="dark"] {
            --bg-1: #0b1120;
            --bg-2: #181442;
            --bg-3: #241253;
            --card-bg: rgba(15, 23, 42, 0.75);
            --card-border: rgba(255, 255, 255, 0.08);
            --shadow-color: rgba(0, 0, 0, 0.6);
            --text-strong: #f1f5f9;
            --text: #cbd5e1;
            --muted: #94a3b8;
            --line: rgba(255, 255, 255, 0.08);
            --surface: rgba(15, 23, 42, 0.8);
            --student: #60a5fa;
            --student-soft: rgba(96, 165, 250, 0.15);
            --logo-bg: rgba(255, 255, 255, 0.92);
            --ambient-opacity: 0.15;
            --toggle-bg: rgba(30, 41, 59, 0.8);
            --toggle-border: rgba(255, 255, 255, 0.1);
            --error-bg: rgba(127, 29, 29, 0.6);
            --error-text: #fca5a5;
            --error-border: rgba(248, 113, 113, 0.2);
        }

        * { font-family: 'Inter', sans-serif; }
        .font-display { font-family: 'Space Grotesk', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; letter-spacing: 0.08em; }

        html, body { 
            /* Fixed mobile cut-off issues */
            min-height: 100dvh; 
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        body {
            background: linear-gradient(135deg, var(--bg-1), var(--bg-2), var(--bg-3));
            /* Grid place-items-center ensures perfect vertical centering without cutting off top/bottom */
            display: grid;
            place-items: center;
        }

        .theme-fade {
            transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease, box-shadow 0.3s ease;
        }

        /* Optimized Ambient Glows to fix lag */
        .ambient-glow {
            position: absolute;
            border-radius: 9999px;
            filter: blur(60px); /* Reduced blur for performance */
            pointer-events: none;
            will-change: opacity; /* Only animating opacity now to prevent layout thrashing */
            animation: ambientPulse 8s ease-in-out infinite alternate;
            z-index: 0;
        }
        .ambient-1 { background-color: var(--student); }
        .ambient-2 { background-color: #a855f7; animation-delay: -4s; }
        
        @keyframes ambientPulse {
            0% { opacity: var(--ambient-opacity); }
            100% { opacity: calc(var(--ambient-opacity) + 0.15); }
        }

        .card-surface {
            background: var(--card-bg);
            border-color: var(--card-border);
            box-shadow: 0 20px 40px -10px var(--shadow-color);
            backdrop-filter: blur(16px); /* Reduced blur for smoother rendering */
            -webkit-backdrop-filter: blur(16px);
            z-index: 10;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(15px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .reveal { opacity: 0; animation: fadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .reveal-1 { animation-delay: 0.05s; }
        .reveal-2 { animation-delay: 0.1s; }
        .reveal-3 { animation-delay: 0.15s; }
        .reveal-4 { animation-delay: 0.2s; }
        .reveal-5 { animation-delay: 0.25s; }

        .input-field {
            background-color: var(--surface);
            border: 1px solid var(--line);
            color: var(--text-strong);
        }
        .input-field:focus {
            border-color: var(--student);
            box-shadow: 0 0 0 4px var(--student-soft);
            outline: none;
        }

        .error-box {
            background-color: var(--error-bg);
            color: var(--error-text);
            border: 1px solid var(--error-border);
        }

        .theme-toggle {
            position: relative;
            width: 36px;
            height: 36px;
            border-radius: 9999px;
            background: var(--toggle-bg);
            border: 1px solid var(--toggle-border);
            color: var(--text);
            cursor: pointer;
            transition: transform 0.2s ease;
            z-index: 20;
        }
        .theme-toggle:hover { transform: scale(1.05); }
        .theme-toggle:active { transform: scale(0.95); }
        .icon-sun, .icon-moon {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.3s ease, transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .icon-sun svg, .icon-moon svg { width: 18px; height: 18px; }
        [data-theme="light"] .icon-sun  { opacity: 1; transform: rotate(0deg) scale(1); }
        [data-theme="light"] .icon-moon { opacity: 0; transform: rotate(70deg) scale(0.4); }
        [data-theme="dark"]  .icon-sun  { opacity: 0; transform: rotate(-70deg) scale(0.4); }
        [data-theme="dark"]  .icon-moon { opacity: 1; transform: rotate(0deg) scale(1); }
        
        /* Hardware accelerated float */
        @keyframes float {
            0%, 100% { transform: translate3d(0px, 0px, 0px); }
            50% { transform: translate3d(0px, -8px, 0px); }
        }
        .animate-float { 
            animation: float 5s ease-in-out infinite; 
            will-change: transform;
        }
    </style>
</head>
<body class="theme-fade antialiased relative">

    <div class="theme-fade ambient-glow ambient-1 w-64 h-64 sm:w-[30rem] sm:h-[30rem] -top-10 -left-10 sm:-top-20 sm:-left-20"></div>
    <div class="theme-fade ambient-glow ambient-2 w-64 h-64 sm:w-[30rem] sm:h-[30rem] -bottom-10 -right-10 sm:-bottom-20 sm:-right-20"></div>

    <div class="w-full h-full flex items-center justify-center p-4 sm:p-6 lg:p-8 py-8">
        
        <main class="relative w-full max-w-[900px] reveal reveal-1 flex flex-col md:flex-row shadow-2xl rounded-[28px] overflow-hidden card-surface border theme-fade z-10 mx-auto my-auto">
            
            <div class="hidden md:flex md:w-[45%] bg-gradient-to-br from-blue-600 to-indigo-800 p-8 flex-col items-center justify-center relative overflow-hidden">
                
                <svg class="absolute top-0 left-0 w-full h-full opacity-20 pointer-events-none" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <circle cx="20" cy="80" r="40" fill="none" stroke="white" stroke-width="0.5"/>
                    <circle cx="80" cy="20" r="50" fill="none" stroke="white" stroke-width="0.5"/>
                    <circle cx="50" cy="50" r="30" fill="none" stroke="white" stroke-width="1"/>
                </svg>

                <div class="relative z-10 animate-float mb-6">
                    <svg width="200" height="200" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="30" y="40" width="140" height="120" rx="16" fill="white" fill-opacity="0.1" stroke="white" stroke-width="2" stroke-opacity="0.3"/>
                        <rect x="50" y="20" width="100" height="160" rx="16" fill="url(#paint0_linear)" shadow="0 10px 20px rgba(0,0,0,0.5)"/>
                        <circle cx="100" cy="80" r="28" fill="#60A5FA"/>
                        <circle cx="100" cy="80" r="14" fill="#DBEAFE"/>
                        <path d="M60 150C60 130 80 120 100 120C120 120 140 130 140 150" stroke="white" stroke-width="10" stroke-linecap="round"/>
                        <circle cx="150" cy="140" r="22" fill="#A78BFA"/>
                        <path d="M143 140L148 145L157 135" stroke="white" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                        <defs>
                            <linearGradient id="paint0_linear" x1="50" y1="20" x2="150" y2="180" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#3B82F6"/>
                                <stop offset="1" stop-color="#8B5CF6"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>

                <h3 class="text-white text-2xl font-display font-bold text-center z-10 tracking-wide">Student Portal</h3>
                <p class="text-blue-200 text-xs text-center mt-2 z-10 font-mono tracking-wider">MARK YOUR PRESENCE</p>
            </div>

            <div class="w-full md:w-[55%] p-6 sm:p-10 relative flex flex-col justify-center">
                
                <button id="theme-toggle" class="theme-fade theme-toggle absolute top-5 right-5 z-20" aria-label="Toggle dark mode">
                    <span class="icon-sun">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="4"/>
                            <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>
                        </svg>
                    </span>
                    <span class="icon-moon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/>
                        </svg>
                    </span>
                </button>

                <div class="flex flex-col mb-6 relative z-10 mt-2 sm:mt-0">
                    <div class="theme-fade bg-[var(--logo-bg)] p-2 rounded-xl inline-flex md:hidden w-max mb-5">
                        <img src="https://www.bbit.edu.in/assets/frontend_template/img/logo.png" alt="BBIT Logo" class="h-10 w-auto drop-shadow-sm">
                    </div>
                    <div class="hidden md:inline-flex theme-fade bg-[var(--logo-bg)] p-1.5 rounded-lg w-max mb-5">
                        <img src="https://www.bbit.edu.in/assets/frontend_template/img/logo.png" alt="BBIT Logo" class="h-8 w-auto drop-shadow-sm">
                    </div>

                    <h2 class="reveal reveal-2 font-display text-2xl sm:text-3xl font-bold text-[var(--text-strong)] tracking-tight">
                        Welcome Back
                    </h2>
                    <p class="theme-fade reveal reveal-3 mt-1.5 text-sm sm:text-[15px] text-[var(--muted)]">
                        Enter your credentials to access your dashboard.
                    </p>
                </div>

                <div id="clientErrorBox" class="error-box rounded-xl p-3 mb-5 text-sm font-medium text-center hidden animate-pulse">
                    <p id="clientError"></p>
                </div>
                <?php if (!empty($error)) : ?>
                    <div class="error-box rounded-xl p-3 mb-5 text-sm font-medium text-center animate-pulse">
                        <p><?= htmlspecialchars($error) ?></p>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-4 reveal reveal-4 relative z-10" id="loginForm" autocomplete="off" novalidate>
                    
                    <div class="space-y-1.5 text-left">
                        <label class="block text-sm font-semibold text-[var(--text-strong)] ml-1">Roll Number</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="8" r="4" fill="#3B82F6" fill-opacity="0.85"/>
                                    <path d="M4 20C4 16 7.58172 13 12 13C16.4183 13 20 16 20 20" stroke="#60A5FA" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <input 
                                type="text" 
                                name="roll" 
                                required 
                                maxlength="11" 
                                inputmode="numeric"
                                pattern="\d{1,11}" 
                                title="Only digits allowed, max 11 characters"
                                oninput="this.value = this.value.replace(/\D/g, '').slice(0,11)" 
                                onkeypress="return event.charCode >= 48 && event.charCode <= 57" 
                                onpaste="return false;" 
                                placeholder="11-digit roll number"
                                class="theme-fade input-field w-full pl-11 pr-4 py-3 rounded-xl transition-all duration-300 text-[15px]">
                        </div>
                    </div>

                    <div class="space-y-1.5 text-left">
                        <label class="block text-sm font-semibold text-[var(--text-strong)] ml-1">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="5" y="11" width="14" height="10" rx="3" fill="#8B5CF6" fill-opacity="0.85"/>
                                    <path d="M8 11V7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7V11" stroke="#A78BFA" stroke-width="2" stroke-linecap="round"/>
                                    <circle cx="12" cy="16" r="1.5" fill="white"/>
                                </svg>
                            </div>
                            <input 
                                type="password" 
                                name="password" 
                                id="passwordField" 
                                required 
                                placeholder="Enter your password"
                                class="theme-fade input-field w-full pl-11 pr-12 py-3 rounded-xl transition-all duration-300 text-[15px]">
                            
                            <button type="button" class="toggle-password absolute inset-y-0 right-0 pr-3.5 flex items-center text-[var(--muted)] hover:text-[var(--student)] transition-colors focus:outline-none">
                                <svg id="eyeOpen" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg id="eyeClosed" class="hidden" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-gradient-to-r from-[var(--student)] to-indigo-500 text-white font-semibold py-3 rounded-xl hover:shadow-lg hover:shadow-[var(--student-soft)] hover:-translate-y-0.5 focus:ring-4 focus:ring-[var(--student-soft)] transition-all duration-300 mt-4 text-[15px]">
                        Log In to Portal
                    </button>
                </form>

                <div class="theme-fade reveal reveal-5 mt-6 pt-5 border-t border-[var(--line)] w-full text-center relative z-10">
                    <p class="font-mono text-[10px] sm:text-[11px] uppercase tracking-[0.15em] text-[var(--muted)]">
                        &copy; <span id="year"></span> BBIT Attendance Portal
                    </p>
                </div>
            </div>
        </main>
    </div>

    <div id="loaderOverlay" class="fixed inset-0 h-[100dvh] w-full bg-[var(--card-bg)] backdrop-blur-md z-[100] flex flex-col items-center justify-center hidden transition-opacity duration-300 overscroll-none">
        <div class="flex flex-col items-center bg-[var(--surface)] border border-[var(--line)] p-8 rounded-3xl shadow-2xl">
            <svg class="animate-spin h-10 w-10 mb-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="12" cy="12" r="10" stroke="#E5E7EB" stroke-width="4"></circle>
                <path d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" fill="#3B82F6"></path>
            </svg>
            <p class="text-[var(--text-strong)] text-lg font-semibold animate-pulse font-display">Authenticating</p>
        </div>
    </div>

    <script>
        // Theme Setup & Toggle Sync
        var root = document.documentElement;
        var metaTheme = document.getElementById('meta-theme-color');
        var toggleBtn = document.getElementById('theme-toggle');

        function syncMetaColor() {
            var isDark = root.getAttribute('data-theme') === 'dark';
            metaTheme.setAttribute('content', isDark ? '#0b1120' : '#eff6ff');
        }
        syncMetaColor();

        toggleBtn.addEventListener('click', function () {
            var current = root.getAttribute('data-theme');
            root.setAttribute('data-theme', current === 'dark' ? 'light' : 'dark');
            syncMetaColor();
        });

        document.getElementById('year').textContent = new Date().getFullYear();

        // Custom SVG Password Toggle
        document.querySelector(".toggle-password").addEventListener("click", function () {
            const passwordField = document.getElementById("passwordField");
            const eyeOpen = document.getElementById("eyeOpen");
            const eyeClosed = document.getElementById("eyeClosed");
            
            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyeOpen.classList.add("hidden");
                eyeClosed.classList.remove("hidden");
            } else {
                passwordField.type = "password";
                eyeClosed.classList.add("hidden");
                eyeOpen.classList.remove("hidden");
            }
        });

        // Client-side form validation
        const form = document.getElementById("loginForm");
        const clientErrorBox = document.getElementById("clientErrorBox");
        const clientErrorText = document.getElementById("clientError");

        form.addEventListener("submit", function (e) {
            const rollInput = form.querySelector('input[name="roll"]');
            const passwordInput = form.querySelector('input[name="password"]');
            const rollValue = rollInput.value.trim();
            const passwordValue = passwordInput.value.trim();
            
            clientErrorBox.classList.add('hidden');
            clientErrorText.textContent = '';

            if (!/^\d{1,11}$/.test(rollValue)) {
                clientErrorText.textContent = "Roll Number must contain only digits (max 11).";
                clientErrorBox.classList.remove('hidden');
                rollInput.focus();
                e.preventDefault();
                return;
            }

            if (passwordValue === "") {
                clientErrorText.textContent = "Password is required.";
                clientErrorBox.classList.remove('hidden');
                passwordInput.focus();
                e.preventDefault();
                return;
            }

            // Show perfectly centered themed loader
            document.getElementById("loaderOverlay").classList.remove("hidden");
        });
    </script>
</body>
</html>
