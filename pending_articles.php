 <?php
session_start();

// بررسی اینکه نقش کاربر "admin" باشد
if ($_SESSION['role'] != 'admin') {
    header("Location: index.php"); // اگر نقش کاربر مدیر نباشد، به صفحه اصلی هدایت می‌شود
    exit();
}

// نمایش مقالات در انتظار تایید
$sql = "SELECT * FROM articles WHERE status = 'pending'";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    echo "<div>" . $row['title'] . " - <a href='approve_article.php?id=" . $row['id'] . "'>تایید</a> | <a href='reject_article.php?id=" . $row['id'] . "'>رد</a></div>";
}
?>

