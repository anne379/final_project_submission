<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

secure_session_start();


if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'Customer') {
        header("Location: portal.php");
    } else {
        header("Location: dashboard.php");
    }
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $pdo->prepare("
                SELECT p.id, p.email, p.password_hash, p.customer_id, c.first_name 
                FROM portal_users p 
                JOIN customers c ON p.customer_id = c.id 
                WHERE p.email = :email
            ");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
               
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['first_name'] = $user['first_name']; 
                $_SESSION['role'] = 'Customer';
                $_SESSION['customer_id'] = $user['customer_id'];
    
            //log Activity
            log_activity($user['id'], 'Login', 'Customer login successful');
    
            header("Location: portal.php");
            exit;
        } else {
            $error = "Invalid credentials.";
        }
    }
}

$pageTitle = "Login - BankAssist";
require_once 'includes/header.php';
?>

<div class="flex items-center justify-center min-h-screen p-4 transition-colors duration-300 relative overflow-hidden">

    <div class="absolute top-1/4 -left-20 w-96 h-96 bg-blue-400/20 rounded-full blur-[100px] -z-10"></div>
    <div class="absolute bottom-1/4 -right-20 w-96 h-96 bg-cyan-400/20 rounded-full blur-[100px] -z-10"></div>

    <div class="glass-panel w-full max-w-md p-8 animated-fade-in relative">
        
        <div class="relative z-10">
            <h2 class="text-3xl font-bold text-center mb-2 text-gray-900 dark:text-white">Welcome!</h2>
            <p class="text-center text-gray-600 dark:text-gray-300 mb-8">Sign in to your portal</p>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-200 text-red-700 dark:bg-red-500/10 dark:border-red-500/20 dark:text-red-200 p-3 rounded-lg mb-6 text-sm flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <?= h($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email Address</label>
                    <input type="email" name="email" value="<?= h($email) ?>" class="input-field" placeholder="you@example.com" required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="password" class="input-field pr-10" required>
                        <button type="button" onclick="togglePassword('password', this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-white focus:outline-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        </button>
                    </div>
                </div>

                <script>
                    function togglePassword(inputId, btn) {
                        const input = document.getElementById(inputId);
                        const icon = btn.querySelector('svg');
                        if (input.type === 'password') {
                            input.type = 'text';
                            //switch toeye off icon
                            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>';
                        } else {
                            input.type = 'password';
                            //switch to Eye Icon
                            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
                        }
                    }
                </script>
                
                <button type="submit" class="btn-primary w-full">
                    Sign In
                </button>
                
                <div class="text-center mt-6">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Don't have an account? 
                        <a href="register.php" class="text-brand-primary hover:text-brand-primary/80 dark:hover:text-white transition-colors">Register</a>
                    </p>
                    
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
