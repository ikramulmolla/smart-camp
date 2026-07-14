<?php
session_start();
date_default_timezone_set('Asia/Kolkata');

if (!isset($_SESSION['teacher'])) {
    header('Location: ../index.php');
    exit();
}

$teacherId   = $_SESSION['teacher'];
$teacherName = $_SESSION['teacher_name'] ?? $teacherId;

$attendanceDir = __DIR__ . "/../../attendance_files/";

// ----------------------------------
// ACCURATE SUBJECT AGGREGATION
// ----------------------------------
$subjectStats = [];

// Ensure directory exists to prevent glob() errors
if (!is_dir($attendanceDir)) {
    mkdir($attendanceDir, 0777, true);
}

foreach (glob($attendanceDir . "attendance_{$teacherId}_*.txt") as $file) {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $parts = array_map('trim', explode('|', $line));
        // Expecting: Roll | Name | Stream | Subject | Date | Time | Status
        if (count($parts) < 5) continue;

        $subject = $parts[3] ?? 'Unknown Subject';
        $date    = $parts[4] ?? '';
        $status  = strtolower($parts[6] ?? 'present'); // Fallback to present if not explicitly marked

        if (!isset($subjectStats[$subject])) {
            $subjectStats[$subject] = [
                'sessions' => [],
                'total_recorded' => 0,
                'total_present' => 0
            ];
        }

        $sessionKey = $subject . "_" . $date;

        // Mark session as conducted
        if (!isset($subjectStats[$subject]['sessions'][$sessionKey])) {
            $subjectStats[$subject]['sessions'][$sessionKey] = true;
        }

        // Track total student entries for percentage calculation
        $subjectStats[$subject]['total_recorded']++;

        // Track present students
        if (strpos($status, 'present') !== false || $status === 'p') {
            $subjectStats[$subject]['total_present']++;
        }
    }
}

// Prepare data for Chart.js
$chartLabels = [];
$chartData = [];

foreach ($subjectStats as $subject => $data) {
    $avg = ($data['total_recorded'] > 0) ? round(($data['total_present'] / $data['total_recorded']) * 100) : 0;
    
    // Shorten subject names for chart labels if too long
    $shortLabel = (strlen($subject) > 20) ? substr($subject, 0, 20) . '...' : $subject;
    $chartLabels[] = $shortLabel;
    $chartData[] = $avg;
}
?>

<!DOCTYPE html>
<html lang="en" class="h-full scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject Analytics | Smart Faculty Portal</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border: 1px solid rgba(229, 231, 235, 0.5); }
    </style>
</head>

<body class="text-gray-800 pb-12">

<!-- TOP NAV (Consistent with Dashboard) -->
<nav class="bg-white sticky top-0 z-40 px-4 py-4 shadow-sm border-b border-gray-200 mb-6">
    <div class="max-w-6xl mx-auto flex justify-between items-center">
        <h2 class="text-xl font-bold text-gray-800 flex items-center gap-3">
            <div class="w-8 h-8 bg-emerald-500 text-white rounded-lg flex items-center justify-center shadow-md">
                <i data-feather="book-open" class="w-4 h-4"></i>
            </div>
            Subject-wise Analytics
        </h2>
        <a href="../dashboard" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl text-sm font-semibold flex items-center gap-2 transition shadow-sm border border-gray-200">
            <i data-feather="arrow-left" class="w-4 h-4"></i> Back to Hub
        </a>
    </div>
</nav>

<div class="max-w-6xl mx-auto px-4 space-y-6">

    <!-- FACULTY PROFILE BANNER -->
    <div class="glass-card p-6 rounded-3xl shadow-sm flex flex-col sm:flex-row items-center gap-5 relative overflow-hidden">
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-emerald-50 rounded-full blur-3xl opacity-60 pointer-events-none"></div>
        <div class="w-16 h-16 bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center rounded-2xl text-2xl font-bold shadow-lg z-10">
            <?= strtoupper(substr($teacherName, 0, 1)) ?>
        </div>
        <div class="text-center sm:text-left z-10">
            <h3 class="text-xl font-bold text-gray-900"><?= htmlspecialchars($teacherName) ?></h3>
            <p class="text-sm text-gray-500 font-medium mt-1">Faculty ID: <span class="text-emerald-600"><?= htmlspecialchars($teacherId) ?></span></p>
        </div>
        <div class="ml-auto mt-4 sm:mt-0 flex gap-3 z-10">
            <span class="bg-blue-50 text-blue-600 border border-blue-100 px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wide flex items-center gap-1.5">
                <i data-feather="layers" class="w-3.5 h-3.5"></i> <?= count($subjectStats) ?> Active Subjects
            </span>
        </div>
    </div>

    <!-- MAIN GRID: CHART & TABLE -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- VISUAL BAR CHART (Span 5) -->
        <div class="lg:col-span-5 bg-white p-6 rounded-3xl shadow-sm border border-gray-200 h-full">
            <h4 class="font-bold text-gray-800 mb-6 flex items-center gap-2 border-b border-gray-100 pb-3">
                <i data-feather="bar-chart-2" class="text-emerald-500"></i> Overall Health Tracker
            </h4>
            
            <?php if (empty($subjectStats)): ?>
                <div class="flex flex-col items-center justify-center h-48 text-gray-400">
                    <i data-feather="inbox" class="w-10 h-10 mb-2 opacity-50"></i>
                    <p class="text-sm font-medium">No class data found.</p>
                </div>
            <?php else: ?>
                <div class="relative h-64 sm:h-72 w-full">
                    <canvas id="attendanceBarChart"></canvas>
                </div>
            <?php endif; ?>
        </div>

        <!-- DETAILED TABLE WITH PROGRESS BARS (Span 7) -->
        <div class="lg:col-span-7 bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h4 class="font-bold text-gray-800 flex items-center gap-2">
                    <i data-feather="list" class="text-blue-600"></i> Subject Detail Matrix
                </h4>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white text-gray-400 text-xs uppercase tracking-wider border-b border-gray-100">
                            <th class="px-6 py-4 font-semibold w-1/2">Subject Module</th>
                            <th class="px-6 py-4 font-semibold text-center">Classes</th>
                            <th class="px-6 py-4 font-semibold text-right">Avg. Attendance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if (empty($subjectStats)): ?>
                            <tr>
                                <td colspan="3" class="text-center py-10 text-gray-500 font-medium bg-gray-50/30">
                                    No subjects have been taught yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($subjectStats as $subject => $data): 
                                $classCount = count($data['sessions']);
                                $avg = ($data['total_recorded'] > 0) ? round(($data['total_present'] / $data['total_recorded']) * 100) : 0;
                                
                                // Dynamic Color Logic based on MAKAUT/NAAC standards
                                $barColor = $avg >= 75 ? 'bg-emerald-500' : ($avg >= 60 ? 'bg-amber-400' : 'bg-red-500');
                                $textColor = $avg >= 75 ? 'text-emerald-700' : ($avg >= 60 ? 'text-amber-600' : 'text-red-600');
                                $bgColor = $avg >= 75 ? 'bg-emerald-50' : ($avg >= 60 ? 'bg-amber-50' : 'bg-red-50');
                            ?>
                            <tr class="hover:bg-gray-50 transition group">
                                <td class="px-6 py-5">
                                    <div class="font-bold text-gray-800 text-sm"><?= htmlspecialchars($subject) ?></div>
                                    <div class="text-xs text-gray-400 mt-1">Recorded entries: <?= $data['total_recorded'] ?></div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-600 font-bold text-xs group-hover:bg-[#1E5AF6] group-hover:text-white transition">
                                        <?= $classCount ?>
                                    </span>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-col gap-2">
                                        <div class="flex justify-end items-center gap-2">
                                            <span class="text-sm font-black <?= $textColor ?>"><?= $avg ?>%</span>
                                        </div>
                                        <!-- Progress Bar -->
                                        <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden flex justify-end">
                                            <div class="<?= $barColor ?> h-1.5 rounded-full transition-all duration-1000" style="width: <?= $avg ?>%"></div>
                                        </div>
                                    </div>
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
    // Initialize Feather Icons
    feather.replace();

    // Chart.js Configuration
    <?php if (!empty($subjectStats)): ?>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('attendanceBarChart').getContext('2d');
        
        // Setup gradient for the bars
        let gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, '#10B981'); // Emerald top
        gradient.addColorStop(1, '#059669'); // Darker emerald bottom

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [{
                    label: 'Average Attendance (%)',
                    data: <?= json_encode($chartData) ?>,
                    backgroundColor: gradient,
                    borderRadius: 6,
                    borderSkipped: false,
                    barThickness: 20
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1F2937',
                        padding: 10,
                        titleFont: { family: 'Inter', size: 13 },
                        bodyFont: { family: 'Inter', size: 12, weight: 'bold' },
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return context.raw + '% Students Present';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: { color: '#F3F4F6', drawBorder: false },
                        ticks: { font: { family: 'Inter' }, stepSize: 25, callback: function(value) { return value + '%'; } }
                    },
                    x: {
                        grid: { display: false, drawBorder: false },
                        ticks: { font: { family: 'Inter', size: 10 }, maxRotation: 45, minRotation: 45 }
                    }
                }
            }
        });
    });
    <?php endif; ?>
</script>
</body>
</html>