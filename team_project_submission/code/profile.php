<!-- @krystable -->
<?php


if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include('database.php');

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please login first!'); window.location.href = 'login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];


$client_sql = "SELECT * FROM client WHERE clientId = ?";
$client_stmt = $conn->prepare($client_sql);
$client_stmt->bind_param("i", $user_id);
$client_stmt->execute();
$client_result = $client_stmt->get_result();
$client_data = $client_result->fetch_assoc();

if (!$client_data) {
    die("Client not found!");
}


$following_count_sql = "SELECT COUNT(*) as following_count FROM follows WHERE client_id = ?";
$following_count_stmt = $conn->prepare($following_count_sql);
$following_count_stmt->bind_param("i", $user_id);
$following_count_stmt->execute();
$following_count_result = $following_count_stmt->get_result();
$following_count_data = $following_count_result->fetch_assoc();
$following_count = $following_count_data['following_count'];

//Get favorites
$favorites_result = null;
$favorites_count = 0;
$has_favorites = false;

$check_favorite = $conn->query("SHOW TABLES LIKE 'favourite'");
$check_recipes = $conn->query("SHOW TABLES LIKE 'recipes'");

if ($check_favorite->num_rows > 0 && $check_recipes->num_rows > 0) {
    $favorites_sql = "SELECT recipes.* FROM favourite 
                      JOIN recipes ON favourite.recipeId = recipes.recipeId 
                      WHERE favourite.clientId = ? 
                      ORDER BY favourite.created_at DESC";
    $favorites_stmt = $conn->prepare($favorites_sql);
    
    if ($favorites_stmt) {
        $favorites_stmt->bind_param("i", $user_id);
        $favorites_stmt->execute();
        $favorites_result = $favorites_stmt->get_result();
        $has_favorites = ($favorites_result && $favorites_result->num_rows > 0);
    }
    
    $favorites_count_sql = "SELECT COUNT(*) as count FROM favourite WHERE clientId = ?";
    $favorites_count_stmt = $conn->prepare($favorites_count_sql);
    
    if ($favorites_count_stmt) {
        $favorites_count_stmt->bind_param("i", $user_id);
        $favorites_count_stmt->execute();
        $favorites_count_result = $favorites_count_stmt->get_result();
        $favorites_count_data = $favorites_count_result->fetch_assoc();
        $favorites_count = $favorites_count_data['count'];
    }
} else {
    $favorites_count = 0;
    $has_favorites = false;
}

$username = isset($client_data['username']) ? $client_data['username'] : 
           (isset($client_data['name']) ? $client_data['name'] : 
           (isset($client_data['email']) ? $client_data['email'] : 'User'));

$profile_picture = isset($client_data['profile_picture']) ? $client_data['profile_picture'] : '';
$bio = isset($client_data['bio']) ? $client_data['bio'] : 'Food enthusiast';
$follower_count = isset($client_data['follower_count']) ? $client_data['follower_count'] : 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile - RecipeBook</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
        }
        
        .profile-box {
            width: 90%;
            max-width: 900px;
            background: white;
            padding: 20px 30px;
            border-radius: 12px;
            box-shadow: 0px 3px 8px rgba(0,0,0,0.1);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-pic {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            border: 3px solid #ff8a00;
        }

        .profile-header h2 {
            margin: 10px 0 5px;
            font-size: 24px;
        }

        .profile-header .bio {
            color: #777;
            font-size: 14px;
        }

        .back-btn {
            background: #ff8a00;
            border: none;
            padding: 10px 20px;
            color: white;
            font-size: 14px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
        }

        .section-title {
            margin: 20px 0 10px;
            font-size: 20px;
        }

        .profile-stats {
            display: flex;
            gap: 30px;
            margin: 20px 0;
            justify-content: center;
        }
        
        .stat {
            text-align: center;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #ff8a00;
        }
        
        .stat-label {
            font-size: 14px;
            color: #666;
        }

        /* Favorites Section */
        .favorites-section {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .fav-card {
            background: #fff;
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            box-shadow: 0px 3px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .fav-card:hover {
            transform: translateY(-2px);
            box-shadow: 0px 5px 15px rgba(0,0,0,0.2);
        }

        .recipe-img {
            width: 100%;
            height: 120px;
            border-radius: 10px;
            object-fit: cover;
            margin-bottom: 10px;
            max-width: 100%;
        }

        .recipe-title {
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0;
            color: #333;
        }

        .recipe-description {
            color: #777;
            font-size: 14px;
            margin-bottom: 15px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .remove-btn {
            background: #d9534f;
            border: none;
            padding: 8px 12px;
            color: white;
            font-size: 14px;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            transition: background 0.3s;
            margin-top: 5px;
        }

        .remove-btn:hover {
            background: #c9302c;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #777;
            background: #f9f9f9;
            border-radius: 10px;
            margin: 20px 0;
        }

        .view-recipe-btn {
            background: #4CAF50;
            border: none;
            padding: 8px 1px;
            color: white;
            font-size: 14px;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            margin-bottom: 8px;
            transition: background 0.3s;
            text-decoration: none;
            display: block;
            text-align: center;
        }

        .view-recipe-btn:hover {
            background: #45a049;
        }
    </style>
</head>
<body>
    <div class="profile-box">
        <a href="homepage.php" class="back-btn">← Back to Home</a>

        <div class="profile-header">
            <?php if (!empty($profile_picture)): ?>
                <img src="<?php echo htmlspecialchars($profile_picture); ?>" 
                     alt="Profile Picture" class="profile-pic">
            <?php else: ?>
                <div class="profile-pic" style="background: #ddd; display: flex; align-items: center; justify-content: center; color: #777;">👤</div>
            <?php endif; ?>
            
            <div class="profile-info">
                <h2><?php echo htmlspecialchars($username); ?></h2>
                <p class="bio"><?php echo htmlspecialchars($bio); ?></p>
                
                <div class="profile-stats">
                    <div class="stat">
                        <div class="stat-number"><?php echo $favorites_count; ?></div>
                        <div class="stat-label">Favorites</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number"><?php echo $following_count; ?></div>
                        <div class="stat-label">Following</div>
                    </div>
                    
                </div>
            </div>
        </div>

        <div class="section-title">❤️ My Favorite Recipes</div>
        
        <?php if ($has_favorites): ?>
            <div class="favorites-section">
                <?php while ($recipe = $favorites_result->fetch_assoc()): ?>
                    <div class="fav-card">
                        <?php if (!empty($recipe['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($recipe['image_url']?? ''); ?>" 
                                 alt="<?php echo htmlspecialchars($recipe['title']?? 'Recipe'); ?>" 
                                 class="recipe-img">
                        <?php else: ?>
                            <div class="recipe-img" style="background: #ddd; display: flex; align-items: center; justify-content: center; color: #777;">🍳</div>
                        <?php endif; ?>
                        
                        <h3 class="recipe-title"><?php echo htmlspecialchars($recipe['title']); ?></h3>
                        <p class="recipe-description">
                            <?php 
                            $description = $recipe['description'];
                            echo htmlspecialchars(strlen($description) > 80 ? substr($description, 0, 80) . '...' : $description);
                            ?>
                        </p>
                        
                        <a href="recipe_detail.php?id=<?php echo $recipe['recipeId']?? ''; ?>" class="view-recipe-btn">
                            View Recipe
                        </a>
                        <button class="remove-btn favorite-btn" data-recipe-id="<?php echo $recipe['recipeId']?? ''; ?>">
                            🗑 Remove
                        </button>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <p>No favorite recipes yet!</p>
                <p>Start exploring and add some recipes to your favorites.</p>
                <a href="recipes.php" style="color: #ff8a00; text-decoration: none; font-weight: bold;">Browse Recipes →</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        const favoriteButtons = document.querySelectorAll('.favorite-btn');
        
        favoriteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const recipeId = this.getAttribute('data-recipe-id');
                const card = this.closest('.fav-card');
                
                if (!confirm('Are you sure you want to remove this recipe from favorites?')) {
                    return;
                }
                
                fetch('addFavorite.php?recipeId=' + recipeId)
                    .then(response => response.text())
                    .then(data => {
                        if (data === 'removed') {
                            card.remove();
                            
                            const favoritesCount = document.querySelector('.stat:nth-child(1) .stat-number');
                            favoritesCount.textContent = parseInt(favoritesCount.textContent) - 1;
                            
                            if (document.querySelectorAll('.fav-card').length === 0) {
                                location.reload();
                            }
                        } else if (data === 'error') {
                            alert('Error removing from favorites!');
                        } else if (data === 'not_logged_in') {
                            alert('Please login again!');
                            window.location.href = 'login.php';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Network error! Please try again.');
                    });
            });
        });
    </script>
</body>
</html>
<?php
if(isset($conn)) {
    $conn->close();
}