<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

secure_session_start();

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}

$customerId = $_SESSION['customer_id'];
$pageTitle = "Messages - BankAssist";
require_once 'includes/header.php';

$sql = "
    SELECT sr.*, 
           (SELECT message FROM request_messages WHERE request_id = sr.id ORDER BY created_at DESC LIMIT 1) as last_message,
           (SELECT created_at FROM request_messages WHERE request_id = sr.id ORDER BY created_at DESC LIMIT 1) as last_message_time
    FROM service_requests sr
    WHERE sr.customer_id = :cid
    ORDER BY COALESCE(last_message_time, sr.created_at) DESC
";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':cid' => $customerId]);
    $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $conversations = [];
    $error = "Failed to load messages.";
}

$selectedId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$selectedRequest = null;
$messages = [];

if ($selectedId) {
   
    $stmt = $pdo->prepare("SELECT * FROM service_requests WHERE id = ? AND customer_id = ?");
    $stmt->execute([$selectedId, $customerId]);
    $selectedRequest = $stmt->fetch();
} elseif (!empty($conversations)) {
    $selectedRequest = $conversations[0];
    $selectedId = $selectedRequest['id'];
}

if ($selectedRequest) {
    try {
        $msgStmt = $pdo->prepare("
            SELECT rm.*, u.username, u.role
            FROM request_messages rm
            LEFT JOIN users u ON rm.sender_user_id = u.id
            WHERE rm.request_id = :rid
            ORDER BY rm.created_at ASC
        ");
        $msgStmt->execute([':rid' => $selectedId]);
        $messages = $msgStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Failed to load conversation details.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $selectedRequest) {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    
    if (isset($_POST['message']) && !empty(trim($_POST['message']))) {
        $message = trim($_POST['message']);
        try {
            
            $stmt = $pdo->prepare("INSERT INTO request_messages (request_id, sender_user_id, message) VALUES (:rid, NULL, :msg)");
            $stmt->execute([
                ':rid' => $selectedId,
                ':msg' => $message
            ]);
            
            header("Location: messages.php?id=$selectedId");
            exit;
        } catch (PDOException $e) {
            $error = "Failed to send message.";
        }
    }
}

?>

<div class="flex h-screen bg-gray-50 dark:bg-brand-dark overflow-hidden">

    <?php include 'includes/sidebar_customer.php'; ?>

    
    <main class="flex-1 flex flex-col h-screen overflow-hidden relative">
        <div class="flex h-full">
            <div class="<?= $selectedId ? 'hidden md:flex' : 'flex' ?> w-full md:w-80 lg:w-96 flex-col border-r border-gray-200 dark:border-white/10 bg-white dark:bg-brand-dark z-10">
                <div class="p-4 border-b border-gray-200 dark:border-white/10 flex justify-between items-center bg-gray-50 dark:bg-white/5">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Messages</h2>
                    <a href="portal.php" class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-white/10 text-gray-500 dark:text-gray-400" title="Back to Dashboard">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    </a>
                </div>
                
                <div class="flex-1 overflow-y-auto">
                    <?php if (empty($conversations)): ?>
                        <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                            No messages yet.
                        </div>
                    <?php else: ?>
                        <?php foreach ($conversations as $conv): ?>
                            <a href="?id=<?= $conv['id'] ?>" class="block p-4 border-b border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors <?= $selectedId == $conv['id'] ? 'bg-blue-50 dark:bg-white/10 border-l-4 border-l-brand-primary' : 'border-l-4 border-l-transparent' ?>">
                                <div class="flex justify-between items-start mb-1">
                                    <h3 class="font-semibold text-gray-900 dark:text-white truncate pr-2"><?= h($conv['subject']) ?></h3>
                                    <span class="text-xs text-gray-500 whitespace-nowrap">
                                        <?= $conv['last_message_time'] ? date('M j', strtotime($conv['last_message_time'])) : date('M j', strtotime($conv['created_at'])) ?>
                                    </span>
                                </div>
                                <p class="text-xs text-gray-600 dark:text-gray-400 truncate mb-2">
                                    <?= $conv['last_message'] ? h($conv['last_message']) : '<span class="italic">No messages yet</span>' ?>
                                </p>
                                <div class="flex items-center">
                                    <span class="px-2 py-0.5 text-[10px] rounded-full uppercase font-bold tracking-wider
                                        <?php 
                                            if($conv['status'] === 'Open') echo 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-200';
                                            elseif($conv['status'] === 'In Progress') echo 'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-200';
                                            else echo 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-200';
                                        ?>">
                                        <?= h($conv['status']) ?>
                                    </span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

          
            <div class="flex-1 flex flex-col bg-gray-50 dark:bg-brand-dark relative <?= !$selectedId ? 'hidden md:flex' : 'flex' ?>">
                <?php if ($selectedRequest): ?>
                   
                    <div class="h-16 px-6 border-b border-gray-200 dark:border-white/10 flex items-center justify-between bg-white dark:bg-white/5 shrink-0">
                        <div class="flex items-center">
                            <a href="messages.php" class="md:hidden mr-4 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                            </a>
                            <div class="overflow-hidden">
                                <h2 class="text-lg font-bold text-gray-900 dark:text-white truncate"><?= h($selectedRequest['subject']) ?></h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Ref: #<?= h($selectedRequest['id']) ?></p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <a href="customer_request_details.php?id=<?= $selectedRequest['id'] ?>" class="text-sm text-brand-primary hover:underline hidden md:block">
                                View Details
                            </a>
                        </div>
                    </div>

                  
                    <div class="flex-1 overflow-y-auto p-4 md:p-6 space-y-4" id="messagesContainer">
                      
                        <div class="flex justify-start">
                             <div class="max-w-[85%] md:max-w-[70%]">
                                <div class="text-xs text-gray-500 mb-1 ml-1" >Me • <?= date('g:i A', strtotime($selectedRequest['created_at'])) ?></div>
                                <div class="p-4 rounded-2xl rounded-tl-none bg-gray-200 dark:bg-white/10 text-gray-800 dark:text-gray-200 text-sm shadow-sm">
                                    <p class="font-semibold mb-1 block text-xs uppercase tracking-wide opacity-70">Original Request</p>
                                    <?= nl2br(h($selectedRequest['description'])) ?>
                                </div>
                            </div>
                        </div>

                        <?php foreach ($messages as $msg): ?>
                            <?php $isMe = is_null($msg['sender_user_id']);  ?>
                            <div class="flex <?= $isMe ? 'justify-end' : 'justify-start' ?>">
                                <div class="max-w-[85%] md:max-w-[70%]">
                                    <div class="text-xs text-gray-500 mb-1 <?= $isMe ? 'text-right mr-1' : 'text-left ml-1' ?>">
                                        <?= $isMe ? 'Me' : h($msg['username'] ?? 'Support') ?> • <?= date('g:i A', strtotime($msg['created_at'])) ?>
                                    </div>
                                    <div class="p-3 rounded-2xl text-sm shadow-sm
                                        <?= $isMe 
                                            ? 'bg-brand-primary text-white rounded-tr-none' 
                                            : 'bg-white border border-gray-200 text-gray-800 dark:bg-white/10 dark:border-transparent dark:text-gray-200 rounded-tl-none' 
                                        ?>">
                                        <?= nl2br(h($msg['message'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div id="scrollAnchor"></div>
                    </div>

            
                    <div class="p-4 bg-white dark:bg-white/5 border-t border-gray-200 dark:border-white/10 shrink-0">
                        <form method="POST" action="" class="flex gap-2">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <input type="text" name="message" class="flex-1 input-field rounded-full px-6" placeholder="Type a message..." required autocomplete="off">
                            <button type="submit" class="bg-brand-primary hover:bg-brand-primary/90 text-white p-3 rounded-full shadow-lg shadow-brand-primary/20 transition-transform active:scale-95 flex-shrink-0">
                                <svg class="w-5 h-5 translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                            </button>
                        </form>
                    </div>

                    <script>
                        
                        const container = document.getElementById('messagesContainer');
                        container.scrollTop = container.scrollHeight;
                    </script>

                <?php else: ?>
                    <div class="flex-1 flex flex-col items-center justify-center text-center p-8 text-gray-400">
                        <div class="w-20 h-20 rounded-full bg-gray-100 dark:bg-white/5 flex items-center justify-center mb-4">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Your Messages</h3>
                        <p class="max-w-md">Select a conversation from the list to view details or send a reply.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
