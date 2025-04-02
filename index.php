<?php
// Database connection
$servername = "localhost";
$username = "root"; // Most default installations use 'root'
$password = ""; // Many local installations have no password
$dbname = "gamehub";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// No success message

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

// Fetch games with additional details
$games_query = "
    SELECT g.gameID, g.gameName, g.price, g.likeCount, 
           p.publisherName AS publishers,
           GROUP_CONCAT(DISTINCT g2.genreName) AS genres
    FROM Game g
    LEFT JOIN PublishedBy pb ON g.gameID = pb.gameID
    LEFT JOIN Publisher p ON pb.publisherName = p.publisherName
    LEFT JOIN Game_Genre gg ON g.gameID = gg.gameID
    LEFT JOIN Genre g2 ON gg.genreName = g2.genreName
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
    <title>GameHub - Your Gaming Database</title>
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
        
        .sort-options {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .sort-button {
            background-color: #16213e;
            color: #fff;
            border: 1px solid #0f3460;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .sort-button:hover, .sort-button.active {
            background-color: #0f3460;
            border-color: #ff6b00;
            transform: scale(1.05);
        }
        
        .games-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 30px;
        }
        
        .game-card {
            display: flex;
            width: 90%;
            max-width: 1000px;
            background-color: #16213e;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            border: 1px solid #0f3460;
        }
        
        .game-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(255,107,0,0.3);
            border-color: #ff6b00;
        }
        
        .game-image {
            width: 300px;
            height: 300px;
            object-fit: cover;
        }
        
        .game-details {
            flex: 1;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .game-name {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #ff6b00;
        }
        
        .game-info {
            font-size: 18px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .game-info i {
            width: 25px;
            color: #ff6b00;
        }
        
        .footer {
            margin-top: 50px;
            text-align: center;
            padding: 20px;
            background-color: #16213e;
            border-top: 1px solid #0f3460;
            color: #ccc;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="title-container">
            <a href="register.php" class="register-button">
                <i class="fas fa-user-plus"></i> Register
            </a>
            <a href="CS_306_Project/module/login.php" class="login-button">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
            <h1 class="title">GameHUB</h1>
            
            <div class="sort-options">
                <a href="index.php?sort=likes" class="sort-button <?php echo $sort == 'likes' ? 'active' : ''; ?>">
                    <i class="fas fa-heart"></i> LIKES
                </a>
                <a href="index.php?sort=genre" class="sort-button <?php echo $sort == 'genre' ? 'active' : ''; ?>">
                    <i class="fas fa-gamepad"></i> GENRE
                </a>
                <a href="index.php?sort=price" class="sort-button <?php echo $sort == 'price' ? 'active' : ''; ?>">
                    <i class="fas fa-tag"></i> PRICE
                </a>
            </div>
        </div>
        
        <div class="games-container">
            <?php
            if ($games_result && $games_result->num_rows > 0) {
                while ($game = $games_result->fetch_assoc()) {
                    // Create sanitized filename for image (lowercase, replace spaces with hyphens)
                    $image_filename = strtolower(str_replace(' ', '-', $game['gameName'])) . '.jpg';
            ?>
                <div class="game-card">
                    <img class="game-image" src="img/<?php echo $image_filename; ?>" alt="<?php echo $game['gameName']; ?>" onerror="this.src='img/placeholder-game.jpg'">
                    <div class="game-details">
                        <h2 class="game-name"><?php echo $game['gameName']; ?></h2>
                        <div class="game-info">
                            <i class="fas fa-building"></i> 
                            <span>Publisher: <?php echo $game['publishers'] ? $game['publishers'] : 'Unknown'; ?></span>
                        </div>
                        <div class="game-info">
                            <i class="fas fa-gamepad"></i> 
                            <span>Genres: <?php echo $game['genres'] ? $game['genres'] : 'Uncategorized'; ?></span>
                        </div>
                        <div class="game-info">
                            <i class="fas fa-tag"></i> 
                            <span>Price: $<?php echo number_format($game['price'], 2); ?></span>
                        </div>
                        <div class="game-info">
                            <i class="fas fa-heart"></i> 
                            <span>Likes: <?php echo $game['likeCount']; ?></span>
                        </div>
                    </div>
                </div>
            <?php
                }
            } else {
                echo '<div style="text-align: center; padding: 50px;"><h2>No games found. Please check your database connection and game data.</h2></div>';
            }
            ?>
        </div>
    </div>
    
    <footer class="footer">
        <p>&copy; 2025 GameHub - Your Gaming Database | CS306 Project Phase II</p>
    </footer>
</body>
</html>