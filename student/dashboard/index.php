<?php
session_start();
date_default_timezone_set('Asia/Kolkata');

// ----------------------
// FIXED REDIRECT TARGET
// ----------------------
if (!isset($_SESSION['roll']) || !isset($_SESSION['name'])) {
    header('Location: ../index.php');
    exit();
}

// -----------------------
// DEVICE/IP VALIDATION
// -----------------------
if (
    !isset($_SESSION['IPaddress']) ||
    !isset($_SESSION['userAgent']) ||
    $_SESSION['IPaddress'] !== $_SERVER['REMOTE_ADDR'] ||
    $_SESSION['userAgent'] !== $_SERVER['HTTP_USER_AGENT']
) {
    session_unset();
    session_destroy();
    header('Location: ../index.php');
    exit();
}

$roll = $_SESSION['roll'];
$name = $_SESSION['name'];
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Student Dashboard | BBIT</title>
    <meta name="theme-color" content="#eff6ff" id="meta-theme-color">
    <script>
        (function () {
            var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.setAttribute('data-theme', prefersDark ? 'dark' : 'light');
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Restored CDN for Instant Premium Icons Load -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-1: #eff6ff; --bg-2: #e0e7ff; --bg-3: #f3e8ff;
            --card-bg: rgba(255, 255, 255, 0.85); --card-border: rgba(255, 255, 255, 1);
            --shadow-color: rgba(59, 130, 246, 0.08); --text-strong: #0f172a;
            --text: #334155; --muted: #64748b; --line: rgba(15, 23, 42, 0.06);
            --surface: rgba(255, 255, 255, 0.95); --surface-hover: rgba(248, 250, 252, 0.9);
            --student: #2563eb; --student-soft: rgba(37, 99, 235, 0.08);
            --logo-bg: rgba(255, 255, 255, 0.8); --ambient-opacity: 0.35;
            --toggle-bg: rgba(255, 255, 255, 0.9); --toggle-border: rgba(15, 23, 42, 0.08);
        }
        [data-theme="dark"] {
            --bg-1: #020617; --bg-2: #0f172a; --bg-3: #1e1b4b;
            --card-bg: rgba(15, 23, 42, 0.65); --card-border: rgba(255, 255, 255, 0.05);
            --shadow-color: rgba(0, 0, 0, 0.4); --text-strong: #f8fafc;
            --text: #cbd5e1; --muted: #94a3b8; --line: rgba(255, 255, 255, 0.06);
            --surface: rgba(30, 41, 59, 0.7); --surface-hover: rgba(51, 65, 85, 0.75);
            --student: #3b82f6; --student-soft: rgba(59, 130, 246, 0.12);
            --logo-bg: rgba(30, 41, 59, 0.9); --ambient-opacity: 0.15;
            --toggle-bg: rgba(30, 41, 59, 0.9); --toggle-border: rgba(255, 255, 255, 0.08);
        }
        * { font-family: 'Inter', sans-serif; box-sizing: border-box; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        html, body { margin: 0; padding: 0; min-height: 100vh; overflow-x: hidden; background-color: var(--bg-1); }
        body { background: linear-gradient(135deg, var(--bg-1), var(--bg-2), var(--bg-3)); background-attachment: fixed; display: flex; flex-direction: column; align-items: center; }
        
        /* Smooth Hardware Accelerated Transitions */
        .theme-fade { transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease, transform 0.15s ease; will-change: background-color, border-color; }
        
        .ambient-glow { position: fixed; border-radius: 50%; filter: blur(90px); opacity: var(--ambient-opacity); pointer-events: none; z-index: -1; transform: translateZ(0); }
        .ambient-1 { background-color: var(--student); width: 450px; height: 450px; top: -120px; left: -120px; }
        .ambient-2 { background-color: #a855f7; width: 400px; height: 400px; bottom: -100px; right: -100px; }
        .card-surface { background: var(--card-bg); border-color: var(--card-border); box-shadow: 0 25px 50px -12px var(--shadow-color); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); }
        
        @keyframes fadeUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
        .reveal { opacity: 0; animation: fadeUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        
        .input-field { background-color: var(--surface); border: 1px solid var(--line); color: var(--text-strong); }
        .input-field:focus { border-color: var(--student); box-shadow: 0 0 0 4px var(--student-soft); outline: none; }
        
        .theme-toggle { width: 42px; height: 42px; border-radius: 9999px; background: var(--toggle-bg); border: 1px solid var(--toggle-border); color: var(--text); cursor: pointer; display: flex; align-items: center; justify-content: center; shrink-0: 0; }
        .theme-toggle:hover { transform: scale(1.04); box-shadow: 0 4px 12px var(--shadow-color); }
        
        .icon-sun, .icon-moon { position: absolute; transition: opacity 0.2s, transform 0.25s ease; }
        [data-theme="light"] .icon-sun  { opacity: 1; transform: scale(1); }
        [data-theme="light"] .icon-moon { opacity: 0; transform: scale(0.6) rotate(30deg); }
        [data-theme="dark"]  .icon-sun  { opacity: 0; transform: scale(0.6) rotate(-30deg); }
        [data-theme="dark"]  .icon-moon { opacity: 1; transform: scale(1); }
    </style>
</head>
<body class="theme-fade antialiased text-[var(--text)]">

    <div class="ambient-glow ambient-1 theme-fade"></div>
    <div class="ambient-glow ambient-2 theme-fade"></div>

    <!-- MAIN PORTAL WRAPPER -->
    <div class="w-full max-w-3xl px-4 py-4 sm:py-8 flex-1 flex flex-col justify-center">
        <main class="w-full reveal rounded-[2rem] overflow-hidden card-surface border theme-fade p-4 sm:p-7 shadow-2xl">
            
            <!-- CONTROLS ROW ROW HEADER (Safe spacing fix for mobile overlap) -->
            <header class="flex flex-row items-center justify-between border-b border-[var(--line)] pb-4 mb-5 gap-4">
                <div class="theme-fade bg-[var(--logo-bg)] px-3 py-2 rounded-xl border border-[var(--line)] shadow-sm shrink-0">
                    <img src="https://www.bbit.edu.in/assets/frontend_template/img/logo.png" alt="BBIT Logo" class="h-8 sm:h-9 w-auto object-contain">
                </div>
                <div class="flex items-center gap-2 sm:gap-3 ml-auto">
                    <button id="theme-toggle" class="theme-fade theme-toggle" aria-label="Toggle theme">
                        <span class="icon-sun"><i class="fa-solid fa-sun text-base"></i></span>
                        <span class="icon-moon"><i class="fa-solid fa-moon text-base"></i></span>
                    </button>
                    <a href="../../logout/" class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-rose-500/10 hover:bg-rose-600 text-rose-600 hover:text-white border border-rose-500/10 transition-all duration-200 font-bold text-xs sm:text-sm shadow-sm">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span class="hidden sm:inline">Logout</span>
                    </a>
                </div>
            </header>

            <!-- PROFILE SUMMARY CHIP -->
            <div class="mb-5 bg-[var(--surface)] p-4 sm:p-5 rounded-2xl border border-[var(--line)] flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-sm">
                <div>
                    <h2 class="text-lg sm:text-xl font-extrabold text-[var(--text-strong)] tracking-tight">
                        Welcome, <span class="bg-clip-text text-transparent bg-gradient-to-r from-[var(--student)] to-purple-500"><?= htmlspecialchars($name); ?></span> 👋
                    </h2>
                </div>
                <div class="bg-[var(--surface-hover)] border border-[var(--line)] px-3.5 py-2 rounded-xl text-left sm:text-right shrink-0">
                    <span class="block text-[9px] text-[var(--muted)] uppercase font-black tracking-wider mb-0.5">Student Roll</span>
                    <span class="font-mono text-sm sm:text-base font-bold text-[var(--text-strong)] tracking-wider"><?= htmlspecialchars($roll); ?></span>
                </div>
            </div>

            <!-- 3 STACKED COMPONENT SECTIONS (Numbers removed for corporate design look) -->
            <div class="space-y-4">
                
                <!-- COMPONENT 1: MANUAL PIN MODULE -->
                <div class="bg-[var(--surface)] p-5 rounded-2xl border border-[var(--line)] shadow-sm theme-fade">
                    <div class="flex items-start gap-3 mb-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-purple-500/10 text-purple-500 border border-purple-500/10 shadow-sm">
                            <i class="fa-solid fa-keyboard text-sm"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm sm:text-base font-bold text-[var(--text-strong)] leading-tight">Manual Verification Code</h3>
                                <span class="bg-purple-100 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 text-[9px] font-extrabold px-1.5 py-0.5 rounded uppercase tracking-wide">Fallback</span>
                            </div>
                            <p class="text-xs text-[var(--muted)] mt-0.5">Input the secure 4-digit code generated on the class screen.</p>
                        </div>
                    </div>
                    <form method="POST" action="submit_pin_attendance.php" id="pinForm" class="max-w-md mx-auto flex flex-col gap-3">
                        <input type="text" name="pin" maxlength="4" required pattern="[0-9]{4}" placeholder="• • • •" class="theme-fade input-field w-full px-4 py-3 rounded-xl text-center text-2xl font-mono tracking-[0.3em] font-extrabold shadow-inner bg-slate-50/40 focus:bg-white" inputmode="numeric" autocomplete="off">
                        <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-fuchsia-600 text-white px-5 py-3 rounded-xl hover:shadow-lg font-bold text-xs sm:text-sm tracking-wide transition active:scale-[0.99]">Validate & Sync PIN</button>
                    </form>
                </div>

                <!-- COMPONENT 2: STANDALONE QR RADAR LINK -->
                <div class="bg-[var(--surface)] p-5 rounded-2xl border border-[var(--line)] shadow-sm theme-fade">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-500/10 text-blue-500 border border-blue-500/10 shadow-sm">
                                <i class="fa-solid fa-qrcode text-base"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm sm:text-base font-bold text-[var(--text-strong)] leading-tight">Secure QR Radar</h3>
                                    <span class="bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 text-[9px] font-extrabold px-1.5 py-0.5 rounded uppercase tracking-wide">High Speed</span>
                                </div>
                                <p class="text-xs text-[var(--muted)] mt-0.5">Launches sandbox camera page to verify session certificates instantly.</p>
                            </div>
                        </div>
                        <a href="scan_qr.php" class="w-full sm:w-auto text-center bg-gradient-to-r from-[var(--student)] to-indigo-600 text-white px-5 py-3 rounded-xl hover:shadow-lg font-bold text-xs sm:text-sm tracking-wide block active:scale-[0.99] whitespace-nowrap">
                            <i class="fa-solid fa-camera mr-1"></i> Open Radar Lens
                        </a>
                    </div>
                </div>

                <!-- COMPONENT 3: HISTORICAL PERFORMANCE FOOTPRINT -->
                <div class="bg-[var(--surface)] p-4 rounded-2xl border border-[var(--line)] shadow-sm theme-fade hover:border-emerald-500/30 transition-all">
                    <a href="../view_attendance" class="group flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 border border-emerald-500/10 shadow-sm">
                                <i class="fa-solid fa-chart-user text-sm"></i>
                            </div>
                            <div>
                                <h3 class="text-sm sm:text-base font-bold text-[var(--text-strong)] group-hover:text-[var(--student)] transition-colors">Performance Logs & History</h3>
                                <p class="text-xs text-[var(--muted)] mt-0.5">Track and cross-reference active academic threshold percentages.</p>
                            </div>
                        </div>
                        <div class="h-8 w-8 flex items-center justify-center rounded-full bg-[var(--surface-hover)] group-hover:bg-[var(--student)] group-hover:text-white text-[var(--muted)] shadow-sm transition-all shrink-0">
                            <i class="fa-solid fa-angle-right text-xs"></i>
                        </div>
                    </a>
                </div>

            </div>
        </main>
        <footer class="mt-5 text-center pb-2 text-[var(--muted)] font-mono text-[10px] uppercase tracking-widest">&copy; <span id="year"></span> BBIT Attendance Portal</footer>
    </div>

    <script>
        var root = document.documentElement;
        var metaTheme = document.getElementById('meta-theme-color');
        var toggleBtn = document.getElementById('theme-toggle');
        function syncMetaColor() { metaTheme.setAttribute('content', root.getAttribute('data-theme') === 'dark' ? '#020617' : '#eff6ff'); }
        toggleBtn.addEventListener('click', function () {
            root.setAttribute('data-theme', root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
            syncMetaColor();
        });
        document.getElementById('year').textContent = new Date().getFullYear();
    </script>
</body>
</html>
