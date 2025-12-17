<aside id="sidebar" class="h-screen glass-panel border-r border-glass-border flex flex-col hidden md:flex z-50 transition-all duration-300 w-64 overflow-hidden">
    <div class="h-16 flex items-center justify-between px-6 border-b border-gray-200 dark:border-white/10 shrink-0">
         <span id="brandName" class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-brand-primary to-brand-accent transition-opacity duration-300 whitespace-nowrap">
            BankAssist
        </span>
        <button id="sidebarToggle" class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline-none">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg>
        </button>
    </div>
    
    <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto overflow-x-hidden">
        <?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
        
        <a href="portal.php" class="flex items-center px-4 py-3 rounded-lg transition-colors group <?= ($currentPage == 'portal.php' || $currentPage == 'customer_request_details.php') ? 'bg-brand-primary/10 text-brand-primary dark:bg-white/10 dark:text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5' ?>" title="My Dashboard">
            <svg class="w-5 h-5 min-w-[20px] <?= ($currentPage == 'portal.php' || $currentPage == 'customer_request_details.php') ? 'text-brand-primary' : 'text-gray-400 dark:text-gray-400 group-hover:text-brand-primary' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
            <span class="ml-3 font-medium sidebar-text whitespace-nowrap">My Dashboard</span>
        </a>

        <a href="messages.php" class="flex items-center px-4 py-3 rounded-lg transition-colors group <?= $currentPage == 'messages.php' ? 'bg-brand-primary/10 text-brand-primary dark:bg-white/10 dark:text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5' ?>" title="Messages">
             <svg class="w-5 h-5 min-w-[20px] <?= $currentPage == 'messages.php' ? 'text-brand-primary' : 'text-gray-400 dark:text-gray-400 group-hover:text-brand-primary' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
            <span class="ml-3 font-medium sidebar-text whitespace-nowrap">Messages</span>
        </a>

        <a href="profile.php" class="flex items-center px-4 py-3 rounded-lg transition-colors group <?= $currentPage == 'profile.php' ? 'bg-brand-primary/10 text-brand-primary dark:bg-white/10 dark:text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5' ?>" title="My Profile">
             <svg class="w-5 h-5 min-w-[20px] <?= $currentPage == 'profile.php' ? 'text-brand-accent' : 'text-gray-400 dark:text-gray-400 group-hover:text-brand-accent' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            <span class="ml-3 font-medium sidebar-text whitespace-nowrap">My Profile</span>
        </a>
    </nav>
    
    <div class="p-4 border-t border-gray-200 dark:border-white/10">
        <div class="flex items-center mb-4 p-2">
            <div class="w-8 h-8 min-w-[32px] rounded-full bg-brand-accent flex items-center justify-center text-xs font-bold text-white">
                C
            </div>
            <div class="ml-3 sidebar-text overflow-hidden">
                <div class="text-sm font-medium text-gray-900 dark:text-white truncate"><?= h($_SESSION['first_name'] ?? 'Customer') ?></div>
                <div class="text-xs text-gray-500 dark:text-gray-400 truncate">Member</div>
            </div>
        </div>
        <a href="logout.php" class="flex items-center justify-center w-full py-2 text-xs text-red-500 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10 rounded transition-colors group" title="Logout">
            <svg class="w-5 h-5 min-w-[20px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
             <span class="ml-2 sidebar-text">Logout</span>
        </a>
    </div>
</aside>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebarToggle');
        const brandName = document.getElementById('brandName');
        const sidebarTexts = document.querySelectorAll('.sidebar-text');
        
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        
        function updateSidebar() {
            if (sidebar.classList.contains('w-20')) {
                // Collapsed
                brandName.classList.add('hidden');
                sidebarTexts.forEach(el => el.classList.add('hidden'));
                toggleBtn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>';
            } else {
                // Expanded
                brandName.classList.remove('hidden');
                sidebarTexts.forEach(el => el.classList.remove('hidden'));
                toggleBtn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg>';
            }
        }

        // Apply initial state
        if (isCollapsed) {
            sidebar.classList.remove('w-64');
            sidebar.classList.add('w-20');
            updateSidebar();
        }

        toggleBtn.addEventListener('click', () => {
            if (sidebar.classList.contains('w-64')) {
                sidebar.classList.remove('w-64');
                sidebar.classList.add('w-20');
                localStorage.setItem('sidebarCollapsed', 'true');
            } else {
                sidebar.classList.remove('w-20');
                sidebar.classList.add('w-64');
                localStorage.setItem('sidebarCollapsed', 'false');
            }
            updateSidebar();
        });
    });
</script>
