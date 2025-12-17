<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

secure_session_start();

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}

$customerId = $_SESSION['customer_id'];
$success = '';
$error = '';

// Handle Form Submission (Modal)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    
    $subject = trim($_POST['subject']);
    $description = trim($_POST['description']);
    
    if (empty($subject) || empty($description)) {
        $error = "Please fill in all required fields.";
    } else {
        try {
            // Updated INSERT based on actual schema: id, customer_id, subject, description, status, source
            $stmt = $pdo->prepare("INSERT INTO service_requests (customer_id, subject, description, source) VALUES (:cid, :subj, :desc, 'Portal')");
            $stmt->execute([
                ':cid' => $customerId,
                ':subj' => $subject,
                ':desc' => $description
            ]);
            
            // Log & Notify
            $newId = $pdo->lastInsertId();
            log_activity($_SESSION['user_id'], 'New Request', "Created Request #$newId");
            
            $success = "Request submitted successfully!";
            
            // Refresh recent requests to show the new one immediately
             $stmt = $pdo->prepare("SELECT * FROM service_requests WHERE customer_id = ? ORDER BY created_at DESC LIMIT 5");
            $stmt->execute([$customerId]);
            $recentRequests = $stmt->fetchAll();

        } catch (PDOException $e) {
            $error = "Failed to submit request: " . $e->getMessage();
        }
    }
}

// Fetch Stats
$totalRequests = $pdo->prepare("SELECT COUNT(*) FROM service_requests WHERE customer_id = ?");
$totalRequests->execute([$customerId]);
$totalReq = $totalRequests->fetchColumn();

// Fetch Active Stats
$activeRequests = $pdo->prepare("SELECT COUNT(*) FROM service_requests WHERE customer_id = ? AND status IN ('Open', 'In Progress')");
$activeRequests->execute([$customerId]);
$activeReq = $activeRequests->fetchColumn();

// Fetch Recent Requests (if not already refreshed above)
if (!isset($recentRequests)) {
    $stmt = $pdo->prepare("SELECT * FROM service_requests WHERE customer_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$customerId]);
    $recentRequests = $stmt->fetchAll();
}



$pageTitle = "My Portal - BankAssist";
require_once 'includes/header.php';
?>

<div class="flex h-screen bg-gray-50 dark:bg-brand-dark overflow-hidden transition-colors duration-300">
    
    <?php include 'includes/sidebar_customer.php'; ?>

    <main class="flex-1 flex flex-col h-screen overflow-hidden relative">
        
        <!-- Mobile Header -->
        <header class="md:hidden h-16 glass-panel border-b border-glass-border flex items-center justify-between px-4 z-20 shrink-0">
             <div class="flex items-center">
                <span class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-brand-primary to-brand-accent">BankAssist</span>
             </div>
             <button id="mobileMenuBtn" class="text-gray-500 dark:text-gray-300 focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
             </button>
        </header>

        <div class="flex-1 overflow-y-auto p-4 md:p-8 relative z-10">
            
            <!-- Welcome Section -->
            <div class="mb-8 animated-fade-in">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                    Welcome back, <?= h($_SESSION['first_name'] ?? 'Customer') ?>!
                </h1>
                <p class="text-gray-600 dark:text-gray-400">Manage your requests and track their status.</p>
            </div>
            
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-200 text-green-700 dark:bg-green-500/10 dark:border-green-500/20 dark:text-green-200 p-4 rounded-xl mb-6 flex items-center animated-fade-in">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <?= h($success) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-200 text-red-700 dark:bg-red-500/10 dark:border-red-500/20 dark:text-red-200 p-4 rounded-xl mb-6 flex items-center animated-fade-in">
                     <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <?= h($error) ?>
                </div>
            <?php endif; ?>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 animated-fade-in" style="animation-delay: 0.1s">
                <div class="glass-panel p-6 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <svg class="w-20 h-20 text-brand-primary" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path></svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Requests</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?= $totalReq ?></p>
                </div>

                <div class="glass-panel p-6 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <svg class="w-20 h-20 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">In Progress</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1"><?= $activeReq ?></p>
                </div>

                <!-- New Request Trigger -->
                <button onclick="document.getElementById('newRequestModal').classList.remove('hidden')" class="glass-panel p-6 flex flex-col items-center justify-center text-center cursor-pointer hover:border-brand-primary/50 transition-colors group text-left w-full">
                     <div class="w-12 h-12 bg-brand-primary/10 rounded-full flex items-center justify-center text-brand-primary mb-3 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                     </div>
                     <h3 class="font-semibold text-gray-900 dark:text-white">New Request</h3>
                     <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Submit a new inquiry</p>
                </button>
            </div>

            <!-- Recent Requests List -->
            <div class="glass-panel animated-fade-in" style="animation-delay: 0.2s">
                <div class="p-6 border-b border-gray-200 dark:border-white/10 flex justify-between items-center bg-gray-50/50 dark:bg-white/5">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Recent Activity</h2>
                </div>
                
                <div class="overflow-x-auto">
                    <?php if (count($recentRequests) > 0): ?>
                    <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                        <thead class="bg-gray-50 dark:bg-white/5 uppercase font-semibold text-gray-500 text-xs">
                            <tr>
                                <th class="px-6 py-4">ID</th>
                                <th class="px-6 py-4">Subject</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Date</th>
                                <th class="px-6 py-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                            <?php foreach ($recentRequests as $req): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                <td class="px-6 py-4 font-mono text-xs">#<?= $req['id'] ?></td>
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white"><?= h($req['subject']) ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide
                                        <?= match($req['status']) {
                                            'Open' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300',
                                            'In Progress' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-300',
                                            'Completed' => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-300',
                                            default => 'bg-gray-100 text-gray-700'
                                        } ?>">
                                        <?= h($req['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4"><?= date('M j', strtotime($req['created_at'])) ?></td>
                                <td class="px-6 py-4 text-right">
                                    <a href="customer_request_details.php?id=<?= $req['id'] ?>" class="text-brand-primary hover:text-brand-accent font-medium">View</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                        <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                             No requests found.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </main>
    
    <!-- New Request Modal -->
    <div id="newRequestModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('newRequestModal').classList.add('hidden')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div class="inline-block align-bottom glass-panel text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                New Service Request
                            </h3>
                            <div class="mt-4">
                                <form method="POST" action="" id="requestForm" class="space-y-4">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    
                
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subject</label>
                                        <input type="text" name="subject" class="input-field" required>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                                        <textarea name="description" rows="3" class="input-field" required></textarea>
                                    </div>

                                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-brand-primary text-base font-medium text-white hover:bg-brand-primary/90 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                            Submit Request
                                        </button>
                                        <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm" onclick="document.getElementById('newRequestModal').classList.add('hidden')">
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
