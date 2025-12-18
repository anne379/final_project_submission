<?php

include "database.php";
include "functions.php";

checkLogin('client');

function getAllChefs() {
    global $conn;

    $sql = "SELECT * FROM chef ORDER BY name ASC";
    $result = $conn->query($sql);

    $chef = [];
    while ($row = $result->fetch_assoc()) {
        $chef[] = $row;
    }

    return $chef;
}


$allChefs = getAllChefs();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Chefs | Delisssh</title>
    <link rel="stylesheet" href="allchefs.css">
</head>

<body>
    <div class="container">
        <a href="homepage.php" class="back-btn">← Back to Home</a>

        <div class="chefs-box">
            <header>
                <h1>Our Chefs</h1>
                <p class="subtitle">Meet the talented chefs behind these exceptional recipes</p>
            </header>

            <div class="chef-grid">
                <?php if (!empty($allChefs)): ?>
                    <?php foreach ($allChefs as $chef): ?>
                        <div class="chef-card">
                            <div class="chef-image">
                                <div class="chef-initial"><?= strtoupper(substr($chef['name'], 0, 1)) ?></div>
                            </div>
                            <h3 class="chef-name"><?= htmlspecialchars($chef['name']) ?></h3>
                            <a class="profile-btn" href="chefprofile.php?id=<?= $chef['chefId'] ?>">View Profile</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">👨‍🍳</div>
                        <p>No chefs have registered yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>