<!-- @Tina-ayim and @krystable -->
<?php
include 'functions.php';
include 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $role = $_POST['role'];

    $result = registerUser($name, $email, $password, $confirmPassword, $role);

    if ($result === "success") {
        echo "<script>alert('Registration successful!'); window.location='login.php';</script>";
    } else {
        echo "<script>alert('$result'); window.history.back();</script>";
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="register.css">
</head>
<body>
    <div class="page">
        
        <div class="decor" aria-hidden="true">
            <div class="burgundy-sweep"></div>
        </div>

        
        <aside class="left-column" aria-hidden="true">
            <div class="photo-stack">
                <img src="images/shrimp.jpg" alt="Delicious food" class="photo-main">
            </div>
        </aside>

        <main class="right-area">
            <div class="register-card">
                <h1>Register</h1>

                <form id="registerForm" action="register.php" method="POST">
                    <div class="field">
                        <input type="text" id="username" name="username" placeholder="Username" required>
                    </div>

                    <div class="field">
                        <input type="email" id="email" name="email" placeholder="Email" required>
                    </div>

                    <div class="field">
                        <input type="password" id="password" name="password" placeholder="Password" required minlength="8">
                    </div>

                    <div class="field">
                        <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm Password" required minlength="8">
                    </div>

                    <div class="field">
                        <select id="role" name="role" required>
                            <option value="" disabled selected>Select Role</option>
                            <option value="chef">Chef</option>
                            <option value="client">Client</option>
                        </select>
                    </div>

                    <button type="submit" class="btn">Register</button>

                    <div class="login-link">
                        <p>Already have an account? <a href="login.php">Login</a></p>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
