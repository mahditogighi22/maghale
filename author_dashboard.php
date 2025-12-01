<?php
// author_dashboard.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include('db.php');

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'author') {
    header("Location: index.php");
    exit();
}

$authorId   = $_SESSION['user_id']      ?? null;
$authorName = $_SESSION['display_name'] ?? ($_SESSION['username'] ?? 'نویسنده');

if (!$authorId) {
    die("شناسه نویسنده یافت نشد.");
}

// آمار و آرایه‌ها
$stats = [
    'total'     => 0,
    'draft'     => 0,
    'pending'   => 0,
    'published' => 0,
    'rejected'  => 0,
];

$draftArticles     = [];
$pendingArticles   = [];
$publishedArticles = [];
$rejectedArticles  = [];

// خواندن مقالات نویسنده
$sql = "SELECT id, title, content, category, status, created_at 
        FROM articles 
        WHERE author_id = ?
        ORDER BY id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $authorId);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $stats['total']++;

    $statusRaw   = isset($row['status']) ? trim($row['status']) : '';
    $statusLower = mb_strtolower($statusRaw, 'UTF-8');

    if ($statusLower === 'draft') {
        $stats['draft']++;
        $draftArticles[] = $row;
    } elseif ($statusLower === 'published' || $statusLower === '1') {
        $stats['published']++;
        $publishedArticles[] = $row;
    } elseif ($statusLower === 'rejected' || $statusLower === '2') {
        $stats['rejected']++;
        $rejectedArticles[] = $row;
    } elseif ($statusLower === 'pending' || $statusLower === '0' || $statusLower === '') {
        $stats['pending']++;
        $pendingArticles[] = $row;
    } else {
        // هر مقدار ناشناخته را pending حساب می‌کنیم
        $stats['pending']++;
        $pendingArticles[] = $row;
    }
}

$stmt->close();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>داشبورد نویسنده | Vento Blog</title>
    <link rel="stylesheet" href="style.css">

    <style>
        * { box-sizing: border-box; }

        body.author-dashboard-page {
            margin: 0;
            font-family: "Vazirmatn", "IRANSans", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #020617;
            color: #e5e7eb;
            overflow-x: hidden;
        }

        .author-dashboard-content {
            padding-top: 20px;
            padding-bottom: 32px;
        }

        .author-hero {
            background: linear-gradient(135deg, #0f172a, #020617);
            border-radius: 20px;
            padding: 20px 18px 16px;
            margin-bottom: 22px;
            border: 1px solid rgba(55, 65, 81, 0.9);
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.95);
        }

        .author-hero-title {
            font-size: 21px;
            font-weight: 800;
            margin: 0 0 6px 0;
        }

        .author-hero-subtitle {
            font-size: 13px;
            color: #cbd5f5;
            margin: 0 0 14px 0;
            line-height: 1.9;
        }

        .author-hero-subtitle span {
            color: #f9fafb;
            font-weight: 600;
        }

        .author-hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 4px;
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
            transition: transform 0.1s ease, box-shadow 0.15s ease, opacity 0.15s ease,
                        background-color 0.15s ease, color 0.15s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #f9fafb;
            box-shadow: 0 14px 30px rgba(79, 70, 229, 0.8);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 18px 38px rgba(79, 70, 229, 0.95);
            opacity: 0.98;
        }

        .btn-primary:active {
            transform: translateY(1px);
            box-shadow: 0 10px 22px rgba(79, 70, 229, 0.85);
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

        .author-stats-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 12px;
        }

        .author-stat-pill {
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

        .author-stat-pill .stat-label { color: #9ca3af; }
        .author-stat-pill .stat-value { font-weight: 700; }

        .author-stat-pill--draft     .stat-value { color: #e5e7eb; }
        .author-stat-pill--pending   .stat-value { color: #fbbf24; }
        .author-stat-pill--published .stat-value { color: #4ade80; }
        .author-stat-pill--rejected  .stat-value { color: #f97373; }

        .author-section-title {
            font-size: 17px;
            font-weight: 700;
            margin: 18px 0 6px 0;
        }

        .author-section-subtitle {
            font-size: 13px;
            color: #9ca3af;
            margin-bottom: 14px;
        }

        .author-empty {
            font-size: 13px;
            color: #9ca3af;
            padding: 10px 0;
        }

        .author-articles-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) minmax(0, 1.4fr);
            gap: 14px;
        }

        .author-article-card {
            background: radial-gradient(circle at top, rgba(30, 64, 175, 0.3), rgba(15, 23, 42, 0.98));
            border-radius: 16px;
            padding: 12px 13px 10px;
            border: 1px solid rgba(148, 163, 184, 0.5);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.9);
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .author-article-header {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            align-items: flex-start;
        }

        .author-article-title {
            font-size: 14px;
            font-weight: 700;
            margin: 0 0 2px 0;
            color: #e5e7eb;
        }

        .author-article-meta {
            font-size: 11px;
            color: #9ca3af;
        }

        .author-article-tag {
            display: inline-block;
            font-size: 11px;
            padding: 2px 7px;
            border-radius: 999px;
            background: rgba(30, 64, 175, 0.65);
            color: #e0f2fe;
            margin-inline-start: 4px;
        }

        .author-article-body {
            font-size: 12px;
            color: #cbd5f5;
            max-height: 60px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .author-article-footer {
            margin-top: 6px;
            display: flex;
            justify-content: flex-end;
            gap: 6px;
            flex-wrap: wrap;
        }

        .btn-small {
            font-size: 11px;
            padding: 5px 10px;
            border-radius: 999px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: background 0.15s ease, box-shadow 0.15s ease, transform 0.08s ease;
        }

        .btn-edit {
            background: rgba(59, 130, 246, 0.14);
            color: #bfdbfe;
            border: 1px solid rgba(59, 130, 246, 0.9);
        }

        .btn-edit:hover {
            background: rgba(59, 130, 246, 0.26);
            box-shadow: 0 8px 18px rgba(59, 130, 246, 0.6);
            transform: translateY(-1px);
        }

        .btn-view {
            background: rgba(15, 23, 42, 0.9);
            color: #e5e7eb;
            border: 1px solid rgba(148, 163, 184, 0.9);
        }

        .btn-view:hover {
            background: rgba(15, 23, 42, 1);
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.8);
            transform: translateY(-1px);
        }

        @media (max-width: 900px) {
            .author-articles-grid {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        @media (max-width: 768px) {
            .author-hero {
                padding: 18px 14px 14px;
                border-radius: 18px;
            }

            .author-hero-title {
                font-size: 19px;
            }
        }

        @media (max-width: 480px) {
            .author-hero {
                padding: 16px 12px 12px;
            }

            .author-hero-title {
                font-size: 18px;
            }

            .author-stats-row {
                gap: 8px;
            }

            .author-stat-pill {
                min-width: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body class="author-dashboard-page">

<?php include "includes/header.php"; ?>

<main class="content author-dashboard-content">

    <section class="author-hero">
        <h1 class="author-hero-title">داشبورد نویسنده</h1>
        <p class="author-hero-subtitle">
            <span><?php echo htmlspecialchars($authorName); ?></span>، در این بخش می‌توانید پیش‌نویس‌ها، مقالات در انتظار تأیید،
            منتشر شده و رد شده خود را مدیریت کنید.
        </p>

        <div class="author-hero-actions">
            <a href="submit_article.php" class="btn-primary">ارسال مقاله جدید</a>
            <a href="index.php" class="btn-ghost">مشاهده سایت</a>
        </div>

        <div class="author-stats-row">
            <div class="author-stat-pill author-stat-pill--draft">
                <span class="stat-label">پیش‌نویس‌ها</span>
                <span class="stat-value"><?php echo (int)$stats['draft']; ?></span>
            </div>
            <div class="author-stat-pill author-stat-pill--pending">
                <span class="stat-label">در انتظار تأیید</span>
                <span class="stat-value"><?php echo (int)$stats['pending']; ?></span>
            </div>
            <div class="author-stat-pill author-stat-pill--published">
                <span class="stat-label">منتشر شده</span>
                <span class="stat-value"><?php echo (int)$stats['published']; ?></span>
            </div>
            <div class="author-stat-pill author-stat-pill--rejected">
                <span class="stat-label">رد شده</span>
                <span class="stat-value"><?php echo (int)$stats['rejected']; ?></span>
            </div>
        </div>
    </section>

    <!-- پیش‌نویس‌ها -->
    <section>
        <h2 class="author-section-title">پیش‌نویس‌ها</h2>
        <p class="author-section-subtitle">
            مقالاتی که هنوز برای مدیر ارسال نکرده‌اید در این بخش هستند. می‌توانید آن‌ها را ویرایش کرده و هر زمان خواستید برای تأیید ارسال کنید.
        </p>

        <?php if (empty($draftArticles)): ?>
            <div class="author-empty">در حال حاضر هیچ پیش‌نویسی ندارید.</div>
        <?php else: ?>
            <div class="author-articles-grid">
                <?php foreach ($draftArticles as $article): ?>
                    <?php
                    $id       = (int)$article['id'];
                    $title    = $article['title'] ?? 'بدون عنوان';
                    $category = $article['category'] ?? '';
                    $date_disp = '';
                    if (!empty($article['created_at'])) {
                        $ts        = strtotime($article['created_at']);
                        $date_disp = $ts ? date('Y/m/d', $ts) : '';
                    }
                    $excerpt = '';
                    if (!empty($article['content'])) {
                        $excerpt = mb_substr(strip_tags($article['content']), 0, 120, 'UTF-8');
                        if (mb_strlen(strip_tags($article['content']), 'UTF-8') > 120) {
                            $excerpt .= '...';
                        }
                    }
                    $categoryLabel = '';
                    if ($category === 'technology')      $categoryLabel = 'تکنولوژی';
                    elseif ($category === 'health')      $categoryLabel = 'سلامت';
                    elseif ($category === 'economy')     $categoryLabel = 'اقتصاد';
                    elseif ($category === 'art')         $categoryLabel = 'هنر';
                    ?>
                    <article class="author-article-card">
                        <div class="author-article-header">
                            <div>
                                <h3 class="author-article-title"><?php echo htmlspecialchars($title); ?></h3>
                                <div class="author-article-meta">
                                    <?php if ($date_disp): ?>
                                        <span>تاریخ ایجاد: <?php echo htmlspecialchars($date_disp); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if ($categoryLabel): ?>
                                <span class="author-article-tag"><?php echo htmlspecialchars($categoryLabel); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if ($excerpt): ?>
                            <div class="author-article-body">
                                <?php echo nl2br(htmlspecialchars($excerpt)); ?>
                            </div>
                        <?php endif; ?>
                        <div class="author-article-footer">
                            <a href="edit_article.php?id=<?php echo $id; ?>" class="btn-small btn-edit">
                                ویرایش / ارسال برای تأیید
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- در انتظار تأیید -->
    <section>
        <h2 class="author-section-title" style="margin-top: 26px;">مقالات در انتظار تأیید</h2>
        <p class="author-section-subtitle">
            این مقالات برای مدیر ارسال شده‌اند و منتظر تأیید یا رد هستند.
        </p>

        <?php if (empty($pendingArticles)): ?>
            <div class="author-empty">مقاله‌ای در وضعیت در انتظار تأیید ندارید.</div>
        <?php else: ?>
            <div class="author-articles-grid">
                <?php foreach ($pendingArticles as $article): ?>
                    <?php
                    $id       = (int)$article['id'];
                    $title    = $article['title'] ?? 'بدون عنوان';
                    $category = $article['category'] ?? '';
                    $date_disp = '';
                    if (!empty($article['created_at'])) {
                        $ts        = strtotime($article['created_at']);
                        $date_disp = $ts ? date('Y/m/d', $ts) : '';
                    }
                    $excerpt = '';
                    if (!empty($article['content'])) {
                        $excerpt = mb_substr(strip_tags($article['content']), 0, 120, 'UTF-8');
                        if (mb_strlen(strip_tags($article['content']), 'UTF-8') > 120) {
                            $excerpt .= '...';
                        }
                    }
                    $categoryLabel = '';
                    if ($category === 'technology')      $categoryLabel = 'تکنولوژی';
                    elseif ($category === 'health')      $categoryLabel = 'سلامت';
                    elseif ($category === 'economy')     $categoryLabel = 'اقتصاد';
                    elseif ($category === 'art')         $categoryLabel = 'هنر';

                    $view_link = "view_article.php?id=" . urlencode($id);
                    ?>
                    <article class="author-article-card">
                        <div class="author-article-header">
                            <div>
                                <h3 class="author-article-title"><?php echo htmlspecialchars($title); ?></h3>
                                <div class="author-article-meta">
                                    <?php if ($date_disp): ?>
                                        <span>تاریخ ارسال: <?php echo htmlspecialchars($date_disp); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if ($categoryLabel): ?>
                                <span class="author-article-tag"><?php echo htmlspecialchars($categoryLabel); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if ($excerpt): ?>
                            <div class="author-article-body">
                                <?php echo nl2br(htmlspecialchars($excerpt)); ?>
                            </div>
                        <?php endif; ?>
                        <div class="author-article-footer">
                            <a href="<?php echo htmlspecialchars($view_link); ?>" class="btn-small btn-view">
                                مشاهده
                            </a>
                            <a href="edit_article.php?id=<?php echo $id; ?>" class="btn-small btn-edit">
                                ویرایش
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- منتشر شده -->
    <section>
        <h2 class="author-section-title" style="margin-top: 26px;">مقالات منتشر شده</h2>
        <p class="author-section-subtitle">
            این مقالات توسط مدیر تأیید شده‌اند و در صفحه‌ی اصلی سایت نمایش داده می‌شوند.
        </p>

        <?php if (empty($publishedArticles)): ?>
            <div class="author-empty">هنوز مقاله‌ای برای شما منتشر نشده است.</div>
        <?php else: ?>
            <div class="author-articles-grid">
                <?php foreach ($publishedArticles as $article): ?>
                    <?php
                    $id       = (int)$article['id'];
                    $title    = $article['title'] ?? 'بدون عنوان';
                    $category = $article['category'] ?? '';
                    $date_disp = '';
                    if (!empty($article['created_at'])) {
                        $ts        = strtotime($article['created_at']);
                        $date_disp = $ts ? date('Y/m/d', $ts) : '';
                    }
                    $excerpt = '';
                    if (!empty($article['content'])) {
                        $excerpt = mb_substr(strip_tags($article['content']), 0, 120, 'UTF-8');
                        if (mb_strlen(strip_tags($article['content']), 'UTF-8') > 120) {
                            $excerpt .= '...';
                        }
                    }
                    $categoryLabel = '';
                    if ($category === 'technology')      $categoryLabel = 'تکنولوژی';
                    elseif ($category === 'health')      $categoryLabel = 'سلامت';
                    elseif ($category === 'economy')     $categoryLabel = 'اقتصاد';
                    elseif ($category === 'art')         $categoryLabel = 'هنر';

                    $view_link = "view_article.php?id=" . urlencode($id);
                    ?>
                    <article class="author-article-card">
                        <div class="author-article-header">
                            <div>
                                <h3 class="author-article-title"><?php echo htmlspecialchars($title); ?></h3>
                                <div class="author-article-meta">
                                    <?php if ($date_disp): ?>
                                        <span>تاریخ انتشار: <?php echo htmlspecialchars($date_disp); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if ($categoryLabel): ?>
                                <span class="author-article-tag"><?php echo htmlspecialchars($categoryLabel); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if ($excerpt): ?>
                            <div class="author-article-body">
                                <?php echo nl2br(htmlspecialchars($excerpt)); ?>
                            </div>
                        <?php endif; ?>
                        <div class="author-article-footer">
                            <a href="<?php echo htmlspecialchars($view_link); ?>" class="btn-small btn-view">
                                مشاهده در سایت
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- رد شده -->
    <section>
        <h2 class="author-section-title" style="margin-top: 26px;">مقالات رد شده</h2>
        <p class="author-section-subtitle">
            این مقالات توسط مدیر رد شده‌اند. می‌توانید آن‌ها را در یک پیش‌نویس جدید ویرایش و دوباره ارسال کنید.
        </p>

        <?php if (empty($rejectedArticles)): ?>
            <div class="author-empty">مقاله‌ای در وضعیت رد شده ندارید.</div>
        <?php else: ?>
            <div class="author-articles-grid">
                <?php foreach ($rejectedArticles as $article): ?>
                    <?php
                    $id       = (int)$article['id'];
                    $title    = $article['title'] ?? 'بدون عنوان';
                    $category = $article['category'] ?? '';
                    $date_disp = '';
                    if (!empty($article['created_at'])) {
                        $ts        = strtotime($article['created_at']);
                        $date_disp = $ts ? date('Y/m/d', $ts) : '';
                    }
                    $excerpt = '';
                    if (!empty($article['content'])) {
                        $excerpt = mb_substr(strip_tags($article['content']), 0, 120, 'UTF-8');
                        if (mb_strlen(strip_tags($article['content']), 'UTF-8') > 120) {
                            $excerpt .= '...';
                        }
                    }
                    $categoryLabel = '';
                    if ($category === 'technology')      $categoryLabel = 'تکنولوژی';
                    elseif ($category === 'health')      $categoryLabel = 'سلامت';
                    elseif ($category === 'economy')     $categoryLabel = 'اقتصاد';
                    elseif ($category === 'art')         $categoryLabel = 'هنر';

                    $view_link = "view_article.php?id=" . urlencode($id);
                    ?>
                    <article class="author-article-card">
                        <div class="author-article-header">
                            <div>
                                <h3 class="author-article-title"><?php echo htmlspecialchars($title); ?></h3>
                                <div class="author-article-meta">
                                    <?php if ($date_disp): ?>
                                        <span>تاریخ ثبت: <?php echo htmlspecialchars($date_disp); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if ($categoryLabel): ?>
                                <span class="author-article-tag"><?php echo htmlspecialchars($categoryLabel); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if ($excerpt): ?>
                            <div class="author-article-body">
                                <?php echo nl2br(htmlspecialchars($excerpt)); ?>
                            </div>
                        <?php endif; ?>
                        <div class="author-article-footer">
                            <a href="<?php echo htmlspecialchars($view_link); ?>" class="btn-small btn-view">
                                مشاهده
                            </a>
                            <a href="edit_article.php?id=<?php echo $id; ?>" class="btn-small btn-edit">
                                ویرایش / ارسال مجدد
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
