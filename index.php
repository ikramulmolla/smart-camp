<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>QR Attendance System | Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            --card-bg: rgba(255,255,255,0.75);
            --card-border: rgba(255,255,255,0.8);
            --shadow-color: rgba(99,102,241,0.15);
            --text-strong: #1f2937;
            --text: #4b5563;
            --muted: #9ca3af;
            --line: rgba(15,23,42,0.08);
            --surface: rgba(255,255,255,0.6);
            --student: #3b82f6;
            --student-soft: rgba(59,130,246,0.10);
            --student-border: rgba(59,130,246,0.40);
            --teacher: #a855f7;
            --teacher-soft: rgba(168,85,247,0.10);
            --teacher-border: rgba(168,85,247,0.40);
            --logo-bg: transparent;
            --logo-pad: 0px;
            --ambient-opacity: 0.35;
            --toggle-bg: rgba(255,255,255,0.6);
            --toggle-border: rgba(15,23,42,0.08);
        }

        [data-theme="dark"] {
            --bg-1: #0b1120;
            --bg-2: #181442;
            --bg-3: #241253;
            --card-bg: rgba(15,23,42,0.65);
            --card-border: rgba(255,255,255,0.08);
            --shadow-color: rgba(0,0,0,0.55);
            --text-strong: #f1f5f9;
            --text: #cbd5e1;
            --muted: #94a3b8;
            --line: rgba(255,255,255,0.08);
            --surface: rgba(255,255,255,0.04);
            --student: #60a5fa;
            --student-soft: rgba(96,165,250,0.14);
            --student-border: rgba(96,165,250,0.45);
            --teacher: #c084fc;
            --teacher-soft: rgba(192,132,252,0.14);
            --teacher-border: rgba(192,132,252,0.45);
            --logo-bg: rgba(255,255,255,0.92);
            --logo-pad: 0.5rem;
            --ambient-opacity: 0.18;
            --toggle-bg: rgba(255,255,255,0.06);
            --toggle-border: rgba(255,255,255,0.1);
        }

        * { font-family: 'Inter', sans-serif; }
        .font-display { font-family: 'Space Grotesk', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; letter-spacing: 0.08em; }

        html, body { min-height: 100%; }

        body {
            background: linear-gradient(135deg, var(--bg-1), var(--bg-2), var(--bg-3));
        }

        /* Hardware acceleration added to smooth transitions */
        .theme-fade {
            transition: background-color 0.4s ease, border-color 0.4s ease, color 0.4s ease, box-shadow 0.4s ease;
            will-change: background-color, border-color, color;
        }

        .ambient-glow {
            position: absolute;
            border-radius: 9999px;
            filter: blur(100px);
            pointer-events: none;
            animation: ambientPulse 10s ease-in-out infinite alternate;
            will-change: transform, opacity;
            transform: translateZ(0);
        }
        .ambient-1 { background-color: var(--student); }
        .ambient-2 { background-color: var(--teacher); animation-delay: -5s; }
        
        @keyframes ambientPulse {
            0% { opacity: var(--ambient-opacity); transform: scale(0.9); }
            100% { opacity: calc(var(--ambient-opacity) * 1.5); transform: scale(1.1); }
        }

        .card-surface {
            background: var(--card-bg);
            border-color: var(--card-border);
            box-shadow: 0 25px 60px -15px var(--shadow-color);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            transform: translateZ(0);
        }

        .logo-wrap {
            display: inline-flex;
            background: var(--logo-bg);
            padding: var(--logo-pad);
            border-radius: 1rem;
        }

        /* Entrance reveal sequence */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .reveal { opacity: 0; animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; will-change: transform, opacity; }
        .reveal-1 { animation-delay: 0.05s; }
        .reveal-2 { animation-delay: 0.15s; }
        .reveal-3 { animation-delay: 0.25s; }
        .reveal-4 { animation-delay: 0.35s; }
        .reveal-5 { animation-delay: 0.45s; }
        .reveal-6 { animation-delay: 0.55s; }

        /* Viewfinder corner brackets */
        @keyframes bracketIn {
            from { opacity: 0; transform: scale(0.4); }
            to   { opacity: 0.85; transform: scale(1); }
        }
        .corner {
            position: absolute;
            width: 26px;
            height: 26px;
            border-width: 3px;
            border-style: solid;
            opacity: 0;
            animation: bracketIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            pointer-events: none;
        }
        .corner-tl { top: -3px; left: -3px; border-right: none; border-bottom: none; border-radius: 10px 0 0 0; border-color: var(--student); animation-delay: 0.5s; }
        .corner-tr { top: -3px; right: -3px; border-left: none; border-bottom: none; border-radius: 0 10px 0 0; border-color: var(--teacher); animation-delay: 0.6s; }
        .corner-bl { bottom: -3px; left: -3px; border-right: none; border-top: none; border-radius: 0 0 0 10px; border-color: var(--teacher); animation-delay: 0.6s; }
        .corner-br { bottom: -3px; right: -3px; border-left: none; border-top: none; border-radius: 0 0 10px 0; border-color: var(--student); animation-delay: 0.5s; }

        /* Status dot */
        @keyframes pulseDot {
            0%   { box-shadow: 0 0 0 0 var(--student-border); }
            70%  { box-shadow: 0 0 0 7px rgba(0,0,0,0); }
            100% { box-shadow: 0 0 0 0 rgba(0,0,0,0); }
        }
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--student);
            animation: pulseDot 2.4s ease-out infinite;
        }

        /* Theme toggle */
        .theme-toggle {
            position: relative;
            width: 38px;
            height: 38px;
            border-radius: 9999px;
            background: var(--toggle-bg);
            border: 1px solid var(--toggle-border);
            color: var(--text);
            cursor: pointer;
            backdrop-filter: blur(8px);
            transition: transform 0.2s ease;
        }
        .theme-toggle:hover { transform: scale(1.08); }
        .theme-toggle:active { transform: scale(0.95); }
        .icon-sun, .icon-moon {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.35s ease, transform 0.45s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .icon-sun svg, .icon-moon svg { width: 18px; height: 18px; }
        [data-theme="light"] .icon-sun  { opacity: 1; transform: rotate(0deg) scale(1); }
        [data-theme="light"] .icon-moon { opacity: 0; transform: rotate(70deg) scale(0.4); }
        [data-theme="dark"]  .icon-sun  { opacity: 0; transform: rotate(-70deg) scale(0.4); }
        [data-theme="dark"]  .icon-moon { opacity: 1; transform: rotate(0deg) scale(1); }

        @media (prefers-reduced-motion: reduce) {
            .reveal, .corner, .status-dot, .ambient-glow {
                animation: none !important;
                opacity: 1 !important;
                transform: none !important;
            }
            .ambient-glow { opacity: var(--ambient-opacity) !important; }
        }
    </style>
</head>
<body class="theme-fade min-h-screen flex items-center justify-center p-4 sm:p-8 antialiased relative overflow-hidden">

    <div class="theme-fade ambient-glow ambient-1 w-72 h-72 sm:w-[28rem] sm:h-[28rem] -top-24 -left-24"></div>
    <div class="theme-fade ambient-glow ambient-2 w-72 h-72 sm:w-[28rem] sm:h-[28rem] -bottom-24 -right-24"></div>

    <main class="relative w-full max-w-md md:max-w-2xl lg:max-w-3xl reveal reveal-1">

        <span class="theme-fade corner corner-tl"></span>
        <span class="theme-fade corner corner-tr"></span>
        <span class="theme-fade corner corner-bl"></span>
        <span class="theme-fade corner corner-br"></span>

        <div class="theme-fade card-surface relative overflow-hidden rounded-[28px] border px-6 py-10 sm:px-10 sm:py-14">

            <button id="theme-toggle" class="theme-fade theme-toggle absolute top-4 right-4 z-20" aria-label="Toggle dark mode">
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

            <div class="relative z-10 flex flex-col items-center text-center">

                <div class="theme-fade logo-wrap reveal reveal-2">
                    <img src="https://www.bbit.edu.in/assets/frontend_template/img/logo.png" alt="BBIT Logo" class="h-12 sm:h-16 w-auto drop-shadow-md">
                </div>

                <p class="theme-fade reveal reveal-3 mt-5 font-mono text-[11px] sm:text-xs uppercase tracking-[0.25em] text-[var(--muted)]">
                    BBIT &middot; Attendance Portal
                </p>

                <h1 class="reveal reveal-3 font-display mt-2 text-2xl sm:text-4xl font-bold bg-clip-text text-transparent bg-[linear-gradient(90deg,var(--student),var(--teacher))] pb-1">
                    QR Attendance System
                </h1>

                <p class="theme-fade reveal reveal-4 mt-2 text-sm sm:text-base text-[var(--muted)] max-w-sm">
                    Select your portal to mark or manage today's attendance.
                </p>

                <div class="reveal reveal-5 mt-8 grid w-full grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">

                    <a href="student" class="theme-fade group relative flex items-center gap-4 overflow-hidden rounded-2xl border border-[var(--line)] bg-[var(--surface)] p-5 transition-all duration-300 hover:-translate-y-1 hover:border-[var(--student-border)] hover:bg-[var(--student-soft)] hover:shadow-[0_12px_30px_-12px_var(--student)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--student)]">
                        <span class="absolute left-0 top-3 bottom-3 w-1 rounded-full bg-[var(--student)] opacity-50 transition-opacity duration-300 group-hover:opacity-100"></span>
                        
                        <span class="theme-fade flex h-12 w-12 sm:h-14 sm:w-14 shrink-0 items-center justify-center rounded-xl transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 3L1 9L12 15L21 10.09V17H23V9L12 3Z" fill="var(--student)"/>
                                <path d="M5 13.18V17.18L12 21L19 17.18V13.18L12 17L5 13.18Z" fill="var(--student)" opacity="0.6"/>
                            </svg>
                        </span>

                        <span class="flex-1 text-left">
                            <span class="theme-fade block font-display text-lg sm:text-xl font-semibold text-[var(--text-strong)]">Student</span>
                            <span class="theme-fade block font-mono text-xs text-[var(--muted)] mt-1 tracking-wide">Mark your attendance</span>
                        </span>
                        
                        <svg class="theme-fade h-5 w-5 shrink-0 text-[var(--muted)] transition-all duration-300 group-hover:translate-x-1.5 group-hover:text-[var(--student)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14M13 6l6 6-6 6"/>
                        </svg>
                    </a>

                    <a href="teacher" class="theme-fade group relative flex items-center gap-4 overflow-hidden rounded-2xl border border-[var(--line)] bg-[var(--surface)] p-5 transition-all duration-300 hover:-translate-y-1 hover:border-[var(--teacher-border)] hover:bg-[var(--teacher-soft)] hover:shadow-[0_12px_30px_-12px_var(--teacher)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--teacher)]">
                        <span class="absolute left-0 top-3 bottom-3 w-1 rounded-full bg-[var(--teacher)] opacity-50 transition-opacity duration-300 group-hover:opacity-100"></span>
                        
                        <span class="theme-fade flex h-12 w-12 sm:h-14 sm:w-14 shrink-0 items-center justify-center rounded-xl transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-3">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M21 3H3C1.89543 3 1 3.89543 1 5V19C1 20.1046 1.89543 21 3 21H21C22.1046 21 23 20.1046 23 19V5C23 3.89543 22.1046 3 21 3ZM21 19H3V5H21V19Z" fill="var(--teacher)"/>
                                <path d="M7 8H17V10H7V8ZM7 12H14V14H7V12Z" fill="var(--teacher)" opacity="0.5"/>
                            </svg>
                        </span>

                        <span class="flex-1 text-left">
                            <span class="theme-fade block font-display text-lg sm:text-xl font-semibold text-[var(--text-strong)]">Teacher</span>
                            <span class="theme-fade block font-mono text-xs text-[var(--muted)] mt-1 tracking-wide">Manage class sessions</span>
                        </span>

                        <svg class="theme-fade h-5 w-5 shrink-0 text-[var(--muted)] transition-all duration-300 group-hover:translate-x-1.5 group-hover:text-[var(--teacher)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14M13 6l6 6-6 6"/>
                        </svg>
                    </a>

                </div>

                <div class="theme-fade reveal reveal-6 mt-8 sm:mt-10 flex items-center gap-2 pt-6 border-t border-[var(--line)] w-full justify-center">
                    <span class="status-dot"></span>
                    <p class="font-mono text-[10px] sm:text-xs uppercase tracking-[0.2em] text-[var(--muted)]">
                        System active &middot; &copy; <span id="year"></span> BBIT
                    </p>
                </div>

            </div>
        </div>
    </main>

    <script>
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
    </script>
</body>
</html>