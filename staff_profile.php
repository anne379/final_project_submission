<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

secure_session_start();
require_login();
require_role(['Staff', 'Admin']);

$userId = $_SESSION['user_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    
    if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
        $currentPass = $_POST['current_password'];
        $newPass = $_POST['new_password'];
        $confirmPass = $_POST['confirm_password'];
        
        if (empty($currentPass) || empty($newPass)) {
            $error = "Please fill in all fields.";
        } elseif ($newPass !== $confirmPass) {
             $error = "New passwords do not match.";
        } elseif (strlen($newPass) < 6) {
             $error = "Password must be at least 6 characters.";
        } else {
             
             $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = :uid");
             $stmt->execute([':uid' => $userId]);
             $userHash = $stmt->fetchColumn();
             
             if (password_verify($currentPass, $userHash)) {
               
                 $newHash = password_hash($newPass, PASSWORD_BCRYPT);
                 $stmt = $pdo->prepare("UPDATE users SET password_hash = :hash WHERE id = :uid");
                 $stmt->execute([':hash' => $newHash, ':uid' => $userId]);
                 $success = "Password changed successfully.";
             } else {
                 $error = "Current password is incorrect.";
             }
        }
    }
}

$pageTitle = "Staff Profile - BankAssist";
require_once 'includes/header.php';
?>

<div class="flex h-screen bg-gray-50 dark:bg-brand-dark overflow-hidden">
    <?php include 'includes/sidebar_staff.php'; ?>

    <main class="flex-1 flex flex-col h-screen overflow-hidden relative">
        <div class="flex-1 overflow-y-auto p-4 md:p-8">
            
            <h1 class="text-2xl font-bold text-white mb-8">My Staff Profile</h1>

            <?php if ($success): ?>
                <div class="mb-6 p-4 bg-green-500/10 border border-green-500/20 text-green-200 rounded-lg flex items-center animated-fade-in">
                    <?= h($success) ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 text-red-200 rounded-lg flex items-center animated-fade-in">
                    <?= h($error) ?>
                </div>
            <?php endif; ?>

            <div class="max-w-xl">
                 <div class="space-y-6">
                     
                    
                     <div id="viewPassword" class="glass-panel p-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-semibold text-white">Security</h3>
                                <p class="text-xs text-gray-500">Manage your password</p>
                            </div>
                            <button onclick="toggleSection('Password')" class="text-xs bg-white/10 hover:bg-white/20 text-white px-3 py-1.5 rounded transition-colors">
                                Change Password
                            </button>
                        </div>
                     </div>

                     <div id="editPassword" class="glass-panel p-6 hidden">
                         <h3 class="text-lg font-semibold text-white mb-6">Change Password</h3>
                    
                        <form method="POST" action="" class="space-y-4">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <input type="hidden" name="action" value="change_password">
                            
                             <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Current Password</label>
                                <input type="password" name="current_password" class="input-field" required>
                            </div>
                            
                             <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">New Password</label>
                                <input type="password" name="new_password" class="input-field" required>
                            </div>
                            
                             <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="input-field" required>
                            </div>

                             <div class="flex space-x-3 pt-2">
                                <button type="submit" class="btn-primary">
                                    Update Password
                                </button>
                                <button type="button" onclick="toggleSection('Password')" class="bg-gray-600/50 hover:bg-gray-600/70 text-white px-4 py-2 rounded-lg transition-colors text-sm font-medium">
                                    Cancel
                                </button>
                            </div>
                        </form>
                     </div>

                 
                    <div class="glass-panel p-6">
                        <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-4">Account Stats</h3>
                        <div class="grid grid-cols-2 gap-4">
                             <div class="bg-white/5 p-4 rounded-lg text-center">
                                 <div class="text-2xl font-bold text-white mb-1">
                                     <?php 
                                        
                                        $cnt = $pdo->prepare("SELECT COUNT(*) FROM service_requests WHERE assigned_to_user_id = :uid");
                                        $cnt->execute([':uid' => $userId]);
                                        echo $cnt->fetchColumn();
                                     ?>
                                 </div>
                                 <div class="text-xs text-gray-500">My Requests</div>
                             </div>
                             <div class="bg-white/5 p-4 rounded-lg text-center">
                                 <div class="text-2xl font-bold text-white mb-1">
                                     Active
                                 </div>
                                 <div class="text-xs text-gray-500">Status</div>
                             </div>
                        </div>
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
            // Show View, Hide Edit
            viewDiv.classList.remove('hidden');
            editDiv.classList.add('hidden');
        } else {
            // Hide View, Show Edit
            viewDiv.classList.add('hidden');
            editDiv.classList.remove('hidden');
        }
    }
</script>

<?php require_once 'includes/footer.php'; ?>
