<!-- @jaaa-b -->
<?php 
$pageTitle = "Home";
include 'header.php'; 
?>
<div class="ml-64 flex-1">
       
        <div class="gradient-bg py-16 px-8 text-center">
            <h1 class="text-5xl font-serif text-red-800 mb-4">Welcome to Delisssh</h1>
            <p class="text-xl text-red-700 max-w-2xl mx-auto mb-8">
                Discover amazing recipes from talented chefs around the world
            </p>
            <button onclick="window.location.href='recipes.php'" 
                    class="bg-red-800 text-white px-8 py-3 rounded-lg font-semibold hover:bg-red-900 transition-colors shadow-lg">
                Explore All Recipes
            </button>
        </div>

       
        <div class="py-12 px-8">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-3xl font-serif text-red-800 mb-2 text-center">Most Favorited Recipes</h2>
                <p class="text-gray-600 text-center mb-10">Discover what our community loves the most</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                  
                    <?php
       include 'database.php';

$sql = "SELECT r.recipeId, r.title, r.image_url, r.description, r.chefId,
               COUNT(f.recipeId) AS total_favs
        FROM favourite f
        JOIN recipes r ON r.recipeId = f.recipeId
        GROUP BY f.recipeId
        ORDER BY total_favs DESC
        LIMIT 3";

$result = $conn->query($sql);

foreach ($result as $recipe) {
    echo '
    <a href="recipe_detail.php?id=' . $recipe['recipeId'] . '" class="block no-underline text-inherit">
        <div class="recipe-card bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg">
            <div class="relative">
                <img src="' . $recipe['image_url'] . '" alt="' . $recipe['title'] . '" 
                     class="w-full h-48 object-cover">

                <div class="absolute top-4 right-4 bg-red-800 text-white px-3 py-1 rounded-full text-sm font-semibold">
                    <i class="fas fa-heart mr-1"></i> ' . $recipe['total_favs'] . '
                </div>
            </div>

            <div class="p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-2">' . $recipe['title'] . '</h3>
                <p class="text-gray-600 mb-4">' . substr($recipe['description'], 0, 90) . '...</p>
                <div class="text-orange-500 font-semibold text-center">
                    View Recipe →
                </div>
            </div>
        </div>
    </a>';
}
?>

                </div>
            </div>
        </div>

      
        <div class="py-16 px-8 bg-gradient-to-r from-red-800 to-red-900 text-white">
            <div class="max-w-7xl mx-auto text-center">
                <h3 class="text-4xl font-serif mb-6">Life is just a bowl of Cherries</h3>
                <p class="text-xl max-w-2xl mx-auto mb-8">
                    Discover the joy of cooking with our collection of delicious recipes from around the world
                </p>
                <button onclick="window.location.href='recipes.php'" 
                        class="bg-white text-red-800 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors shadow-lg">
                    Explore All Recipes
                </button>
            </div>
        </div>

     
        <div class="py-16 px-8 bg-white">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col lg:flex-row items-center">
                    <div class="lg:w-1/2 mb-10 lg:mb-0 lg:pr-12">
                        <h3 class="text-3xl font-serif text-red-800 mb-4">Sugar, Spice and Everything Nice</h3>
                        <h4 class="text-2xl text-orange-500 mb-4">Hi, I'm Chef Abbys</h4>
                        <p class="text-gray-700 text-lg mb-6 leading-relaxed">
                            Welcome to your safe haven... Discover delicious recipes from around the world and learn cooking techniques from professional chefs.
                        </p>
                        <button onclick="window.location.href='allchefs.php'" 
                                class="bg-red-800 text-white px-6 py-3 rounded-lg font-semibold hover:bg-red-900 transition-colors">
                            Meet All Our Chefs
                        </button>
                    </div>
                    <div class="lg:w-1/2 flex justify-center">
                        <div class="relative">
                            <img src="chef.png" alt="Chef Abbys" class="rounded-xl shadow-lg w-full max-w-md">
                            <div class="absolute -bottom-4 -right-4 bg-orange-500 text-white px-6 py-3 rounded-lg shadow-lg">
                                <span class="font-semibold">15+ Years Experience</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-12 px-8 bg-gray-100">
            <div class="max-w-4xl mx-auto text-center">
                <h3 class="text-3xl font-serif text-red-800 mb-4">Ready to Start Cooking?</h3>
                <p class="text-gray-600 text-lg mb-8">
                    Join thousands of home cooks who are already creating delicious meals with Delisssh
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <button onclick="window.location.href='recipes.php'" 
                            class="bg-orange-500 text-white px-8 py-3 rounded-lg font-semibold hover:bg-orange-600 transition-colors">
                        Browse Recipes
                    </button>
                    <button onclick="window.location.href='allchefs.php'" 
                            class="bg-white text-red-800 border border-red-800 px-8 py-3 rounded-lg font-semibold hover:bg-red-50 transition-colors">
                        Meet Our Chefs
                    </button>
                </div>
            </div>
        </div>
    </div>

  
</body>
</html>