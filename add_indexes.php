<?php
require_once 'includes/db_connect.php';

try {
    $pdo->beginTransaction();

    try {
        $pdo->exec("ALTER TABLE activity_logs ADD INDEX idx_ip_action_time (ip_address, action, created_at)");
        echo "Added index to activity_logs.<br>";
    } catch (PDOException $e) { echo "Index on activity_logs may already exist or error: " . $e->getMessage() . "<br>"; }

   
    try {
        $pdo->exec("ALTER TABLE service_requests ADD INDEX idx_customer (customer_id)");
        $pdo->exec("ALTER TABLE service_requests ADD INDEX idx_status (status)");
        $pdo->exec("ALTER TABLE service_requests ADD INDEX idx_assigned (assigned_to_user_id)");
        echo "Added indexes to service_requests.<br>";
    } catch (PDOException $e) { echo "Indexes on service_requests may already exist.<br>"; }

 
    try {
        $pdo->exec("ALTER TABLE customers ADD INDEX idx_search_composite (first_name, last_name, email, account_number)");
        echo "Added indexes to customers.<br>";
    } catch (PDOException $e) { echo "Index on customers may already exist.<br>"; }
    

    try {
         $pdo->exec("ALTER TABLE request_messages ADD INDEX idx_req_created (request_id, created_at)");
         echo "Added indexes to request_messages.<br>";
    } catch (PDOException $e) { echo "Index on request_messages may already exist.<br>"; }

    $pdo->commit();
    echo "Performance optimization complete.";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Optimization failed: " . $e->getMessage();
}
?>
