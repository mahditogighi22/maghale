<?php
// view_article.php

session_start();
include 'db.php';

// بررسی و دریافت ID مقاله از URL
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    $article = null;
    $errorMessage = "شناسه‌ی مقاله معتبر نیست.";
} else {
    $articleId = (int) $_GET['id'];

    // گرفتن مقاله به همراه نام نویسنده
    $sql = "SELECT a.*, u.display_name, u.username 
            FROM articles a 
            LEFT JOIN users u ON a.author_id = u.id
            WHERE a.id = ? 
            LIMIT 1";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $articleId);
        $stmt->execute();
        $result  = $stmt->get_result();
        $article = $result->fetch_assoc();
        $stmt->close();
    } else {
        $article      = null;
        $errorMessage = "خطا در خواندن مقاله از دیتابیس.";
    }
}

// تبدیل دسته‌بندی به برچسب فارسی
function category_label($cat) {
    switch ($cat) {
        case 'technology': return 'تکنولوژی';
        case 'health':     return 'سلامت';
        case 'economy':    return 'اقتصاد';
        case 'art':        return 'هنر';
        default:           return 'سایر';
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>
        <?php 
        if (!empty($article['title'])) {
            echo htmlspecialchars($article['title']) . " | Vento Blog";
        } else {
            echo "مقاله | Vento Blog";
        }
        ?>
    </title>
    <link rel="stylesheet" href="style.css">
    <style>
        * { box-sizing: border-box; }

        body.single-article-page {
            margin: 0;
            font-family: "Vazirmatn", "IRANSans", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #020617; /* پس‌زمینه کلی سایت تیره */
            color: #e5e7eb;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .single-article-content {
            padding-top: 20px;
            padding-bottom: 32px;
        }

        .single-article-card {
            max-width: 860px;
            margin: 0 auto;
            /* بدون گرادیان آبی */
            background: #020617; /* کارت هم‌سان با تم تیره */
            border-radius: 20px;
            padding: 22px 20px 18px;
            border: 1px solid rgba(31, 41, 55, 0.95);
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.95);
        }

        .single-article-header {
            border-bottom: 1px solid rgba(55, 65, 81, 0.9);
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .single-article-title {
            font-size: 22px;
            font-weight: 800;
            margin: 0 0 8px 0;
            /* اینجا رنگ عنوان را ملایم کردیم */
            color: #e5e7eb; /* خاکستری روشن به‌جای تون آبی یا سفید تند */
            line-height: 1.8;
        }

        .single-article-meta {
            font-size: 12px;
            color: #9ca3af;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }

        .meta-separator {
            opacity: 0.6;
        }

        .article-category-pill {
            display: inline-block;
            padding: 2px 9px;
            border-radius: 999px;
            font-size: 11px;
            background: rgba(31, 41, 55, 0.95);
            color: #e5e7eb;
            border: 1px solid rgba(148, 163, 184, 0.9);
        }

        .single-article-body {
            font-size: 14px;
            line-height: 2.1;
            color: #e5e7eb;
            white-space: pre-line; /* برای حفظ اینترها */
        }

        .single-article-body p {
            margin: 0 0 12px 0;
        }

        .single-article-footer {
            margin-top: 18px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: space-between;
            align-items: center;
        }

        .back-link,
        .edit-link {
            padding: 8px 14px;
            border-radius: 999px;
            font-size: 13px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid rgba(148, 163, 184, 0.9);
            background: rgba(15, 23, 42, 0.96);
            color: #e5e7eb;
            transition: background 0.15s ease, border-color 0.15s ease, transform 0.08s ease, box-shadow 0.12s ease;
        }

        .back-link:hover,
        .edit-link:hover {
            background: rgba(31, 41, 55, 0.98);
            border-color: rgba(148, 163, 184, 1);
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.8);
            transform: translateY(-1px);
        }

        .single-article-error {
            max-width: 600px;
            margin: 0 auto;
            padding: 18px 16px;
            border-radius: 16px;
            background: rgba(15, 23, 42, 0.95);
            border: 1px solid rgba(248, 113, 113, 0.9);
            color: #fecaca;
            font-size: 13px;
            line-height: 1.9;
        }

        .single-article-error h2 {
            margin-top: 0;
            margin-bottom: 6px;
            font-size: 17px;
            font-weight: 700;
        }

        .single-article-error a {
            color: #bfdbfe;
            text-decoration: none;
            border-bottom: 1px dashed rgba(191, 219, 254, 0.7);
        }

        .single-article-error a:hover {
            color: #e5e7eb;
            border-bottom-style: solid;
        }

        @media (max-width: 640px) {
            .single-article-card {
                padding: 18px 14px 16px;
                border-radius: 18px;
            }

            .single-article-title {
                font-size: 19px;
            }

            .single-article-body {
                font-size: 13px;
            }
        }
    </style>
</head>
<body class="single-article-page">

<?php include "includes/header.php"; ?>

<main class="content single-article-content">

    <?php if (empty($article)): ?>

        <div class="single-article-error">
            <h2>مقاله یافت نشد</h2>
            <p>
                متأسفانه مقاله‌ای با این مشخصات در سیستم پیدا نشد.
                ممکن است لینک اشتباه باشد یا مقاله حذف شده باشد.
            </p>
            <p>
                برای بازگشت به صفحه‌ی اصلی، 
                <a href="index.php">اینجا کلیک کنید</a>.
            </p>
        </div>

    <?php else: ?>

        <?php
        $title       = $article['title']        ?? 'بدون عنوان';
        $content     = $article['content']      ?? '';
        $category    = $article['category']     ?? '';
        $authorName  = $article['display_name'] ?? ($article['username'] ?? 'نامشخص');
        $createdAt   = $article['created_at']   ?? '';

        $categoryFa  = category_label($category);

        $dateDisp = '';
        if (!empty($createdAt)) {
            $ts = strtotime($createdAt);
            if ($ts) {
                $dateDisp = date('Y/m/d', $ts);
            }
        }
        ?>

        <article class="single-article-card">
            <header class="single-article-header">
                <h1 class="single-article-title">
                    <?php echo htmlspecialchars($title); ?>
                </h1>
                <div class="single-article-meta">
                    <?php if ($categoryFa): ?>
                        <span class="article-category-pill">
                            <?php echo htmlspecialchars($categoryFa); ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($authorName): ?>
                        <span>
                            نویسنده: <?php echo htmlspecialchars($authorName); ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($dateDisp): ?>
                        <span class="meta-separator">|</span>
                        <span>تاریخ ثبت: <?php echo htmlspecialchars($dateDisp); ?></span>
                    <?php endif; ?>
                </div>
            </header>

            <div class="single-article-body">
                <?php echo nl2br(htmlspecialchars($content)); ?>
            </div>

            <footer class="single-article-footer">
                <a href="index.php" class="back-link">بازگشت به صفحه اصلی</a>

                <?php if (!empty($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'author')): ?>
                    <!-- اگر صفحه ویرایش نداری، این لینک کامنت بمونه -->
                    <!-- <a href="edit_article.php?id=<?php echo (int)$article['id']; ?>" class="edit-link">ویرایش مقاله</a> -->
                <?php endif; ?>
            </footer>
        </article>

    <?php endif; ?>

</main>

<?php include "includes/footer.php"; ?>

</body>
</html>
