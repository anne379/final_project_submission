<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

secure_session_start();

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'Staff' || $_SESSION['role'] === 'Admin') {
        header("Location: dashboard.php");
    } else {
        header("Location: portal.php");
    }
    exit;
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
        
        if ($user && in_array($user['role'], ['Staff', 'Admin']) && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $user['username'];
            
       
            log_activity($user['id'], 'Login', $user['role'] . ' login successful');

            if ($user['role'] === 'Admin') {
                header("Location: admin_analytics.php");
            } else {
                header("Location: dashboard.php");
            }
            exit;
        } else {
           
            $error = "Invalid credentials or unauthorized access.";
        }
    }
}

$pageTitle = "Staff Login - BankAssist";
require_once 'includes/header.php';
?>

<div class="flex items-center justify-center min-h-screen p-4 transition-colors duration-300 relative overflow-hidden">
 
    <div class="absolute top-1/4 -left-20 w-96 h-96 bg-blue-400/20 rounded-full blur-[100px] -z-10"></div>
    <div class="absolute bottom-1/4 -right-20 w-96 h-96 bg-cyan-400/20 rounded-full blur-[100px] -z-10"></div>

    <div class="glass-panel w-full max-w-md p-8 animated-fade-in relative">
        
        <div class="relative z-10">
            <h2 class="text-3xl font-bold text-center mb-2 text-gray-900 dark:text-white">Staff Login</h2>
            <p class="text-center text-gray-600 dark:text-gray-400 mb-8">Authorized Personnel Only</p>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-200 text-red-700 dark:bg-red-500/10 dark:border-red-500/20 dark:text-red-200 p-3 rounded-lg mb-6 text-sm flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <?= h($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Staff Username</label>
                    <input type="text" name="username" value="<?= h($username) ?>" class="input-field font-mono" placeholder="staff username" required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password</label>
                    <input type="password" name="password" class="input-field" required>
                </div>
                
                <button type="submit" class="btn-primary w-full">
                     Login
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
