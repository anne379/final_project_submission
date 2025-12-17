<?php
require_once 'includes/db_connect.php';

echo "<h1>Setup Staff Account</h1>";

try {
    $username = 'admin';
    $password = 'admin123';
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $role = 'Admin';

   
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :u");
    $stmt->execute([':u' => $username]);
    $user = $stmt->fetch();

    if ($user) {
        $sql = "UPDATE users SET password_hash = :h, role = :r WHERE username = :u";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':h' => $hash, ':r' => $role, ':u' => $username]);
        echo "<p style='color:green'>Updated existing 'admin' user with password 'admin123'.</p>";
    } else {
        $sql = "INSERT INTO users (username, password_hash, role) VALUES (:u, :h, :r)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':u' => $username, ':h' => $hash, ':r' => $role]);
        echo "<p style='color:green'>Created new 'admin' user with password 'admin123'.</p>";
    }

} catch (PDOException $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
?>
<a href="staff_login.php">Go to Staff Login</a>
