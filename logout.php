 <?php
// شروع سشن
session_start();

// پاک کردن تمامی داده‌های سشن
session_unset();
session_destroy();

// هدایت به صفحه ورود
header("Location: login.php");
exit();
?>
