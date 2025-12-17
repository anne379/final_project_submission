<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

secure_session_start();


if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}

$customerId = $_SESSION['customer_id'];
$userId = $_SESSION['user_id'];
$success = '';
$error = '';


$stmt = $pdo->prepare("SELECT c.*, p.email FROM customers c JOIN portal_users p ON c.id = p.customer_id WHERE c.id = :cid");
$stmt->execute([':cid' => $customerId]);
$customer = $stmt->fetch();

if (!$customer) die("Customer record not found.");


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'update_profile') {
            $firstName = trim($_POST['first_name']);
            $lastName = trim($_POST['last_name']);
            $phone = trim($_POST['phone_number']);
            
            if (empty($firstName) || empty($lastName) || empty($phone)) {
                $error = "All fields are required.";
            } else {
                try {
                    $stmt = $pdo->prepare("UPDATE customers SET first_name = :fn, last_name = :ln, phone_number = :ph WHERE id = :cid");
                    $stmt->execute([':fn' => $firstName, ':ln' => $lastName, ':ph' => $phone, ':cid' => $customerId]);
                    $success = "Profile updated successfully.";
 
                    $customer['first_name'] = $firstName;
                    $customer['last_name'] = $lastName;
                    $customer['phone_number'] = $phone;
                } catch (PDOException $e) {
                    $error = "Update failed: " . $e->getMessage();
                }
            }
        } elseif ($_POST['action'] === 'change_password') {
            $currentPass = $_POST['current_password'];
            $newPass = $_POST['new_password'];
            $confirmPass = $_POST['confirm_password'];
            
            if (empty($currentPass) || empty($newPass)) {
                $error = "Please fill in all password fields.";
            } elseif ($newPass !== $confirmPass) {
                $error = "New passwords do not match.";
            } elseif (!is_strong_password($newPass)) {
                $error = "New password is not strong enough (min 8 chars, uppercase, lowercase, number, special).";
            } else {
          
                 $stmt = $pdo->prepare("SELECT password_hash FROM portal_users WHERE id = :uid");
                 $stmt->execute([':uid' => $userId]);
                 $userHash = $stmt->fetchColumn();
                 
                 if (password_verify($currentPass, $userHash)) {
    
                     $newHash = password_hash($newPass, PASSWORD_BCRYPT);
                     $stmt = $pdo->prepare("UPDATE portal_users SET password_hash = :hash WHERE id = :uid");
                     $stmt->execute([':hash' => $newHash, ':uid' => $userId]);
                     $success = "Password changed successfully.";
                 } else {
                     $error = "Current password is incorrect.";
                 }
            }
        }
    }
}

$pageTitle = "My Profile - BankAssist";
require_once 'includes/header.php';
?>

<div class="flex h-screen bg-gray-50 dark:bg-brand-dark overflow-hidden">

    <?php include 'includes/sidebar_customer.php'; ?>


    <main class="flex-1 flex flex-col h-screen overflow-hidden relative">

        <header class="md:hidden h-16 glass-panel border-b border-glass-border flex items-center justify-between px-4 z-20">
             <span class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-brand-primary to-brand-accent">
                BankAssist
            </span>
             <div class="flex items-center space-x-3">
                 <a href="portal.php" class="text-gray-300">
                     <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                 </a>
             </div>
        </header>

        <div class="flex-1 overflow-y-auto p-4 md:p-8">
            <div class="flex justify-between items-center mb-8">
                 <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Profile</h1>
            </div>
           
            <?php if ($success): ?>
                <div class="mb-6 p-4 bg-green-100 border border-green-200 text-green-700 dark:bg-green-500/10 dark:border-green-500/20 dark:text-green-200 rounded-lg flex items-center animated-fade-in">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <?= h($success) ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="mb-6 p-4 bg-red-100 border border-red-200 text-red-700 dark:bg-red-500/10 dark:border-red-500/20 dark:text-red-200 rounded-lg flex items-center animated-fade-in">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <?= h($error) ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <div class="space-y-6">
             
                    <div id="viewPersonal" class="glass-panel p-6">
                        <div class="flex justify-between items-start mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                                <svg class="w-5 h-5 mr-2 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                Personal Information
                            </h3>
                            <button onclick="toggleSection('Personal')" class="text-xs bg-gray-100 hover:bg-gray-200 dark:bg-white/10 dark:hover:bg-white/20 text-gray-700 dark:text-white px-3 py-1.5 rounded transition-colors">
                                Edit Details
                            </button>
                        </div>
                        
                        <div class="space-y-4">
                             <div class="grid grid-cols-2 gap-4">
                                 <div>
                                     <span class="block text-xs text-gray-500 uppercase">First Name</span>
                                     <span class="text-gray-900 dark:text-white text-lg font-medium"><?= h($customer['first_name']) ?></span>
                                 </div>
                                 <div>
                                     <span class="block text-xs text-gray-500 uppercase">Last Name</span>
                                     <span class="text-gray-900 dark:text-white text-lg font-medium"><?= h($customer['last_name']) ?></span>
                                 </div>
                             </div>
                             <div>
                                 <span class="block text-xs text-gray-500 uppercase">Email Address</span>
                                 <span class="text-gray-700 dark:text-gray-300"><?= h($customer['email']) ?></span>
                             </div>
                             <div>
                                 <span class="block text-xs text-gray-500 uppercase">Phone Number</span>
                                 <span class="text-gray-300"><?= h($customer['phone_number']) ?></span>
                             </div>
                        </div>
                    </div>

                    <div id="editPersonal" class="glass-panel p-6 hidden">
                         <div class="flex justify-between items-start mb-6">
                            <h3 class="text-lg font-semibold text-white">Edit Personal Info</h3>
                        </div>

                        <form method="POST" action="" class="space-y-4" data-validate="true">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-400 mb-1">First Name</label>
                                    <input type="text" name="first_name" value="<?= h($customer['first_name']) ?>" class="input-field" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-400 mb-1">Last Name</label>
                                    <input type="text" name="last_name" value="<?= h($customer['last_name']) ?>" class="input-field" required>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Phone Number</label>
                                <input type="tel" name="phone_number" value="<?= h($customer['phone_number']) ?>" class="input-field" required>
                            </div>

                            <div class="flex space-x-3 pt-2">
                                <button type="submit" class="btn-primary">
                                    Save Changes
                                </button>
                                <button type="button" onclick="toggleSection('Personal')" class="bg-gray-600/50 hover:bg-gray-600/70 text-white px-4 py-2 rounded-lg transition-colors text-sm font-medium">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>

             
                     <div class="glass-panel p-6">
                          <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-4">Account Details</h3>
                          <div class="space-y-3 text-sm">
                               <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-500">Account Number</span>
                                    <span class="text-gray-900 dark:text-white font-mono tracking-wider"><?= h($customer['account_number']) ?></span>
                               </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-500">Identity Type</span>
                                    <span class="text-gray-900 dark:text-white"><?= h($customer['id_type']) ?></span>
                               </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-500">Identity Number</span>
                                    <span class="text-gray-900 dark:text-white font-mono"><?= h($customer['id_number']) ?></span>
                               </div>
                               <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-500">Join Date</span>
                                    <span class="text-gray-900 dark:text-white"><?= date('M j, Y', strtotime($customer['created_at'])) ?></span>
                               </div>
                          </div>
                     </div>
                </div>

                <div class="space-y-6">
            
                     <div id="viewSecurity" class="glass-panel p-6">
                        <div class="flex justify-between items-start mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                                <svg class="w-5 h-5 mr-2 text-brand-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                Security
                            </h3>
                             <button onclick="toggleSection('Security')" class="text-xs bg-gray-100 hover:bg-gray-200 dark:bg-white/10 dark:hover:bg-white/20 text-gray-700 dark:text-white px-3 py-1.5 rounded transition-colors">
                                Change Password
                            </button>
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            Password last changed: <span class="text-gray-900 dark:text-gray-300">Recently</span>
                            <p class="mt-2 text-xs">Used for logging into your portal account.</p>
                        </div>
                     </div>

                 
                     <div id="editSecurity" class="glass-panel p-6 hidden">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Change Password</h3>

                        <form method="POST" action="" class="space-y-4">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <input type="hidden" name="action" value="change_password">
                            
                             <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Current Password</label>
                                <input type="password" name="current_password" class="input-field" required>
                            </div>
                            
                             <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">New Password</label>
                                <input type="password" name="new_password" class="input-field" placeholder="Min 8 chars, A-Z, 0-9, symbol" required>
                            </div>
                            
                             <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="input-field" required>
                            </div>

                             <div class="flex space-x-3 pt-2">
                                <button type="submit" class="btn-primary">
                                    Update Password
                                </button>
                                <button type="button" onclick="toggleSection('Security')" class="bg-gray-200 hover:bg-gray-300 dark:bg-gray-600/50 dark:hover:bg-gray-600/70 text-gray-800 dark:text-white px-4 py-2 rounded-lg transition-colors text-sm font-medium">
                                    Cancel
                                </button>
                            </div>
                        </form>
                     </div>

                </div>
            </div>
        </div>
    </main>
</div>

<script>
    function toggleSection(section) {
        const viewDiv = document.getElementById('view' + section);
        const editDiv = document.getElementById('edit' + section);
        
        if (viewDiv.classList.contains('hidden')) {
          
            viewDiv.classList.remove('hidden');
            editDiv.classList.add('hidden');
        } else {
         
            viewDiv.classList.add('hidden');
            editDiv.classList.remove('hidden');
        }
    }
</script>

<?php require_once 'includes/footer.php'; ?>
