<?php
session_start();
date_default_timezone_set('Asia/Kolkata');

// Verify session context strictly
if (!isset($_SESSION['roll']) || !isset($_SESSION['name'])) {
    header('Location: ../index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Radar QR Scanner | BBIT</title>
    <script>
        (function () {
            var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.setAttribute('data-theme', prefersDark ? 'dark' : 'light');
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        :root {
            --bg-1: #eff6ff; --bg-2: #e0e7ff; --bg-3: #f3e8ff;
            --card-bg: rgba(255, 255, 255, 0.85); --card-border: rgba(255, 255, 255, 1);
            --text-strong: #0f172a; --text: #334155; --muted: #64748b;
            --line: rgba(15, 23, 42, 0.06); --surface-hover: rgba(248, 250, 252, 0.9);
            --student: #2563eb; --warning-text: #d97706; --success-text: #16a34a;
        }
        [data-theme="dark"] {
            --bg-1: #020617; --bg-2: #0f172a; --bg-3: #1e1b4b;
            --card-bg: rgba(15, 23, 42, 0.65); --card-border: rgba(255, 255, 255, 0.05);
            --text-strong: #f8fafc; --text: #cbd5e1; --muted: #94a3b8;
            --line: rgba(255, 255, 255, 0.06); --surface-hover: rgba(51, 65, 85, 0.75);
            --student: #3b82f6; --warning-text: #fbbf24; --success-text: #4ade80;
        }
        body { background: linear-gradient(135deg, var(--bg-1), var(--bg-2), var(--bg-3)); background-attachment: fixed; }
        #qr-reader { border: none !important; width: 100%; max-width: 340px; margin: 0 auto; }
        #qr-reader video { border-radius: 24px; object-fit: cover; }
        #qr-reader__scan_region { background: var(--surface-hover); border-radius: 24px; padding: 6px; }
        #qr-reader__dashboard { display: none !important; }
    </style>
</head>
<body class="antialiased min-h-screen flex items-center justify-center p-4">

    <!-- FULLSCREEN QR CONTAINER CARD -->
    <div class="w-full max-w-md bg-[var(--card-bg)] border border-[var(--card-border)] shadow-2xl rounded-[2.5rem] p-6 text-center backdrop-blur-xl">
        
        <!-- HEADER ROW -->
        <div class="flex justify-between items-center mb-6 border-b border-[var(--line)] pb-4">
            <a href="../dashboard" class="inline-flex items-center gap-1 text-xs font-bold uppercase text-[var(--muted)] hover:text-[var(--student)] transition">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg> Back
            </a>
            <span class="text-xs font-black uppercase tracking-wider text-[var(--student)]">Live Scanner</span>
        </div>

        <h3 class="text-lg font-extrabold text-[var(--text-strong)] tracking-tight mb-1">Radar QR Camera</h3>
        <p class="text-xs text-[var(--muted)] mb-5">Align teacher's dynamic QR token within the borders.</p>

        <!-- DEDICATED QR TARGET WINDOW -->
        <div id="qr-reader" class="aspect-square bg-[var(--surface-hover)] rounded-3xl border-2 border-dashed border-[var(--line)] mb-5 flex items-center justify-center overflow-hidden"></div>
        
        <!-- DYNAMIC TRACKER STATUS CHIP -->
        <div id="scanStatus" class="mb-4 text-center text-xs font-bold uppercase tracking-wide bg-[var(--warning-text)]/10 border border-[var(--warning-text)]/10 px-4 py-3 rounded-xl w-full animate-pulse">
            Initializing hardware lens...
        </div>

        <!-- HIDDEN ATTENDANCE ACTION POST FORM -->
        <form method="POST" action="submit_qr_attendance.php" id="attendanceForm">
            <input type="hidden" name="session_id" id="session_id">
        </form>
    </div>

    <script>
        let html5QrCode;
        const statusBox = document.getElementById('scanStatus');

        function startScanner() {
            html5QrCode = new Html5Qrcode("qr-reader");
            html5QrCode.start(
                { facingMode: "environment" },
                { fps: 15, qrbox: { width: 240, height: 240 } },
                onScanSuccess
            )
            .then(() => {
                statusBox.innerHTML = "Camera active • Point at QR Code";
                statusBox.className = "mb-4 text-center text-xs font-bold uppercase tracking-wide text-[var(--student)] bg-[var(--student-soft)] border border-[var(--student)]/10 px-4 py-3 rounded-xl w-full remove-pulse";
            })
            .catch((err) => {
                statusBox.innerHTML = "Camera Error • Check permissions";
                statusBox.className = "mb-4 text-center text-xs font-bold uppercase tracking-wide text-[var(--error-text)] bg-[var(--error-bg)] px-4 py-3 rounded-xl w-full";
            });
        }

        function onScanSuccess(decodedText) {
            statusBox.innerHTML = "Token Intercepted! Submitting...";
            statusBox.className = "mb-4 text-center text-xs font-bold uppercase tracking-wide text-[var(--success-text)] bg-[var(--success-text)]/10 border border-[var(--success-text)]/20 px-4 py-3 rounded-xl w-full transition-all";
            
            if(navigator.vibrate) navigator.vibrate(200);

            setTimeout(() => {
                document.getElementById('session_id').value = decodedText;
                document.getElementById('attendanceForm').submit();
                html5QrCode.stop().catch(console.error);
            }, 500);
        }

        // Auto trigger scanner setup loop instantly on page load
        window.onload = startScanner;
    </script>
</body>
</html>