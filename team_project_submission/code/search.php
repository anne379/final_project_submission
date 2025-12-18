<!-- @Tina-ayim -->
<?php
include 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['query']) || empty($_GET['query'])) {
    echo "No search term provided.";
    exit();
}

$query = $_GET['query'];
$results = searchRecipes($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Delisssh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<!-- Styling with tailwind: @krystable -->
<body class="bg-gray-50 min-h-screen flex">
   
    <div class="w-64 bg-gray-50 text-red-800 h-screen fixed left-0 top-0 py-5 overflow-y-auto z-50 border-r border-gray-200 shadow-sm">
        <div class="px-5 pb-5 border-b border-red-100 mb-5">
            <a href="homepage.html" class="text-2xl font-bold text-red-800 no-underline">
                Deli<span class="text-orange-500">sssh</span>
            </a>
        </div>
        
        <div class="mb-5">
            <ul class="space-y-1">
                <li><a href="homepage.php" class="block py-2.5 px-5 text-red-700 hover:bg-red-50 hover:text-red-900 no-underline transition-all border-l-4 border-transparent hover:border-orange-500">Home</a></li>
                <li><a href="profile.php" class="block py-2.5 px-5 text-red-700 hover:bg-red-50 hover:text-red-900 no-underline transition-all border-l-4 border-transparent hover:border-orange-500">Profile</a></li>
                <li><a href="allchefs.php" class="block py-2.5 px-5 text-red-700 hover:bg-red-50 hover:text-red-900 no-underline transition-all border-l-4 border-transparent hover:border-orange-500">Chefs</a></li>
            </ul>
        </div>
        
        <div class="px-5 mb-6">
            <form method="GET" action="search.php" class="flex bg-red-50 rounded border border-red-200 overflow-hidden">
                <input type="text" name="query" placeholder="Search recipes..." 
                       value="<?php echo htmlspecialchars($query); ?>"
                       class="flex-1 bg-transparent border-none py-2.5 px-4 text-red-800 placeholder-red-400 outline-none">
                <button type="submit" class="bg-orange-500 text-white px-4 hover:bg-orange-600 transition-colors">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
        
        
    </div>

  
    <div class="ml-64 flex-1 p-8">
      
        <div class="bg-white rounded-xl shadow-sm p-8 text-center mb-8">
            <h1 class="text-3xl font-serif text-red-800 mb-3">
                Search Results for: <span class="text-orange-500 font-bold">"<?php echo htmlspecialchars($query); ?>"</span>
            </h1>
            <p class="text-gray-600 text-lg">
                <?php echo count($results); ?> recipe<?php echo count($results) !== 1 ? 's' : ''; ?> found
            </p>
        </div>

        <?php if (empty($results)): ?>

            <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                <div class="text-6xl text-gray-300 mb-6">🔍</div>
                <h3 class="text-2xl text-gray-600 mb-4">No recipes found</h3>
                <p class="text-gray-500 text-lg mb-6">
                    We couldn't find any recipes matching "<?php echo htmlspecialchars($query); ?>"
                </p>
                <p class="text-gray-500 mb-8">
                    Try searching with different keywords or browse all recipes.
                </p>
                <a href="recipes.php" class="bg-orange-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-orange-600 transition-colors no-underline inline-block">
                    Browse All Recipes
                </a>
            </div>
        <?php else: ?>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($results as $recipe): ?>
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-lg transition-shadow duration-300 group">
                        <a href="recipe_detail.php?id=<?php echo $recipe['recipeId']; ?>" class="no-underline text-inherit block h-full">
                            <?php if (!empty($recipe['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($recipe['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($recipe['title']); ?>" 
                                     class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
                            <?php else: ?>
                                <div class="w-full h-48 bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center text-white text-5xl">
                                    🍳
                                </div>
                            <?php endif; ?>
                            
                            <div class="p-6">
                                <h3 class="text-xl font-semibold text-gray-800 mb-3 line-clamp-2 group-hover:text-orange-500 transition-colors">
                                    <?php echo htmlspecialchars($recipe['title']); ?>
                                </h3>
                                <p class="text-gray-600 text-sm leading-relaxed mb-4 line-clamp-3">
                                    <?php 
                                    $description = $recipe['description'] ?? 'A delicious recipe you\'ll love';
                                    echo htmlspecialchars($description);
                                    ?>
                                </p>
                                
                                <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                                    <?php if (!empty($recipe['chef_name'])): ?>
                                        <span class="text-gray-500 text-sm">By <?php echo htmlspecialchars($recipe['chef_name']); ?></span>
                                    <?php else: ?>
                                        <span class="text-gray-500 text-sm">By Chef</span>
                                    <?php endif; ?>
                                    <span class="text-orange-500 font-semibold text-sm group-hover:translate-x-1 transition-transform">
                                        View Recipe →
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
       
        .overflow-y-auto::-webkit-scrollbar {
            width: 4px;
        }
        
        .overflow-y-auto::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        .overflow-y-auto::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 2px;
        }
        
        .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }
    </style>
</body>
</html>