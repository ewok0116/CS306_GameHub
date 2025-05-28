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
$game_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($game_id <= 0) {
    header("Location: user_dashboard.php");
    exit;
}

// Check if user owns the game
$library_check_query = "SELECT * FROM Library WHERE userID = ? AND gameID = ?";
$library_stmt = $conn->prepare($library_check_query);
$library_stmt->bind_param("ii", $user_id, $game_id);
$library_stmt->execute();
$library_result = $library_stmt->get_result();
$is_in_library = $library_result->num_rows > 0;

// Fetch game details
$game_query = "
    SELECT g.gameID, g.gameName, g.price, g.likeCount, 
           p.publisherName AS publishers,
           GROUP_CONCAT(DISTINCT gen.genreName) AS genres,
           GROUP_CONCAT(DISTINCT plat.platformName) AS platforms,
           (SELECT COUNT(*) FROM Liked l WHERE l.gameID = g.gameID) as total_likes,
           (SELECT COUNT(*) FROM Liked l WHERE l.gameID = g.gameID AND l.userID = ?) as user_liked
    FROM Game g
    LEFT JOIN PublishedBy pb ON g.gameID = pb.gameID
    LEFT JOIN Publisher p ON pb.publisherName = p.publisherName
    LEFT JOIN Game_Genre gg ON g.gameID = gg.gameID
    LEFT JOIN Genre gen ON gg.genreName = gen.genreName
    LEFT JOIN Game_Platform gp ON g.gameID = gp.gameID
    LEFT JOIN Platform plat ON gp.platformName = plat.platformName
    WHERE g.gameID = ?
    GROUP BY g.gameID, g.gameName, g.price, g.likeCount, p.publisherName
";
$game_stmt = $conn->prepare($game_query);
$game_stmt->bind_param("ii", $user_id, $game_id);
$game_stmt->execute();
$game_result = $game_stmt->get_result();

if ($game_result->num_rows == 0) {
    header("Location: user_dashboard.php");
    exit;
}

$game = $game_result->fetch_assoc();

// Fetch reviews
$reviews_query = "
    SELECT r.reviewText, u.name, u.surname 
    FROM Displaying_Reviews dr
    JOIN Review r ON dr.reviewID = r.reviewID
    JOIN Writing_Reviews wr ON r.reviewID = wr.reviewID
    JOIN User u ON wr.userID = u.userID
    WHERE dr.gameID = ?
    LIMIT 5
";
$reviews_stmt = $conn->prepare($reviews_query);
$reviews_stmt->bind_param("i", $game_id);
$reviews_stmt->execute();
$reviews_result = $reviews_stmt->get_result();

// Handle Like/Unlike
if (isset($_POST['like_action'])) {
    $like_query = $is_liked ? 
        "DELETE FROM Liked WHERE userID = ? AND gameID = ?" :
        "INSERT INTO Liked (userID, gameID) VALUES (?, ?)";
    
    $like_stmt = $conn->prepare($like_query);
    $like_stmt->bind_param("ii", $user_id, $game_id);
    $like_stmt->execute();
    
    // Update like count in Game table
    $update_like_count_query = $is_liked ?
        "UPDATE Game SET likeCount = likeCount - 1 WHERE gameID = ?" :
        "UPDATE Game SET likeCount = likeCount + 1 WHERE gameID = ?";
    $update_like_stmt = $conn->prepare($update_like_count_query);
    $update_like_stmt->bind_param("i", $game_id);
    $update_like_stmt->execute();
    
    header("Location: game_page.php?id=" . $game_id);
    exit;
}

// Handle Buy/Add to Library
if (isset($_POST['buy_game'])) {
    // First, check if game is already in library
    if (!$is_in_library) {
        // Insert into Library
        $add_to_library_query = "INSERT INTO Library (userID, gameID, dateAdded) VALUES (?, ?, CURRENT_DATE)";
        $library_stmt = $conn->prepare($add_to_library_query);
        $library_stmt->bind_param("ii", $user_id, $game_id);
        $library_stmt->execute();
        
        // Update numberOfGamesInLibrary
        $update_games_count_query = "UPDATE User SET numberOfGamesInLibrary = numberOfGamesInLibrary + 1 WHERE userID = ?";
        $count_stmt = $conn->prepare($update_games_count_query);
        $count_stmt->bind_param("i", $user_id);
        $count_stmt->execute();
        
        header("Location: game_page.php?id=" . $game_id);
        exit;
    }
}

$is_liked = $game['user_liked'] > 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($game['gameName']); ?> - GameHub</title>
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
            line-height: 1.6;
        }
        
        .game-image-container {
            position: relative;
            width: 400px;
            margin-bottom: 15px;
        }
        
        .dashboard-link {
            position: absolute;
            bottom: 10px;
            left: 10px;
            background-color: rgba(22, 33, 62, 0.8);
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
        
        .dashboard-link:hover {
            background-color: rgba(15, 52, 96, 0.9);
            border-color: #ff6b00;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255,107,0,0.2);
        }
        
        .dashboard-link-icon {
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
        
        .dashboard-link-info {
            line-height: 1.4;
        }
        
        .game-header {
            display: flex;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .game-image {
            width: 400px;
            height: 225px;
            object-fit: cover;
            border-radius: 12px;
        }
        
        .game-info {
            flex-grow: 1;
        }
        
        .game-title {
            font-size: 36px;
            color: #ff6b00;
            margin-bottom: 15px;
        }
        
        .game-meta {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .game-price {
            font-size: 24px;
            color: #ff6b00;
            font-weight: bold;
        }
        
        .game-actions {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .btn-buy {
            background-color: #ff6b00;
            color: #1a1a2e;
        }
        
        .btn-buy:hover {
            background-color: #ff8533;
        }
        
        .btn-like {
            background-color: #16213e;
            color: #fff;
            border: 1px solid #0f3460;
        }
        
        .btn-like:hover {
            background-color: #0f3460;
        }
        
        .btn-like.active {
            background-color: #ff6b00;
            color: #1a1a2e;
        }
        
        .game-details {
            background-color: #16213e;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .detail-section {
            margin-bottom: 15px;
        }
        
        .detail-title {
            color: #ff6b00;
            margin-bottom: 10px;
            font-weight: bold;
        }
        
        .reviews-section {
            background-color: #16213e;
            border-radius: 12px;
            padding: 20px;
        }
        
        .review {
            background-color: #0f3460;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .review-author {
            font-weight: bold;
            color: #ff6b00;
            margin-bottom: 10px;
        }
        
        .no-reviews {
            text-align: center;
            color: #ccc;
            padding: 20px;
        }
        
        .library-status {
            display: inline-block;
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            border-radius: 5px;
            margin-right: 15px;
        }
    </style>
</head>
<body>
    <div class="game-container">
        <?php 
        // Generate image filename (lowercase, replace spaces with hyphens)
        $image_filename = strtolower(str_replace(' ', '-', $game['gameName'])) . '.jpg';
        ?>
        
        <div class="game-header">
            <div class="game-image-container">
                <img class="game-image" 
                     src="../../img/<?php echo $image_filename; ?>" 
                     alt="<?php echo htmlspecialchars($game['gameName']); ?>" 
                     onerror="this.src='../../img/placeholder-game.jpg'">
                
                <a href="user_dashboard.php" class="dashboard-link">
                    <div class="dashboard-link-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="dashboard-link-info">
                        <div class="user-name">Dashboard</div>
                        <div class="user-role">Game Store</div>
                    </div>
                </a>
            </div>
            
            <div class="game-info">
                <h1 class="game-title"><?php echo htmlspecialchars($game['gameName']); ?></h1>
                
                <div class="game-meta">
                    <span class="game-price">$<?php echo number_format($game['price'], 2); ?></span>
                    
                    <?php if ($is_in_library): ?>
                        <div class="library-status">
                            <i class="fas fa-check"></i> In Your Library
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="game-actions">
                    <?php if (!$is_in_library): ?>
                        <form method="post" style="display:inline;">
                            <button type="submit" name="buy_game" class="btn btn-buy">
                                <i class="fas fa-shopping-cart"></i> Buy Game
                            </button>
                        </form>
                    <?php endif; ?>
                    
                    <form method="post" style="display:inline;">
                        <button type="submit" name="like_action" class="btn btn-like <?php echo $is_liked ? 'active' : ''; ?>">
                            <i class="fas fa-heart"></i> 
                            <?php echo $is_liked ? 'Liked' : 'Like'; ?> 
                            (<?php echo number_format($game['total_likes']); ?>)
                        </button>
                    </form>
                </div>
                
                <div class="game-details">
                    <div class="detail-section">
                        <div class="detail-title">Publisher</div>
                        <?php echo htmlspecialchars($game['publishers'] ?? 'Unknown'); ?>
                    </div>
                    
                    <div class="detail-section">
                        <div class="detail-title">Genres</div>
                        <?php echo htmlspecialchars($game['genres'] ?? 'No genres'); ?>
                    </div>
                    
                    <div class="detail-section">
                        <div class="detail-title">Platforms</div>
                        <?php echo htmlspecialchars($game['platforms'] ?? 'No platforms'); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="reviews-section">
            <h2 class="detail-title">Recent Reviews</h2>
            
            <?php if ($reviews_result->num_rows > 0): ?>
                <?php while($review = $reviews_result->fetch_assoc()): ?>
                    <div class="review">
                        <div class="review-author">
                            <?php echo htmlspecialchars($review['name'] . ' ' . $review['surname']); ?>
                        </div>
                        <div class="review-text">
                            <?php echo htmlspecialchars($review['reviewText']); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-reviews">
                    No reviews available for this game.
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>