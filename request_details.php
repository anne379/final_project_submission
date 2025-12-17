<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

secure_session_start();
require_login();
require_role(['Staff', 'Admin']);

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$requestId = $_GET['id'];
$userId = $_SESSION['user_id'];
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'Admin';
$success = '';
$error = '';


$stmt = $pdo->prepare("
    SELECT r.*, c.first_name, c.last_name, c.account_number, c.email as customer_email, c.phone_number, u.username as assigned_to_name
    FROM service_requests r
    JOIN customers c ON r.customer_id = c.id
    LEFT JOIN users u ON r.assigned_to_user_id = u.id
    WHERE r.id = :id
");
$stmt->execute([':id' => $requestId]);
$request = $stmt->fetch();

if (!$request) {
    die("Request not found.");
}

if (!$isAdmin && $request['assigned_to_user_id'] != $userId) {
     die("Access Denied: You are not assigned to this request.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'update_status') {
             $newStatus = $_POST['status'];
             try {
                 $completedAt = ($newStatus === 'Completed') ? date('Y-m-d H:i:s') : null;
                 $sql = "UPDATE service_requests SET status = :status";
                 if ($completedAt) $sql .= ", completed_at = :cat";
                 $sql .= " WHERE id = :rid";
                 
                 $stmt = $pdo->prepare($sql);
                 $params = [':status' => $newStatus, ':rid' => $requestId];
                 if ($completedAt) $params[':cat'] = $completedAt;
                 $stmt->execute($params);
                 $success = "Status updated to $newStatus.";
                
                 $request['status'] = $newStatus;
             } catch (PDOException $e) {
                 $error = "Error updating status.";
             }
        } elseif ($_POST['action'] === 'assign' && $isAdmin) {
             $assignTo = $_POST['assigned_to'];
             try {
                 $stmt = $pdo->prepare("UPDATE service_requests SET assigned_to_user_id = :uid WHERE id = :rid");
                 $stmt->execute([':uid' => $assignTo ?: null, ':rid' => $requestId]);
                 $success = "Assignment updated.";
                 
             } catch (PDOException $e) {
                 $error = "Assignment failed.";
             }
        } elseif ($_POST['action'] === 'send_message') {
             $message = trim($_POST['message']);
             if (!empty($message)) {
                 try {
                    $stmt = $pdo->prepare("INSERT INTO request_messages (request_id, sender_user_id, message) VALUES (:rid, :uid, :msg)");
                    $stmt->execute([
                        ':rid' => $requestId,
                        ':uid' => $_SESSION['user_id'],
                        ':msg' => $message
                    ]);
        
                    
                    if ($request['status'] === 'Open') {
                        $pdo->prepare("UPDATE service_requests SET status = 'In Progress' WHERE id = :id")->execute([':id' => $requestId]);
                      
                        $request['status'] = 'In Progress';
                    }
                    
                    log_activity($_SESSION['user_id'], 'Reply', "Replied to Request #$requestId");
        
                   
                    require_once 'includes/mailer.php';
                   
                    NotificationMailer::sendRequestUpdate($request['customer_email'], $requestId, $request['subject']);
                 } catch (Exception $e) {
                     $error = "Message sent, but failed to send email notification: " . h($e->getMessage());
                    
                     if ($e instanceof PDOException) {
                         $error = "Failed to save message: " . h($e->getMessage());
                     }
                 }
             }
        }
    }
}


$msgStmt = $pdo->prepare("
    SELECT m.*, u.username, u.role
    FROM request_messages m
    LEFT JOIN users u ON m.sender_user_id = u.id
    WHERE m.request_id = :rid
    ORDER BY m.created_at ASC
");
$msgStmt->execute([':rid' => $requestId]);
$messages = $msgStmt->fetchAll();


$allStaff = [];
if ($isAdmin) {
    $allStaff = $pdo->query("SELECT id, username FROM users WHERE role = 'Staff'")->fetchAll();
}

$pageTitle = "Request #$requestId - BankAssist";
require_once 'includes/header.php';
?>

<div class="flex h-screen bg-gray-50 dark:bg-brand-dark overflow-hidden">
    
    <?php include 'includes/sidebar_staff.php'; ?>


    <main class="flex-1 flex flex-col h-screen overflow-hidden relative">
      
        <header class="md:hidden h-16 glass-panel border-b border-glass-border flex items-center justify-between px-4 z-20">
             <span class="text-lg font-bold text-white">Request #<?= $requestId ?></span>
             <a href="logout.php" class="text-xs text-gray-300">Logout</a>
        </header>

        <div class="flex-1 overflow-y-auto p-4 md:p-8">
            <a href="dashboard.php" class="inline-flex items-center text-sm text-gray-400 hover:text-white mb-6">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back
            </a>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              
                <div class="lg:col-span-2 space-y-6">
             
                    <div class="glass-panel p-6">
                        <div class="flex justify-between items-start mb-4">
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white"><?= h($request['subject']) ?></h1>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                <?= match($request['status']) {
                                    'Open' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300',
                                    'In Progress' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-300',
                                    'Completed' => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-300',
                                    default => 'bg-gray-100 text-gray-700 dark:bg-gray-500/20 dark:text-gray-300'
                                } ?>">
                                <?= h($request['status']) ?>
                            </span>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 mb-6"><?= nl2br(h($request['description'])) ?></p>
                        
                        <div class="flex items-center text-xs text-gray-500 space-x-4 border-t border-gray-200 dark:border-white/10 pt-4">
                            <span>Submitted: <?= date('M j, Y g:i A', strtotime($request['created_at'])) ?></span>
                            <?php if ($request['completed_at']): ?>
                                <span>Completed: <?= date('M j, Y g:i A', strtotime($request['completed_at'])) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

               
                    <div class="space-y-4">
                        <h3 class="font-semibold text-gray-900 dark:text-white">Chat</h3>
                        
                        <div class="space-y-4 h-96 overflow-y-auto p-4 md:p-6" id="messageContainer">
                            <?php if (empty($messages)): ?>
                                <p class="text-sm text-gray-500 italic text-center mt-10">No messages yet.</p>
                            <?php else: ?>
                                <?php foreach ($messages as $msg): ?>
                                    <?php 
                                
                                        $isMe = ($msg['sender_user_id'] == $userId);
                                        $isStaffMessage = !empty($msg['username']);
                                        
                                    
                                        $align = $isMe ? 'justify-end' : 'justify-start';
                                        
                                        
                                        $bgColor = $isMe ? 'bg-blue-600 text-white rounded-tr-none' : 'bg-gray-100 dark:bg-white/10 text-gray-800 dark:text-gray-200 rounded-tl-none';
                                        $nameAlign = $isMe ? 'text-right mr-1' : 'text-left ml-1';
                                        
                                     
                                        if ($isMe) {
                                            $displayName = 'Me';
                                        } elseif ($isStaffMessage) {
                                            $displayName = $msg['username'] . ' (Staff)';
                                        } else {
                                            $displayName = $request['first_name'] . ' ' . $request['last_name'];
                                        }
                                    ?>
                                    <div class="flex <?= $align ?>">
                                        <div class="max-w-[85%] md:max-w-[70%]">
                                            <div class="text-xs text-gray-500 mb-1 <?= $nameAlign ?>">
                                                 <?= h($displayName) ?> • <?= date('g:i A', strtotime($msg['created_at'])) ?>
                                            </div>
                                            <div class="p-3 rounded-2xl text-sm shadow-sm <?= $bgColor ?>">
                                                <?= nl2br(h($msg['message'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <div id="scrollAnchor"></div>
                        </div>

                        <!-- Auto-scroll script -->
                        <script>
                            const msgContainer = document.getElementById('messageContainer');
                            if(msgContainer) msgContainer.scrollTop = msgContainer.scrollHeight;
                        </script>

          
                        <div class="p-4 border-t border-white/10 bg-white/5">
                            <form method="POST" action="">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                <input type="hidden" name="action" value="send_message">
                                <div class="flex space-x-2">
                                    <input type="text" name="message" class="input-field rounded-full" placeholder="Type a message..." required autocomplete="off">
                                    <button type="submit" class="bg-brand-primary hover:bg-brand-primary/80 text-white p-2 rounded-full w-10 h-10 flex items-center justify-center transition-colors">
                                        <svg class="w-5 h-5 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                
                <div class="lg:col-span-1 space-y-6">
           
                    <div class="glass-panel p-6">
                        <h3 class="font-semibold text-white mb-4">Actions</h3>
                        
      
                        <form method="POST" action="" class="mb-6">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <input type="hidden" name="action" value="update_status">
                            <label class="block text-xs text-gray-500 mb-2">Update Status</label>
                            <select name="status" onchange="this.form.submit()" class="input-field mb-2">
                                <option value="Open" <?= $request['status'] == 'Open' ? 'selected' : '' ?>>Open</option>
                                <option value="In Progress" <?= $request['status'] == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                <option value="Completed" <?= $request['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                            </select>
                        </form>

                   
                        <?php if ($isAdmin): ?>
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <input type="hidden" name="action" value="assign">
                            <label class="block text-xs text-gray-500 mb-2">Re-Assign Request</label>
                            <div class="flex space-x-2">
                                <select name="assigned_to" class="input-field">
                                    <option value="">-- Unassigned --</option>
                                    <?php foreach ($allStaff as $staff): ?>
                                        <option value="<?= $staff['id'] ?>" <?= $request['assigned_to_user_id'] == $staff['id'] ? 'selected' : '' ?>>
                                            <?= h($staff['username']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="bg-white/10 hover:bg-white/20 text-white px-3 rounded-lg text-sm">Update</button>
                            </div>
                        </form>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="mt-4 p-3 bg-green-500/20 text-green-200 text-xs rounded border border-green-500/30">
                                <?= h($success) ?>
                            </div>
                        <?php endif; ?>
                         <?php if ($error): ?>
                            <div class="mt-4 p-3 bg-red-500/20 text-red-200 text-xs rounded border border-red-500/30">
                                <?= h($error) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                   
                    <div class="glass-panel p-6">
                        <h3 class="font-semibold text-white mb-4">Customer Info</h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Name</span>
                                <span class="text-gray-300"><?= h($request['first_name'] . ' ' . $request['last_name']) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Email</span>
                                <span class="text-gray-300"><?= h($request['customer_email']) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Phone</span>
                                <span class="text-gray-300"><?= h($request['phone_number']) ?></span>
                            </div>
                             <div class="flex justify-between">
                                <span class="text-gray-500">ID Number</span>
                                <span class="text-gray-300"><?= h($request['id_number']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>
</div>

<script>

    const container = document.getElementById('messageContainer');
    if(container) container.scrollTop = container.scrollHeight;
</script>

<?php require_once 'includes/footer.php'; ?>
