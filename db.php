<?php


// اطلاعات اتصال به دیتابیس
$servername = "localhost";  
$username = "root";  
$password = "";  
$database = "vento_blog";

// ایجاد اتصال به دیتابیس
$conn = new mysqli($servername, $username, $password, $database);

// بررسی اتصال به دیتابیس
if ($conn->connect_error) {
    die("❌ خطا در اتصال به دیتابیس: " . $conn->connect_error);  // در صورت خطا در اتصال، پیامی نمایش داده می‌شود
}

// تنظیم charset برای جلوگیری از مشکلات نمایش کاراکترها
$conn->set_charset("utf8mb4");  // استفاده از utf8mb4 برای پشتیبانی از کاراکترهای یونیکد کامل
?>
