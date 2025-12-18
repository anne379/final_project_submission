<?php



include 'database.php';
include 'functions.php';
checkLogin('chef');

if(!isset($_SESSION['user_id'])) {
    
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$upload_message = "";


$chef_check_sql = "SELECT * FROM chef WHERE chefId = ?";
$chef_check_stmt = $conn->prepare($chef_check_sql);
$chef_check_stmt->bind_param("i", $user_id);
$chef_check_stmt->execute();
$chef_result = $chef_check_stmt->get_result();

if($chef_result->num_rows === 0) {
    die("Access denied. You are not registered as a chef. <a href='login.php'>Go back</a>");
}


$chef_data = $chef_result->fetch_assoc();
$chef_name = $chef_data['name'];


if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])){
    $title = $_POST['title'];
    $descr = $_POST['description'];
    $ingredients = $_POST['ingredients'];

    $target_dir = "uploads/";
    if(!is_dir($target_dir)){
        mkdir($target_dir, 0777, true);
    }

    if(isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if($check === false){
            $upload_message = "The file you uploaded is not an image.";
        } else {
            if ($_FILES["image"]["size"] > 5000000) {
                $upload_message = "Sorry, your file is too large.";
            } else {
                if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
                    $upload_message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                } else {
                    $new_filename = uniqid() . '.' . $imageFileType;
                    $target_file = $target_dir . $new_filename;
                    
                    if(move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                        $sql = "INSERT INTO recipes (chefId, title, ingredients, description, image_url) VALUES (?, ?, ?, ?, ?)";
                        $stmt = $conn->prepare($sql);
                        
                        if ($stmt) {
                            $stmt->bind_param("issss", $user_id, $title, $ingredients, $descr, $target_file);
                            
                            if($stmt->execute()) {
                                $upload_message = "Recipe uploaded successfully!";
                            } else {
                                $upload_message = "Database error occurred.";
                            }
                            $stmt->close();
                        } else {
                            $upload_message = "Failed to prepare statement.";
                        }
                    } else {
                        $upload_message = "Error uploading image.";
                    }
                }
            }
        }
    } else {
        $upload_message = "No image file uploaded.";
    }
}

$messages_sql = "SELECT  i.clientId, i.chefId, i.message, i.created_at,c.name,c.email
FROM inquiries i 
JOIN client c ON i.clientId = c.clientId 
WHERE i.chefId = ? 
ORDER BY i.created_at DESC";
$messages_stmt = $conn->prepare($messages_sql);
$messages_stmt->bind_param("i", $user_id);
$messages_stmt->execute();
$messages_result = $messages_stmt->get_result();
$has_messages = $messages_result->num_rows > 0;


$recipes_sql = "SELECT * FROM recipes WHERE chefId = ? ORDER BY created_at DESC";
$recipes_stmt = $conn->prepare($recipes_sql);
$recipes_stmt->bind_param("i", $user_id);
$recipes_stmt->execute();
$recipes_result = $recipes_stmt->get_result();
$has_recipes = $recipes_result->num_rows > 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chef Dashboard | Delisssh</title>
  <link rel="stylesheet" href="chefpage.css">
  <style>
    .message {
        padding: 15px;
        margin: 20px 0;
        border-radius: 5px;
        text-align: center;
        font-weight: bold;
    }
    .success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    .error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    .message-card {
        background: white;
        padding: 15px;
        margin: 10px 0;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        border-left: 4px solid #ff8a00;
    }
    .message-card strong {
        color: #333;
        display: block;
        margin-bottom: 5px;
        font-size: 1.1em;
    }
    .message-card p {
        color: #666;
        margin: 0;
        line-height: 1.4;
    }
    .message-meta {
        font-size: 0.8em;
        color: #999;
        margin-top: 8px;
        font-style: italic;
    }
    .no-messages {
        text-align: center;
        color: #666;
        padding: 40px 20px;
        background: #f9f9f9;
        border-radius: 8px;
        border: 2px dashed #ddd;
    }
    .recipe-card {
        background: white;
        padding: 15px;
        margin: 10px 0;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .recipe-card img {
        max-width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 5px;
        margin-bottom: 10px;
    }
    .recipe-meta {
        font-size: 0.8em;
        color: #999;
        margin-top: 8px;
    }
    .message-list {
        max-height: 400px;
        overflow-y: auto;
    }
  </style>
</head>
<body>
  <div class="sidebar">
    <div class="logo-container">
      <a href="#" class="logo">Deli<span>sssh</span></a>
    </div>

    <div class="nav-section">
      <ul class="nav-links">
        <li><a href="chefpage.php">Home</a></li>
        <li><a href="cprofile.php">Profile</a></li>
        <li><a href="logout.php">Logout</a></li>
      </ul>
    </div>

   
  </div>

  <div class="main-content">
    <section class="welcome">
      <h2>👋🏽 Welcome back, Chef <?php echo htmlspecialchars($chef_name); ?>!</h2>
      <p>Here's an overview of your recent activity and messages.</p>
    </section>

    <?php if(!empty($upload_message)): ?>
        <div class="message <?php echo (strpos($upload_message, 'successfully') !== false) ? 'success' : 'error'; ?>">
            <?php echo $upload_message; ?>
        </div>
    <?php endif; ?>

    <section class="messages">
      <h3>👥 People Who Contacted You</h3>
      <div id="message-list" class="message-list">
        <?php if ($has_messages): ?>
            <?php while ($message = $messages_result->fetch_assoc()): ?>
                <div class="message-card">
                    <strong>
                        <?php 
                        $sender_name = 'Anonymous User';
                        if (isset($message['name']) && !empty(trim($message['name']))) {
                          $sender_name = $message['name'];
                         
                        } else if (isset($message['email']) && !empty(trim($message['email']))) {
                        $email_parts = explode('@', $message['email']);
                        $sender_name = $email_parts[0];
                      }
                      
                      echo htmlspecialchars($sender_name);
                      

                        ?>
                    </strong>
                    <p><?php echo htmlspecialchars($message['message']); ?></p>
                    <p>Respond: <?php echo htmlspecialchars($message['email']); ?></p>
                    <div class="message-meta">
                        <?php echo date('M j, Y g:i A', strtotime($message['created_at'])); ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-messages">
                <p>No messages yet. Your messages from clients will appear here.</p>
            </div>
        <?php endif; ?>
      </div>
    </section>

    <section class="recipes">
      <h3>🍲 Your Recipes</h3>
      <form id="recipe-form" method="POST" action="" enctype="multipart/form-data">
        <input type="text" id="recipe-title" name="title" placeholder="Recipe Title" required>
        <textarea name="ingredients" placeholder="Recipe Ingredients (one per line)" required></textarea>
        <textarea id="recipe-desc" name="description" placeholder="Recipe Description" required></textarea>
        <input type="file" id="recipe-img" name="image" accept="image/*" required>
        <button type="submit">Add Recipe</button>
      </form>

      <div id="recipe-list" class="recipe-list">
        <?php if ($has_recipes): ?>
            <?php while ($recipe = $recipes_result->fetch_assoc()): ?>
                <div class="recipe-card">
                    <?php if (!empty($recipe['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($recipe['image_url']); ?>" alt="<?php echo htmlspecialchars($recipe['title']); ?>">
                    <?php else: ?>
                        <div style="background: #f0f0f0; height: 150px; display: flex; align-items: center; justify-content: center; border-radius: 5px; margin-bottom: 10px; color: #666;">
                            No Image Available
                        </div>
                    <?php endif; ?>
                    <h4><?php echo htmlspecialchars($recipe['title']); ?></h4>
                    <p><?php 
                        $description = $recipe['description'];
                        echo htmlspecialchars(strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description);
                    ?></p>
                    <div class="recipe-meta">
                        Created: <?php echo date('M j, Y', strtotime($recipe['created_at'])); ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-messages">
                <p>No recipes yet. Add your first recipe above!</p>
            </div>
        <?php endif; ?>
      </div>
    </section>
  </div>
</body>
</html>
<?php
if(isset($conn)) {
    $conn->close();
}
?>