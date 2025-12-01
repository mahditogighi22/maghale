<?php
// admin_dashboard.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('db.php');

// فقط مدیر اجازه دسترسی دارد
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$adminName = $_SESSION['display_name'] ?? ($_SESSION['username'] ?? 'مدیر');

// آمار کلی
$stats = [
    'total'     => 0,
    'draft'     => 0,   // پیش‌نویس‌ها (برای آمار داخلی)
    'pending'   => 0,
    'published' => 0,
    'rejected'  => 0,
];

$pendingArticles   = [];
$publishedArticles = [];
$rejectedArticles  = [];
$errorMessage      = '';

// خواندن همه مقالات به همراه نام نویسنده از جدول users
$sqlAll = "
    SELECT 
        a.id,
        a.title,
        a.content,
        a.category,
        a.status,
        a.author_id,
        a.created_at,
        u.display_name,
        u.username
    FROM articles a
    LEFT JOIN users u ON a.author_id = u.id
    ORDER BY a.id DESC
";

if ($res = $conn->query($sqlAll)) {
    while ($row = $res->fetch_assoc()) {
        $stats['total']++;

        $statusRaw   = isset($row['status']) ? trim($row['status']) : '';
        $statusLower = mb_strtolower($statusRaw, 'UTF-8');

        // پیش‌نویس‌ها فقط در داشبورد نویسنده مدیریت می‌شوند
        if ($statusLower === 'draft') {
            $stats['draft']++;
            continue;
        }
        // published: هم متن، هم عددی
        elseif ($statusLower === 'published' || $statusLower === '1') {
            $stats['published']++;
            $publishedArticles[] = $row;
        }
        // rejected: هم متن، هم عددی
        elseif ($statusLower === 'rejected' || $statusLower === '2') {
            $stats['rejected']++;
            $rejectedArticles[] = $row;
        }
        // pending: هم متن، هم ۰، هم خالی
        elseif ($statusLower === 'pending' || $statusLower === '0' || $statusLower === '') {
            $stats['pending']++;
            $pendingArticles[]  = $row;
        }
        // هر مقدار ناشناخته‌ای را هم احتیاطاً pending حساب می‌کنیم
        else {
            $stats['pending']++;
            $pendingArticles[] = $row;
        }
    }
} else {
    $errorMessage = "خطا در خواندن لیست مقالات از دیتابیس.";
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>داشبورد مدیر | Vento Blog</title>
    <link rel="stylesheet" href="style.css">

    <style>
        * { box-sizing: border-box; }

        body.admin-dashboard-page {
            margin: 0;
            font-family: "Vazirmatn", "IRANSans", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: radial-gradient(circle at top, #020617, #020617);
            color: #e5e7eb;
            overflow-x: hidden;
        }

        .admin-dashboard-content {
            padding-top: 20px;
            padding-bottom: 32px;
        }

        .admin-hero {
            background: linear-gradient(135deg, #0f172a, #020617);
            border-radius: 20px;
            padding: 20px 18px 16px;
            margin-bottom: 22px;
            border: 1px solid rgba(55, 65, 81, 0.9);
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.95);
            position: relative;
            overflow: hidden;
        }

        .admin-hero::before {
            content: "";
            position: absolute;
            inset-inline-end: -80px;
            top: -80px;
            width: 180px;
            height: 180px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(248, 250, 252, 0.16), transparent);
        }

        .admin-hero-title {
            font-size: 21px;
            font-weight: 800;
            margin: 0 0 6px 0;
            position: relative;
            z-index: 1;
        }

        .admin-hero-subtitle {
            font-size: 13px;
            color: #cbd5f5;
            margin: 0 0 14px 0;
            position: relative;
            z-index: 1;
            line-height: 1.9;
        }

        .admin-hero-subtitle span {
            color: #f9fafb;
            font-weight: 600;
        }

        .admin-hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 4px;
            position: relative;
            z-index: 1;
        }

        .btn-primary,
        .btn-ghost {
            border-radius: 999px;
            padding: 7px 15px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: none;
            outline: none;
            transition:
                transform 0.1s ease,
                box-shadow 0.15s ease,
                opacity 0.15s ease,
                background-color 0.15s ease,
                color 0.15s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: #f9fafb;
            box-shadow: 0 14px 30px rgba(22, 163, 74, 0.8);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 18px 38px rgba(22, 163, 74, 0.95);
            opacity: 0.98;
        }

        .btn-primary:active {
            transform: translateY(1px);
            box-shadow: 0 10px 22px rgba(22, 163, 74, 0.85);
        }

        .btn-ghost {
            background: rgba(15, 23, 42, 0.95);
            color: #e5e7eb;
            border: 1px solid rgba(148, 163, 184, 0.8);
        }

        .btn-ghost:hover {
            background: rgba(31, 41, 55, 0.98);
            border-color: rgba(148, 163, 184, 1);
        }

        .admin-stats-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 12px;
            position: relative;
            z-index: 1;
        }

        .admin-stat-pill {
            min-width: 140px;
            padding: 7px 10px;
            border-radius: 14px;
            border: 1px solid rgba(148, 163, 184, 0.7);
            background: rgba(15, 23, 42, 0.96);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
        }

        .admin-stat-pill span { display: inline-block; }

        .admin-stat-pill .stat-label { color: #9ca3af; }

        .admin-stat-pill .stat-value { font-weight: 700; }

        .admin-stat-pill--pending  .stat-value { color: #fbbf24; }
        .admin-stat-pill--published .stat-value { color: #4ade80; }
        .admin-stat-pill--rejected  .stat-value { color: #f97373; }

        .admin-section-title {
            font-size: 17px;
            font-weight: 700;
            margin: 18px 0 6px 0;
        }

        .admin-section-subtitle {
            font-size: 13px;
            color: #9ca3af;
            margin-bottom: 14px;
        }

        .admin-error {
            font-size: 13px;
            color: #fecaca;
            background: rgba(239, 68, 68, 0.16);
            border: 1px solid rgba(248, 113, 113, 0.9);
            border-radius: 12px;
            padding: 10px 12px;
            margin-bottom: 12px;
        }

        .admin-empty {
            font-size: 13px;
            color: #9ca3af;
            padding: 10px 0;
        }

        .admin-articles-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) minmax(0, 1.4fr);
            gap: 14px;
        }

        .admin-article-card {
            background: radial-gradient(circle at top, rgba(30, 64, 175, 0.3), rgba(15, 23, 42, 0.98));
            border-radius: 16px;
            padding: 12px 13px 10px;
            border: 1px solid rgba(148, 163, 184, 0.5);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.9);
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .admin-article-header {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            align-items: flex-start;
        }

        .admin-article-title {
            font-size: 14px;
            font-weight: 700;
            margin: 0 0 2px 0;
            color: #e5e7eb;
        }

        .admin-article-meta {
            font-size: 11px;
            color: #9ca3af;
        }

        .admin-article-tag {
            display: inline-block;
            font-size: 11px;
            padding: 2px 7px;
            border-radius: 999px;
            background: rgba(30, 64, 175, 0.65);
            color: #e0f2fe;
            margin-inline-start: 4px;
        }

        .admin-article-body {
            font-size: 12px;
            color: #cbd5f5;
            max-height: 60px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .admin-article-footer {
            margin-top: 6px;
            display: flex;
            justify-content: flex-end;
            gap: 6px;
            flex-wrap: wrap;
        }

        .btn-approve,
        .btn-reject,
        .btn-unpublish,
        .btn-delete {
            font-size: 11px;
            padding: 5px 10px;
            border-radius: 999px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: background 0.15s ease, box-shadow 0.15s ease, transform 0.08s.ease;
        }

        .btn-approve {
            background: rgba(22, 163, 74, 0.15);
            color: #bbf7d0;
            border: 1px solid rgba(34, 197, 94, 0.9);
        }

        .btn-approve:hover {
            background: rgba(22, 163, 74, 0.28);
            box-shadow: 0 8px 18px rgba(22, 163, 74, 0.6);
            transform: translateY(-1px);
        }

        .btn-reject {
            background: rgba(220, 38, 38, 0.12);
            color: #fecaca;
            border: 1px solid rgba(239, 68, 68, 0.9);
        }

        .btn-reject:hover {
            background: rgba(220, 38, 38, 0.22);
            box-shadow: 0 8px 18px rgba(220, 38, 38, 0.6);
            transform: translateY(-1px);
        }

        .btn-unpublish {
            background: rgba(234, 179, 8, 0.12);
            color: #facc15;
            border: 1px solid rgba(234, 179, 8, 0.9);
        }

        .btn-unpublish:hover {
            background: rgba(234, 179, 8, 0.22);
            box-shadow: 0 8px 18px rgba(234, 179, 8, 0.6);
            transform: translateY(-1px);
        }

        .btn-delete {
            background: rgba(15, 23, 42, 0.9);
            color: #fecaca;
            border: 1px solid rgba(148, 163, 184, 0.9);
        }

        .btn-delete:hover {
            background: rgba(15, 23, 42, 1);
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.8);
            transform: translateY(-1px);
        }

        @media (max-width: 900px) {
            .admin-articles-grid {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        @media (max-width: 768px) {
            .admin-hero {
                padding: 18px 14px 14px;
                border-radius: 18px;
            }

            .admin-hero-title {
                font-size: 19px;
            }
        }

        @media (max-width: 480px) {
            .admin-hero {
                padding: 16px 12px 12px;
            }

            .admin-hero-title {
                font-size: 18px;
            }

            .admin-stats-row {
                gap: 8px;
            }

            .admin-stat-pill {
                min-width: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body class="admin-dashboard-page">

<?php include "includes/header.php"; ?>

<main class="content admin-dashboard-content">

    <section class="admin-hero">
        <h1 class="admin-hero-title">داشبورد مدیر</h1>
        <p class="admin-hero-subtitle">
            <span><?php echo htmlspecialchars($adminName); ?></span>، در این بخش می‌توانید وضعیت کلی مقالات را ببینید
            و مقالات در انتظار تأیید، منتشر شده و رد شده را مدیریت کنید.
        </p>

        <div class="admin-hero-actions">
            <a href="submit_article.php" class="btn-primary">ارسال مقاله به عنوان مدیر</a>
            <a href="index.php" class="btn-ghost">مشاهده سایت</a>
        </div>

        <div class="admin-stats-row">
            <div class="admin-stat-pill">
                <span class="stat-label">همه مقالات</span>
                <span class="stat-value"><?php echo (int)$stats['total']; ?></span>
            </div>
            <div class="admin-stat-pill admin-stat-pill--pending">
                <span class="stat-label">در انتظار تأیید</span>
                <span class="stat-value"><?php echo (int)$stats['pending']; ?></span>
            </div>
            <div class="admin-stat-pill admin-stat-pill--published">
                <span class="stat-label">منتشر شده</span>
                <span class="stat-value"><?php echo (int)$stats['published']; ?></span>
            </div>
            <div class="admin-stat-pill admin-stat-pill--rejected">
                <span class="stat-label">رد شده</span>
                <span class="stat-value"><?php echo (int)$stats['rejected']; ?></span>
            </div>
        </div>
    </section>

    <!-- مقالات در انتظار تأیید -->
    <section>
        <h2 class="admin-section-title">مقالات در انتظار تأیید</h2>
        <p class="admin-section-subtitle">
            مقالاتی که هنوز وضعیت آن‌ها تأیید نشده است در این قسمت نمایش داده می‌شوند. می‌توانید آن‌ها را تأیید یا رد کنید.
        </p>

        <?php if ($errorMessage): ?>
            <div class="admin-error">
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($pendingArticles) && !$errorMessage): ?>
            <div class="admin-empty">
                در حال حاضر هیچ مقاله‌ای در انتظار تأیید وجود ندارد.
            </div>
        <?php elseif (!empty($pendingArticles)): ?>
            <div class="admin-articles-grid">
                <?php foreach ($pendingArticles as $article): ?>
                    <?php
                    $id        = (int)$article['id'];
                    $title     = $article['title'] ?? 'بدون عنوان';
                    $category  = $article['category'] ?? '';
                    $author_id = $article['author_id'] ?? null;

                    $author_display_name = $article['display_name'] ?? '';
                    $author_username     = $article['username'] ?? '';

                    $author_label = '';
                    if ($author_display_name !== '') {
                        $author_label = $author_display_name;
                    } elseif ($author_username !== '') {
                        $author_label = $author_username;
                    } elseif (!empty($author_id)) {
                        $author_label = 'شناسه ' . (int)$author_id;
                    }

                    $date_disp = '';
                    if (!empty($article['created_at'])) {
                        $ts        = strtotime($article['created_at']);
                        $date_disp = $ts ? date('Y/m/d', $ts) : '';
                    }

                    $excerpt = '';
                    if (!empty($article['content'])) {
                        $plain   = strip_tags($article['content']);
                        $excerpt = mb_substr($plain, 0, 120, 'UTF-8');
                        if (mb_strlen($plain, 'UTF-8') > 120) {
                            $excerpt .= '...';
                        }
                    }

                    $categoryLabel = '';
                    if     ($category === 'technology') $categoryLabel = 'تکنولوژی';
                    elseif ($category === 'health')     $categoryLabel = 'سلامت';
                    elseif ($category === 'economy')    $categoryLabel = 'اقتصاد';
                    elseif ($category === 'art')        $categoryLabel = 'هنر';
                    ?>
                    <article class="admin-article-card">
                        <div class="admin-article-header">
                            <div>
                                <h3 class="admin-article-title"><?php echo htmlspecialchars($title); ?></h3>
                                <div class="admin-article-meta">
                                    <?php if ($date_disp): ?>
                                        <span>تاریخ ثبت: <?php echo htmlspecialchars($date_disp); ?></span>
                                    <?php endif; ?>
                                    <?php if ($author_label !== ''): ?>
                                        <span> | نویسنده: <?php echo htmlspecialchars($author_label); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if ($categoryLabel): ?>
                                <span class="admin-article-tag"><?php echo htmlspecialchars($categoryLabel); ?></span>
                            <?php endif; ?>
                        </div>

                        <?php if ($excerpt): ?>
                            <div class="admin-article-body">
                                <?php echo nl2br(htmlspecialchars($excerpt)); ?>
                            </div>
                        <?php endif; ?>

                        <div class="admin-article-footer">
                            <a href="approve_article.php?id=<?php echo $id; ?>" class="btn-approve">تأیید</a>
                            <a href="reject_article.php?id=<?php echo $id; ?>" class="btn-reject">رد</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- مقالات تأیید شده -->
    <section>
        <h2 class="admin-section-title" style="margin-top: 26px;">مقالات تأیید شده (نمایش در صفحه خانه)</h2>
        <p class="admin-section-subtitle">
            مقالاتی که وضعیت آن‌ها «published» است در صفحه خانه به کاربران نمایش داده می‌شوند. از اینجا می‌توانید آن‌ها را
            از خانه بردارید (بازگشت به حالت در انتظار تأیید) یا حذف کامل کنید.
        </p>

        <?php if (empty($publishedArticles)): ?>
            <div class="admin-empty">
                هنوز هیچ مقاله‌ای تأیید و منتشر نشده است.
            </div>
        <?php else: ?>
            <div class="admin-articles-grid">
                <?php foreach ($publishedArticles as $article): ?>
                    <?php
                    $id        = (int)$article['id'];
                    $title     = $article['title'] ?? 'بدون عنوان';
                    $category  = $article['category'] ?? '';
                    $author_id = $article['author_id'] ?? null;

                    $author_display_name = $article['display_name'] ?? '';
                    $author_username     = $article['username'] ?? '';

                    $author_label = '';
                    if ($author_display_name !== '') {
                        $author_label = $author_display_name;
                    } elseif ($author_username !== '') {
                        $author_label = $author_username;
                    } elseif (!empty($author_id)) {
                        $author_label = 'شناسه ' . (int)$author_id;
                    }

                    $date_disp = '';
                    if (!empty($article['created_at'])) {
                        $ts        = strtotime($article['created_at']);
                        $date_disp = $ts ? date('Y/m/d', $ts) : '';
                    }

                    $excerpt = '';
                    if (!empty($article['content'])) {
                        $plain   = strip_tags($article['content']);
                        $excerpt = mb_substr($plain, 0, 120, 'UTF-8');
                        if (mb_strlen($plain, 'UTF-8') > 120) {
                            $excerpt .= '...';
                        }
                    }

                    $categoryLabel = '';
                    if     ($category === 'technology') $categoryLabel = 'تکنولوژی';
                    elseif ($category === 'health')     $categoryLabel = 'سلامت';
                    elseif ($category === 'economy')    $categoryLabel = 'اقتصاد';
                    elseif ($category === 'art')        $categoryLabel = 'هنر';

                    $view_link = "view_article.php?id=" . urlencode($id);
                    ?>
                    <article class="admin-article-card">
                        <div class="admin-article-header">
                            <div>
                                <h3 class="admin-article-title"><?php echo htmlspecialchars($title); ?></h3>
                                <div class="admin-article-meta">
                                    <?php if ($date_disp): ?>
                                        <span>تاریخ انتشار: <?php echo htmlspecialchars($date_disp); ?></span>
                                    <?php endif; ?>
                                    <?php if ($author_label !== ''): ?>
                                        <span> | نویسنده: <?php echo htmlspecialchars($author_label); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if ($categoryLabel): ?>
                                <span class="admin-article-tag"><?php echo htmlspecialchars($categoryLabel); ?></span>
                            <?php endif; ?>
                        </div>

                        <?php if ($excerpt): ?>
                            <div class="admin-article-body">
                                <?php echo nl2br(htmlspecialchars($excerpt)); ?>
                            </div>
                        <?php endif; ?>

                        <div class="admin-article-footer">
                            <a href="<?php echo htmlspecialchars($view_link); ?>" class="btn-ghost" style="font-size:11px;padding:4px 10px;">
                                مشاهده در سایت
                            </a>
                            <a href="unpublish_article.php?id=<?php echo $id; ?>" class="btn-unpublish">
                                برداشتن از خانه
                            </a>
                            <a href="delete_article.php?id=<?php echo $id; ?>" class="btn-delete"
                               onclick="return confirm('از حذف کامل این مقاله مطمئن هستید؟ این عمل قابل بازگشت نیست.');">
                                حذف کامل
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- مقالات رد شده -->
    <section>
        <h2 class="admin-section-title" style="margin-top: 26px;">مقالات رد شده (آرشیو)</h2>
        <p class="admin-section-subtitle">
            مقالاتی که وضعیت آن‌ها «rejected» است در این بخش نمایش داده می‌شوند. می‌توانید آن‌ها را
            دوباره به حالت «در انتظار تأیید» برگردانید یا به‌طور کامل حذف کنید.
        </p>

        <?php if (empty($rejectedArticles)): ?>
            <div class="admin-empty">
                در حال حاضر هیچ مقاله‌ای در وضعیت رد شده قرار ندارد.
            </div>
        <?php else: ?>
            <div class="admin-articles-grid">
                <?php foreach ($rejectedArticles as $article): ?>
                    <?php
                    $id        = (int)$article['id'];
                    $title     = $article['title'] ?? 'بدون عنوان';
                    $category  = $article['category'] ?? '';
                    $author_id = $article['author_id'] ?? null;

                    $author_display_name = $article['display_name'] ?? '';
                    $author_username     = $article['username'] ?? '';

                    $author_label = '';
                    if ($author_display_name !== '') {
                        $author_label = $author_display_name;
                    } elseif ($author_username !== '') {
                        $author_label = $author_username;
                    } elseif (!empty($author_id)) {
                        $author_label = 'شناسه ' . (int)$author_id;
                    }

                    $date_disp = '';
                    if (!empty($article['created_at'])) {
                        $ts        = strtotime($article['created_at']);
                        $date_disp = $ts ? date('Y/m/d', $ts) : '';
                    }

                    $excerpt = '';
                    if (!empty($article['content'])) {
                        $plain   = strip_tags($article['content']);
                        $excerpt = mb_substr($plain, 0, 120, 'UTF-8');
                        if (mb_strlen($plain, 'UTF-8') > 120) {
                            $excerpt .= '...';
                        }
                    }

                    $categoryLabel = '';
                    if     ($category === 'technology') $categoryLabel = 'تکنولوژی';
                    elseif ($category === 'health')     $categoryLabel = 'سلامت';
                    elseif ($category === 'economy')    $categoryLabel = 'اقتصاد';
                    elseif ($category === 'art')        $categoryLabel = 'هنر';

                    $view_link = "view_article.php?id=" . urlencode($id);
                    ?>
                    <article class="admin-article-card">
                        <div class="admin-article-header">
                            <div>
                                <h3 class="admin-article-title"><?php echo htmlspecialchars($title); ?></h3>
                                <div class="admin-article-meta">
                                    <?php if ($date_disp): ?>
                                        <span>تاریخ ثبت: <?php echo htmlspecialchars($date_disp); ?></span>
                                    <?php endif; ?>
                                    <?php if ($author_label !== ''): ?>
                                        <span> | نویسنده: <?php echo htmlspecialchars($author_label); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if ($categoryLabel): ?>
                                <span class="admin-article-tag"><?php echo htmlspecialchars($categoryLabel); ?></span>
                            <?php endif; ?>
                        </div>

                        <?php if ($excerpt): ?>
                            <div class="admin-article-body">
                                <?php echo nl2br(htmlspecialchars($excerpt)); ?>
                            </div>
                        <?php endif; ?>

                        <div class="admin-article-footer">
                            <a href="<?php echo htmlspecialchars($view_link); ?>" class="btn-ghost" style="font-size:11px;padding:4px 10px;">
                                مشاهده
                            </a>
                            <a href="unpublish_article.php?id=<?php echo $id; ?>" class="btn-unpublish">
                                بازگشت به در انتظار تأیید
                            </a>
                            <a href="delete_article.php?id=<?php echo $id; ?>" class="btn-delete"
                               onclick="return confirm('از حذف کامل این مقاله مطمئن هستید؟ این عمل قابل بازگشت نیست.');">
                                حذف کامل
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

</main>

<?php include "includes/footer.php"; ?>

</body>
</html>
