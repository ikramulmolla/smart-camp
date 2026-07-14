<?php
session_start();

// Determine user type based on cookies
if (isset($_COOKIE['auto_login'])) {
    $redirect = '../student';
} elseif (isset($_COOKIE['auto_login_teacher'])) {
    $redirect = '../';
} else {
    // Default fallback if no cookies found
    $redirect = '../';
}

// Clear session data
$_SESSION = [];
session_unset();
session_destroy();

// Delete PHP session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Delete custom cookies
$customCookies = ['auto_login', 'auto_login_teacher', 'roll', 'name', 'remember_me'];
foreach ($customCookies as $cookie) {
    if (isset($_COOKIE[$cookie])) {
        setcookie($cookie, '', time() - 3600, '/');
    }
}

// Redirect user based on type
header("Location: $redirect");
exit();
?>

