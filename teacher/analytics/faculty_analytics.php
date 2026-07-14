<?php
session_start();
date_default_timezone_set('Asia/Kolkata');

if (!isset($_SESSION['teacher'])) {
    header('Location: ../index.php');
    exit();
}

$teacherId   = $_SESSION['teacher'];
$teacherName = $_SESSION['teacher_name'] ?? $teacherId;

// ----------------------------
// PATHS
// ----------------------------
$scheduleFile  = __DIR__ . "/../dashboard/teacher_schedules/teacher_" . preg_replace('/[^a-zA-Z0-9]/', '', $teacherId) . ".json";
$attendanceDir = __DIR__ . "/../../attendance_files/";

// ----------------------------
// 1. CALCULATE WEEKLY TARGET (From Schedule)
// ----------------------------
$weeklyScheduledClasses = 0;
if (file_exists($scheduleFile)) {
    $schedule = json_decode(file_get_contents($scheduleFile), true);
    if (is_array($schedule)) {
        foreach ($schedule as $day => $classes) {
            if (is_array($classes)) {
                $weeklyScheduledClasses += count($classes);
            }
        }
    }
}

// ----------------------------
// 2. PARSE ATTENDANCE & CALCULATE STATS
// ----------------------------
$allSessions = [];
$totalStudents = 0;
$presentStudents = 0;

$thisWeekStart = strtotime('monday this week 00:00:00');
$thisWeekEnd = strtotime('sunday this week 23:59:59');

$classesTakenThisWeek = 0;
$subjectWiseCount = [];

// Fallback directory creation just in case it doesn't exist
if (!is_dir($attendanceDir)) {
    mkdir($attendanceDir, 0777, true);
}

// Loop through all attendance files for this teacher
foreach (glob($attendanceDir . "attendance_{$teacherId}_*.txt") as $file) {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        $parts = array_map('trim', explode('|', $line));
        // Assuming format: Roll | Name | Stream | Subject | Date | Time | Status
        if (count($parts) < 5) continue; 

        $subject = $parts[3] ?? 'Unknown Subject';
        $dateStr = $parts[4] ?? ''; 
        $status  = strtolower($parts[6] ?? 'present'); // Assuming 6th index is status, default to present if missing
        
        $sessionKey = $subject . "_" . $dateStr;
        
        if (!isset($allSessions[$sessionKey])) {
            $timestamp = strtotime($dateStr) ?: time();
            $allSessions[$sessionKey] = [
                'subject' => $subject,
                'date' => $dateStr,
                'timestamp' => $timestamp,
                'total' => 0,
                'present' => 0
            ];
        }

        // Track student attendance counts
        $allSessions[$sessionKey]['total']++;
        $totalStudents++;
        
        if (strpos($status, 'present') !== false || $status === 'p') {
            $allSessions[$sessionKey]['present']++;
            $presentStudents++;
        }
    }
}

// Process unique sessions
foreach ($allSessions as $key => $data) {
    // Count lifetime subject distribution
    if (!isset($subjectWiseCount[$data['subject']])) {
        $subjectWiseCount[$data['subject']] = 0;
    }
    $subjectWiseCount[$data['subject']]++;

    // Count this week's classes
    if ($data['timestamp'] >= $thisWeekStart && $data['timestamp'] <= $thisWeekEnd) {
        $classesTakenThisWeek++;
    }
}

// Sort sessions to get recent ones
usort($allSessions, function($a, $b) {
    return $b['timestamp'] - $a['timestamp'];
});
$recentSessions = array_slice($allSessions, 0, 5); // Get top 5 recent

// ----------------------------
// 3. FINAL METRICS
// ----------------------------
$totalLifetimeClasses = count($allSessions);
$missedThisWeek = max(0, $weeklyScheduledClasses - $classesTakenThisWeek);
$overallAttendanceAvg = ($totalStudents > 0) ? round(($presentStudents / $totalStudents) * 100, 1) : 0;

// Prepare chart data safely
$chartLabels = json_encode(array_keys($subjectWiseCount));
$chartData = json_encode(array_values($subjectWiseCount));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Analytics | Faculty Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border: 1px solid rgba(229, 231, 235, 0.5); }
    </style>
</head>

<body class="text-gray-800 pb-12">

<!-- TOP NAV -->
<nav class="bg-white sticky top-0 z-40 px-4 py-4 shadow-sm border-b border-gray-200 mb-6">
    <div class="max-w-6xl mx-auto flex justify-between items-center">
        <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
            <div class="w-8 h-8 bg-[#1E5AF6] text-white rounded-lg flex items-center justify-center"><i data-feather="pie-chart" class="w-4 h-4"></i></div>
            Performance Analytics
        </h2>
        <a href="../dashboard" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2 rounded-xl text-sm font-semibold flex items-center gap-2 transition">
            <i data-feather="arrow-left" class="w-4 h-4"></i> Back to Hub
        </a>
    </div>
</nav>

<div class="max-w-6xl mx-auto px-4 space-y-6">

    <!-- FACULTY PROFILE BANNER -->
    <div class="glass-card p-6 rounded-3xl shadow-sm flex flex-col sm:flex-row items-center gap-5 relative overflow-hidden">
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-blue-50 rounded-full blur-3xl opacity-50"></div>
        <div class="w-16 h-16 bg-gradient-to-br from-[#1E5AF6] to-[#4F46E5] text-white flex items-center justify-center rounded-2xl text-2xl font-bold shadow-lg">
            <?= strtoupper(substr($teacherName, 0, 1)) ?>
        </div>
        <div class="text-center sm:text-left z-10">
            <h3 class="text-xl font-bold text-gray-900"><?= htmlspecialchars($teacherName) ?></h3>
            <p class="text-sm text-gray-500 font-medium mt-1">Faculty ID: <span class="text-[#1E5AF6]"><?= htmlspecialchars($teacherId) ?></span></p>
        </div>
        <div class="ml-auto mt-4 sm:mt-0 flex gap-3 z-10">
            <span class="bg-emerald-50 text-emerald-600 border border-emerald-100 px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wide flex items-center gap-1.5"><i data-feather="check-circle" class="w-3.5 h-3.5"></i> Status Active</span>
        </div>
    </div>

    <!-- 4-GRID ANALYTICS COUNTERS -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <!-- Weekly Target -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-16 h-16 bg-blue-50 rounded-full group-hover:scale-150 transition duration-500 z-0"></div>
            <div class="relative z-10">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Weekly Target</p>
                <div class="flex items-end gap-2">
                    <p class="text-4xl font-black text-gray-800"><?= $weeklyScheduledClasses ?></p>
                    <p class="text-sm text-gray-500 mb-1">classes</p>
                </div>
            </div>
        </div>

        <!-- Taken This Week -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-16 h-16 bg-emerald-50 rounded-full group-hover:scale-150 transition duration-500 z-0"></div>
            <div class="relative z-10">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Taken This Week</p>
                <div class="flex items-end gap-2">
                    <p class="text-4xl font-black text-emerald-600"><?= $classesTakenThisWeek ?></p>
                    <p class="text-sm text-gray-500 mb-1">done</p>
                </div>
            </div>
        </div>

        <!-- Lifetime Classes -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-16 h-16 bg-purple-50 rounded-full group-hover:scale-150 transition duration-500 z-0"></div>
            <div class="relative z-10">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Total Lifetime</p>
                <div class="flex items-end gap-2">
                    <p class="text-4xl font-black text-purple-600"><?= $totalLifetimeClasses ?></p>
                    <p class="text-sm text-gray-500 mb-1">conducted</p>
                </div>
            </div>
        </div>

        <!-- Avg Attendance -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-16 h-16 bg-amber-50 rounded-full group-hover:scale-150 transition duration-500 z-0"></div>
            <div class="relative z-10">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Avg Student Att.</p>
                <div class="flex items-end gap-2">
                    <p class="text-4xl font-black text-amber-500"><?= $overallAttendanceAvg ?>%</p>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN TWO-COLUMN SPLIT -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- LEFT: VISUAL CHART (Span 1) -->
        <div class="lg:col-span-1 bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
            <h4 class="font-bold text-gray-800 mb-6 flex items-center gap-2">
                <i data-feather="bar-chart" class="text-[#1E5AF6]"></i> Subject Distribution
            </h4>
            <?php if($totalLifetimeClasses > 0): ?>
                <div class="relative h-64 w-full">
                    <canvas id="subjectChart"></canvas>
                </div>
            <?php else: ?>
                <div class="flex flex-col items-center justify-center h-48 text-gray-400">
                    <i data-feather="inbox" class="w-10 h-10 mb-2"></i>
                    <p class="text-sm">No class data available yet.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- RIGHT: RECENT CLASSES LOG (Span 2) -->
        <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h4 class="font-bold text-gray-800 flex items-center gap-2">
                    <i data-feather="clock" class="text-emerald-600"></i> Recent Sessions Log
                </h4>
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Last 5 Classes</span>
            </div>
            
            <div class="p-0 overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white text-gray-400 text-xs uppercase tracking-wider border-b border-gray-100">
                            <th class="px-6 py-4 font-semibold">Subject Module</th>
                            <th class="px-6 py-4 font-semibold">Date Conducted</th>
                            <th class="px-6 py-4 font-semibold text-center">Students Present</th>
                            <th class="px-6 py-4 font-semibold text-right">Att. Ratio</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if (empty($recentSessions)): ?>
                            <tr><td colspan="4" class="text-center py-8 text-gray-500 font-medium">No sessions logged yet. Conduct a class to see it here!</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentSessions as $session): 
                                $ratio = ($session['total'] > 0) ? round(($session['present'] / $session['total']) * 100) : 0;
                                $ratioColor = $ratio >= 75 ? 'text-emerald-600 bg-emerald-50' : ($ratio >= 50 ? 'text-amber-600 bg-amber-50' : 'text-red-600 bg-red-50');
                            ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-800"><?= htmlspecialchars($session['subject']) ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 font-medium">
                                    <span class="flex items-center gap-1.5"><i data-feather="calendar" class="w-3.5 h-3.5 text-gray-400"></i> <?= htmlspecialchars($session['date']) ?></span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="font-bold text-gray-800"><?= $session['present'] ?></span> <span class="text-gray-400 text-xs">/ <?= $session['total'] ?></span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="px-2.5 py-1 rounded-md text-xs font-bold <?= $ratioColor ?>"><?= $ratio ?>%</span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
    // Initialize Icons
    feather.replace();

    // Chart.js Configuration
    <?php if($totalLifetimeClasses > 0): ?>
    const ctx = document.getElementById('subjectChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: <?= $chartLabels ?>,
            datasets: [{
                data: <?= $chartData ?>,
                backgroundColor: ['#1E5AF6', '#10B981', '#8B5CF6', '#F59E0B', '#EF4444', '#3B82F6'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, font: { family: 'Inter' } } }
            },
            cutout: '75%'
        }
    });
    <?php endif; ?>
</script>
</body>
</html>
