<?php
// edit_article.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'author')) {
    header("Location: index.php");
    exit();
}

include('db.php');

$user_id      = $_SESSION['user_id']      ?? null;
$user_role    = $_SESSION['role']         ?? null;
$display_name = $_SESSION['display_name'] ?? ($_SESSION['username'] ?? 'کاربر');

$article_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($article_id <= 0) {
    die("شناسه مقاله نامعتبر است.");
}

// خواندن مقاله
$sql = "SELECT id, title, content, category, status, author_id FROM articles WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $article_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    die("مقاله مورد نظر یافت نشد.");
}

$article = $result->fetch_assoc();
$stmt->close();

// محدودیت دسترسی: نویسنده فقط مقاله‌های خودش را می‌تواند ویرایش کند
if ($user_role === 'author' && $article['author_id'] != $user_id) {
    die("شما اجازه ویرایش این مقاله را ندارید.");
}

// مقداردهی اولیه فرم
$title    = $article['title']    ?? '';
$content  = $article['content']  ?? '';
$category = $article['category'] ?? '';
$status   = $article['status']   ?? 'draft';

$message       = '';
$message_class = '';

$categories = [
    'technology' => 'تکنولوژی',
    'health'     => 'سلامت',
    'economy'    => 'اقتصاد',
    'art'        => 'هنر',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = trim($_POST['title']    ?? '');
    $content  = trim($_POST['content']  ?? '');
    $category = $_POST['category']      ?? '';
    $action   = $_POST['action']        ?? 'submit';

    if ($title === '' || $content === '' || $category === '') {
        $message       = "لطفاً تمامی فیلدها را پر کنید.";
        $message_class = "error";
    } elseif (!array_key_exists($category, $categories)) {
        $message       = "دسته‌بندی انتخاب‌شده معتبر نیست.";
        $message_class = "error";
    } else {
        if ($action === 'draft') {
            $new_status  = 'draft';
            $success_msg = "پیش‌نویس مقاله با موفقیت به‌روزرسانی شد.";
        } else {
            $new_status  = 'pending';
            $success_msg = "مقاله به‌روزرسانی شد و در وضعیت «در انتظار تأیید» قرار گرفت.";
        }

        $sql = "UPDATE articles 
                SET title = ?, content = ?, category = ?, status = ?
                WHERE id = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssi", $title, $content, $category, $new_status, $article_id);

            if ($stmt->execute()) {
                // اگر از اینجا به pending رفت، می‌توانی ایمیل مدیر را هم بفرستی
                $status        = $new_status;
                $message       = $success_msg;
                $message_class = "success";
            } else {
                $message       = "خطا در به‌روزرسانی مقاله. لطفاً دوباره تلاش کنید.";
                $message_class = "error";
            }

            $stmt->close();
        } else {
            $message       = "خطا در آماده‌سازی کوئری ویرایش مقاله.";
            $message_class = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ویرایش مقاله | Vento Blog</title>
    <link rel="stylesheet" href="style.css">

    <style>
        * { box-sizing: border-box; }

        body.edit-article-page {
            margin: 0;
            font-family: "Vazirmatn", "IRANSans", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #020617;
            color: #e5e7eb;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .edit-article-content {
            padding-top: 20px;
            padding-bottom: 32px;
        }

        .edit-article-card {
            max-width: 820px;
            margin: 0 auto;
            background: #020617;
            border-radius: 18px;
            padding: 20px 18px 16px;
            border: 1px solid rgba(31, 41, 55, 0.9);
            box-shadow: 0 14px 32px rgba(15, 23, 42, 0.9);
        }

        .edit-article-header {
            margin: -4px -4px 16px;
            padding: 14px 14px 12px;
            border-radius: 14px;
            background: rgba(15, 23, 42, 0.96);
            border: 1px solid rgba(55, 65, 81, 0.95);
        }

        .edit-article-title {
            font-size: 19px;
            font-weight: 800;
            margin: 0 0 6px 0;
            color: #f9fafb;
        }

        .edit-article-subtitle {
            font-size: 13px;
            line-height: 2;
            color: #e5e7eb;
        }

        .edit-article-subtitle span {
            color: #f9fafb;
            font-weight: 600;
        }

        .edit-article-form { margin-top: 10px; }

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

        .edit-actions {
            margin-top: 6px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .btn-primary {
            padding: 9px 18px;
            border-radius: 999px;
            border: none;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #ffffff;
            box-shadow: 0 14px 32px rgba(79, 70, 229, 0.8);
            transition: transform 0.12s ease, box-shadow 0.12s.ease, opacity 0.12s ease;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 20px 42px rgba(79, 70, 229, 0.95);
            opacity: 0.98;
        }

        .btn-primary:active {
            transform: translateY(1px);
            box-shadow: 0 10px 24px rgba(79, 70, 229, 0.85);
        }

        .btn-ghost {
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
            transition: background 0.15s ease, border-color 0.15s ease;
        }

        .btn-ghost:hover {
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

        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 11px;
            border: 1px solid rgba(148, 163, 184, 0.8);
            background: rgba(15, 23, 42, 0.9);
            color: #e5e7eb;
            margin-top: 4px;
        }

        /* CKEditor مثل submit_article */
        .ck.ck-editor {
            margin-top: 4px;
        }

        .ck.ck-editor__main > .ck-editor__editable {
            min-height: 260px;
            max-height: 600px;
            direction: rtl;
            text-align: right;
            background: #ffffff;
            color: #111827;
            border-radius: 12px !important;
            border: 1px solid rgba(148, 163, 184, 0.7);
            padding: 10px 12px;
            font-size: 13px;
            line-height: 1.9;
        }

        .ck.ck-editor__editable[role="textbox"]::placeholder {
            color: #6b7280;
        }

        .ck.ck-toolbar {
            border-radius: 12px 12px 0 0 !important;
            background: #f3f4f6;
            border-color: rgba(148, 163, 184, 0.9);
        }

        .ck.ck-toolbar .ck-button {
            font-size: 11px;
        }

        .ck.ck-toolbar .ck-button.ck-on {
            background: rgba(99, 102, 241, 0.25);
        }

        .ck.ck-dropdown__panel {
            z-index: 9999 !important;
        }

        @media (max-width: 900px) {
            .form-row {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        @media (max-width: 600px) {
            .edit-article-card {
                padding: 18px 14px 16px;
                border-radius: 16px;
            }

            .edit-article-title {
                font-size: 18px;
            }
        }
    </style>

    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
</head>
<body class="edit-article-page">

<?php include "includes/header.php"; ?>

<main class="content edit-article-content">
    <section class="edit-article-card">
        <header class="edit-article-header">
            <h1 class="edit-article-title">ویرایش مقاله</h1>
            <p class="edit-article-subtitle">
                <span><?php echo htmlspecialchars($display_name); ?></span>، در این صفحه می‌توانید مقاله خود را
                ویرایش کنید. می‌توانید آن را به‌صورت پیش‌نویس ذخیره کنید یا برای تأیید مدیر ارسال نمایید.
            </p>
            <div class="status-badge">
                وضعیت فعلی: 
                <?php
                if ($status === 'draft')       echo 'پیش‌نویس';
                elseif ($status === 'pending') echo 'در انتظار تأیید';
                elseif ($status === 'published') echo 'منتشر شده';
                elseif ($status === 'rejected')  echo 'رد شده';
                else                           echo htmlspecialchars($status);
                ?>
            </div>
        </header>

        <?php if ($message): ?>
            <div class="message <?php echo htmlspecialchars($message_class); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="" class="edit-article-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="title">عنوان مقاله:</label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="<?php echo htmlspecialchars($title); ?>"
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
                    required
                ><?php echo $content; ?></textarea>
            </div>

            <div class="edit-actions">
                <button type="submit" name="action" value="draft" class="btn-ghost">
                    ذخیره به عنوان پیش‌نویس
                </button>

                <button type="submit" name="action" value="submit" class="btn-primary">
                    ارسال برای تأیید
                </button>

                <?php if ($user_role === 'author'): ?>
                    <a href="author_dashboard.php" class="btn-ghost">بازگشت به داشبورد نویسنده</a>
                <?php else: ?>
                    <a href="admin_dashboard.php" class="btn-ghost">بازگشت به داشبورد مدیر</a>
                <?php endif; ?>
            </div>
        </form>
    </section>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var contentElement = document.querySelector('#content');
        if (contentElement && window.ClassicEditor) {
            ClassicEditor
                .create(contentElement, {
                    language: {
                        ui: 'fa',
                        content: 'fa'
                    },
                    toolbar: [
                        'heading', '|',
                        'bold', 'italic', 'link',
                        'bulletedList', 'numberedList',
                        'blockQuote',
                        'undo', 'redo'
                    ]
                })
                .then(function (editor) {
                    editor.editing.view.change(function (writer) {
                        writer.setAttribute('dir', 'rtl', editor.editing.view.document.getRoot());
                    });
                })
                .catch(function (error) {
                    console.error('خطا در بارگذاری CKEditor:', error);
                });
        }
    });
</script>

<?php include "includes/footer.php"; ?>

</body>
</html>
