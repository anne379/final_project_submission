<?php
session_start();
include('database.php');

if (!isset($_SESSION['user_id'])) {
    echo "not_logged_in";
    exit();
}

if (isset($_GET['recipeId']) && is_numeric($_GET['recipeId'])) {
    $user_id = $_SESSION['user_id'];
    $recipe_id = intval($_GET['recipeId']);

    try {
        
        $check_sql = "SELECT * FROM favourite WHERE clientId = ? AND recipeId = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $user_id, $recipe_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows == 0) {
            
            $insert_sql = "INSERT INTO favourite (clientId, recipeId, created_at) VALUES (?, ?, NOW())";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("ii", $user_id, $recipe_id);
            
            if ($insert_stmt->execute()) {
                echo "added";
            } else {
                echo "error";
            }
            $insert_stmt->close();
        } else {
            
            $delete_sql = "DELETE FROM favourite WHERE clientId = ? AND recipeId = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("ii", $user_id, $recipe_id);
            
            if ($delete_stmt->execute()) {
                echo "removed";
            } else {
                echo "error";
            }
            $delete_stmt->close();
        }
        $check_stmt->close();
    } catch (Exception $e) {
        echo "error";
    }
} else {
    echo "invalid_id";
}
?>