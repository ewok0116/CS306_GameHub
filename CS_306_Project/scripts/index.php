<?php
// Database connection configuration
$servername = "localhost";
$username = "root"; // Most default installations use 'root'
$password = ""; // Many local installations have no password
$dbname = "gamehub";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // Log the error or handle it more gracefully
    error_log("Database Connection Failed: " . $conn->connect_error);
    die("Sorry, there was a problem connecting to the database. Please try again later.");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GameHub - Database Integration Project</title>
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
        }
        
        .container {
            width: 90%;
            margin: 0 auto;
            padding: 20px;
            max-width: 1200px;
        }
        
        .title-container {
            text-align: center;
            padding: 50px 0;
            position: relative;
            margin-bottom: 30px;
            border-bottom: 1px solid #0f3460;
        }
        
        .login-button {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: #16213e;
            color: white;
            border: 1px solid #0f3460;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .login-button:hover {
            background-color: #0f3460;
            border-color: #ff6b00;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255,107,0,0.2);
        }
        
        .register-button {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: #16213e;
            color: white;
            border: 1px solid #0f3460;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .register-button:hover {
            background-color: #0f3460;
            border-color: #ff6b00;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255,107,0,0.2);
        }
        
        .title {
            font-size: 5rem;
            font-weight: bold;
            color: #ff6b00;
            margin-bottom: 20px;
        }
        
        .subtitle {
            font-size: 1.2rem;
            color: #ccc;
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 2.5rem;
            font-weight: bold;
            color: #ff6b00;
            margin: 40px 0 30px 0;
            text-align: center;
            border-bottom: 2px solid #0f3460;
            padding-bottom: 15px;
        }
        
        .triggers-container, .procedures-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 30px;
            margin-bottom: 50px;
        }
        
        .trigger-card, .procedure-card {
            display: flex;
            width: 90%;
            max-width: 1000px;
            background-color: #16213e;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            border: 1px solid #0f3460;
            text-decoration: none;
            color: inherit;
        }
        
        .trigger-card:hover, .procedure-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(255,107,0,0.3);
            border-color: #ff6b00;
            color: inherit;
        }
        
        .trigger-icon, .procedure-icon {
            width: 300px;
            height: 200px;
            background: linear-gradient(135deg, #0f3460, #16213e);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: #ff6b00;
        }
        
        .trigger-details, .procedure-details {
            flex: 1;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .trigger-name, .procedure-name {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #ff6b00;
        }
        
        .trigger-info, .procedure-info {
            font-size: 16px;
            margin-bottom: 10px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            line-height: 1.5;
        }
        
        .trigger-info i, .procedure-info i {
            width: 25px;
            color: #ff6b00;
            margin-top: 2px;
        }
        
        .support-link {
            text-align: center;
            margin: 50px 0;
        }
        
        .support-button {
            background-color: #16213e;
            color: white;
            border: 1px solid #0f3460;
            padding: 15px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 18px;
        }
        
        .support-button:hover {
            background-color: #0f3460;
            border-color: #ff6b00;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255,107,0,0.2);
        }
        
        .footer {
            margin-top: 50px;
            text-align: center;
            padding: 20px;
            background-color: #16213e;
            border-top: 1px solid #0f3460;
            color: #ccc;
        }

        @media (max-width: 768px) {
            .title {
                font-size: 3rem;
            }
            
            .trigger-card, .procedure-card {
                flex-direction: column;
                width: 95%;
            }
            
            .trigger-icon, .procedure-icon {
                width: 100%;
                height: 150px;
            }
            
            .login-button, .register-button {
                position: static;
                margin: 10px;
            }
            
            .title-container {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="title-container">
            <a href="user/register.php" class="register-button">
                <i class="fas fa-user-plus"></i> Register
            </a>
            <a href="user/login.php" class="login-button">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
            <h1 class="title">GameHUB</h1>
            <p class="subtitle">Database Integration Project - CS306 Phase III</p>
        </div>
        
        <!-- Triggers Section -->
        <h2 class="section-title"><i class="fas fa-bolt"></i> Database Triggers</h2>
        <div class="triggers-container">
            
            <a href="user/after_library_insert.php" class="trigger-card">
                <div class="trigger-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <div class="trigger-details">
                    <h2 class="trigger-name">after_library_insert</h2>
                    <div class="trigger-info">
                        <i class="fas fa-info-circle"></i>
                        <span>Automatically maintains an accurate count of games in a user's library when new games are added</span>
                    </div>
                    <div class="trigger-info">
                        <i class="fas fa-user"></i>
                        <span>Responsible: [Your Name]</span>
                    </div>
                    <div class="trigger-info">
                        <i class="fas fa-database"></i>
                        <span>Triggered on: INSERT on Library table</span>
                    </div>
                </div>
            </a>

            <a href="user/update_like_count.php" class="trigger-card">
                <div class="trigger-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="trigger-details">
                    <h2 class="trigger-name">update_like_count</h2>
                    <div class="trigger-info">
                        <i class="fas fa-info-circle"></i>
                        <span>Automatically maintains accurate like counts for games when users add them to their liked list</span>
                    </div>
                    <div class="trigger-info">
                        <i class="fas fa-user"></i>
                        <span>Responsible: [Team Member Name]</span>
                    </div>
                    <div class="trigger-info">
                        <i class="fas fa-database"></i>
                        <span>Triggered on: INSERT on Liked table</span>
                    </div>
                </div>
            </a>

            <a href="user/validate_discount.php" class="trigger-card">
                <div class="trigger-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="trigger-details">
                    <h2 class="trigger-name">validate_discount</h2>
                    <div class="trigger-info">
                        <i class="fas fa-info-circle"></i>
                        <span>Enforces valid discount ranges (0-100%) to maintain business logic integrity</span>
                    </div>
                    <div class="trigger-info">
                        <i class="fas fa-user"></i>
                        <span>Responsible: [Team Member Name]</span>
                    </div>
                    <div class="trigger-info">
                        <i class="fas fa-database"></i>
                        <span>Triggered on: INSERT/UPDATE with discount validation</span>
                    </div>
                </div>
            </a>

            <a href="user/update_admin_last_login.php" class="trigger-card">
                <div class="trigger-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="trigger-details">
                    <h2 class="trigger-name">update_admin_last_login</h2>
                    <div class="trigger-info">
                        <i class="fas fa-info-circle"></i>
                        <span>Automatically updates the updatedAt timestamp whenever an admin's last login time is modified</span>
                    </div>
                    <div class="trigger-info">
                        <i class="fas fa-user"></i>
                        <span>Responsible: [Team Member Name]</span>
                    </div>
                    <div class="trigger-info">
                        <i class="fas fa-database"></i>
                        <span>Triggered on: UPDATE of lastLogin field</span>
                    </div>
                </div>
            </a>

            <a href="user/log_admin_status_change.php" class="trigger-card">
                <div class="trigger-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="trigger-details">
                    <h2 class="trigger-name">log_admin_status_change</h2>
                    <div class="trigger-info">
                        <i class="fas fa-info-circle"></i>
                        <span>Automatically updates the updatedAt timestamp whenever an admin's active status changes</span>
                    </div>
                    <div class="trigger-info">
                        <i class="fas fa-user"></i>
                        <span>Responsible: [Team Member Name]</span>
                    </div>
                    <div class="trigger-info">
                        <i class="fas fa-database"></i>
                        <span>Triggered on: UPDATE of isActive field</span>
                    </div>
                </div>
            </a>
            
        </div>

        <!-- Stored Procedures Section -->
        <h2 class="section-title"><i class="fas fa-cogs"></i> Stored Procedures</h2>
        <div class="procedures-container">
            
            <a href="user/get_user_library.php" class="procedure-card">
                <div class="procedure-icon">
                    <i class="fas fa-list"></i>
                </div>
                <div class="procedure-details">
                    <h2 class="procedure-name">GetUserLibrary</h2>
                    <div class="procedure-info">
                        <i class="fas fa-info-circle"></i>
                        <span>Dynamically retrieves and sorts a user's game library with publisher/genre details</span>
                    </div>
                    <div class="procedure-info">
                        <i class="fas fa-user"></i>
                        <span>Responsible: [Your Name]</span>
                    </div>
                    <div class="procedure-info">
                        <i class="fas fa-list-ul"></i>
                        <span>Parameters: user_id (INT), sort_option (VARCHAR)</span>
                    </div>
                </div>
            </a>

            <a href="user/get_user_profile.php" class="procedure-card">
                <div class="procedure-icon">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="procedure-details">
                    <h2 class="procedure-name">GetUserProfile</h2>
                    <div class="procedure-info">
                        <i class="fas fa-info-circle"></i>
                        <span>Retrieves core user profile data (ID, name, email, and game library count)</span>
                    </div>
                    <div class="procedure-info">
                        <i class="fas fa-user"></i>
                        <span>Responsible: [Team Member Name]</span>
                    </div>
                    <div class="procedure-info">
                        <i class="fas fa-list-ul"></i>
                        <span>Parameters: user_id (INT)</span>
                    </div>
                </div>
            </a>

            <a href="user/get_top_liked_games_by_genre.php" class="procedure-card">
                <div class="procedure-icon">
                    <i class="fas fa-trophy"></i>
                </div>
                <div class="procedure-details">
                    <h2 class="procedure-name">GetTopLikedGamesByGenre</h2>
                    <div class="procedure-info">
                        <i class="fas fa-info-circle"></i>
                        <span>Retrieves the most popular games in a specific genre, ranked by like count</span>
                    </div>
                    <div class="procedure-info">
                        <i class="fas fa-user"></i>
                        <span>Responsible: [Team Member Name]</span>
                    </div>
                    <div class="procedure-info">
                        <i class="fas fa-list-ul"></i>
                        <span>Parameters: genre_name (VARCHAR), limit_count (INT)</span>
                    </div>
                </div>
            </a>

            <a href="user/change_user_password.php" class="procedure-card">
                <div class="procedure-icon">
                    <i class="fas fa-key"></i>
                </div>
                <div class="procedure-details">
                    <h2 class="procedure-name">change_user_password</h2>
                    <div class="procedure-info">
                        <i class="fas fa-info-circle"></i>
                        <span>Securely updates user passwords with transactional safety and explicit success feedback</span>
                    </div>
                    <div class="procedure-info">
                        <i class="fas fa-user"></i>
                        <span>Responsible: [Team Member Name]</span>
                    </div>
                    <div class="procedure-info">
                        <i class="fas fa-list-ul"></i>
                        <span>Parameters: user_id (INT), new_password (VARCHAR)</span>
                    </div>
                </div>
            </a>

            <a href="user/add_game_to_library.php" class="procedure-card">
                <div class="procedure-icon">
                    <i class="fas fa-plus"></i>
                </div>
                <div class="procedure-details">
                    <h2 class="procedure-name">add_game_to_library</h2>
                    <div class="procedure-info">
                        <i class="fas fa-info-circle"></i>
                        <span>Robustly handles game additions to user libraries with comprehensive validation checks</span>
                    </div>
                    <div class="procedure-info">
                        <i class="fas fa-user"></i>
                        <span>Responsible: [Team Member Name]</span>
                    </div>
                    <div class="procedure-info">
                        <i class="fas fa-list-ul"></i>
                        <span>Parameters: user_id (INT), game_id (INT)</span>
                    </div>
                </div>
            </a>
            
        </div>

        <!-- Support Section Link -->
        <div class="support-link">
            <a href="support/tickets.php" class="support-button">
                <i class="fas fa-life-ring"></i> Support Tickets
            </a>
        </div>
    </div>
    
    <footer class="footer">
        <p>&copy; 2025 GameHub - Database Integration Project | CS306 Project Phase III</p>
    </footer>
</body>
</html>

<?php
$conn->close();
?>