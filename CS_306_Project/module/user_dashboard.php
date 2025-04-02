<div class="user-actions">
               
                <a href="logout.php" class="logout-link">
                    <div class="logout-icon">
                        <i class="fas fa-sign-out-alt"></i>
                    </div>
                    <div class="user-info">
                        <div class="user-name">Logout</div>
                        <div class="user-role">End Session</div>
                    </div>
                </a>
            </div>        
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
$user_query = "SELECT * FROM User WHERE userID = $user_id";
$user_result = $conn->query($user_query);
$user = $user_result->fetch_assoc();

// Set default sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'likes';
$order_query = "";

// Set the ordering based on sort parameter
if ($sort == 'likes') {
    $order_query = "ORDER BY g.likeCount DESC";
} elseif ($sort == 'genre') {
    $order_query = "ORDER BY genres ASC";
} elseif ($sort == 'price') {
    $order_query = "ORDER BY g.price ASC";
}

// Get all games for display
$games_query = "
    SELECT g.gameID, g.gameName, g.price, g.likeCount, 
           p.publisherName AS publishers,
           GROUP_CONCAT(DISTINCT gen.genreName) AS genres
    FROM Game g
    LEFT JOIN PublishedBy pb ON g.gameID = pb.gameID
    LEFT JOIN Publisher p ON pb.publisherName = p.publisherName
    LEFT JOIN Game_Genre gg ON g.gameID = gg.gameID
    LEFT JOIN Genre gen ON gg.genreName = gen.genreName
    GROUP BY g.gameID, g.gameName, g.price, g.likeCount, p.publisherName
    $order_query
";
$games_result = $conn->query($games_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - GameHub</title>
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
        
        .dashboard {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #0f3460;
        }
        
        .welcome-message {
            font-size: 28px;
            font-weight: bold;
            color: #ff6b00;
        }
        
        .user-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .profile-link, .library-link {
            background-color: #16213e;
            padding: 12px 20px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
            color: #fff;
            transition: all 0.3s ease;
            border: 1px solid #0f3460;
        }
        
        .profile-link:hover, .library-link:hover {
            background-color: #0f3460;
            border-color: #ff6b00;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255,107,0,0.2);
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #ff6b00;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 18px;
            font-weight: bold;
            color: white;
        }
        
        .library-link-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #4a4e69;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 18px;
            color: white;
        }
        
        .user-info {
            line-height: 1.4;
        }
        
        .user-name {
            font-weight: bold;
        }
        
        .user-role {
            font-size: 12px;
            color: #ccc;
        }
        .logout-link {
            background-color: #16213e;
            padding: 12px 20px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
            color: #fff;
            transition: all 0.3s ease;
            border: 1px solid #0f3460;
        }
        
        .logout-link:hover {
            background-color: #0f3460;
            border-color: #ff6b00;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255,107,0,0.2);
        }
        
        .logout-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #8b0000;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 18px;
            color: white;
        }
        
        /* Rest of the CSS remains the same as in the previous version */
        .section-title {
            font-size: 22px;
            margin-bottom: 20px;
            color: #ff6b00;
            font-weight: bold;
            text-align: center;
        }
        
        .games-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }
        
        .sort-options {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .sort-button {
            background-color: #333;
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .sort-button:hover, .sort-button.active {
            background-color: #FF6B00;
            transform: scale(1.05);
        }
        
        .games-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 30px;
        }

        .game-card {
            background-color: #16213e;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            cursor: pointer;
            height: 100%;
            display: flex;
            flex-direction: column;
            text-decoration: none;
            color: #fff;
            border: 1px solid #0f3460;
        }
        
        .game-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(255,107,0,0.3);
            border-color: #ff6b00;
        }
        
        .game-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }
        
        .game-details {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .game-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 12px;
            color: #ff6b00;
        }
        
        .game-info {
            font-size: 14px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .game-info i {
            width: 16px;
            color: #ff6b00;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            padding: 20px;
            color: #ccc;
            font-size: 14px;
            border-top: 1px solid #0f3460;
        }
        
        .no-games-message {
            text-align: center;
            padding: 50px 20px;
            background-color: #16213e;
            border-radius: 12px;
            margin: 30px auto;
            max-width: 600px;
        }
        
        .no-games-message i {
            font-size: 48px;
            color: #ff6b00;
            margin-bottom: 20px;
        }
        
        .no-games-message h3 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .no-games-message p {
            color: #ccc;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="top-bar">
            <div class="welcome-message">Welcome, <?php echo isset($user['name']) ? $user['name'] : 'User'; ?>!</div>
            <div class="user-actions">
                <a href="my_library.php" class="library-link">
                    <div class="library-link-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="user-info">
                        <div class="user-name">My Library</div>
                        <div class="user-role">Purchased Games</div>
                    </div>
                </a>
                <a href="profile.php" class="profile-link">
                    <div class="user-avatar">
                        <?php echo isset($user['name']) ? substr($user['name'], 0, 1) : 'U'; ?>
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?php echo isset($user['name']) ? $user['name'] . ' ' . $user['surname'] : 'User Name'; ?></div>
                        <div class="user-role">User Profile</div>
                    </div>
                </a>
            </div>
        </div>
        
        <div class="sort-options">
            <a href="user_dashboard.php?sort=likes" class="sort-button <?php echo $sort == 'likes' ? 'active' : ''; ?>">
                <i class="fas fa-heart"></i> LIKES
            </a>
            <a href="user_dashboard.php?sort=genre" class="sort-button <?php echo $sort == 'genre' ? 'active' : ''; ?>">
                <i class="fas fa-gamepad"></i> GENRE
            </a>
            <a href="user_dashboard.php?sort=price" class="sort-button <?php echo $sort == 'price' ? 'active' : ''; ?>">
                <i class="fas fa-tag"></i> PRICE
            </a>
        </div>
        
        <h1 class="section-title">STORE</h1>
        
        <?php if($games_result && $games_result->num_rows > 0): ?>
            <div class="games-grid">
                <?php while($game = $games_result->fetch_assoc()): ?>
                    <a href="game_page.php?id=<?php echo $game['gameID']; ?>" class="game-card">
                        <?php 
                        // Generate image filename (lowercase, replace spaces with hyphens)
                        $image_filename = strtolower(str_replace(' ', '-', $game['gameName'])) . '.jpg';
                        ?>
                        <img class="game-image" src="../../img/<?php echo $image_filename; ?>" alt="<?php echo $game['gameName']; ?>" onerror="this.src='../../img/placeholder-game.jpg'">
                        <div class="game-details">
                            <h3 class="game-name"><?php echo $game['gameName']; ?></h3>
                            
                            <div class="game-info">
                                <i class="fas fa-building"></i>
                                <span><?php echo $game['publishers'] ? $game['publishers'] : 'Unknown Publisher'; ?></span>
                            </div>
                            
                            <div class="game-info">
                                <i class="fas fa-tags"></i>
                                <span><?php echo $game['genres'] ? $game['genres'] : 'Uncategorized'; ?></span>
                            </div>
                            
                            <div class="game-info">
                                <i class="fas fa-tag"></i>
                                <span>$<?php echo number_format($game['price'], 2); ?></span>
                            </div>
                            
                            <div class="game-info">
                                <i class="fas fa-heart"></i>
                                <span><?php echo number_format($game['likeCount']); ?> likes</span>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-games-message">
                <i class="fas fa-gamepad"></i>
                <h3>No games available</h3>
                <p>There are currently no games in our database. Please check back later.</p>
            </div>
        <?php endif; ?>
        
        <div class="footer">
            &copy; 2025 GameHub - Your Gaming Database | CS306 Project
        </div>
    </div>
</body>
</html>