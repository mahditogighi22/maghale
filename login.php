<?php
// اطلاعات اتصال به دیتابیس
$servername = "localhost";  
$username = "root";  
$password = "";  
$dbname = "vento_blog";  

// متغیرهای برای پیام‌ها
$message = '';  // برای نگهداری پیام خطا یا موفقیت
$message_class = '';  // برای تعیین کلاس رنگ پیام

// ایجاد اتصال
$conn = new mysqli($servername, $username, $password, $dbname);

// بررسی اتصال
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // دریافت داده‌ها از فرم
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // بررسی نام کاربری و رمز عبور
    $sql = "SELECT id, username, password, display_name FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    // اگر کاربر پیدا شد
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // بررسی رمز عبور به صورت معمولی
        if ($pass == $row['password']) {  
            // ورود موفقیت‌آمیز
            $message = "خوش آمدید " . $row['display_name'];
            $message_class = "success"; // رنگ سبز برای موفقیت
        } else {
            $message = "رمز عبور اشتباه است.";
            $message_class = "error"; // رنگ قرمز برای خطا
        }
    } else {
        $message = "کاربری با این نام وجود ندارد.";
        $message_class = "error"; // رنگ قرمز برای خطا
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <title>ورود | Vento Blog</title>
    <link rel="stylesheet" href="style.css">  <!-- لینک به فایل CSS -->
</head>
<body>

    <?php include "includes/header.php"; ?>  <!-- هدر سایت -->

    <main class="content">
        <div class="login-container">
            <h2>ورود به حساب کاربری</h2>
            <form method="post" action="">
                <div class="input-group">
                    <label for="username">نام کاربری:</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="input-group">
                    <label for="password">رمز عبور:</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <input type="submit" value="ورود" class="submit-btn">
            </form>

            <!-- نمایش پیام خطا یا موفقیت -->
            <?php if ($message): ?>
                <div class="message <?php echo $message_class; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include "includes/footer.php"; ?>  <!-- فوتر سایت -->

</body>
</html>
