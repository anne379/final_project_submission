<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

secure_session_start();
require_login();
require_role(['Staff', 'Admin']);

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'Admin';
$userId = $_SESSION['user_id'];


$requests = [];
if ($isAdmin) {

    $stmt = $pdo->query("SELECT r.*, c.first_name, c.last_name, u.username as assigned_to_name 
                         FROM service_requests r 
                         JOIN customers c ON r.customer_id = c.id 
                         LEFT JOIN users u ON r.assigned_to_user_id = u.id 
                         ORDER BY FIELD(r.status, 'Open', 'In Progress', 'Completed'), r.created_at DESC");
    $requests = $stmt->fetchAll();
} else {
  
    $stmt = $pdo->prepare("SELECT r.*, c.first_name, c.last_name, u.username as assigned_to_name 
                           FROM service_requests r 
                           JOIN customers c ON r.customer_id = c.id 
                           LEFT JOIN users u ON r.assigned_to_user_id = u.id 
                           WHERE r.assigned_to_user_id = :uid
                           ORDER BY FIELD(r.status, 'Open', 'In Progress', 'Completed'), r.created_at DESC");
    $stmt->execute([':uid' => $userId]);
    $requests = $stmt->fetchAll();
}

$pageTitle = "Staff Dashboard - BankAssist";
require_once 'includes/header.php';
?>

<div class="flex h-screen bg-gray-50 dark:bg-brand-dark overflow-hidden">

    <?php include 'includes/sidebar_staff.php'; ?>

  
    <main class="flex-1 flex flex-col h-screen overflow-hidden relative">
     
        <header class="md:hidden h-16 glass-panel border-b border-glass-border flex items-center justify-between px-4 z-20">
             <span class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-brand-primary to-brand-accent">
                BankAssist
            </span>
             <button class="text-white">
                 <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
             </button>
        </header>

        <div class="flex-1 overflow-y-auto p-4 md:p-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Service Requests</h2>
                <div class="text-sm text-gray-600 dark:text-gray-400">Total Requests: <span class="font-bold text-gray-900 dark:text-white"><?= count($requests) ?></span></div>
            </div>

            <div class="glass-panel overflow-hidden hidden md:block">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600 dark:text-gray-400">
                        <thead class="bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-200 uppercase font-medium">
                            <tr>
                                <th class="px-6 py-4">ID</th>
                                <th class="px-6 py-4">Customer</th>
                                <th class="px-6 py-4">Subject</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Assigned To</th>
                                <th class="px-6 py-4">Created</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                            <?php foreach ($requests as $req): ?>
                         
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors cursor-pointer group" onclick="window.location.href='request_details.php?id=<?= $req['id'] ?>'">
                                <td class="px-6 py-4 font-mono text-xs group-hover:text-brand-primary transition-colors">#<?= h($req['id']) ?></td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900 dark:text-white"><?= h($req['first_name'] . ' ' . $req['last_name']) ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-gray-700 dark:text-gray-300"><?= h($req['subject']) ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        <?php 
                                            if($req['status'] === 'Open') echo 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-200';
                                            elseif($req['status'] === 'In Progress') echo 'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-200';
                                            else echo 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-200';
                                        ?>">
                                        <?= h($req['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($req['assigned_to_user_id']): ?>
                                        <div class="flex items-center">
                                            <div class="w-6 h-6 rounded-full bg-brand-primary flex items-center justify-center text-xs text-white mr-2">
                                                <?= strtoupper(substr($req['assigned_to_name'], 0, 1)) ?>
                                            </div>
                                            <?= h($req['assigned_to_name']) ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-gray-600 italic">Unassigned</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?= date('M j, Y', strtotime($req['created_at'])) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (count($requests) === 0): ?>
                                <tr><td colspan="6" class="p-8 text-center text-gray-500">No requests found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

      
            <div class="space-y-4 md:hidden">
                <?php foreach ($requests as $req): ?>
                    <a href="request_details.php?id=<?= $req['id'] ?>" class="block">
                        <div class="glass-panel p-4 active:scale-95 transition-transform">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <span class="text-xs font-mono text-gray-500">#<?= h($req['id']) ?></span>
                                    <h3 class="text-white font-medium"><?= h($req['subject']) ?></h3>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full 
                                    <?php 
                                        if($req['status'] === 'Open') echo 'bg-blue-500/20 text-blue-200 border border-blue-500/30';
                                        elseif($req['status'] === 'In Progress') echo 'bg-yellow-500/20 text-yellow-200 border border-yellow-500/30';
                                        else echo 'bg-green-500/20 text-green-200 border border-green-500/30';
                                    ?>">
                                    <?= h($req['status']) ?>
                                </span>
                            </div>
                            
                            <p class="text-sm text-gray-300 mb-3 line-clamp-2"><?= h($req['description']) ?></p>
                            
                            <div class="flex items-center justify-between border-t border-white/5 pt-3 mt-2 text-sm">
                                <div class="flex items-center text-gray-400">
                                    <?= h($req['first_name']) ?>
                                </div>
                                <div class="text-xs text-gray-500"><?= date('M j', strtotime($req['created_at'])) ?></div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</div>

<?php require_once 'includes/footer.php'; ?>
