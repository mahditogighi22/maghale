 <?php
session_start();
header('Content-Type: application/json');

// بررسی اینکه نقش کاربر "author" باشد
if ($_SESSION['role'] != 'author') {
    echo json_encode(['message' => 'دسترسی غیرمجاز']);
    exit();
}

// دریافت مقالات نویسنده از دیتابیس
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM articles WHERE author_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// ارسال مقالات در قالب JSON
$articles = [];
while ($row = $result->fetch_assoc()) {
    $articles[] = $row;
}

echo json_encode($articles);
?>

