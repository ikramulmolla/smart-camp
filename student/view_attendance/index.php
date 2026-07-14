<?php
session_start();
date_default_timezone_set('Asia/Kolkata');

// 🔒 Basic session validation
if (!isset($_SESSION['roll']) || !isset($_SESSION['name'])) {
    header('Location: ../index.php');
    exit();
}

$studentRoll = $_SESSION['roll'];
$studentName = $_SESSION['name'];

// ----------- ATTENDANCE HISTORY LOADING -------------
$attendanceDir = '../../attendance_files/';
$attendanceRecords = [];

if (is_dir($attendanceDir)) {
    $files = scandir($attendanceDir);

    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) !== 'txt') continue;

        $filePath = $attendanceDir . $file;
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $parts = explode('|', $line);
            if (count($parts) < 6) continue;

            $roll = trim($parts[0]);
            $subject = trim($parts[3]);
            $date = trim($parts[4]);
            $time = trim($parts[5]);

            if ($roll === $studentRoll) {
                $attendanceRecords[] = [
                    'subject' => $subject,
                    'date' => $date,
                    'time' => $time
                ];
            }
        }
    }
}

// 🔄 Sort by datetime descending (recent first)
if (!empty($attendanceRecords)) {
    usort($attendanceRecords, function($a, $b) {
        $timeA = strtotime(str_replace('/', '-', $a['date']) . ' ' . $a['time']);
        $timeB = strtotime(str_replace('/', '-', $b['date']) . ' ' . $b['time']);
        return $timeB <=> $timeA; // recent first
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BBIT | Attendance History</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script> <!-- Feather Icons -->
</head>
<body class="bg-gradient-to-r from-green-100 to-blue-200 min-h-screen flex items-center justify-center p-4 relative">

<!-- Loader -->
<div id="loaderOverlay" class="fixed inset-0 bg-white z-50 flex flex-col items-center justify-center">
    <svg class="animate-spin h-12 w-12 text-blue-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none"
         viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
    <p class="text-blue-700 text-lg font-medium animate-pulse">Loading attendance history...</p>
</div>

<!-- Main Card -->
<div class="bg-white shadow-2xl rounded-2xl p-6 w-full max-w-4xl animate-slideIn relative z-10">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4 sm:gap-0">
        <div class="flex items-center gap-2 justify-center sm:justify-start">
            <img src="https://www.bbit.edu.in/assets/frontend_template/img/logo.png" alt="BBIT Logo" class="h-10">
        </div>
        <a href="../../logout/"
           class="text-sm bg-red-100 text-red-600 px-4 py-1 rounded-full hover:bg-red-600 hover:text-white transition duration-300 text-center w-full sm:w-auto flex items-center justify-center gap-1">
            <i data-feather="log-out"></i> Logout
        </a>
    </div>

    <h2 class="text-2xl font-bold text-blue-700 mb-2 text-center">
        Hello, <?php echo htmlspecialchars($studentName); ?> 👋
    </h2>
    <p class="text-gray-600 mb-6 text-center">
        Roll Number: <strong><?php echo htmlspecialchars($studentRoll); ?></strong>
    </p>

    <!-- Title + Refresh -->
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-semibold text-gray-700">Your Attendance History</h3>
        <button onclick="refreshPage()"
                class="text-blue-600 hover:text-blue-800 transition transform hover:rotate-90 duration-300">
            <i data-feather="refresh-cw" class="h-6 w-6"></i> <!-- Feather Icon for refresh -->
        </button>
    </div>

    <!-- Table -->
    <?php if (empty($attendanceRecords)): ?>
        <p class="text-red-500 text-lg text-center">No attendance records found.</p>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full border rounded-xl overflow-hidden text-center">
                <thead class="bg-blue-600 text-white">
                <tr>
                    <th class="px-4 py-2">Subject</th>
                    <th class="px-4 py-2">Date</th>
                    <th class="px-4 py-2">Time</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($attendanceRecords as $record): ?>
                    <tr class="border-b hover:bg-blue-50 transition duration-300">
                        <td class="px-4 py-2"><?php echo htmlspecialchars($record['subject']); ?></td>
                        <td class="px-4 py-2"><?php echo htmlspecialchars($record['date']); ?></td>
                        <td class="px-4 py-2"><?php echo htmlspecialchars($record['time']); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- Buttons -->
    <div class="flex justify-center mt-6">
        <a href="../dashboard/">
            <button class="bg-blue-600 text-white px-6 py-2 rounded-xl hover:bg-blue-700 transition duration-300">
                Back to Dashboard
            </button>
        </a>
    </div>

    <p class="text-center text-gray-500 mt-6 text-sm">
        © <?php echo date("Y"); ?> Budge Budge Institute Of Technology
    </p>
</div>

<!-- Animation -->
<style>
    @keyframes slideIn {
        from {opacity: 0; transform: translateY(20px);}
        to {opacity: 1; transform: translateY(0);}
    }
    .animate-slideIn {animation: slideIn 1s ease-out forwards;}
</style>

<script>
    window.addEventListener('load', () => {
        document.getElementById('loaderOverlay').classList.add('hidden');
    });

    function refreshPage() {
        document.getElementById('loaderOverlay').classList.remove('hidden');
        location.reload();
    }

    feather.replace(); // Initialize Feather Icons
</script>

</body>
</html>
