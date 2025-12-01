<?php
// index.php

session_start();
include('db.php');

// فقط مقالات با status = 'published'
$articles = [];
$sql = "SELECT id, title, content, category, slug, created_at 
        FROM articles 
        WHERE status = 'published'
        ORDER BY created_at DESC, id DESC";

if ($res = $conn->query($sql)) {
    while ($row = $res->fetch_assoc()) {
        $articles[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>خانه | Vento Blog</title>
    <link rel="stylesheet" href="style.css">
    <style>
        * { box-sizing: border-box; }

        body.index-page {
            margin: 0;
            font-family: "Vazirmatn", "IRANSans", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: radial-gradient(circle at top, #020617, #020617);
            color: #e5e7eb;
            overflow-x: hidden;
        }

        .home-content {
            padding-top: 20px;
            padding-bottom: 32px;
        }

        .home-hero {
            max-width: 980px;
            margin: 0 auto 22px;
            padding: 18px 18px 16px;
            border-radius: 20px;
            background: linear-gradient(135deg, #0f172a, #020617);
            border: 1px solid rgba(55, 65, 81, 0.9);
            box-shadow: 0 22px 50px rgba(15, 23, 42, 0.95);
            position: relative;
            overflow: hidden;
        }

        .home-hero::before {
            content: "";
            position: absolute;
            inset-inline-end: -80px;
            top: -80px;
            width: 190px;
            height: 190px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.45), transparent);
            opacity: 0.7;
        }

        .home-hero-title {
            font-size: 22px;
            font-weight: 800;
            margin: 0 0 8px 0;
            position: relative;
            z-index: 1;
        }

        .home-hero-subtitle {
            font-size: 13px;
            color: #cbd5f5;
            margin: 0 0 10px 0;
            line-height: 1.9;
            position: relative;
            z-index: 1;
        }

        .home-hero-subtitle span {
            color: #e5e7eb;
            font-weight: 600;
        }

        .home-hero-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 6px;
            position: relative;
            z-index: 1;
        }

        .home-tag-pill {
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 999px;
            border: 1px solid rgba(148, 163, 184, 0.8);
            background: rgba(15, 23, 42, 0.96);
            color: #e5e7eb;
        }

        .home-articles-wrapper {
            max-width: 980px;
            margin: 0 auto;
        }

        .home-section-title {
            font-size: 17px;
            font-weight: 700;
            margin: 0 0 8px 0;
        }

        .home-section-subtitle {
            font-size: 13px;
            color: #9ca3af;
            margin: 0 0 16px 0;
        }

        .home-articles-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) minmax(0, 1.4fr);
            gap: 16px;
        }

        .home-article-card {
            background: radial-gradient(circle at top, rgba(30, 64, 175, 0.35), rgba(15, 23, 42, 0.98));
            border-radius: 16px;
            padding: 12px 13px 10px;
            border: 1px solid rgba(148, 163, 184, 0.55);
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.9);
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .home-article-header {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: flex-start;
        }

        .home-article-title {
            font-size: 14px;
            font-weight: 700;
            margin: 0 0 3px 0;
            color: #e5e7eb;
        }

        .home-article-title a {
            color: inherit;
            text-decoration: none;
        }

        .home-article-title a:hover {
            text-decoration: underline;
        }

        .home-article-meta {
            font-size: 11px;
            color: #9ca3af;
        }

        .home-article-tag {
            display: inline-block;
            font-size: 11px;
            padding: 2px 7px;
            border-radius: 999px;
            background: rgba(30, 64, 175, 0.65);
            color: #e0f2fe;
        }

        .home-article-body {
            font-size: 12px;
            color: #cbd5f5;
            max-height: 70px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .home-article-footer {
            margin-top: 6px;
            display: flex;
            justify-content: flex-end;
        }

        .home-article-link {
            font-size: 11px;
            color: #a5b4fc;
            text-decoration: none;
        }

        .home-article-link:hover {
            text-decoration: underline;
        }

        .home-empty {
            font-size: 13px;
            color: #9ca3af;
            padding: 10px 0 4px;
        }

        @media (max-width: 900px) {
            .home-articles-grid {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        @media (max-width: 600px) {
            .home-hero {
                padding: 16px 14px 14px;
                border-radius: 18px;
            }

            .home-hero-title {
                font-size: 20px;
            }
        }
    </style>
</head>
<body class="index-page">

<?php include "includes/header.php"; ?>

<main class="content home-content">

    <section class="home-hero">
        <h1 class="home-hero-title">Vento Blog</h1>
        <p class="home-hero-subtitle">
            در اینجا می‌توانید جدیدترین مقالات تأیید شده را در حوزه‌های
            <span>تکنولوژی</span>، <span>سلامت</span>، <span>اقتصاد</span> و <span>هنر</span> بخوانید.
            فقط مقالاتی که توسط مدیر سایت تأیید شده‌اند در این صفحه نمایش داده می‌شوند.
        </p>
        <div class="home-hero-tags">
            <span class="home-tag-pill">مقالات تأیید شده</span>
            <span class="home-tag-pill">تکنولوژی · سلامت · اقتصاد · هنر</span>
        </div>
    </section>

    <section class="home-articles-wrapper">
        <h2 class="home-section-title">آخرین مقالات منتشر شده</h2>
        <p class="home-section-subtitle">
            لیست زیر فقط شامل مقالاتی است که ادمین وضعیت آن‌ها را تأیید و منتشر کرده است.
        </p>

        <?php if (empty($articles)): ?>
            <div class="home-empty">
                هنوز هیچ مقاله تأیید و منتشر نشده است. به‌زودی محتواهای جدید در این بخش قرار می‌گیرند.
            </div>
        <?php else: ?>
            <div class="home-articles-grid">
                <?php foreach ($articles as $article): ?>
                    <?php
                    $id       = (int)$article['id'];
                    $title    = $article['title'] ?? 'بدون عنوان';
                    $content  = $article['content'] ?? '';
                    $category = $article['category'] ?? '';
                    $slug     = $article['slug'] ?? '';

                    $date_disp = '';
                    if (!empty($article['created_at'])) {
                        $ts        = strtotime($article['created_at']);
                        $date_disp = $ts ? date('Y/m/d', $ts) : '';
                    }

                    $excerpt = '';
                    if ($content !== '') {
                        $excerpt = mb_substr($content, 0, 140, 'UTF-8');
                        if (mb_strlen($content, 'UTF-8') > 140) {
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
                    <article class="home-article-card">
                        <div class="home-article-header">
                            <div>
                                <h3 class="home-article-title">
                                    <a href="<?php echo htmlspecialchars($view_link); ?>">
                                        <?php echo htmlspecialchars($title); ?>
                                    </a>
                                </h3>
                                <div class="home-article-meta">
                                    <?php if ($date_disp): ?>
                                        <span>تاریخ: <?php echo htmlspecialchars($date_disp); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if ($categoryLabel): ?>
                                <span class="home-article-tag">
                                    <?php echo htmlspecialchars($categoryLabel); ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <?php if ($excerpt): ?>
                            <div class="home-article-body">
                                <?php echo nl2br(htmlspecialchars($excerpt)); ?>
                            </div>
                        <?php endif; ?>

                        <div class="home-article-footer">
                            <a href="<?php echo htmlspecialchars($view_link); ?>" class="home-article-link">
                                مطالعه مقاله
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
