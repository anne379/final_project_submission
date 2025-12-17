<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

secure_session_start();
require_login();
require_role(['Admin']);

$success = '';
$error = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add_staff') {
            $username = trim($_POST['username']);
            $password = $_POST['password'];
            
            if (empty($username) || empty($password)) {
                $error = "All fields are required.";
            } elseif (strlen($password) < 6) {
                $error = "Password too short.";
            } else {
                try {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (:u, :h, 'Staff')");
                    $stmt->execute([':u' => $username, ':h' => $hash]);
                    $success = "Staff member '$username' created successfully.";
                } catch (PDOException $e) {
                    if ($e->getCode() == 23000) { 
                         $error = "Username already exists.";
                    } else {
                         $error = "Failed to create staff: " . $e->getMessage();
                    }
                }
            }
        } elseif ($_POST['action'] === 'delete_staff') {
             $staffId = $_POST['staff_id'];
             try {
                 $pdo->beginTransaction();
                 $pdo->prepare("UPDATE service_requests SET assigned_to_user_id = NULL WHERE assigned_to_user_id = :uid")->execute([':uid' => $staffId]);
                 $pdo->prepare("DELETE FROM users WHERE id = :uid AND role = 'Staff'")->execute([':uid' => $staffId]);
                 $pdo->commit();
                 $success = "Staff member deleted.";
             } catch (PDOException $e) {
                 $pdo->rollBack();
                 $error = "Failed to delete: " . $e->getMessage();
             }
        }
    }
}


$staffMembers = $pdo->query("SELECT * FROM users WHERE role = 'Staff' ORDER BY id DESC")->fetchAll();

$pageTitle = "Manage Staff - BankAssist";
require_once 'includes/header.php';
?>

<div class="flex h-screen bg-gray-50 dark:bg-brand-dark overflow-hidden">

    <?php include 'includes/sidebar_staff.php'; ?>

   
    <main class="flex-1 flex flex-col h-screen overflow-hidden relative">
        <div class="flex-1 overflow-y-auto p-4 md:p-8">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Staff Management</h1>
                <p class="text-gray-600 dark:text-gray-400 text-sm">Create and remove staff access.</p>
            </div>

            <?php if ($success): ?>
                <div class="mb-6 p-4 bg-green-100 border border-green-200 text-green-700 dark:bg-green-500/10 dark:border-green-500/20 dark:text-green-200 rounded-lg flex items-center animated-fade-in">
                    <?= h($success) ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="mb-6 p-4 bg-red-100 border border-red-200 text-red-700 dark:bg-red-500/10 dark:border-red-500/20 dark:text-red-200 rounded-lg flex items-center animated-fade-in">
                    <?= h($error) ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                
                <div class="md:col-span-1">
                    <div class="glass-panel p-6 sticky top-8">
                        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Add New Staff</h3>
                        <form method="POST" action="" class="space-y-4">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <input type="hidden" name="action" value="add_staff">
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Username</label>
                                <input type="text" name="username" class="input-field" placeholder="johndoe" required>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Password</label>
                                <input type="password" name="password" class="input-field" placeholder="Min 6 chars" required>
                            </div>

                            <button type="submit" class="btn-primary w-full shadow-lg shadow-brand-primary/20">
                                Create Staff Account
                            </button>
                        </form>
                    </div>
                </div>

               
                <div class="md:col-span-2">
                     <div class="glass-panel overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5">
                            <h3 class="font-semibold text-gray-900 dark:text-white">Existing Staff</h3>
                        </div>
                        <ul class="divide-y divide-gray-200 dark:divide-white/5">
                            <?php foreach ($staffMembers as $staff): ?>
                                <li class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-full bg-brand-primary flex items-center justify-center text-white font-bold mr-4">
                                            <?= strtoupper(substr($staff['username'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-white"><?= h($staff['username']) ?></div>
                                            <div class="text-xs text-gray-500">Created: <?= date('M j, Y', strtotime($staff['created_at'])) ?></div>
                                        </div>
                                    </div>
                                    
                                    <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this staff member?');">
                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                        <input type="hidden" name="action" value="delete_staff">
                                        <input type="hidden" name="staff_id" value="<?= $staff['id'] ?>">
                                        <button type="submit" class="text-red-400 hover:text-red-300 p-2 rounded hover:bg-red-500/10 transition-colors" title="Delete">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                            
                            <?php if (count($staffMembers) === 0): ?>
                                <li class="px-6 py-8 text-center text-gray-500">No staff members found.</li>
                            <?php endif; ?>
                        </ul>
                     </div>
                </div>

            </div>
        </div>
    </main>
</div>

<?php require_once 'includes/footer.php'; ?>
