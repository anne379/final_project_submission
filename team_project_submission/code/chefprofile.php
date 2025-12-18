<?php


if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include('database.php');


if (!isset($_GET['id'])) {
    echo "<script>alert('Chef ID not specified!'); window.location.href = 'allchefs.php';</script>";
    exit();
}

$chef_id = $_GET['id'];
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($user_id) {
        if ($_POST['action'] === 'follow') {
           
            $check_sql = "SELECT * FROM follows WHERE client_id = ? AND chef_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("ii", $user_id, $chef_id);
            $check_stmt->execute();
            
            if ($check_stmt->get_result()->num_rows === 0) {
                
                $follow_sql = "INSERT INTO follows (client_id, chef_id) VALUES (?, ?)";
                $follow_stmt = $conn->prepare($follow_sql);
                $follow_stmt->bind_param("ii", $user_id, $chef_id);
                $follow_stmt->execute();
            }
        } elseif ($_POST['action'] === 'unfollow') {
           
            $unfollow_sql = "DELETE FROM follows WHERE client_id = ? AND chef_id = ?";
            $unfollow_stmt = $conn->prepare($unfollow_sql);
            $unfollow_stmt->bind_param("ii", $user_id, $chef_id);
            $unfollow_stmt->execute();
        }
    }
    exit(); 
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    if ($user_id) {
        $message = $_POST['message'];
        $message_sql = "INSERT INTO inquiries (clientId, chefId, message) VALUES (?, ?, ?)";
        $message_stmt = $conn->prepare($message_sql);
        $message_stmt->bind_param("iis", $user_id, $chef_id, $message);
        
        if ($message_stmt->execute()) {
            $message_success = "Message sent successfully!";
        } else {
            $message_error = "Failed to send message.";
        }
    } else {
        $message_error = "Please log in to send messages.";
    }
}


$chef_sql = "SELECT * FROM chef WHERE chefId = ?";
$chef_stmt = $conn->prepare($chef_sql);
$chef_stmt->bind_param("i", $chef_id);
$chef_stmt->execute();
$chef_result = $chef_stmt->get_result();
$chef_data = $chef_result->fetch_assoc();

if (!$chef_data) {
    echo "<script>alert('Chef not found!'); window.location.href = 'allchefs.php';</script>";
    exit();
}

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

$is_following = false;
if ($user_id) {
    $check_follow_sql = "SELECT * FROM follows WHERE client_id = ? AND chef_id = ?";
    $check_follow_stmt = $conn->prepare($check_follow_sql);
    $check_follow_stmt->bind_param("ii", $user_id, $chef_id);
    $check_follow_stmt->execute();
    $is_following = ($check_follow_stmt->get_result()->num_rows > 0);
}


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
        <a href="allchefs.php" class="back-btn">
            ← Back to Chefs
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

                    <div class="chef-buttons">
                        <?php if ($user_id): ?>
                            <button class="but <?php echo $is_following ? 'following-btn' : 'follow-btn'; ?>" 
                                    id="follow-btn"
                                    data-chef-id="<?php echo $chef_id; ?>"
                                    data-action="<?php echo $is_following ? 'unfollow' : 'follow'; ?>">
                                <?php if ($is_following): ?>
                                    ✓ Following
                                <?php else: ?>
                                    + Follow
                                <?php endif; ?>
                            </button>
                        <?php else: ?>
                            <button class="but follow-btn" onclick="alert('Please log in to follow chefs')">
                                + Follow
                            </button>
                        <?php endif; ?>
                        
                        <button class="but message-btn" id="message-btn">
                            💬 Message
                        </button>
                    </div>
                </div>
            </div>

        
            <div class="modal" id="message-modal">
                <div class="modal-content">
                    <button class="close-modal" id="close-modal">&times;</button>
                    <h2>Message <?php echo htmlspecialchars($chef_name); ?></h2>
                    
                    <?php if (isset($message_success)): ?>
                        <div class="message-alert success"><?php echo $message_success; ?></div>
                    <?php elseif (isset($message_error)): ?>
                        <div class="message-alert error"><?php echo $message_error; ?></div>
                    <?php endif; ?>

                    <form class="message-form" method="POST">
                        <textarea name="message" placeholder="Write your message to <?php echo htmlspecialchars($chef_name); ?>..." required></textarea>
                        <button type="submit">Send Message</button>
                    </form>
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
                                        <a href="recipe_detail.php?id=<?php echo $recipe['recipeId']; ?>" class="view-recipe-btn">
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

    <script>
       
        document.getElementById('follow-btn')?.addEventListener('click', function() {
            const chefId = this.getAttribute('data-chef-id');
            const action = this.getAttribute('data-action');
            const button = this;
            
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=' + action
            })
            .then(response => response.text())
            .then(() => {
               
                if (action === 'follow') {
                    button.classList.remove('follow-btn');
                    button.classList.add('following-btn');
                    button.innerHTML = '✓ Following';
                    button.setAttribute('data-action', 'unfollow');
                    
                   
                    const followerCount = document.getElementById('follower-count');
                    followerCount.textContent = parseInt(followerCount.textContent) + 1;
                } else {
                    button.classList.remove('following-btn');
                    button.classList.add('follow-btn');
                    button.innerHTML = '+ Follow';
                    button.setAttribute('data-action', 'follow');
                    
                  
                    const followerCount = document.getElementById('follower-count');
                    followerCount.textContent = parseInt(followerCount.textContent) - 1;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });

    
        const messageBtn = document.getElementById('message-btn');
        const messageModal = document.getElementById('message-modal');
        const closeModal = document.getElementById('close-modal');

        if (messageBtn) {
            messageBtn.addEventListener('click', function() {
                <?php if (!$user_id): ?>
                    alert('Please log in to send messages.');
                <?php else: ?>
                    messageModal.style.display = 'flex';
                <?php endif; ?>
            });
        }

        if (closeModal) {
            closeModal.addEventListener('click', function() {
                messageModal.style.display = 'none';
            });
        }

        window.addEventListener('click', function(event) {
            if (event.target === messageModal) {
                messageModal.style.display = 'none';
            }
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>