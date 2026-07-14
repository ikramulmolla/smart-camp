<?php
session_start();
date_default_timezone_set('Asia/Kolkata');

if(!isset($_SESSION['roll'], $_SESSION['name'])){
    exit("Unauthorized. Please log in.");
}

$studentRoll = $_SESSION['roll'];
$studentName = $_SESSION['name'];
$inputPin = trim($_POST['pin'] ?? '');
$found = false;

$attendanceFolder = "../../attendance_files/";   // ✅ FIXED universal folder
$activePinsFile = "../../teacher/class/active_pins.txt";

$attendanceFile = '';
$subject = '';


// Read active PINs
if(file_exists($activePinsFile)){
    $lines = file($activePinsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach($lines as $line){
        $parts = explode('|', $line);
        if(count($parts) < 4) continue;

        list($pin, $file, $pinSubject, $expires) = $parts;

        if($pin === $inputPin && time() <= intval($expires)){
            $found = true;

            // ✅ force attendance file to exist inside attendance_files folder
            $attendanceFile = $attendanceFolder . basename($file);

            $subject = $pinSubject;
            break;
        }
    }
}

if(!$found){
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Invalid PIN</title>
        <meta http-equiv="refresh" content="4;url=../">
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="https://unpkg.com/feather-icons"></script>
    </head>
    <body class="bg-gradient-to-r from-red-100 to-orange-200 min-h-screen flex items-center justify-center p-4">

        <div class="bg-white shadow-2xl rounded-2xl p-8 w-full max-w-md text-center animate-slideIn">
            <i data-feather="x-circle" class="text-red-600 w-16 h-16 mx-auto mb-4"></i>
            <h2 class="text-2xl font-bold text-red-700 mb-2">Invalid or Expired PIN!</h2>
            <p class="text-gray-500 mb-4">Please try again with a valid PIN.</p>
            <a href="../" class="bg-blue-600 text-white px-6 py-2 rounded-xl hover:bg-blue-700 transition duration-300 inline-flex items-center gap-2">
                <i data-feather="arrow-left"></i> Go to Dashboard
            </a>
        </div>

        <style>
            @keyframes slideIn { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
            .animate-slideIn { animation: slideIn 1s ease-out forwards; }
        </style>
        <script>feather.replace();</script>
    </body>
    </html>
    <?php
    exit();
}


// Ensure attendance folder exists
if (!is_dir($attendanceFolder)) {
    mkdir($attendanceFolder, 0777, true);
}

// Mark attendance
$date = date('d/m/y');
$time = date('h:i A');
$sessionId = uniqid();

$logEntry = "$studentRoll | $studentName | $sessionId | $subject | $date | $time\n";

// ✅ ALWAYS write inside attendance_files folder
file_put_contents($attendanceFile, $logEntry, FILE_APPEND);

// Save last scan also inside attendance_files folder
$lastScanFile = $attendanceFolder . "last_scan_" . basename($attendanceFile);
file_put_contents($lastScanFile, "$studentRoll | $studentName | $date | $time");

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Attendance Success</title>
<meta http-equiv="refresh" content="4;url=../">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/feather-icons"></script>
</head>
<body class="bg-gradient-to-r from-green-100 to-blue-200 min-h-screen flex items-center justify-center p-4">

<div class="bg-white shadow-2xl rounded-2xl p-8 w-full max-w-md text-center animate-slideIn">
    <i data-feather="check-circle" class="text-green-600 w-16 h-16 mx-auto mb-4"></i>
    <h2 class="text-2xl font-bold text-green-700 mb-2">Attendance Marked Successfully!</h2>
    <p class="text-lg text-gray-700 mb-2"><?= htmlspecialchars($studentName) ?> (Roll: <?= htmlspecialchars($studentRoll) ?>)</p>
    <p class="text-gray-500 mb-4">You will be redirected to the dashboard shortly.</p>
    <a href="../" class="bg-blue-600 text-white px-6 py-2 rounded-xl hover:bg-blue-700 transition duration-300 inline-flex items-center gap-2">
        <i data-feather="arrow-right"></i> Go to Dashboard Now
    </a>
</div>

<style>
    @keyframes slideIn { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
    .animate-slideIn { animation: slideIn 1s ease-out forwards; }
</style>
<script>feather.replace();</script>
</body>
</html>
