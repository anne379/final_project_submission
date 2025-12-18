<!-- @jaaa-b -->
 <?php

 
 include 'functions.php';
 checkLogin('client');
   ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Delisssh - <?php echo $pageTitle ?? 'Home'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="recipes.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&display=swap');
        
        .font-serif {
            font-family: 'Playfair Display', serif;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #fed7aa 0%, #fdba74 100%);
        }
        
        .recipe-card {
            transition: all 0.3s ease;
        }
        
        .recipe-card:hover {
            transform: translateY(-5px);
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
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex">
    <?php 
   
    $currentPage = basename($_SERVER['PHP_SELF']);
    ?>
    
   
    <div class="w-64 bg-white h-screen fixed left-0 top-0 py-6 overflow-y-auto shadow-lg z-10">
        <div class="px-6 pb-6 border-b border-gray-200 mb-6">
            <a href="homepage.php" class="text-2xl font-bold text-red-800 no-underline">
                Deli<span class="text-orange-500">sssh</span>
            </a>
        </div>
        
        <div class="mb-8">
            <ul class="space-y-2">
                <li>
                    <a href="homepage.php" 
                       class="flex items-center py-3 px-6 text-red-700 hover:bg-red-50 hover:text-red-900 no-underline transition-all border-l-4 <?php echo $currentPage == 'homepage.php' ? 'border-orange-500 bg-red-50' : 'border-transparent'; ?>">
                        <i class="fas fa-home mr-3"></i> Home
                    </a>
                </li>
                <li>
                    <a href="profile.php" 
                       class="flex items-center py-3 px-6 text-red-700 hover:bg-red-50 hover:text-red-900 no-underline transition-all border-l-4 <?php echo $currentPage == 'profile.php' ? 'border-orange-500 bg-red-50' : 'border-transparent'; ?>">
                        <i class="fas fa-user mr-3"></i> Profile
                    </a>
                </li>
                <li>
                    <a href="allchefs.php" 
                       class="flex items-center py-3 px-6 text-red-700 hover:bg-red-50 hover:text-red-900 no-underline transition-all border-l-4 <?php echo $currentPage == 'allchefs.php' ? 'border-orange-500 bg-red-50' : 'border-transparent'; ?>">
                        <i class="fas fa-utensils mr-3"></i> Chefs
                    </a>
                </li>
                <li>
                    <a href="logout.php" 
                       class="flex items-center py-3 px-6 text-red-700 hover:bg-red-50 hover:text-red-900 no-underline transition-all border-l-4 <?php echo $currentPage == 'logout.php' ? 'border-orange-500 bg-red-50' : 'border-transparent'; ?>">
                        <i class="fas fa-sign-out-alt mr-3"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="px-6">
            <form action="search.php" method="GET" class="flex bg-red-50 rounded-lg border border-red-200 overflow-hidden">
                <input type="text" name="query" placeholder="Search recipes..." required
                       class="flex-1 bg-transparent border-none py-3 px-4 text-red-800 placeholder-red-400 outline-none">
                <button type="submit" class="bg-orange-500 text-white px-4 hover:bg-orange-600 transition-colors">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
    </div>

 
    