<?php
include 'CS_306_Project/includes/header.php';
include 'CS_306_Project/includes/db_connection.php';

// Initialize variables
$name = $surname = $email = $password = $confirm_password = "";
$errors = [];

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get input values
    $name = trim($_POST['name']);
    $surname = trim($_POST['surname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate input
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($surname)) {
        $errors[] = "Surname is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } else {
        // Check if email already exists
        $sql = "SELECT userID FROM User WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $errors[] = "Email already exists";
        }
        
        $stmt->close();
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    if ($password != $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // If no errors, insert new user
    if (empty($errors)) {
        // In a real application, you would hash the password
        // $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // For this demo, we're using plain text (not recommended for production)
        $hashed_password = $password;
        
        // Insert new user
        $sql = "INSERT INTO User (name, surname, email, password, numberOfGamesInLibrary) VALUES (?, ?, ?, ?, 0)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $surname, $email, $hashed_password);
        
        if ($stmt->execute()) {
            // Registration successful, redirect to login page
            header("location: login.php?registered=true");
            exit;
        } else {
            $errors[] = "Registration failed: " . $conn->error;
        }
        
        $stmt->close();
    }
}
?>

<div class="auth-container">
    <h1>Create an Account</h1>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="auth-form">
        <div class="form-group">
            <label for="name">First Name</label>
            <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($name); ?>">
        </div>
        
        <div class="form-group">
            <label for="surname">Last Name</label>
            <input type="text" name="surname" id="surname" value="<?php echo htmlspecialchars($surname); ?>">
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="text" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>">
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password">
            <small>Password must be at least 6 characters long</small>
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" name="confirm_password" id="confirm_password">
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn">REGISTER</button>
        </div>
        
        <div class="form-footer">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </form>
</div>

<style>
    .auth-container {
        max-width: 500px;
        margin: 40px auto;
        background-color: #2a2a2a;
        border-radius: 3px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
        padding: 30px;
    }
    
    .auth-container h1 {
        text-align: center;
        margin-bottom: 30px;
        color: #ff7800;
        border-bottom: none;
    }
    
    .auth-form .form-group {
        margin-bottom: 20px;
    }
    
    .auth-form input {
        padding: 10px;
    }
    
    .auth-form small {
        display: block;
        margin-top: 5px;
        color: #888;
        font-size: 12px;
    }
    
    .form-actions {
        margin-top: 30px;
    }
    
    .form-actions .btn {
        width: 100%;
        padding: 12px;
        font-size: 16px;
    }
    
    .form-footer {
        margin-top: 20px;
        text-align: center;
        font-size: 14px;
    }
    
    .form-footer a {
        color: #ff7800;
        text-decoration: none;
    }
    
    .form-footer a:hover {
        text-decoration: underline;
    }
    
    .alert ul {
        margin-left: 20px;
    }
</style>

<?php
include '../includes/footer.php';
?>