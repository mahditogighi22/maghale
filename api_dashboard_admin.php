<?php
session_start();
header('Content-Type: application/json');

// بررسی اینکه نقش کاربر "admin" باشد
if ($_SESSION['role'] != 'admin') {
    echo json_encode(['message' => 'دسترسی غیرمجاز']);
    exit();
}

// دریافت مقالات در انتظار تایید از دیتابیس
$sql = "SELECT * FROM articles WHERE status = 'pending'";
$result = $conn->query($sql);

// ارسال مقالات در قالب JSON
$articles = [];
while ($row = $result->fetch_assoc()) {
    $articles[] = $row;
}

echo json_encode($articles);
?>
