<?php
session_start();

// Include database connection
require_once '../includes/db_connection.php';

$errorMsg = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']);
    
    // First check if user is an admin
    $admin_query = "SELECT * FROM Admin WHERE email = '$email' AND password = '$password'";
    $admin_result = $conn->query($admin_query);
    
    if ($admin_result && $admin_result->num_rows > 0) {
        // Admin login successful
        $admin = $admin_result->fetch_assoc();
        $_SESSION['admin_id'] = $admin['adminID'];
        $_SESSION['admin_name'] = $admin['firstName'] . ' ' . $admin['lastName'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['user_type'] = 'admin';
        
        // Redirect to admin dashboard
        header("Location: admin_dashboard.php");
        exit();
    } else {
        // If not admin, check if regular user
        $user_query = "SELECT * FROM User WHERE email = '$email' AND password = '$password'";
        $user_result = $conn->query($user_query);
        
        if ($user_result && $user_result->num_rows > 0) {
            // User login successful
            $user = $user_result->fetch_assoc();
            $_SESSION['user_id'] = $user['userID'];
            $_SESSION['user_name'] = $user['name'] . ' ' . $user['surname'];
            $_SESSION['user_type'] = 'user';
            
            // Redirect to user dashboard
            header("Location: user_dashboard.php");
            exit();
        } else {
            // Login failed
            $errorMsg = "Invalid email or password";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GameHub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #1a1a2e;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-image: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('../../img/gaming-background.jpg');
            background-size: cover;
            background-position: center;
        }
        
        .login-container {
            width: 400px;
            background-color: rgba(22, 33, 62, 0.9);
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
        }
        
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-logo h1 {
            font-size: 3rem;
            font-weight: bold;
            color: #ff6b00;
            text-shadow: 0 0 20px rgba(255,107,0,0.5);
        }
        
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .form-group label {
            font-size: 16px;
            font-weight: 500;
        }
        
        .form-group input {
            padding: 12px 15px;
            border-radius: 5px;
            border: 1px solid #0f3460;
            background-color: rgba(15, 52, 96, 0.7);
            color: #fff;
            font-size: 16px;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #FF6B00;
            box-shadow: 0 0 10px rgba(255,107,0,0.3);
        }
        
        .submit-btn {
            background-color: #FF6B00;
            color: white;
            border: none;
            padding: 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .submit-btn:hover {
            background-color: #FF8C00;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255, 107, 0, 0.3);
        }
        
        .error-message {
            background-color: rgba(220, 53, 69, 0.2);
            color: #ff6b6b;
            padding: 12px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .register-link {
            text-align: center;
            margin-top: 20px;
            color: #ddd;
        }
        
        .register-link a {
            color: #FF6B00;
            text-decoration: none;
        }
        
        .register-link a:hover {
            text-decoration: underline;
            text-shadow: 0 0 5px rgba(255,107,0,0.5);
        }
        
        .back-to-home {
            position: absolute;
            top: 20px;
            left: 20px;
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            background-color: rgba(15, 52, 96, 0.7);
            padding: 10px 15px;
            border-radius: 5px;
            border: 1px solid #ff6b00;
        }
        
        .back-to-home:hover {
            color: #FF6B00;
            transform: translateX(-5px);
            background-color: rgba(15, 52, 96, 0.9);
            box-shadow: 0 0 15px rgba(255,107,0,0.3);
        }
    </style>
</head>
<body>
    <a href="../../index.php" class="back-to-home">
        <i class="fas fa-arrow-left"></i> Back to Home
    </a>
    
    <div class="login-container">
        <div class="login-logo">
            <h1>GameHUB</h1>
        </div>
        
        <?php if(!empty($errorMsg)): ?>
            <div class="error-message">
                <?php echo $errorMsg; ?>
            </div>
        <?php endif; ?>
        
        <form class="login-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="submit-btn">Login</button>
        </form>
        
        <div class="register-link">
            Don't have an account? <a href="../module/register.php">Register now</a>
        </div>
    </div>
</body>
</html>