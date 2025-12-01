<?php
// submit_article.php

// شروع سشن فقط اگر قبلاً شروع نشده
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// فقط admin و author اجازه دسترسی دارند
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'author')) {
    header("Location: index.php");
    exit();
}

// اتصال به دیتابیس
include('db.php');  // باید $conn را بسازد

// اطلاعات نویسنده
$user_id      = $_SESSION['user_id']      ?? null;
$display_name = $_SESSION['display_name'] ?? ($_SESSION['username'] ?? 'کاربر');

// متغیرهای فرم
$title         = '';
$content       = '';
$category      = '';
$message       = '';
$message_class = '';

// دسته‌بندی‌ها
$categories = [
    'technology' => 'تکنولوژی',
    'health'     => 'سلامت',
    'economy'    => 'اقتصاد',
    'art'        => 'هنر',
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title    = trim($_POST['title']    ?? '');
    $content  = trim($_POST['content']  ?? '');
    $category = $_POST['category']      ?? '';
    $action   = $_POST['action']        ?? 'submit';   // draft یا submit

    if ($title === '' || $content === '' || $category === '') {
        $message       = "لطفاً تمامی فیلدها را پر کنید.";
        $message_class = "error";
    } elseif (!array_key_exists($category, $categories)) {
        $message       = "دسته‌بندی انتخاب‌شده معتبر نیست.";
        $message_class = "error";
    } elseif (!$user_id) {
        $message       = "شناسه نویسنده یافت نشد. لطفاً دوباره وارد شوید.";
        $message_class = "error";
    } else {
        // تعیین وضعیت بر اساس دکمه
        if ($action === 'draft') {
            $status = 'draft';
        } else {
            $status = 'pending';
        }

        // تولید slug یکتا
        $slug = uniqid('art-');

        // درج مقاله جدید
        // جدول باید ستون‌های: title, slug, content, category, author_id, status داشته باشد
        $sql = "INSERT INTO articles (title, slug, content, category, author_id, status)
                VALUES (?, ?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            // s = string, i = int
            $stmt->bind_param("ssssis", $title, $slug, $content, $category, $user_id, $status);

            if ($stmt->execute()) {
                if ($status === 'draft') {
                    $message       = "پیش‌نویس مقاله با موفقیت ذخیره شد. می‌توانید بعداً آن را ویرایش کرده و برای تأیید ارسال کنید.";
                    $message_class = "success";
                } else {
                    $message       = "مقاله با موفقیت ارسال شد و در وضعیت «در انتظار تأیید» قرار گرفت. یک نوتیفیکیشن برای مدیر (به‌صورت نمایشی) ثبت شد.";
                    $message_class = "success";
                }

                // خالی کردن فرم بعد از ذخیره
                $title    = '';
                $content  = '';
                $category = '';
            } else {
                $message       = "خطا در ارسال مقاله. لطفاً دوباره تلاش کنید.";
                $message_class = "error";
            }

            $stmt->close();
        } else {
            $message       = "خطا در آماده‌سازی کوئری ارسال مقاله.";
            $message_class = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ارسال مقاله | Vento Blog</title>
    <link rel="stylesheet" href="style.css">

    <!-- CKEditor -->
    <script src="https://cdn.ckeditor.com/4.21.0/full-all/ckeditor.js"></script>

    <style>
        * { box-sizing: border-box; }

        body.submit-article-page {
            margin: 0;
            font-family: "Vazirmatn", "IRANSans", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #020617;
            color: #e5e7eb;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .submit-article-content {
            padding-top: 20px;
            padding-bottom: 32px;
        }

        .submit-article-card {
            max-width: 820px;
            margin: 0 auto;
            background: #020617;
            border-radius: 18px;
            padding: 20px 18px 16px;
            border: 1px solid rgba(31, 41, 55, 0.9);
            box-shadow: 0 14px 32px rgba(15, 23, 42, 0.9);
        }

        .submit-article-header {
            margin: -4px -4px 16px;
            padding: 14px 14px 12px;
            border-radius: 14px;
            background: rgba(15, 23, 42, 0.96);
            border: 1px solid rgba(55, 65, 81, 0.95);
        }

        .submit-article-title {
            font-size: 19px;
            font-weight: 800;
            margin: 0 0 6px 0;
            color: #f9fafb;
        }

        .submit-article-subtitle {
            font-size: 13px;
            line-height: 2;
            color: #e5e7eb;
        }

        .submit-article-subtitle span {
            color: #f9fafb;
            font-weight: 600;
        }

        .submit-article-form { margin-top: 10px; }

        .form-row {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(0, 1.2fr);
            gap: 12px;
        }

        .form-group {
            margin-bottom: 12px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group label {
            font-size: 13px;
            color: #e5e7eb;
        }

        .form-group input[type="text"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 9px 11px;
            border-radius: 12px;
            border: 1px solid rgba(148, 163, 184, 0.7);
            background: rgba(15, 23, 42, 0.94);
            color: #f9fafb;
            font-size: 13px;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s.ease, background 0.2s ease;
        }

        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: #6b7280;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 1px rgba(99, 102, 241, 0.5);
            background: rgba(15, 23, 42, 1);
        }

        .form-group textarea {
            min-height: 230px;
            resize: vertical;
            line-height: 1.8;
        }

        .submit-actions {
            margin-top: 6px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .submit-btn-primary,
        .submit-btn-secondary {
            padding: 9px 18px;
            border-radius: 999px;
            border: none;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            color: #ffffff;
            box-shadow: 0 14px 32px rgba(79, 70, 229, 0.5);
            transition: transform 0.12s ease, box-shadow 0.12s ease, opacity 0.12s ease;
        }

        .submit-btn-primary {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
        }

        .submit-btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 20px 42px rgba(79, 70, 229, 0.9);
            opacity: 0.98;
        }

        .submit-btn-primary:active {
            transform: translateY(1px);
            box-shadow: 0 10px 24px rgba(79, 70, 229, 0.85);
        }

        .submit-btn-secondary {
            background: linear-gradient(135deg, #4b5563, #6b7280);
        }

        .submit-btn-secondary:hover {
            transform: translateY(-1px);
            box-shadow: 0 18px 38px rgba(55, 65, 81, 0.9);
            opacity: 0.98;
        }

        .submit-btn-secondary:active {
            transform: translateY(1px);
            box-shadow: 0 10px 24px rgba(55, 65, 81, 0.85);
        }

        .submit-btn-ghost {
            padding: 9px 16px;
            border-radius: 999px;
            border: 1px solid rgba(148, 163, 184, 0.8);
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            background: rgba(15, 23, 42, 0.96);
            color: #e5e7eb;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background 0.15s ease, border-color 0.15s.ease;
        }

        .submit-btn-ghost:hover {
            background: rgba(31, 41, 55, 0.98);
            border-color: rgba(148, 163, 184, 1);
        }

        .message {
            margin-top: 12px;
            padding: 10px 12px;
            border-radius: 12px;
            font-size: 13px;
            line-height: 1.6;
        }

        .message.error {
            background: rgba(239, 68, 68, 0.14);
            border: 1px solid rgba(248, 113, 113, 0.92);
            color: #fecaca;
        }

        .message.success {
            background: rgba(22, 163, 74, 0.18);
            border: 1px solid rgba(74, 222, 128, 0.95);
            color: #bbf7d0;
        }

        .submit-note {
            margin-top: 8px;
            font-size: 11px;
            color: #9ca3af;
        }

        @media (max-width: 900px) {
            .form-row {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        @media (max-width: 600px) {
            .submit-article-card {
                padding: 18px 14px 16px;
                border-radius: 16px;
            }

            .submit-article-title {
                font-size: 18px;
            }
        }
    </style>
</head>
<body class="submit-article-page">

<?php include "includes/header.php"; ?>

<main class="content submit-article-content">
    <section class="submit-article-card">
        <header class="submit-article-header">
            <h1 class="submit-article-title">ارسال مقاله جدید</h1>
            <p class="submit-article-subtitle">
                <span><?php echo htmlspecialchars($display_name); ?></span>، در این صفحه می‌توانید مقاله‌ی جدید خود را
                در یکی از دسته‌های تکنولوژی، سلامت، اقتصاد یا هنر ثبت کنید. می‌توانید آن را به‌صورت «پیش‌نویس» ذخیره کرده
                و بعداً برای تأیید ارسال کنید.
            </p>
        </header>

        <?php if ($message): ?>
            <div class="message <?php echo htmlspecialchars($message_class); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="" class="submit-article-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="title">عنوان مقاله:</label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="<?php echo htmlspecialchars($title); ?>"
                        placeholder="مثلاً: تأثیر هوش مصنوعی بر آینده‌ی تکنولوژی"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="category">دسته‌بندی:</label>
                    <select id="category" name="category" required>
                        <option value="" disabled <?php echo $category === '' ? 'selected' : ''; ?>>
                            انتخاب دسته‌بندی
                        </option>
                        <option value="technology" <?php echo $category === 'technology' ? 'selected' : ''; ?>>
                            تکنولوژی
                        </option>
                        <option value="health" <?php echo $category === 'health' ? 'selected' : ''; ?>>
                            سلامت
                        </option>
                        <option value="economy" <?php echo $category === 'economy' ? 'selected' : ''; ?>>
                            اقتصاد
                        </option>
                        <option value="art" <?php echo $category === 'art' ? 'selected' : ''; ?>>
                            هنر
                        </option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="content">محتوای مقاله:</label>
                <textarea
                    id="content"
                    name="content"
                    placeholder="متن کامل مقاله را اینجا بنویسید..."
                    required
                ><?php echo htmlspecialchars($content); ?></textarea>
            </div>

            <div class="submit-actions">
                <!-- دکمهٔ ذخیره پیش‌نویس -->
                <button type="submit" name="action" value="draft" class="submit-btn-secondary">
                    ذخیره به‌عنوان پیش‌نویس
                </button>

                <!-- دکمهٔ ارسال برای تأیید -->
                <button type="submit" name="action" value="submit" class="submit-btn-primary">
                    ارسال برای تأیید
                </button>

                <?php if ($_SESSION['role'] === 'author'): ?>
                    <a href="author_dashboard.php" class="submit-btn-ghost">بازگشت به داشبورد نویسنده</a>
                <?php elseif ($_SESSION['role'] === 'admin'): ?>
                    <a href="admin_dashboard.php" class="submit-btn-ghost">بازگشت به داشبورد مدیر</a>
                <?php endif; ?>
            </div>

            <p class="submit-note">
                نکته: پیش‌نویس‌ها در داشبورد نویسنده قابل مشاهده و ویرایش هستند. برای نمایش مقاله در سایت، باید آن را برای تأیید ارسال کنید و مدیر آن را تأیید کند.
            </p>
        </form>
    </section>
</main>

<?php include "includes/footer.php"; ?>

<script>
    // تنظیم CKEditor برای فیلد content با پشتیبانی فارسی و متن تیره روی پس‌زمینه روشن
    CKEDITOR.replace('content', {
        language: 'fa',
        contentsLangDirection: 'rtl',
        contentsCss: [
            'body { font-family: Vazirmatn, IRANSans, system-ui, sans-serif; direction: rtl; text-align: right; color: #111827; }'
        ],
        uiColor: '#f3f4f6',
        height: 300,
        removeButtons: 'Save,NewPage,Preview,Print,Templates',
    });
</script>

</body>
</html>
