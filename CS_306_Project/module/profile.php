<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'user') {
    header("Location: login.php");
    exit;
}
// Include database connection
require_once '../includes/db_connection.php';

// Get user details
$user_id = $_SESSION['user_id'];
$user_query = "SELECT * FROM User WHERE userID = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Handle password change message
$password_change_message = '';
if (isset($_SESSION['password_change_message'])) {
    $password_change_message = $_SESSION['password_change_message'];
    unset($_SESSION['password_change_message']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
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
        .profile-card {
            background-color: #16213e;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            padding: 30px;
            width: 350px;
            text-align: center;
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            background-color: #ff6b00;
            color: white;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 20px;
            font-size: 48px;
        }
        .profile-name {
            font-size: 24px;
            color: #ff6b00;
            margin-bottom: 20px;
        }
        .profile-info {
            text-align: left;
            margin-bottom: 20px;
        }
        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px;
            background-color: #0f3460;
            border-radius: 5px;
        }
        .info-item i {
            color: #ff6b00;
            width: 30px;
            text-align: center;
            margin-right: 15px;
        }
        .info-label {
            font-weight: bold;
            margin-right: 10px;
        }
        .back-link, .change-password-btn {
            display: block;
            color: #ff6b00;
            text-decoration: none;
            margin-top: 20px;
            transition: color 0.3s ease;
            background: none;
            border: 2px solid #ff6b00;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
        }
        .back-link:hover, .change-password-btn:hover {
            color: #ff8533;
            border-color: #ff8533;
        }
        .password-message {
            color: #ff6b00;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="profile-card">
        <div class="profile-avatar">
            <?php echo substr($user['name'], 0, 1); ?>
        </div>
        <h2 class="profile-name">
            <?php echo htmlspecialchars($user['name'] . ' ' . $user['surname']); ?>
        </h2>
        <div class="profile-info">
            <div class="info-item">
                <i class="fas fa-envelope"></i>
                <span class="info-label">Email:</span>
                <?php echo htmlspecialchars($user['email']); ?>
            </div>
        </div>
        
        <?php if (!empty($password_change_message)): ?>
            <div class="password-message">
                <?php echo htmlspecialchars($password_change_message); ?>
            </div>
        <?php endif; ?>
        
        <button onclick="window.location.href='change_password.php'" class="change-password-btn">
            <i class="fas fa-key"></i> Change Password
        </button>
        
        <a href="user_dashboard.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</body>
</html>