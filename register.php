<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

secure_session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: portal.php");
    exit;
}

$error = '';
$success = '';


$firstName = '';
$lastName = '';
$idType = 'National ID';
$idNumber = '';
$phone = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $idType = $_POST['id_type'];
    $idNumber = trim($_POST['id_number']);
    $accountNumber = trim($_POST['account_number']);
    $phone = trim($_POST['phone_number']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    

    if (empty($firstName) || empty($lastName) || empty($idNumber) || empty($accountNumber) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } elseif (!is_strong_password($password)) {
        $error = "Password must be at least 8 characters and include uppercase, lowercase, number, and special character.";
    } elseif (!validate_email_domain($email)) {
        $error = "Invalid email domain. Please use a valid email address.";
    } elseif (!preg_match('/^[a-zA-Z0-9]{6,20}$/', $idNumber)) {
        $error = "ID Number must be valid (6-20 alphanumeric characters).";
    } elseif (!preg_match('/^\d{10}$/', $accountNumber)) {
        $error = "Account Number must be exactly 10 digits.";
    } elseif (!preg_match('/^\+?[0-9]{10,15}$/', $phone)) {
        $error = "Phone number must be valid (10 digits, no spaces).";
    } else {
        try {
            
            $stmt = $pdo->prepare("SELECT id FROM portal_users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            if ($stmt->fetch()) {
                $error = "Email is already registered.";
            } else {
                
                $pdo->beginTransaction();
                
               
                $stmt1 = $pdo->prepare("INSERT INTO customers (first_name, last_name, id_type, id_number, account_number, phone_number, email) VALUES (:fn, :ln, :idt, :idn, :acc, :ph, :em)");
                $stmt1->execute([
                    ':fn' => $firstName,
                    ':ln' => $lastName,
                    ':idt' => $idType,
                    ':idn' => $idNumber,
                    ':acc' => $accountNumber,
                    ':ph' => $phone,
                    ':em' => $email
                ]);
                $customerId = $pdo->lastInsertId();
                
             
                $stmt2 = $pdo->prepare("INSERT INTO portal_users (customer_id, email, password_hash) VALUES (:cid, :em, :hash)");
                $stmt2->execute([
                    ':cid' => $customerId,
                    ':em' => $email,
                    ':hash' => password_hash($password, PASSWORD_BCRYPT)
                ]);
                $portalUserId = $pdo->lastInsertId();
                
                log_activity($portalUserId, 'Register', 'New customer registration');
                
                $pdo->commit();
                
                $success = "Registration successful! You can now login.";
              
                $firstName = $lastName = $idNumber = $accountNumber = $phone = $email = '';
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Registration failed: " . $e->getMessage();
        }
    }
}

$pageTitle = "Register - BankAssist";
require_once 'includes/header.php';
?>

<div class="min-h-screen p-6 flex items-center justify-center transition-colors duration-300 relative overflow-hidden">

    <div class="absolute top-1/4 -left-20 w-96 h-96 bg-blue-400/20 rounded-full blur-[100px] -z-10"></div>
    <div class="absolute bottom-1/4 -right-20 w-96 h-96 bg-cyan-400/20 rounded-full blur-[100px] -z-10"></div>

    <div class="glass-panel w-full max-w-2xl p-8 relative animated-fade-in">
        
        <div class="relative z-10">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-500 to-cyan-400">Create Account</h2>
                <p class="text-gray-600 dark:text-gray-400 mt-2">Join BankAssist for premium support</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-200 text-red-700 dark:bg-red-500/10 dark:border-red-500/20 dark:text-red-200 p-4 rounded-lg mb-6 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <?= h($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            
                <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
              
                    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>
                    
           
                    <div class="relative bg-white dark:bg-[#1a1b26] rounded-2xl shadow-2xl p-8 max-w-md w-full text-center transform scale-100 transition-transform">
                        <div class="w-20 h-20 bg-green-100 dark:bg-green-500/20 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-10 h-10 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Registration Successful!</h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-8"><?= h($success) ?></p>
                        
                        <a href="login.php" class="block w-full py-3 px-4 bg-brand-primary hover:bg-blue-600 text-white font-semibold rounded-xl transition-all transform hover:-translate-y-0.5">
                            Login to Your Account
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="grid grid-cols-1 md:grid-cols-2 gap-6" data-validate="true">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                
           
                <div class="space-y-4">
                    <h3 class="text-sm uppercase tracking-wide text-gray-500 dark:text-gray-400 font-semibold border-b border-gray-200 dark:border-white/10 pb-2">Personal Details</h3>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">First Name</label>
                        <input type="text" name="first_name" value="<?= h($firstName) ?>" class="input-field" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Last Name</label>
                        <input type="text" name="last_name" value="<?= h($lastName) ?>" class="input-field" required>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ID Type</label>
                    <select name="id_type" class="input-field appearance-none text-gray-900 dark:text-white">
                        <option value="National ID" <?= $idType == 'National ID' ? 'selected' : '' ?>>National ID</option>
                        <option value="Passport" <?= $idType == 'Passport' ? 'selected' : '' ?>>Passport</option>
                        <option value="Driver License" <?= $idType == 'Driver License' ? 'selected' : '' ?>>Driver License</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ID Number</label>
                        <input type="text" name="id_number" value="<?= h($idNumber) ?>" class="input-field" required>
                    </div>
                     <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Account Number</label>
                        <input type="text" name="account_number" value="<?= h($accountNumber ?? '') ?>" class="input-field" placeholder="10 Digits" required>
                    </div>
                </div>
            </div>

    
            <div class="space-y-4">
                 <h3 class="text-sm uppercase tracking-wide text-gray-500 dark:text-gray-400 font-semibold border-b border-gray-200 dark:border-white/10 pb-2">Account Details</h3>
                 
                 <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone Number</label>
                    <input type="tel" name="phone_number" value="<?= h($phone) ?>" class="input-field" required>
                 </div>

                 <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email Address</label>
                    <input type="email" name="email" value="<?= h($email) ?>" class="input-field" required>
                 </div>

                 <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="reg_password" class="input-field pr-10" required>
                        <button type="button" onclick="togglePassword('reg_password', this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-white focus:outline-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        </button>
                    </div>
                 </div>

                 <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Confirm Password</label>
                    <div class="relative">
                        <input type="password" name="confirm_password" id="reg_confirm_password" class="input-field pr-10" required>
                        <button type="button" onclick="togglePassword('reg_confirm_password', this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-white focus:outline-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        </button>
                    </div>
                 </div>
            </div>

            <script>
                function togglePassword(inputId, btn) {
                    const input = document.getElementById(inputId);
                    const icon = btn.querySelector('svg');
                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>';
                    } else {
                        input.type = 'password';
                        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
                    }
                }
            </script>

            <div class="md:col-span-2 mt-4">
                <button type="submit" class="btn-primary w-full">
                    Create Account
                </button>
                <div class="text-center mt-4 text-sm text-gray-600 dark:text-gray-400">
                    Already have an account? <a href="login.php" class="text-brand-primary hover:text-brand-primary/80 dark:hover:text-white">Login here</a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
