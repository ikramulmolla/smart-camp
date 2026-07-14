<?php
session_start();
date_default_timezone_set('Asia/Kolkata');

$cookie_lifetime = 60 * 60 * 24 * 30; // 30 days
$json_file = '../teachers.json';

// Helper function to validate credentials from JSON
function check_credentials($username, $password, $json_file) {
    if (!file_exists($json_file)) {
        return false;
    }
    $json_data = file_get_contents($json_file);
    $teachers = json_decode($json_data, true);
    
    if (is_array($teachers)) {
        foreach ($teachers as $teacher) {
            if (isset($teacher['username'], $teacher['password']) && 
                $teacher['username'] === $username && 
                $teacher['password'] === $password) {
                return $teacher;
            }
        }
    }
    return false;
}

// 1️⃣ If already logged in via SESSION → redirect
if (isset($_SESSION['teacher'])) {
    header('Location: dashboard/');
    exit();
}

// 2️⃣ Auto-login using cookie → create session → redirect
if (isset($_COOKIE['auto_login_teacher'])) {
    $cookieParts = explode('|', $_COOKIE['auto_login_teacher']);

    if (count($cookieParts) === 2) {
        [$cookieUser, $cookiePass] = $cookieParts;
        $matchedTeacher = check_credentials($cookieUser, $cookiePass, $json_file);

        if ($matchedTeacher) {
            session_regenerate_id(true);
            $_SESSION['teacher'] = $matchedTeacher['username'];
            $_SESSION['teacher_name'] = $matchedTeacher['name'] ?? $matchedTeacher['username'];
            $_SESSION['IPaddress'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['userAgent'] = $_SERVER['HTTP_USER_AGENT'];

            header('Location: dashboard/');
            exit();
        }
    }
    setcookie('auto_login_teacher', '', time() - 3600, '/');
}

// 3️⃣ Manual Login
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $matchedTeacher = check_credentials($username, $password, $json_file);

    if ($matchedTeacher) {
        session_regenerate_id(true);
        $_SESSION['teacher'] = $matchedTeacher['username'];
        $_SESSION['teacher_name'] = $matchedTeacher['name'] ?? $matchedTeacher['username'];
        $_SESSION['IPaddress'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['userAgent'] = $_SERVER['HTTP_USER_AGENT'];

        setcookie(
            'auto_login_teacher',
            $username . '|' . $password,
            time() + $cookie_lifetime,
            '/',
            '',
            false,
            true
        );

        header('Location: dashboard/');
        exit();
    }

    $error = "Invalid Username or Password.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>BBIT | Teacher Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>

<body class="bg-[#E6ECF8] dark:bg-[#0F172A] min-h-screen flex items-center justify-center p-0 sm:p-4 transition-colors duration-300">

    <!-- Container Login Window (Full screen on mobile, elegant card on desktop) -->
    <div class="bg-white dark:bg-[#1E293B] shadow-2xl sm:rounded-3xl max-w-5xl w-full flex flex-col md:flex-row overflow-hidden min-h-screen sm:min-h-[580px] md:min-h-[550px] transition-colors duration-300">
        
        <!-- LEFT SIDE: Visual Banner (Hidden on Mobile/Tablet, visible from md onwards) -->
        <div class="hidden md:flex md:w-1/2 bg-gradient-to-br from-[#1E5AF6] to-[#3B29DC] p-12 flex-col justify-between items-center text-white relative overflow-hidden">
            <div class="absolute inset-0 opacity-20 pointer-events-none">
                <div class="absolute w-96 h-96 border border-white rounded-full -top-10 -left-10"></div>
                <div class="absolute w-[500px] h-[500px] border border-white rounded-full -bottom-20 -right-20"></div>
            </div>
            
            <div class="w-full text-left font-bold text-lg tracking-wider opacity-90">
                BBIT PORTAL
            </div>

            <div class="flex flex-col items-center justify-center space-y-4 z-10 my-auto">
                <div class="relative w-32 h-44 bg-gradient-to-b from-[#4A7DFF] to-[#6355F6] rounded-2xl shadow-xl flex items-center justify-center border border-white/20">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-user-tie text-3xl"></i>
                    </div>
                    <div class="absolute bottom-12 right-4 w-8 h-8 bg-[#10B981] rounded-full flex items-center justify-center border-2 border-[#4A7DFF]">
                        <i class="fas fa-check text-xs"></i>
                    </div>
                </div>
                <h1 class="text-3xl font-bold tracking-tight mt-4">Teacher Portal</h1>
                <p class="text-white/70 text-sm font-light uppercase tracking-widest">Manage Classes & Attendance</p>
            </div>

            <div class="text-xs text-white/50 tracking-wide uppercase">
                Mark Your Presence Securely
            </div>
        </div>

        <!-- RIGHT SIDE: Login Inputs & Theme Switcher (Full screen adaptation on mobile) -->
        <div class="w-full md:w-1/2 p-6 sm:p-8 md:p-12 flex flex-col justify-between bg-white dark:bg-[#1E293B]">
            
            <!-- Header Row (Text removed, Only Clean Logo & Toggle) -->
            <div class="flex justify-between items-center mb-8 md:mb-6">
                <div class="flex items-center">
                    <img src="https://www.bbit.edu.in/assets/frontend_template/img/logo.png" alt="BBIT Logo" class="h-10 sm:h-12 object-contain">
                </div>
                
                <button id="themeToggle" class="w-10 h-10 rounded-full border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition shadow-sm">
                    <i id="themeIcon" class="fas fa-sun text-md"></i>
                </button>
            </div>

            <!-- Form Content Wrapper -->
            <div class="my-auto">
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 dark:text-white mb-2">Welcome Back</h2>
                <p class="text-gray-400 dark:text-gray-400 text-sm mb-6">Enter your credentials to access your dashboard.</p>

                <?php if (!empty($error)) : ?>
                    <div class="bg-red-50 dark:bg-red-900/20 text-red-500 text-sm p-3 rounded-xl mb-5 flex items-center space-x-2">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-5" autocomplete="off">
                    <!-- Username Input -->
                    <div>
                        <label class="block mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Username</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                                <i class="fas fa-user text-sm"></i>
                            </span>
                            <input type="text" name="username" placeholder="Username / Email" required
                                class="w-full pl-11 pr-4 py-3 bg-[#F8FAFC] dark:bg-[#0F172A] border border-gray-100 dark:border-gray-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#1E5AF6] focus:bg-white dark:focus:bg-[#0F172A] text-gray-800 dark:text-white placeholder-gray-400 transition text-base">
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div>
                        <label class="block mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Password</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                                <i class="fas fa-lock text-sm"></i>
                            </span>
                            <input type="password" name="password" id="passwordField" placeholder="Enter your password" required
                                class="w-full pl-11 pr-12 py-3 bg-[#F8FAFC] dark:bg-[#0F172A] border border-gray-100 dark:border-gray-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#1E5AF6] focus:bg-white dark:focus:bg-[#0F172A] text-gray-800 dark:text-white placeholder-gray-400 transition text-base">
                            <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 cursor-pointer hover:text-gray-600 dark:hover:text-gray-200 transition toggle-password">
                                <i id="eyeIcon" class="fas fa-eye"></i>
                            </span>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                        class="w-full bg-[#4F46E5] hover:bg-[#4338CA] text-white py-3.5 rounded-xl font-medium shadow-lg hover:shadow-xl transition duration-300 transform active:scale-[0.99] mt-2 text-base">
                        Log In to Portal
                    </button>
                </form>
            </div>

            <!-- Footer Section -->
            <div class="text-center text-[10px] sm:text-[11px] text-gray-400 dark:text-gray-500 tracking-widest uppercase mt-8 pt-4 border-t border-gray-100 dark:border-gray-800">
                © <?= date("Y") ?> BBIT ATTENDANCE PORTAL
            </div>
        </div>

    </div>

    <script>
        // Password Visibility Toggle
        document.querySelector(".toggle-password").addEventListener("click", function () {
            const passwordField = document.getElementById("passwordField");
            const eyeIcon = document.getElementById("eyeIcon");
            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyeIcon.classList.replace("fa-eye", "fa-eye-slash");
            } else {
                passwordField.type = "password";
                eyeIcon.classList.replace("fa-eye-slash", "fa-eye");
            }
        });

        // Theme Switcher Logic
        const themeToggleBtn = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');

        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
            themeIcon.classList.replace('fa-sun', 'fa-moon');
        } else {
            document.documentElement.classList.remove('dark');
            themeIcon.classList.replace('fa-moon', 'fa-sun');
        }

        themeToggleBtn.addEventListener('click', () => {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
                themeIcon.classList.replace('fa-moon', 'fa-sun');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
                themeIcon.classList.replace('fa-sun', 'fa-moon');
            }
        });
    </script>
</body>
</html>
