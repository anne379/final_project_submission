<!-- @jaaa-b -->

<?php 
$pageTitle = "All Recipes";
include 'header.php'; 
?>

<div class="ml-64 flex-1 p-8" >

   <div class="main-content">
        <div id ="all_recipes">

            <h3 > All Recipes</h3>

            <section class ="recipe_cards">
                
                <?php
                
                
                include 'database.php';
                $sql ="SELECT recipes.*, chef.name AS chef_name 
                      FROM recipes
                    JOIN chef ON recipes.chefId = chef.chefId";
                $result =$conn -> query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo '<div class="recipes">';
                    echo '<a href="recipe_detail.php?id=' . $row["recipeId"] . '" class="recipe-link">';
                    echo '<img src="' . htmlspecialchars($row["image_url"]) . '" alt="' . htmlspecialchars($row["title"]) . '">';
                    
                    echo '<div class="card-content">';
                    echo '<p class="card_headings">' . htmlspecialchars($row["title"]) . '</p>';
                    echo '<p class="card_body"><i class="fas fa-user-alt"></i>'  . htmlspecialchars($row["chef_name"]) . '</p>';
                    echo '<hr>';
                    echo '</a>'; 
                    echo'<button class="favorite-btn" data-recipe-id="' . $row["recipeId"] . '"><i class="fas fa-heart"></i></button>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo "<p>No recipes available yet.</p>";
            }

            $conn->close();

                ?>
            </section>    
        </div>
   </div>
        </div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const favoriteButtons = document.querySelectorAll('.favorite-btn');
    
    favoriteButtons.forEach(button => {
        button.innerHTML = '<i class="far fa-heart"></i>'; 
        button.addEventListener('click', function() {
            const recipeId = this.getAttribute('data-recipe-id');
            
            
            this.classList.toggle('active');
            if(this.classList.contains('active')) {
                this.innerHTML = '<i class="fas fa-heart"></i>';
            } else {
                this.innerHTML = '<i class="far fa-heart"></i>';
            }
         
            fetch('addFavorite.php?recipeId=' + recipeId)
                .then(response => response.text())
                .then(data => {
                    if (data === 'error') {
                        
                        this.classList.toggle('active');
                        if(this.classList.contains('active')) {
                            this.innerHTML = '<i class="fas fa-heart"></i>';
                        } else {
                            this.innerHTML = '<i class="far fa-heart"></i>';
                        }
                        alert('Error saving favorite');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                  
                    this.classList.toggle('active');
                    if(this.classList.contains('active')) {
                        this.innerHTML = '<i class="fas fa-heart"></i>';
                    } else {
                        this.innerHTML = '<i class="far fa-heart"></i>';
                    }
                    alert('Network error');
                });
        });
    });
});

</script>

</body>


</html>