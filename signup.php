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

// برای نگه داشتن مقدار فیلدها بعد از ارسال فرم
$user          = '';
$pass          = '';
$confirm_pass  = '';
$display_name  = '';
$email         = '';
$role          = '';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("خطا در اتصال به دیتابیس: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // دریافت داده‌ها از فرم
    $display_name  = trim($_POST['display_name'] ?? '');
    $user          = trim($_POST['username'] ?? '');
    $pass          = $_POST['password'] ?? '';
    $confirm_pass  = $_POST['confirm_password'] ?? '';
    $email         = trim($_POST['email'] ?? '');
    $role          = $_POST['role'] ?? '';

    // اعتبارسنجی فیلدها
    if (
        $display_name === '' || $user === '' || $pass === '' || $confirm_pass === '' ||
        $email === '' || $role === ''
    ) {
        $message       = "لطفاً تمامی فیلدها را پر کنید.";
        $message_class = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message       = "ایمیل وارد شده معتبر نیست.";
        $message_class = "error";
    } elseif ($pass !== $confirm_pass) {
        $message       = "پسوردها با هم تطابق ندارند.";
        $message_class = "error";
    } else {
        // بررسی تکراری نبودن نام کاربری
        $sql  = "SELECT id FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $message       = "خطا در آماده‌سازی کوئری.";
            $message_class = "error";
        } else {
            $stmt->bind_param("s", $user);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $message       = "نام کاربری قبلاً ثبت شده است.";
                $message_class = "error";
            } else {
                // درج اطلاعات در دیتابیس - ساختار جدول:
                // users(username, password, display_name, email, role)
                $sql_insert = "INSERT INTO users (username, password, display_name, email, role)
                               VALUES (?, ?, ?, ?, ?)";
                $stmt_insert = $conn->prepare($sql_insert);

                if (!$stmt_insert) {
                    $message       = "خطا در آماده‌سازی کوئری درج اطلاعات.";
                    $message_class = "error";
                } else {
                    // در حال حاضر پسورد به صورت ساده ذخیره می‌شود (مثل کد قبلی‌ات)
                    $stmt_insert->bind_param("sssss", $user, $pass, $display_name, $email, $role);

                    if ($stmt_insert->execute()) {
                        // ثبت‌نام موفق -> هدایت به صفحه ورود
                        $stmt_insert->close();
                        $stmt->close();
                        $conn->close();
                        header("Location: login.php?registered=1");
                        exit();
                    } else {
                        $message       = "خطا در ثبت‌نام. لطفاً دوباره تلاش کنید.";
                        $message_class = "error";
                    }

                    $stmt_insert->close();
                }
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
    <title>ثبت‌نام | Vento Blog</title>
    <link rel="stylesheet" href="style.css">

    <style>
        * {
            box-sizing: border-box;
        }

        body.signup-page {
            margin: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI",
            "IRANSans", "Vazirmatn", sans-serif;
            background: radial-gradient(circle at top, #243b55, #141e30);
            min-height: 100vh;
            color: #f9fafb;
            overflow-x: hidden;
        }

        .signup-content {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 24px 12px;
        }

        .signup-card {
            width: 100%;
            max-width: 480px;
            background: rgba(15, 23, 42, 0.96);
            border-radius: 20px;
            padding: 26px 22px 22px;
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.6);
            border: 1px solid rgba(148, 163, 184, 0.4);
            backdrop-filter: blur(12px);
            animation: fadeInUp 0.45s ease-out;
        }

        .signup-header-badge {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            background: linear-gradient(135deg, #6366f1, #ec4899);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #f9fafb;
            font-weight: 700;
            font-size: 18px;
            margin: 0 auto 10px;
            box-shadow: 0 12px 30px rgba(99, 102, 241, 0.55);
        }

        .signup-title {
            text-align: center;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .signup-subtitle {
            text-align: center;
            font-size: 13px;
            color: #9ca3af;
            margin-bottom: 18px;
        }

        .signup-subtitle span {
            color: #e5e7eb;
            font-weight: 500;
        }

        .signup-form {
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

        .input-group input,
        .input-group select {
            width: 100%;
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid rgba(148, 163, 184, 0.7);
            background: rgba(15, 23, 42, 0.9);
            color: #f9fafb;
            font-size: 13px;
            outline: none;
            transition: border-color 0.2s.ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .input-group input::placeholder {
            color: #6b7280;
        }

        .input-group input:focus,
        .input-group select:focus {
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

        .signup-footnote {
            margin-top: 10px;
            font-size: 11px;
            color: #9ca3af;
            text-align: center;
        }

        .signup-footnote a {
            color: #a5b4fc;
            text-decoration: none;
            font-weight: 500;
        }

        .signup-footnote a:hover {
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
            .signup-content {
                padding: 16px 10px;
            }

            .signup-card {
                padding: 20px 16px 18px;
                border-radius: 16px;
            }

            .signup-title {
                font-size: 18px;
            }

            .signup-header-badge {
                width: 52px;
                height: 52px;
                font-size: 16px;
                border-radius: 16px;
            }
        }
    </style>
</head>
<body class="signup-page">

<?php include "includes/header.php"; ?>

<main class="content signup-content">
    <div class="signup-card">
        <div class="signup-header-badge">VB</div>
        <h2 class="signup-title">ثبت‌نام در Vento Blog</h2>
        <p class="signup-subtitle">
            حساب کاربری بسازید و به <span>نویسندگان و خوانندگان ونتو</span> بپیوندید.
        </p>

        <?php if ($message): ?>
            <div class="message <?php echo htmlspecialchars($message_class); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="" class="signup-form">
            <div class="input-group">
                <label for="display_name">نام و نام خانوادگی:</label>
                <input
                    type="text"
                    id="display_name"
                    name="display_name"
                    value="<?php echo htmlspecialchars($display_name); ?>"
                    placeholder="مثلاً: عماد عالم"
                    required
                >
            </div>

            <div class="input-group">
                <label for="username">نام کاربری:</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    value="<?php echo htmlspecialchars($user); ?>"
                    placeholder="نام کاربری یکتا برای ورود"
                    required
                >
            </div>

            <div class="input-group">
                <label for="password">رمز عبور:</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="حداقل ۶ کاراکتر"
                    required
                >
            </div>

            <div class="input-group">
                <label for="confirm_password">تکرار رمز عبور:</label>
                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    placeholder="تأیید رمز عبور"
                    required
                >
            </div>

            <div class="input-group">
                <label for="email">ایمیل:</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?php echo htmlspecialchars($email); ?>"
                    placeholder="example@email.com"
                    required
                >
            </div>

            <div class="input-group">
                <label for="role">نقش:</label>
                <select id="role" name="role" required>
                    <option value="" disabled <?php echo $role === '' ? 'selected' : ''; ?>>انتخاب نقش</option>
                    <option value="author" <?php echo $role === 'author' ? 'selected' : ''; ?>>author</option>
                    <option value="reader" <?php echo $role === 'reader' ? 'selected' : ''; ?>>reader</option>
                </select>
            </div>

            <input type="submit" value="ثبت‌نام" class="submit-btn">
        </form>

        <div class="signup-footnote">
            قبلاً ثبت‌نام کرده‌اید؟
            <a href="login.php">ورود به حساب کاربری</a>
        </div>
    </div>
</main>

<?php include "includes/footer.php"; ?>

</body>
</html>
