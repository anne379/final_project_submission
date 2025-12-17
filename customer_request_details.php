<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

secure_session_start();

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: portal.php");
    exit;
}

$requestId = $_GET['id'];
$customerId = $_SESSION['customer_id'];
$userId = $_SESSION['user_id']; 
$success = '';
$error = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    
    if (isset($_POST['action']) && $_POST['action'] === 'send_message') {
        $message = trim($_POST['message']);
        
        if (!empty($message)) {
            try {
               
                $checkStmt = $pdo->prepare("SELECT id FROM service_requests WHERE id = :rid AND customer_id = :cid");
                $checkStmt->execute([':rid' => $requestId, ':cid' => $customerId]);
                
                if ($checkStmt->fetch()) {
                    $insertStmt = $pdo->prepare("INSERT INTO request_messages (request_id, sender_user_id, message) VALUES (:rid, :uid, :msg)");
                    
                    
                    $insertStmt->execute([':rid' => $requestId, ':uid' => $userId, ':msg' => $message]);
                } else {
                    $error = "Invalid request.";
                }
            } catch (PDOException $e) {
                 
                 if ($e->getCode() == 23000) {
                     $error = "System Error: Message sender validation failed. (Schema Mismatch)";
        
                 } else {
                     $error = "Failed to send message.";
                 }
            }
        }
    }
}


$stmt = $pdo->prepare("SELECT * FROM service_requests WHERE id = :rid AND customer_id = :cid");
$stmt->execute([':rid' => $requestId, ':cid' => $customerId]);
$request = $stmt->fetch();

if (!$request) {
    die("Request not found or access denied.");
}
$msgStmt = $pdo->prepare("SELECT * FROM request_messages WHERE request_id = :rid ORDER BY created_at ASC");
$msgStmt->execute([':rid' => $requestId]);
$messages = $msgStmt->fetchAll();

function get_sender_name($senderId, $pdo) {
  
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = :uid");
    $stmt->execute([':uid' => $senderId]);
    if ($r = $stmt->fetch()) return $r['username'] . " (Staff)";
    
    $stmt = $pdo->prepare("SELECT c.first_name FROM customers c JOIN portal_users p ON c.id = p.customer_id WHERE p.id = :uid");
    $stmt->execute([':uid' => $senderId]);
    if ($r = $stmt->fetch()) return $r['first_name'] . " (Me)";
    
    return "Unknown";
}

$pageTitle = "Request Details - BankAssist";
require_once 'includes/header.php';
?>

<div class="flex h-screen bg-gray-50 dark:bg-brand-dark overflow-hidden">
   
    <?php include 'includes/sidebar_customer.php'; ?>

    <main class="flex-1 flex flex-col h-screen overflow-hidden relative">

        <header class="md:hidden h-16 glass-panel border-b border-glass-border flex items-center justify-between px-4 z-20">
             <span class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-brand-primary to-brand-accent">
                Request #<?= $requestId ?>
            </span>
             <a href="portal.php" class="text-gray-300">
                 Back
             </a>
        </header>

        <div class="flex-1 overflow-y-auto p-4 md:p-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-full">
            
                <div class="lg:col-span-1 space-y-6">
                    <div class="glass-panel p-6">
                        <div class="flex justify-between items-start mb-4">
                            <span class="text-xs text-gray-500 font-mono">#<?= $requestId ?></span>
                             <span class="px-3 py-1 text-sm rounded-full 
                                <?php 
                                    if($request['status'] === 'Open') echo 'bg-blue-500/20 text-blue-200 border border-blue-500/30';
                                    elseif($request['status'] === 'In Progress') echo 'bg-yellow-500/20 text-yellow-200 border border-yellow-500/30';
                                    else echo 'bg-green-500/20 text-green-200 border border-green-500/30';
                                ?>">
                                <?= h($request['status']) ?>
                            </span>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4"><?= h($request['subject']) ?></h2>
                        <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-sm mb-6"><?= h($request['description']) ?></p>
                        
                        <div class="border-t border-gray-200 dark:border-white/10 pt-4">
                             <span class="block text-gray-500 text-xs">Submitted on</span>
                             <span class="text-gray-700 dark:text-gray-300 text-sm"><?= date('M j, Y H:i', strtotime($request['created_at'])) ?></span>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 flex flex-col h-[600px] glass-panel p-0 overflow-hidden">
                    <div class="p-4 border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5">
                        <h3 class="font-semibold text-gray-900 dark:text-white">Conversation</h3>
                    </div>
                    
                    <div class="flex-1 overflow-y-auto p-4 space-y-4" id="messageContainer">
                        <?php foreach ($messages as $msg): ?>
                            <?php $isMe = $msg['sender_user_id'] == $userId; ?>
                            <div class="flex <?= $isMe ? 'justify-end' : 'justify-start' ?>">
                                <div class="max-w-[80%]">
                                    <div class="text-xs text-gray-500 mb-1 <?= $isMe ? 'text-right' : 'text-left' ?>">
                                        <?= $isMe ? 'Me' : get_sender_name($msg['sender_user_id'], $pdo) ?> • <?= date('g:i A', strtotime($msg['created_at'])) ?>
                                    </div>
                                    <div class="p-3 rounded-lg text-sm <?= $isMe ? 'bg-brand-primary text-white rounded-tr-none' : 'bg-gray-100 text-gray-800 dark:bg-white/10 dark:text-gray-200 rounded-tl-none' ?>">
                                        <?= nl2br(h($msg['message'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                         <?php if ($error): ?>
                            <div class="text-center text-red-400 text-sm my-4"><?= h($error) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="p-4 border-t border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5">
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
        </div>
    </main>
</div>

<script>
    const container = document.getElementById('messageContainer');
    if(container) container.scrollTop = container.scrollHeight;
</script>

<?php require_once 'includes/footer.php'; ?>
