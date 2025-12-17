<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

secure_session_start();
require_login();
require_role(['Admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_customer') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $delId = $_POST['customer_id'];
    
    if ($delId) {
        try {
            $pdo->beginTransaction();
            
            
            $reqStmt = $pdo->prepare("SELECT id FROM service_requests WHERE customer_id = ?");
            $reqStmt->execute([$delId]);
            $reqIds = $reqStmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (!empty($reqIds)) {
                $inQuery = implode(',', array_fill(0, count($reqIds), '?'));
                $pdo->prepare("DELETE FROM request_messages WHERE request_id IN ($inQuery)")->execute($reqIds);
            }


            $pdo->prepare("DELETE FROM service_requests WHERE customer_id = ?")->execute([$delId]);
            

            $pdo->prepare("DELETE FROM portal_users WHERE customer_id = ?")->execute([$delId]);
            
          
            $pdo->prepare("DELETE FROM customers WHERE id = ?")->execute([$delId]);
            
            $pdo->commit();

            header("Location: admin_customers.php?msg=deleted");
            exit;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Failed to delete customer: " . $e->getMessage();
        }
    }
}

$pageTitle = "Manage Customers - BankAssist";
require_once 'includes/header.php';


$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;


$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchParams = [];
$whereClause = "";

if ($search) {
    $whereClause = "WHERE c.first_name LIKE :s OR c.last_name LIKE :s OR c.email LIKE :s OR c.account_number LIKE :s";
    $searchParams[':s'] = "%$search%";
}




$sql = "SELECT c.*, p.is_verified, p.email as portal_email 
        FROM customers c 
        LEFT JOIN portal_users p ON c.id = p.customer_id 
        $whereClause 
        ORDER BY c.created_at DESC 
        LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($searchParams);
$customers = $stmt->fetchAll();


$countSql = "SELECT COUNT(*) FROM customers c $whereClause";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($searchParams);
$totalCustomers = $countStmt->fetchColumn();
$totalPages = ceil($totalCustomers / $limit);

?>

<div class="flex h-screen bg-gray-50 dark:bg-brand-dark overflow-hidden">
   
    <?php include 'includes/sidebar_staff.php'; ?>

  
    <main class="flex-1 flex flex-col h-screen overflow-hidden relative">
        <div class="flex-1 overflow-y-auto p-4 md:p-8">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Customers</h1>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">View and manage registered customers.</p>
                </div>
                
           
                <form method="GET" action="" class="relative">
                    <input type="text" name="search" value="<?= h($search) ?>" placeholder="Search customers..." class="input-field pl-10 w-64">
                    <svg class="absolute left-3 top-3 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </form>
            </div>
            
            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
                <div class="mb-4 p-4 bg-green-100 border border-green-200 text-green-700 dark:bg-green-500/10 dark:text-green-200 rounded-lg">
                    Customer deleted successfully.
                </div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                 <div class="mb-4 p-4 bg-red-100 border border-red-200 text-red-700 dark:bg-red-500/10 dark:text-red-200 rounded-lg">
                    <?= h($error) ?>
                </div>
            <?php endif; ?>

            <div class="glass-panel overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600 dark:text-gray-400">
                        <thead class="bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-200 uppercase font-medium">
                            <tr>
                                <th class="px-6 py-4">ID</th>
                                <th class="px-6 py-4">Name</th>
                                <th class="px-6 py-4">Email</th>
                                <th class="px-6 py-4">Account #</th>
                                <th class="px-6 py-4">Phone</th>
                                <th class="px-6 py-4">Joined</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                            <?php foreach ($customers as $cust): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors group">
                                <td class="px-6 py-4 font-mono text-xs">#<?= h($cust['id']) ?></td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900 dark:text-white"><?= h($cust['first_name'] . ' ' . $cust['last_name']) ?></div>
                                    <div class="text-xs text-gray-500"><?= h($cust['id_type']) ?>: <?= h($cust['id_number']) ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <?= h($cust['portal_email']) ?>
                                </td>
                                <td class="px-6 py-4 font-mono">
                                    <?= h($cust['account_number']) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?= h($cust['phone_number']) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?= date('M j, Y', strtotime($cust['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this customer? This action cannot be undone and will delete all their requests.');">
                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                        <input type="hidden" name="action" value="delete_customer">
                                        <input type="hidden" name="customer_id" value="<?= $cust['id'] ?>">
                                        <button type="submit" class="text-red-400 hover:text-red-600 dark:hover:text-red-300 transition-colors bg-red-50 dark:bg-red-500/10 p-2 rounded-lg opacity-0 group-hover:opacity-100 focus:opacity-100">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (count($customers) === 0): ?>
                                <tr><td colspan="6" class="p-8 text-center text-gray-500">No customers found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

     
                <?php if ($totalPages > 1): ?>
                <div class="px-6 py-4 border-t border-gray-200 dark:border-white/5 flex justify-between items-center">
                    <span class="text-xs text-gray-500">Page <?= $page ?> of <?= $totalPages ?></span>
                    <div class="flex space-x-2">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 dark:bg-white/5 dark:hover:bg-white/10 rounded text-xs text-gray-700 dark:text-white">Previous</a>
                        <?php endif; ?>
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 dark:bg-white/5 dark:hover:bg-white/10 rounded text-xs text-gray-700 dark:text-white">Next</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php require_once 'includes/footer.php'; ?>
