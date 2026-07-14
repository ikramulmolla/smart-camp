<?php
session_start();
date_default_timezone_set('Asia/Kolkata');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed. Use POST.');
}

// Ensure student is logged in
if (!isset($_SESSION['roll'], $_SESSION['name'])) {
    http_response_code(401);
    exit('Unauthorized. Please log in.');
}

$studentRoll = $_SESSION['roll'];
$studentName = $_SESSION['name'];
$sessionId = trim($_POST['session_id'] ?? '');

// Load active QR session
$activeSession = trim(@file_get_contents('../../teacher/take_attendance/active_session.txt'));

// ⭐ Load current class info from the correct folder ⭐
$currentClassFile = '../../teacher/class/current_class.txt';

if (!file_exists($currentClassFile)) {
    header('Location: index.php?error=class_not_found');
    exit();
}

$classData = json_decode(file_get_contents($currentClassFile), true);

// Read class info
$subject        = $classData['subject'] ?? '';
$attendanceFile = $classData['attendance_file'] ?? '';
$teacherName    = $classData['teacher'] ?? '';
$classYear      = $classData['year'] ?? '';
$classStream    = $classData['stream'] ?? '';
$classSection   = $classData['section'] ?? 'none';

// Validate attendance file path
if (empty($attendanceFile)) {
    header('Location: index.php?error=class_missing');
    exit();
}

// ⭐ Generate last scan file automatically ⭐
$lastScanFileName = "attendance_lastscan_" . 
                    preg_replace('/[^a-zA-Z0-9]/', '', $teacherName) . "_" .
                    $classYear . "_" . $classStream;

if ($classSection !== 'none') {
    $lastScanFileName .= "_" . $classSection;
}

$lastScanFile = "../../attendance_files/$lastScanFileName.txt";

// Validate QR
if ($sessionId === $activeSession) {

    $date = date('d/m/y');
    $time = date('h:i A');
    $entry = "$studentRoll | $studentName | $sessionId | $subject | $date | $time\n";

    // Save attendance inside attendance_files folder
    file_put_contents($attendanceFile, $entry, FILE_APPEND);

    // Update last scanned student
    $lastScan = "$studentRoll | $studentName | $date | $time";
    file_put_contents($lastScanFile, $lastScan);

    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Attendance Success</title>
        <meta http-equiv="refresh" content="4;url=../">
        <script src="https://cdn.tailwindcss.com"></script>
    </head>

    <body class="bg-gradient-to-r from-green-100 to-blue-200 min-h-screen flex items-center justify-center p-4">

        <div class="bg-white shadow-2xl rounded-2xl p-8 w-full max-w-md text-center animate-slideIn">

            <img src="https://i.gifer.com/7efs.gif" class="w-40 mx-auto mb-6">

            <h2 class="text-2xl font-bold text-green-700 mb-2">Attendance Marked Successfully!</h2>

            <p class="text-lg text-gray-700 mb-4">
                <?= htmlspecialchars($studentName) ?> (Roll: <?= htmlspecialchars($studentRoll) ?>)
            </p>

            <p class="text-gray-500 mb-4">Redirecting to dashboard...</p>

            <a href="../" class="bg-blue-600 text-white px-6 py-2 rounded-xl hover:bg-blue-700">
                Go to Dashboard Now
            </a>
        </div>

        <style>
            @keyframes slideIn {
                from {opacity:0; transform:translateY(20px);}
                to {opacity:1; transform:translateY(0);}
            }
            .animate-slideIn { animation: slideIn 1s ease-out; }
        </style>
    </body>
    </html>
    <?php
    exit();
}

// ❌ INVALID QR — Show error message
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invalid QR</title>
    <meta http-equiv="refresh" content="4;url=../">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-r from-red-100 to-orange-200 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white shadow-2xl rounded-2xl p-8 w-full max-w-md text-center animate-slideIn">

        <img src="https://i.gifer.com/ZZ5H.gif" class="w-40 mx-auto mb-6">

        <h2 class="text-2xl font-bold text-red-700 mb-2">Invalid or Expired QR Code!</h2>
        <p class="text-gray-500 mb-4">Please rescan a valid QR.</p>

        <a href="../" class="bg-blue-600 text-white px-6 py-2 rounded-xl hover:bg-blue-700">
            Go to Dashboard Now
        </a>
    </div>

    <style>
        @keyframes slideIn {
            from {opacity:0; transform:translateY(20px);}
            to {opacity:1; transform:translateY(0);}
        }
        .animate-slideIn { animation: slideIn 1s ease-out; }
    </style>

</body>
</html>
