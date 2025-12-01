<?php
// delete_article.php

session_start();

// فقط مدیر
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// چک id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$articleId = (int) $_GET['id'];

include 'db.php';

// حذف کامل
$sql = "DELETE FROM articles WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $articleId);
    $stmt->execute();
    $stmt->close();
}

header("Location: admin_dashboard.php");
exit();
