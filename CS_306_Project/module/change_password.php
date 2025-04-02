<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'user') {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once '../includes/db_connection.php';

// Initialize error message
$error_message = '';

// Handle password change form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve user ID from session
    $user_id = $_SESSION['user_id'];
    
    // Get form inputs
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate inputs
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = "All fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "New passwords do not match.";
    } elseif (strlen($new_password) < 8) {
        $error_message = "New password must be at least 8 characters long.";
    } else {
        // Verify current password
        $check_password_query = "SELECT password FROM User WHERE userID = ?";
        $stmt = $conn->prepare($check_password_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        // Verify password
        if (empty($user['password']) || !password_verify($current_password, $user['password'])) {
            // Set error message in session to be displayed on profile page
            $_SESSION['password_change_error'] = "Current password is incorrect.";
            
            // Redirect to profile page
            header("Location: profile.php");
            exit;
        } else {
            // Hash new password
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password in database
            $update_password_query = "UPDATE User SET password = ? WHERE userID = ?";
            $stmt = $conn->prepare($update_password_query);
            $stmt->bind_param("si", $hashed_new_password, $user_id);
            
            if ($stmt->execute()) {
                // Verify the password was actually updated
                $verify_update_query = "SELECT password FROM User WHERE userID = ?";
                $verify_stmt = $conn->prepare($verify_update_query);
                $verify_stmt->bind_param("i", $user_id);
                $verify_stmt->execute();
                $verify_result = $verify_stmt->get_result();
                $updated_user = $verify_result->fetch_assoc();
                
                if (password_verify($new_password, $updated_user['password'])) {
                    // Set success message in session
                    $_SESSION['password_change_message'] = "Password changed successfully!";
                    
                    // Redirect to profile page
                    header("Location: profile.php");
                    exit;
                } else {
                    // Log error or handle failed update
                    error_log("Password update failed for user ID: $user_id");
                    $error_message = "Failed to update password. Please try again.";
                }
            } else {
                // Log database error
                error_log("Database error: " . $stmt->error);
                $error_message = "Failed to update password. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #1a1a2e;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #fff;
        }
        .change-password-card {
            background-color: #16213e;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            padding: 30px;
            width: 350px;
            text-align: center;
        }
        .change-password-title {
            color: #ff6b00;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
        .form-input {
            width: 100%;
            padding: 10px;
            background-color: #0f3460;
            border: none;
            border-radius: 5px;
            color: #fff;
            margin-top: 5px;
        }
        .error-message {
            color: #ff3860;
            margin-bottom: 15px;
        }
        .submit-btn {
            width: 100%;
            padding: 10px;
            background-color: #ff6b00;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .submit-btn:hover {
            background-color: #ff8533;
        }
        .back-link {
            display: block;
            color: #ff6b00;
            text-decoration: none;
            margin-top: 15px;
            transition: color 0.3s ease;
        }
        .back-link:hover {
            color: #ff8533;
        }
    </style>
</head>
<body>
    <div class="change-password-card">
        <h2 class="change-password-title">Change Password</h2>
        
        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-input" required>
            </div>
            
            <button type="submit" class="submit-btn">Change Password</button>
        </form>
        
        <a href="profile.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Profile
        </a>
    </div>
</body>
</html>