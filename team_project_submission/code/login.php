<?php
include 'functions.php';
include 'database.php';



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = loginUser($email, $password);

    if ($result && $result !== true) {
        $error_message = $result;
    }
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Delisssh — Login</title>
  <link rel="stylesheet" href="login.css">
</head>
<body>
  <div class="page">
  
    <div class="decor" aria-hidden="true">
      <div class="burgundy-sweep"></div>
    </div>

    
    <aside class="left-column" aria-hidden="true">
      <div class="photo-stack">
        <img src="shrimp.jpg" alt="" class="photo-main">
      </div>
    </aside>

   
    <main class="right-area">
      <div class="login-card">
        <h1>Delisssh</h1>

        <?php if (!empty($error_message)): ?>
          <div class="error-message">
            <?php echo htmlspecialchars($error_message); ?>
          </div>
        <?php endif; ?>

        <form id="loginForm" action="login.php" method="POST">
          <div class="field">
            <input type="email" name="email" placeholder="Email" required>
          </div>
          <div class="field">
            <input type="password" name="password" placeholder="Password" required minlength="8">
          </div>

          <button class="btn" type="submit">Login</button>

          <p class="login-link">
            Don't have an account? <a href="register.php">Sign up</a>
          </p>
        </form>
      </div>
    </main>
  </div>
</body>
</html>