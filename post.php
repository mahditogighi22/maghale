 <?php
include "config/db.php";
include "includes/header.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sql = "SELECT a.*, u.display_name, c.name AS category
        FROM articles a
        JOIN users u ON a.author_id = u.id
        LEFT JOIN article_categories ac ON a.id = ac.article_id
        LEFT JOIN categories c ON ac.category_id = c.id
        WHERE a.id = $id AND a.status='published'";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0):
    $post = $result->fetch_assoc();
?>
    <article class="post-detail">
        <h2><?= htmlspecialchars($post['title']) ?></h2>
        <p><em>نویسنده: <?= htmlspecialchars($post['display_name']) ?> | دسته: <?= htmlspecialchars($post['category']) ?></em></p>
        <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
    </article>
<?php
else:
    echo "<p>مقاله مورد نظر یافت نشد.</p>";
endif;

include "includes/footer.php";
?>

