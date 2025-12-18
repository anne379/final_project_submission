<!-- @Tina-ayim and @krystable -->
<?php
include 'database.php';
session_start();


function registerUser($name, $email, $password, $confirmPassword, $role) {
    global $conn;

    if (empty($name) || empty($email) || empty($password)) {
        return "All fields are required";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Invalid email format";
    }

    if ($password !== $confirmPassword) {
        return "Passwords do not match";
    }

    if (strlen($password) < 8) {
        return "Password must be at least 8 characters long";
    }

    $checkQuery = "SELECT email FROM client WHERE email=?
                UNION
                SELECT email FROM chef WHERE email=?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ss", $email, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        return "Email already exists!";
    }


    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);


    if ($role === 'client') {
        $query = "INSERT INTO client (name, email, password, follower_count, following_count, created_at)
                VALUES (?, ?, ?, 0, 0, NOW())";
    } elseif ($role === 'chef') {
        $query = "INSERT INTO chef (name, email, password, follower_count, following_count, created_at)
                VALUES (?, ?, ?, 0, 0, NOW())";
    } else {
        return "Invalid role selected";
    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $name, $email, $hashedPassword);

    if ($stmt->execute()) {
        return "success";
    } else {
        return "Error during registration: " . $conn->error;
    }
}




function loginUser($email, $password) {
    global $conn;

    if (empty($email) || empty($password)) {
        return "All fields are required";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Invalid email format";
    }

    
    $query = "SELECT clientId AS id, name, email, password, 'client' AS role FROM client WHERE email=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            
            session_regenerate_id(true);
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['name'];
            $_SESSION['user_email'] = $row['email'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['logged_in'] = true;

            header("Location: homepage.php");
            exit();
        } else {
            return "Incorrect password";
        }
    }

   
    $query = "SELECT chefId AS id, name, email, password, 'chef' AS role FROM chef WHERE email=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
           
            session_regenerate_id(true);
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['name'];
            $_SESSION['user_email'] = $row['email'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['logged_in'] = true;

            header("Location: chefpage.php");
            exit();
        } else {
            return "Incorrect password";
        }
    }

    return "No account found with that email";
}


function logoutUser() {
  
    session_unset();
    session_destroy();
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    header("Location: login.php");
    exit();
}

function checkLogin($requiredRole = null) {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            session_unset();
            session_destroy();
            header("Location: login.php");
        exit();
    }

    if ($requiredRole && $_SESSION['role'] !== $requiredRole) {
        $_SESSION['error'] = "Access denied - insufficient privileges";
           session_unset();
            session_destroy();
            header("Location: login.php");
        
        exit();
    }
}

function searchRecipes($term) {
    global $conn;

    $sql = "SELECT recipes.*, chef.name AS chef_name 
                      FROM recipes
                    JOIN chef ON recipes.chefId = chef.chefId
            WHERE title LIKE ? 
            OR description LIKE ?";

    $stmt = $conn->prepare($sql);

    $like = "%" . $term . "%";
    $stmt->bind_param("ss", $like, $like);

    $stmt->execute();
    $result = $stmt->get_result();

    $recipes = [];
    while ($row = $result->fetch_assoc()) {
        $recipes[] = $row;
    }

    return $recipes;
}


?>
