<?php 

include 'functions.php';
checkLogin('chef');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Recipe Details | Delisssh</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }

        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #8b2f1b;
            color: white;
            padding: 12px 24px;
            border-radius: 30px;
            text-decoration: none;
            margin-bottom: 25px;
            transition: all 0.3s ease;
            font-weight: 500;
            box-shadow: 0 2px 5px rgba(139, 47, 27, 0.2);
        }
        
        .back-button:hover {
            background: #a53a23;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(139, 47, 27, 0.3);
        }
        
        .recipe-detail {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .recipe-hero {
            position: relative;
            height: 450px;
            overflow: hidden;
        }
        
        .recipe-hero img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .recipe-hero:hover img {
            transform: scale(1.03);
        }
        
        .recipe-hero-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
            padding: 30px;
            color: white;
        }
        
        .recipe-hero-overlay h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.8rem;
            margin-bottom: 10px;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
        }
        
        .recipe-hero-overlay .chef-name {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .recipe-info {
            padding: 40px;
        }
        
        .recipe-meta {
            display: flex;
            gap: 30px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #7f8c8d;
        }
        
        .meta-item i {
            color: #8b2f1b;
        }
        
        .recipe-section {
            margin-bottom: 35px;
        }
        
        .section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #8b2f1b;
            font-size: 1.5rem;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f39c12;
        }
        
        .section-title i {
            font-size: 1.3rem;
        }
        
        .recipe-ingredients {
            background: #fff9f0;
            padding: 25px;
            border-radius: 10px;
            border-left: 4px solid #f39c12;
        }
        
        .ingredients-list {
            list-style-type: none;
        }
        
        .ingredients-list li {
            padding: 8px 0;
            padding-left: 25px;
            position: relative;
        }
        
        .ingredients-list li:before {
            content: "•";
            color: #f39c12;
            font-weight: bold;
            position: absolute;
            left: 10px;
        }
        
        .recipe-description {
            font-size: 1.1rem;
            line-height: 1.7;
            color: #444;
        }
        
        .recipe-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .action-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            padding: 10px 20px;
            border-radius: 30px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .action-btn:hover {
            background: #e9ecef;
            transform: translateY(-2px);
        }
        
        .action-btn.print {
            background: #8b2f1b;
            color: white;
            border-color: #8b2f1b;
        }
        
        .action-btn.print:hover {
            background: #a53a23;
        }
        
        .error-message {
            text-align: center;
            padding: 60px 20px;
            color: #e74c3c;
            font-size: 1.3rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }
        
        .error-message i {
            font-size: 3rem;
            margin-bottom: 15px;
            display: block;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .recipe-hero {
                height: 300px;
            }
            
            .recipe-hero-overlay h1 {
                font-size: 2rem;
            }
            
            .recipe-info {
                padding: 25px;
            }
            
            .recipe-meta {
                flex-direction: column;
                gap: 15px;
            }
            
            .recipe-actions {
                flex-direction: column;
            }
        }
        
        @media (max-width: 480px) {
            .main-content {
                padding: 15px;
            }
            
            .recipe-hero {
                height: 250px;
            }
            
            .recipe-hero-overlay {
                padding: 20px;
            }
            
            .recipe-hero-overlay h1 {
                font-size: 1.7rem;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <?php
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $recipe_id = $_GET['id'];
            
            include 'database.php';
            
            $stmt = $conn->prepare("
                SELECT recipes.*, chef.name AS chef_name 
                FROM recipes 
                JOIN chef ON recipes.chefId = chef.chefId 
                WHERE recipes.recipeId = ?
            ");
            $stmt->bind_param("i", $recipe_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $recipe = $result->fetch_assoc();
                
                echo '<a href="cprofile.php" class="back-button"><i class="fas fa-arrow-left"></i> Back to Profile</a>';
                echo '<div class="recipe-detail">';
                echo '<div class="recipe-hero">';
                echo '<img src="' . htmlspecialchars($recipe["image_url"]) . '" alt="' . htmlspecialchars($recipe["title"]) . '">';
                echo '<div class="recipe-hero-overlay">';
                echo '<h1>' . htmlspecialchars($recipe["title"]) . '</h1>';
                echo '<div class="chef-name">By ' . htmlspecialchars($recipe["chef_name"]) . '</div>';
                echo '</div>';
                echo '</div>';
                echo '<div class="recipe-info">';
                echo '<div class="recipe-meta">';
                echo '<div class="meta-item"><i class="far fa-user"></i> ' . htmlspecialchars($recipe["chef_name"]) . '</div>';
                echo '<div class="meta-item"><i class="far fa-calendar"></i> ' . date('F j, Y', strtotime($recipe["created_at"])) . '</div>';
             
                echo '</div>';
                
                if (!empty($recipe["ingredients"])) {
                    echo '<div class="recipe-section">';
                    echo '<h3 class="section-title"><i class="fas fa-list-ul"></i> Ingredients</h3>';
                    echo '<div class="recipe-ingredients">';
                    echo '<ul class="ingredients-list">';
                    
                    $ingredients = explode("\n", $recipe["ingredients"]);
                    foreach ($ingredients as $ingredient) {
                        if (trim($ingredient) !== '') {
                            echo '<li>' . htmlspecialchars(trim($ingredient)) . '</li>';
                        }
                    }
                    echo '</ul>';
                    echo '</div>';
                    echo '</div>';
                }
                
                if (!empty($recipe["description"])) {
                    echo '<div class="recipe-section">';
                    echo '<h3 class="section-title"><i class="fas fa-file-alt"></i> Instructions</h3>';
                    echo '<div class="recipe-description">';
                    echo '<p>' . nl2br(htmlspecialchars($recipe["description"])) . '</p>';
                    echo '</div>';
                    echo '</div>';
                }
                
                echo '<div class="recipe-actions">';
                echo '<button class="action-btn print" onclick="window.print()"><i class="fas fa-print"></i> Print Recipe</button>';
                echo '<button class="action-btn"><i class="fas fa-share-alt"></i> Share</button>';
                echo '</div>';
                
                echo '</div>';
                echo '</div>';
            } else {
                echo '<div class="error-message">';
                echo '<i class="fas fa-utensils"></i>';
                echo '<p>Recipe not found. Please check the recipe ID and try again.</p>';
                echo '</div>';
            }
            
            $stmt->close();
            $conn->close();
        } else {
            echo '<div class="error-message">';
            echo '<i class="fas fa-exclamation-triangle"></i>';
            echo '<p>Invalid recipe ID. Please select a valid recipe.</p>';
            echo '</div>';
        }
        ?>
    </div>
    
    <script>
      
        document.addEventListener('DOMContentLoaded', function() {
          
            const sections = document.querySelectorAll('.recipe-section');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, { threshold: 0.1 });
            
            sections.forEach(section => {
                section.style.opacity = '0';
                section.style.transform = 'translateY(20px)';
                section.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                observer.observe(section);
            });
            
          
        });
    </script>
</body>
</html>