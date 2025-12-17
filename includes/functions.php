<?php
// includes/functions.php


// Set OWASP Security Headers
function set_security_headers() {
    // Prevent Clickjacking
    header("X-Frame-Options: SAMEORIGIN");
    // Prevent MIME Type Sniffing
    header("X-Content-Type-Options: nosniff");
    // Enable XSS Protection
    header("X-XSS-Protection: 1; mode=block");
    // Referrer Policy
    header("Referrer-Policy: strict-origin-when-cross-origin");
    // Content Security Policy (Basic) - Allow scripts/styles from self and common CDNs if needed
    // Adjust logic to allow inline scripts if necessary (using 'unsafe-inline' for now as existing code likely uses it)
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; connect-src 'self';");
    
    // Prevent Caching (Fixes Back Button Issue)
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

    // HSTS (HTTP Strict Transport Security) - Force HTTPS
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
    }
}

function secure_session_start() {
    set_security_headers(); // Apply headers on every page load
    
    if (session_status() === PHP_SESSION_NONE) {
        $cookieParams = session_get_cookie_params();
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '', 
            'secure' => isset($_SERVER['HTTPS']), // Auto-detect HTTPS
            'httponly' => true,
            'samesite' => 'Lax' 
        ]);
        session_start();
    }
    // Regenerate ID periodically to prevent fixation
    if (!isset($_SESSION['last_regeneration'])) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    } else {
        $interval = 60 * 30; // 30 minutes
        if (time() - $_SESSION['last_regeneration'] >= $interval) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
}

// Generate CSRF Token
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF Token
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        die('CSRF Validation Failed. Please refresh the page.'); 
    }
}

// Check Login Rate Limit (Brute Force Protection)
function check_login_rate_limit($ip_address) {
    global $pdo;
    $limit = 5; // Max attempts
    $window = 15; // Minutes
    
    // Count 'Failed Login' actions in activity_logs within window
    // We assume user_id=0 or similar for guest actions if allowed, or we just filter by IP and Action
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM activity_logs WHERE ip_address = ? AND action = 'Failed Login' AND created_at > (NOW() - INTERVAL $window MINUTE)");
    $stmt->execute([$ip_address]);
    $count = $stmt->fetchColumn();
    
    if ($count >= $limit) {
        return false; // Blocked
    }
    return true; // Allowed
}

// Log Login Attempt
function log_login_attempt($ip_address, $email) {
    global $pdo;
    // Use user_id = 0 for unauthenticated users (assuming column allows 0 or isn't foreign key constrained to existing users only)
    // If constrained, we might need a dummy user or just catch the error. 
    // Usually 'user_id' in logs is nullable or just an INT. Let's try 0.
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (0, 'Failed Login', ?, ?)");
        $stmt->execute(["Attempt for: $email", $ip_address]);
    } catch (Exception $e) {
        // Ignore logging error to prevent exposing DB structure
    }
}

// Sanitize Output
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Check Login Status
function require_login() {
    secure_session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit;
    }
}

// Check Role
function require_role($allowed_roles) {
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        // Redirect based on actual role
        if ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Staff') {
            header("Location: dashboard.php");
        } else {
            header("Location: portal.php");
        }
        exit;
    }
}

// Password Strength Check
function is_strong_password($password) {
    // Minimum 8 characters
    if (strlen($password) < 8) return false;
    // At least one uppercase
    if (!preg_match('/[A-Z]/', $password)) return false;
    // At least one lowercase
    if (!preg_match('/[a-z]/', $password)) return false;
    // At least one number
    if (!preg_match('/[0-9]/', $password)) return false;
    // At least one special character
    if (!preg_match('/[\W]/', $password)) return false;
    
    return true;
}

// Email Domain Validation (DNS MX Record Check)
function validate_email_domain($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    $domain = substr(strrchr($email, "@"), 1);
    
    // Check for MX records
    if (checkdnsrr($domain, "MX")) {
        return true;
    }
    
    return false;
}

/**
 * Log user activity to database
 */
function log_activity($user_id, $action, $details = '') {
    global $pdo;
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (:uid, :act, :det, :ip)");
        $stmt->execute([
            ':uid' => $user_id,
            ':act' => $action,
            ':det' => $details,
            ':ip' => $ip
        ]);
    } catch (Exception $e) {
    } catch (Exception $e) {
        // Silently fail logging to not disrupt user flow
        error_log("Logging failed: " . $e->getMessage());
    }
}

