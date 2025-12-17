//AI use: deepsee

<?php
require_once 'includes/functions.php';
secure_session_start();

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'Admin') {
        header("Location: admin_analytics.php");
    } elseif ($_SESSION['role'] === 'Staff') {
        header("Location: dashboard.php");
    } else {
        header("Location: portal.php");
    }
    exit;
}

$pageTitle = "Welcome to BankAssist";
require_once 'includes/header.php';
?>

<div class="flex flex-col min-h-screen">
    

    <nav class="absolute top-0 left-0 right-0 z-50 px-6 py-6 flex justify-between items-center max-w-7xl mx-auto w-full">
        <div class="flex items-center">
             <span class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-brand-primary to-brand-accent">
                BankAssist
            </span>
        </div>
        <div class="flex items-center space-x-6">
            <a href="login.php" class="text-gray-700 dark:text-white hover:text-brand-primary transition-colors font-medium">Login</a>
            <a href="register.php" class="bg-brand-primary text-white px-5 py-2 rounded-lg shadow-lg hover:bg-blue-600 transition-all">Get Started</a>
        </div>
    </nav>

    <main class="flex-1 flex flex-col items-center justify-center relative px-6 text-center pt-20 pb-20">
  
        <div class="absolute top-1/4 -left-20 w-96 h-96 bg-blue-400/20 rounded-full blur-[100px] -z-10"></div>
        <div class="absolute bottom-1/4 -right-20 w-96 h-96 bg-cyan-400/20 rounded-full blur-[100px] -z-10"></div>

        <div class="max-w-4xl mx-auto space-y-8 animate-fade-in-up">
           
            
            <h1 class="text-5xl md:text-7xl font-bold text-gray-900 dark:text-white tracking-tight leading-tight">
                Banking Support, <br />
                <span class="text-brand-primary">Reimagined.</span>
            </h1>
            
            <p class="text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto leading-relaxed">
                Experience seamless, secure, and instant support for all your banking needs. 
                Resolve issues faster with our dedicated portal.
            </p>
            
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
                <a href="register.php" class="w-full sm:w-auto px-8 py-4 bg-brand-primary text-white rounded-xl font-bold shadow-lg hover:bg-blue-600 text-lg transition-all transform hover:-translate-y-1">
                    Create Account
                </a>
                <a href="login.php" class="w-full sm:w-auto px-8 py-4 bg-white border border-gray-200 text-gray-900 dark:bg-white/5 dark:border-white/10 dark:text-white rounded-xl font-bold hover:bg-gray-50 dark:hover:bg-white/10 text-lg transition-all">
                    Login
                </a>
            </div>
        </div>

        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto mt-32 w-full">
            <div class="glass-panel p-8 rounded-2xl border border-gray-200 dark:border-white/5 hover:border-brand-primary/30 transition-all group text-left">
                <div class="w-12 h-12 rounded-lg bg-brand-primary/10 dark:bg-brand-primary/20 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform text-brand-primary">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Strong Security</h3>
                <p class="text-gray-600 dark:text-gray-400 leading-relaxed">Your data is protected with state-of-the-art encryption and secure protocols at every step.</p>
            </div>

            <div class="glass-panel p-8 rounded-2xl border border-gray-200 dark:border-white/5 hover:border-brand-accent/30 transition-all group text-left">
                <div class="w-12 h-12 rounded-lg bg-brand-accent/10 dark:bg-brand-accent/20 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform text-brand-accent">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Fast Support</h3>
                <p class="text-gray-600 dark:text-gray-400 leading-relaxed">Our dedicated portal ensures your requests are routed immediately to the right specialists.</p>
            </div>

            <div class="glass-panel p-8 rounded-2xl border border-gray-200 dark:border-white/5 hover:border-brand-primary/30 transition-all group text-left">
                <div class="w-12 h-12 rounded-lg bg-brand-primary/10 dark:bg-brand-primary/20 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform text-brand-primary">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Direct Messaging</h3>
                <p class="text-gray-600 dark:text-gray-400 leading-relaxed">Communicate directly with support staff through our integrated secure messaging system.</p>
            </div>
        </div>
    </main>

    <footer class="border-t border-gray-200 dark:border-white/10 py-12 bg-white/50 dark:bg-black/20 backdrop-blur-lg">
        <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center text-sm text-gray-500">
            <div class="mb-4 md:mb-0">
                &copy; <?= date('Y') ?> BankAssist. All rights reserved.
            </div>
    
        </div>
    </footer>
</div>

<style>
    @keyframes fade-in-up {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-up {
        animation: fade-in-up 0.8s ease-out forwards;
    }
</style>
