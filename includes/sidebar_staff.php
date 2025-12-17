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
        
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
            <a href="admin_analytics.php" class="flex items-center px-4 py-3 rounded-lg transition-colors group <?= $currentPage == 'admin_analytics.php' ? 'bg-brand-primary/10 text-brand-primary dark:bg-white/10 dark:text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5' ?>" title="Analytics">
                 <svg class="w-5 h-5 min-w-[20px] <?= $currentPage == 'admin_analytics.php' ? 'text-brand-accent' : 'text-gray-400 dark:text-gray-400 group-hover:text-brand-accent' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                <span class="ml-3 font-medium sidebar-text whitespace-nowrap">Analytics</span>
            </a>
        <?php endif; ?>

        <a href="dashboard.php" class="flex items-center px-4 py-3 rounded-lg transition-colors group <?= $currentPage == 'dashboard.php' ? 'bg-brand-primary/10 text-brand-primary dark:bg-white/10 dark:text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5' ?>" title="Service Requests">
            <svg class="w-5 h-5 min-w-[20px] <?= $currentPage == 'dashboard.php' ? 'text-brand-primary' : 'text-gray-400 dark:text-gray-400 group-hover:text-brand-primary' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
            <span class="ml-3 font-medium sidebar-text whitespace-nowrap">Service Requests</span>
        </a>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
            <a href="admin_staff.php" class="flex items-center px-4 py-3 rounded-lg transition-colors group <?= $currentPage == 'admin_staff.php' ? 'bg-brand-primary/10 text-brand-primary dark:bg-white/10 dark:text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5' ?>" title="Manage Staff">
                <svg class="w-5 h-5 min-w-[20px] <?= $currentPage == 'admin_staff.php' ? 'text-brand-accent' : 'text-gray-400 dark:text-gray-400 group-hover:text-brand-accent' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <span class="ml-3 font-medium sidebar-text whitespace-nowrap">Manage Staff</span>
            </a>
            <a href="admin_customers.php" class="flex items-center px-4 py-3 rounded-lg transition-colors group <?= $currentPage == 'admin_customers.php' ? 'bg-brand-primary/10 text-brand-primary dark:bg-white/10 dark:text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5' ?>" title="Manage Customers">
                <svg class="w-5 h-5 min-w-[20px] <?= $currentPage == 'admin_customers.php' ? 'text-brand-primary' : 'text-gray-400 dark:text-gray-400 group-hover:text-brand-primary' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span class="ml-3 font-medium sidebar-text whitespace-nowrap">Manage Customers</span>
            </a>
        <?php endif; ?>
    </nav>
    
    <div class="p-4 border-t border-gray-200 dark:border-white/10">
        <a href="staff_profile.php" class="flex items-center mb-4 p-2 rounded-lg transition-colors hover:bg-gray-100 dark:hover:bg-white/5 group" title="My Profile">
            <div class="w-8 h-8 min-w-[32px] rounded-full bg-brand-primary flex items-center justify-center text-xs font-bold text-white">
                <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
            </div>
            <div class="ml-3 sidebar-text overflow-hidden">
                <div class="text-sm font-medium text-gray-900 dark:text-white truncate"><?= h($_SESSION['username']) ?></div>
                <div class="text-xs text-gray-500 dark:text-gray-400 truncate"><?= h($_SESSION['role']) ?></div>
            </div>
        </a>
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
