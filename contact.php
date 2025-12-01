<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تماس با ما | Vento Blog</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body.contact-page {
            margin: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI",
            "IRANSans", "Vazirmatn", sans-serif;
            background: radial-gradient(circle at top, #020617, #020617);
            color: #e5e7eb;
        }

        .contact-content {
            padding-top: 20px;
            padding-bottom: 32px;
        }

        .contact-hero {
            background: radial-gradient(circle at top, rgba(52, 211, 153, 0.14), transparent),
                        radial-gradient(circle at top left, rgba(96, 165, 250, 0.18), transparent);
            border-radius: 20px;
            padding: 24px 20px;
            margin-bottom: 24px;
            border: 1px solid rgba(148, 163, 184, 0.4);
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.8);
            position: relative;
            overflow: hidden;
        }

        .contact-hero::before {
            content: "";
            position: absolute;
            inset-inline-start: -60px;
            top: -80px;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.35), transparent);
            opacity: 0.8;
        }

        .contact-title {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .contact-subtitle {
            font-size: 14px;
            color: #cbd5f5;
            margin-bottom: 16px;
            position: relative;
            z-index: 1;
            line-height: 1.9;
        }

        .contact-meta {
            font-size: 12px;
            color: #9ca3af;
            position: relative;
            z-index: 1;
        }

        .contact-meta a {
            color: #a5b4fc;
            text-decoration: none;
        }

        .contact-meta a:hover {
            text-decoration: underline;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) minmax(0, 2fr);
            gap: 18px;
        }

        .contact-card {
            background: rgba(15, 23, 42, 0.96);
            border-radius: 18px;
            padding: 16px 16px 14px;
            border: 1px solid rgba(148, 163, 184, 0.45);
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.8);
        }

        .contact-card h3 {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .contact-card p {
            font-size: 13px;
            color: #d1d5db;
            line-height: 1.9;
            margin-bottom: 6px;
        }

        .contact-info-list {
            list-style: none;
            padding: 0;
            margin: 6px 0 0 0;
        }

        .contact-info-list li {
            font-size: 13px;
            color: #e5e7eb;
            margin-bottom: 4px;
            position: relative;
            padding-right: 14px;
        }

        .contact-info-list li::before {
            content: "•";
            position: absolute;
            right: 0;
            top: 0;
            color: #34d399;
        }

        .contact-info-list a {
            color: #a5b4fc;
            text-decoration: none;
        }

        .contact-info-list a:hover {
            text-decoration: underline;
        }

        .contact-form {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 4px;
        }

        .contact-form-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .contact-form-group label {
            font-size: 13px;
            color: #e5e7eb;
        }

        .contact-form-group input,
        .contact-form-group textarea {
            width: 100%;
            padding: 9px 10px;
            border-radius: 12px;
            border: 1px solid rgba(148, 163, 184, 0.7);
            background: rgba(15, 23, 42, 0.94);
            color: #f9fafb;
            font-size: 13px;
            outline: none;
            resize: vertical;
            min-height: 40px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .contact-form-group textarea {
            min-height: 110px;
        }

        .contact-form-group input::placeholder,
        .contact-form-group textarea::placeholder {
            color: #6b7280;
        }

        .contact-form-group input:focus,
        .contact-form-group textarea:focus {
            border-color: #22c55e;
            box-shadow: 0 0 0 1px rgba(34, 197, 94, 0.7);
            background: rgba(15, 23, 42, 1);
        }

        .contact-submit-btn {
            align-self: flex-start;
            border-radius: 999px;
            padding: 9px 18px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            outline: none;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: #f9fafb;
            box-shadow: 0 14px 32px rgba(16, 185, 129, 0.75);
            transition: transform 0.1s ease, box-shadow 0.15s ease, opacity 0.15s ease;
        }

        .contact-submit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 20px 40px rgba(16, 185, 129, 0.9);
            opacity: 0.98;
        }

        .contact-submit-btn:active {
            transform: translateY(1px);
            box-shadow: 0 10px 24px rgba(16, 185, 129, 0.8);
        }

        .contact-note {
            font-size: 11px;
            color: #9ca3af;
            margin-top: 6px;
        }

        @media (max-width: 900px) {
            .contact-grid {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        @media (max-width: 768px) {
            .contact-hero {
                padding: 20px 16px;
            }

            .contact-title {
                font-size: 22px;
            }
        }

        @media (max-width: 480px) {
            .contact-hero {
                padding: 18px 14px;
            }

            .contact-title {
                font-size: 20px;
            }

            .contact-subtitle {
                font-size: 13px;
            }
        }
    </style>
</head>
<body class="contact-page">

<?php include "includes/header.php"; ?>

<main class="content contact-content">

    <section class="contact-hero">
        <h2 class="contact-title">تماس با ما</h2>
        <p class="contact-subtitle">
            اگر پیشنهادی برای بهتر شدن Vento Blog دارید، مشکلی در استفاده از سایت مشاهده کرده‌اید
            یا قصد همکاری و ارتباط دارید، از طریق فرم زیر یا ایمیل با ما در تماس باشید.
        </p>
        <div class="contact-meta">
            راه ارتباط مستقیم:
            <a href="mailto:info@vento.ir">info@vento.ir</a>
        </div>
    </section>

    <section class="contact-grid">
        <div class="contact-card">
            <h3>اطلاعات تماس</h3>
            <p>
                برای ارتباط سریع، می‌توانید از ایمیل استفاده کنید. پیام شما در کوتاه‌ترین زمان ممکن بررسی خواهد شد.
            </p>
            <ul class="contact-info-list">
                <li>
                    ایمیل پشتیبانی:
                    <a href="mailto:info@vento.ir">info@vento.ir</a>
                </li>
                <li>
                    زمان پاسخ‌گویی: همه روزه به‌صورت آنلاین
                </li>
                <li>
                    موضوعات: پیشنهاد، گزارش خطا، همکاری در تولید محتوا
                </li>
            </ul>
        </div>

        <div class="contact-card">
            <h3>ارسال پیام</h3>
            <!-- این فرم فعلاً فقط ظاهری است؛ اگر بخواهی می‌توانیم بعداً پردازشش را هم با PHP اضافه کنیم -->
            <form class="contact-form" method="post" action="mailto:info@vento.ir" enctype="text/plain">
                <div class="contact-form-group">
                    <label for="name">نام شما:</label>
                    <input type="text" id="name" name="name" placeholder="نام و نام خانوادگی">
                </div>

                <div class="contact-form-group">
                    <label for="email">ایمیل شما:</label>
                    <input type="email" id="email" name="email" placeholder="example@email.com">
                </div>

                <div class="contact-form-group">
                    <label for="message">متن پیام:</label>
                    <textarea id="message" name="message" placeholder="سؤال، پیشنهاد یا توضیح خود را بنویسید..."></textarea>
                </div>

                <button type="submit" class="contact-submit-btn">ارسال پیام</button>
                
            </form>
        </div>
    </section>

</main>

<?php include "includes/footer.php"; ?>

</body>
</html>
