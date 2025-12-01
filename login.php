<?php
session_start();

// اطلاعات اتصال به دیتابیس
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "vento_blog";

// متغیرهای برای پیام‌ها
$message       = '';
$message_class = '';

// برای نگه‌داشتن مقدار فیلدها بعد از ارسال فرم
$user = '';
$pass = '';

// اگر از signup.php با ?registered=1 آمده‌ایم، پیام موفقیت ثبت‌نام را نمایش بده
if (isset($_GET['registered']) && $_GET['registered'] == '1') {
    $message       = "ثبت‌نام شما با موفقیت انجام شد. حالا می‌توانید وارد شوید.";
    $message_class = "success";
}

// ایجاد اتصال
$conn = new mysqli($servername, $username, $password, $dbname);

// بررسی اتصال
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // دریافت داده‌ها از فرم
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';

    // بررسی اینکه فیلدها خالی نباشند
    if ($user === '' || $pass === '') {
        $message       = "لطفاً نام کاربری و رمز عبور را وارد کنید.";
        $message_class = "error";
    } else {
        // بررسی نام کاربری و رمز عبور
        $sql  = "SELECT id, username, password, display_name, role FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $message       = "خطا در آماده‌سازی کوئری.";
            $message_class = "error";
        } else {
            $stmt->bind_param("s", $user);
            $stmt->execute();
            $result = $stmt->get_result();

            // اگر کاربر پیدا شد
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();

                // بررسی رمز عبور به صورت معمولی (مثل ساختار فعلی‌ات)
                if ($pass == $row['password']) {
                    // ورود موفقیت‌آمیز
                    $_SESSION['username']     = $row['username'];
                    $_SESSION['display_name'] = $row['display_name'];
                    $_SESSION['role']         = $row['role']; // نقش کاربر
                    $_SESSION['user_id']      = $row['id'];   // شناسه کاربر

                    // هدایت کاربر به صفحه مناسب بر اساس نقش
                    if ($_SESSION['role'] == 'admin') {
                        header("Location: admin_dashboard.php");
                    } elseif ($_SESSION['role'] == 'author') {
                        header("Location: author_dashboard.php");
                    } else {
                        header("Location: index.php");
                    }
                    exit();
                } else {
                    $message       = "رمز عبور اشتباه است.";
                    $message_class = "error";
                }
            } else {
                $message       = "کاربری با این نام کاربری یافت نشد.";
                $message_class = "error";
            }

            $stmt->close();
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ورود | Vento Blog</title>
    <link rel="stylesheet" href="style.css">

    <style>
        * {
            box-sizing: border-box;
        }

        /* استایل مخصوص صفحه ورود، مشابه ثبت‌نام */
        body.login-page {
            margin: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI",
            "IRANSans", "Vazirmatn", sans-serif;
            background: radial-gradient(circle at top, #243b55, #141e30);
            min-height: 100vh;
            color: #f9fafb;
            overflow-x: hidden;
        }

        .login-content {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            min-height: calc(100vh - 140px);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 24px 12px;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: rgba(15, 23, 42, 0.96);
            border-radius: 20px;
            padding: 26px 22px 22px;
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.6);
            border: 1px solid rgba(148, 163, 184, 0.4);
            backdrop-filter: blur(12px);
            animation: fadeInUp 0.45s ease-out;
        }

        .login-header-badge {
            width: 56px;
            height: 56px;
            border-radius: 18px;
            background: linear-gradient(135deg, #6366f1, #ec4899);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #f9fafb;
            font-weight: 700;
            font-size: 17px;
            margin: 0 auto 10px;
            box-shadow: 0 12px 30px rgba(99, 102, 241, 0.55);
        }

        .login-title {
            text-align: center;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .login-subtitle {
            text-align: center;
            font-size: 13px;
            color: #9ca3af;
            margin-bottom: 18px;
        }

        .login-subtitle span {
            color: #e5e7eb;
            font-weight: 500;
        }

        .login-form {
            margin-top: 4px;
        }

        .input-group {
            margin-bottom: 14px;
        }

        .input-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            color: #e5e7eb;
        }

        .input-group input {
            width: 100%;
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid rgba(148, 163, 184, 0.7);
            background: rgba(15, 23, 42, 0.9);
            color: #f9fafb;
            font-size: 13px;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .input-group input::placeholder {
            color: #6b7280;
        }

        .input-group input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 1px rgba(99, 102, 241, 0.55);
            background: rgba(15, 23, 42, 1);
        }

        .submit-btn {
            width: 100%;
            margin-top: 4px;
            padding: 11px 0;
            border-radius: 14px;
            border: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #ffffff;
            box-shadow: 0 14px 32px rgba(79, 70, 229, 0.7);
            transition: transform 0.12s ease, box-shadow 0.12s ease, opacity 0.12s ease;
        }

        .submit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 19px 40px rgba(79, 70, 229, 0.85);
            opacity: 0.98;
        }

        .submit-btn:active {
            transform: translateY(1px);
            box-shadow: 0 8px 22px rgba(79, 70, 229, 0.75);
        }

        .message {
            margin-top: 14px;
            padding: 10px 12px;
            border-radius: 12px;
            font-size: 13px;
            line-height: 1.6;
        }

        .message.error {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(248, 113, 113, 0.85);
            color: #fecaca;
        }

        .message.success {
            background: rgba(22, 163, 74, 0.16);
            border: 1px solid rgba(74, 222, 128, 0.9);
            color: #bbf7d0;
        }

        .login-footnote {
            margin-top: 10px;
            font-size: 11px;
            color: #9ca3af;
            text-align: center;
        }

        .login-footnote a {
            color: #a5b4fc;
            text-decoration: none;
            font-weight: 500;
        }

        .login-footnote a:hover {
            text-decoration: underline;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 600px) {
            .login-content {
                padding: 16px 10px;
            }

            .login-card {
                padding: 20px 16px 18px;
                border-radius: 16px;
            }

            .login-title {
                font-size: 18px;
            }

            .login-header-badge {
                width: 50px;
                height: 50px;
                font-size: 15px;
                border-radius: 16px;
            }
        }
    </style>
</head>
<body class="login-page">

<?php include "includes/header.php"; ?>

<main class="content login-content">
    <div class="login-card">
        <div class="login-header-badge">VB</div>
        <h2 class="login-title">ورود به حساب کاربری</h2>
        <p class="login-subtitle">
            وارد <span>حساب Vento Blog</span> خود شوید.
        </p>

        <?php if ($message): ?>
            <div class="message <?php echo htmlspecialchars($message_class); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="" class="login-form">
            <div class="input-group">
                <label for="username">نام کاربری:</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    value="<?php echo htmlspecialchars($user); ?>"
                    placeholder="نام کاربری"
                    required
                >
            </div>

            <div class="input-group">
                <label for="password">رمز عبور:</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="رمز عبور"
                    required
                >
            </div>

            <input type="submit" value="ورود" class="submit-btn">
        </form>

        <div class="login-footnote">
            هنوز ثبت‌نام نکرده‌اید؟
            <a href="signup.php">ایجاد حساب کاربری</a>
        </div>
    </div>
</main>

<?php include "includes/footer.php"; ?>

</body>
</html>
