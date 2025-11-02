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
    $confirm_pass = $_POST['confirm_password'];
    $display_name = $_POST['display_name'];
    $email = $_POST['email'];

    // بررسی اینکه فیلدها خالی نباشند
    if (empty($user) || empty($pass) || empty($confirm_pass) || empty($display_name) || empty($email)) {
        $message = "لطفاً تمامی فیلدها را پر کنید.";
        $message_class = "error"; // رنگ قرمز برای خطا
    } 
    // بررسی تطابق پسورد و تایید پسورد
    else if ($pass !== $confirm_pass) {
        $message = "پسوردها با هم تطابق ندارند.";
        $message_class = "error"; // رنگ قرمز برای خطا
    }
    // بررسی اینکه آیا نام کاربری تکراری است
    else {
        $sql = "SELECT id FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $message = "نام کاربری قبلاً ثبت شده است.";
            $message_class = "error"; // رنگ قرمز برای خطا
        } else {
            // درج اطلاعات در دیتابیس به صورت معمولی (Plain Text)
            $sql = "INSERT INTO users (username, password, display_name, email) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $user, $pass, $display_name, $email);
            
            if ($stmt->execute()) {
                $message = "ثبت‌نام با موفقیت انجام شد.";
                $message_class = "success"; // رنگ سبز برای موفقیت
            } else {
                $message = "خطا در ثبت‌نام. لطفاً دوباره تلاش کنید.";
                $message_class = "error"; // رنگ قرمز برای خطا
            }
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <title>ثبت‌نام | Vento Blog</title>
    <link rel="stylesheet" href="style.css">  <!-- لینک به فایل CSS -->
</head>
<body>

    <?php include "includes/header.php"; ?>  <!-- هدر سایت -->

    <main class="content">
        <div class="signup-container">
            <h2>ثبت‌نام در وبسایت</h2>
            <form method="post" action="">
                <div class="input-group">
                    <label for="display_name">نام و نام خانوادگی:</label>
                    <input type="text" id="display_name" name="display_name" required>
                </div>
                <div class="input-group">
                    <label for="username">نام کاربری:</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="input-group">
                    <label for="password">رمز عبور:</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="input-group">
                    <label for="confirm_password"> تکرار رمز عبور:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <div class="input-group">
                    <label for="email">ایمیل:</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <input type="submit" value="ثبت‌نام" class="submit-btn">
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
