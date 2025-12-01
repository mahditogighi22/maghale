<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>درباره ما | Vento Blog</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body.about-page {
            margin: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI",
            "IRANSans", "Vazirmatn", sans-serif;
            background: radial-gradient(circle at top, #020617, #020617);
            color: #e5e7eb;
        }

        .about-content {
            padding-top: 20px;
            padding-bottom: 32px;
        }

        .about-hero {
            background: radial-gradient(circle at top, rgba(56, 189, 248, 0.12), transparent),
                        radial-gradient(circle at top left, rgba(129, 140, 248, 0.2), transparent);
            border-radius: 20px;
            padding: 24px 20px;
            margin-bottom: 24px;
            border: 1px solid rgba(148, 163, 184, 0.4);
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.8);
            position: relative;
            overflow: hidden;
        }

        .about-hero::before {
            content: "";
            position: absolute;
            inset-inline-end: -60px;
            top: -80px;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(96, 165, 250, 0.35), transparent);
            opacity: 0.8;
        }

        .about-title {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .about-subtitle {
            font-size: 14px;
            color: #cbd5f5;
            margin-bottom: 16px;
            position: relative;
            z-index: 1;
            line-height: 1.9;
        }

        .about-meta {
            font-size: 12px;
            color: #9ca3af;
            position: relative;
            z-index: 1;
        }

        .about-meta span {
            display: inline-block;
            margin-inline-start: 10px;
        }

        .about-grid {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(0, 1.4fr);
            gap: 18px;
        }

        .about-card {
            background: rgba(15, 23, 42, 0.96);
            border-radius: 18px;
            padding: 16px 16px 14px;
            border: 1px solid rgba(148, 163, 184, 0.45);
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.8);
        }

        .about-card h3 {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .about-card p {
            font-size: 13px;
            color: #d1d5db;
            line-height: 1.9;
            margin-bottom: 6px;
        }

        .about-list {
            list-style: none;
            padding: 0;
            margin: 4px 0 0 0;
        }

        .about-list li {
            font-size: 13px;
            color: #e5e7eb;
            margin-bottom: 4px;
            position: relative;
            padding-right: 14px;
        }

        .about-list li::before {
            content: "•";
            position: absolute;
            right: 0;
            top: 0;
            color: #a5b4fc;
        }

        .about-pill-list {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 8px;
        }

        .about-pill {
            font-size: 11px;
            padding: 4px 9px;
            border-radius: 999px;
            border: 1px solid rgba(148, 163, 184, 0.7);
            color: #e5e7eb;
            background: rgba(15, 23, 42, 0.95);
        }

        @media (max-width: 900px) {
            .about-grid {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        @media (max-width: 768px) {
            .about-hero {
                padding: 20px 16px;
            }

            .about-title {
                font-size: 22px;
            }
        }

        @media (max-width: 480px) {
            .about-hero {
                padding: 18px 14px;
            }

            .about-title {
                font-size: 20px;
            }

            .about-subtitle {
                font-size: 13px;
            }
        }
    </style>
</head>
<body class="about-page">

<?php include "includes/header.php"; ?>

<main class="content about-content">

    <section class="about-hero">
        <h2 class="about-title">درباره Vento Blog</h2>
        <p class="about-subtitle">
            Vento Blog یک پلتفرم ساده و مینیمال برای انتشار مقالات، یادداشت‌ها و اخبار است. هدف ما این است که
            نویسنده بدون درگیر شدن با پیچیدگی‌های فنی، روی محتوا تمرکز کند و خواننده نیز در یک محیط تمیز و بدون
            شلوغی، بتواند متن‌ها را دنبال کند.
        </p>
        <div class="about-meta">
            <span>تمرکز روی خوانایی و سادگی</span>
            <span>طراحی شده برای نویسنده‌ها و خواننده‌ها</span>
        </div>
    </section>

    <section class="about-grid">
        <div class="about-card">
            <h3>Vento Blog چه کار می‌کند؟</h3>
            <p>
                این وبسایت به شما امکان می‌دهد به عنوان نویسنده، بعد از ورود به حساب کاربری‌تان، مقالات خود را ثبت و
                ویرایش کنید و به‌روزرسانی‌های‌تان را با دیگران به اشتراک بگذارید. کاربران عادی نیز می‌توانند به‌سادگی
                آخرین مطالب منتشرشده را مرور کنند.
            </p>
            <ul class="about-list">
                <li>ثبت و انتشار مقالات متنی</li>
                <li>مدیریت نقش‌ها (مدیر، نویسنده، خواننده)</li>
                <li>رابط کاربری سبک و سریع برای خواندن محتوا</li>
            </ul>
        </div>

        <div class="about-card">
            <h3>چشم‌انداز و مسیر توسعه</h3>
            <p>
                Vento Blog به‌عنوان یک پروژه در حال توسعه، می‌تواند در آینده امکانات بیشتری مثل دسته‌بندی پیشرفته،
                تگ‌گذاری، جستجو در مقالات، و سیستم نظرات را نیز در خود جای دهد.
            </p>
            <div class="about-pill-list">
                <span class="about-pill">رشد تدریجی امکانات</span>
                <span class="about-pill">تمرکز بر تجربه کاربری</span>
                <span class="about-pill">زیرساخت ساده و قابل توسعه</span>
            </div>
        </div>
    </section>

</main>

<?php include "includes/footer.php"; ?>

</body>
</html>
