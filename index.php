 <?php
include "config/db.php";
include "includes/header.php";

$sql = "SELECT a.id, a.title, a.excerpt, a.slug, a.published_at, u.display_name, c.name AS category
        FROM articles a
        JOIN users u ON a.author_id = u.id
        LEFT JOIN article_categories ac ON a.id = ac.article_id
        LEFT JOIN categories c ON ac.category_id = c.id
        WHERE a.status='published'
        ORDER BY a.published_at DESC";

$result = $conn->query($sql);

if ($result->num_rows > 0):
    echo "<h2>آخرین مقالات</h2>";
    while($row = $result->fetch_assoc()):
?>
        <article class="post">
            <h3><a href="post.php?id=<?= $row['id'] ?>"><?= htmlspecialchars($row['title']) ?></a></h3>
            <p><?= htmlspecialchars($row['excerpt']) ?></p>
            <small>نویسنده: <?= htmlspecialchars($row['display_name']) ?> | دسته: <?= htmlspecialchars($row['category']) ?></small>
        </article>
<?php
    endwhile;
else:
    echo "<p>هیچ مقاله‌ای یافت نشد.</p>";
endif;

include "includes/footer.php";
?>
