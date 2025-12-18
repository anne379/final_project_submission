
<!-- @anne379 -->

<?php


if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include('database.php');


if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Chef ID not specified!'); window.location.href = 'chefpage.php';</script>";
    exit();
}


$chef_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;








$chef_sql = "SELECT * FROM chef WHERE chefId = ?";
$chef_stmt = $conn->prepare($chef_sql);
$chef_stmt->bind_param("i", $chef_id);
$chef_stmt->execute();
$chef_result = $chef_stmt->get_result();
$chef_data = $chef_result->fetch_assoc();



$recipes_sql = "SELECT * FROM recipes WHERE chefId = ? ORDER BY created_at DESC";
$recipes_stmt = $conn->prepare($recipes_sql);
$recipes_stmt->bind_param("i", $chef_id);
$recipes_stmt->execute();
$recipes_result = $recipes_stmt->get_result();
$has_recipes = ($recipes_result && $recipes_result->num_rows > 0);
$recipes_count = $has_recipes ? $recipes_result->num_rows : 0;

$follow_count_sql = "SELECT COUNT(*) as follow_count FROM follows WHERE chef_id = ?";
$follow_count_stmt = $conn->prepare($follow_count_sql);
$follow_count_stmt->bind_param("i", $chef_id);
$follow_count_stmt->execute();
$follow_count_result = $follow_count_stmt->get_result();
$follow_count_data = $follow_count_result->fetch_assoc();
$follower_count = $follow_count_data['follow_count'];




$chef_name = isset($chef_data['name']) ? $chef_data['name'] : 'Chef';
$profile_picture = isset($chef_data['profile_image']) ? $chef_data['profile_image'] : '';
$bio = isset($chef_data['bio']) ? $chef_data['bio'] : 'Passionate chef creating delicious recipes';
$specialty = isset($chef_data['specialty']) ? $chef_data['specialty'] : 'Various Cuisines';
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($chef_name); ?> - Chef Profile | Delisssh</title>
    <link rel ="stylesheet" href="chefprofile.css">
</head>
<body>
    <div class="container">
        <a href="chefpage.php" class="back-btn">
            ← Back Home
        </a>

        <div class="profile-box">
         
            <div class="profile-header">
                <div class="profile-pic-container">
                    <?php if (!empty($profile_picture)): ?>
                        <img src="<?php echo htmlspecialchars($profile_picture); ?>" 
                             alt="<?php echo htmlspecialchars($chef_name); ?>" 
                             class="profile-pic">
                    <?php else: ?>
                        <div class="profile-pic" style="background: var(--gradient); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;">
                            👨‍🍳
                        </div>
                    <?php endif; ?>
                    <div class="chef-badge">⭐</div>
                </div>
                
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($chef_name); ?></h1>
                    <div class="specialty">
                        🍳 <?php echo htmlspecialchars($specialty); ?>
                    </div>
                    <p class="bio"><?php echo htmlspecialchars($bio); ?></p>
                    
                    <div class="profile-stats">
                        <div class="stat">
                            <div class="stat-number"><?php echo $recipes_count; ?></div>
                            <div class="stat-label">Recipes</div>
                        </div>
                        <div class="stat">
                            <div class="stat-number" id="follower-count"><?php echo $follower_count; ?></div>
                            <div class="stat-label">Followers</div>
                        </div>
                    </div>

                    
                </div>
            </div>

          
           

            
            <div class="content-section">
                <h2 class="section-title">🍽️ Chef's Recipes</h2>
                
                <?php if ($has_recipes): ?>
                    <div class="recipes-grid">
                        <?php while ($recipe = $recipes_result->fetch_assoc()): ?>
                            <div class="recipe-card">
                                <?php if (!empty($recipe['image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($recipe['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($recipe['title']); ?>" 
                                         class="recipe-image">
                                <?php else: ?>
                                    <div class="recipe-image" style="background: var(--gradient); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;">
                                        🍳
                                    </div>
                                <?php endif; ?>
                                
                                <div class="recipe-info">
                                    <h3 class="recipe-title"><?php echo htmlspecialchars($recipe['title']); ?></h3>
                                    <p class="recipe-description">
                                        <?php 
                                        $description = $recipe['description'] ?? 'A great delicious recipe by ' . $chef_name;
                                        echo htmlspecialchars(strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description);
                                        ?>
                                    </p>
                                    
                                    <div class="recipe-meta">
                                        <a href="chef_recipe.php?id=<?php echo $recipe['recipeId']; ?>" class="view-recipe-btn">
                                            View Recipe
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty">
                        <div class="empty-icon">🍽️</div>
                        <h3>No Recipes Yet</h3>
                        <p><?php echo htmlspecialchars($chef_name); ?> hasn't shared any recipes yet.</p>
                        <p>Check back later for delicious creations!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    
</body>
</html>
<?php
$conn->close();
?>