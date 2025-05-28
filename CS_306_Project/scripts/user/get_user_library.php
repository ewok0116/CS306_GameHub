<?php
// Enable PHP error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gamehub";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$output_message = "";
$test_executed = false;

// Handle test case execution via POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['test_case'])) {
    $test_case = $_POST['test_case'];
    $test_executed = true;
    
    switch($test_case) {
        case 1:
            $output_message = executeTestCase1($conn);
            break;
        case 2:
            $output_message = executeTestCase2($conn);
            break;
        case 3:
            $output_message = executeTestCase3($conn);
            break;
        case 4:
            $output_message = executeTestCase4($conn);
            break;
        default:
            $output_message = "Invalid test case";
    }
}

function executeTestCase1($conn) {
    try {
        $user_id = 1;
        $sort_option = "recent";
        
        $stmt = $conn->prepare("CALL GetUserLibrary(?, ?)");
        $stmt->bind_param("is", $user_id, $sort_option);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $games = [];
        while ($row = $result->fetch_assoc()) {
            $games[] = $row;
        }
        
        if (empty($games)) {
            return "<div style='color: #ff4444;'>No games found in user {$user_id}'s library</div>";
        }
        
        $games_html = "";
        foreach ($games as $game) {
            $games_html .= "<div style='margin-bottom: 15px; padding: 10px; background-color: #0f3460; border-radius: 8px;'>";
            $games_html .= "<div style='font-weight: bold; color: #ff6b00;'>{$game['gameName']}</div>";
            $games_html .= "<div>Publisher: <span style='color: #4fc3f7;'>{$game['publisherID']}</span></div>";
            $games_html .= "<div>Genre: <span style='color: #a5d6a7;'>{$game['genre']}</span></div>";
            $games_html .= "<div>Added on: <span style='color: #ffcc80;'>{$game['purchaseDate']}</span></div>";
            $games_html .= "</div>";
        }
        
        return "
            <div style='text-align: left;'>
                <div style='color: #00ff00; margin-bottom: 10px; font-weight: bold;'>✓ Test Case 1 - Recent Purchases</div>
                <div style='margin-bottom: 5px; color: #bbbbbb;'>Stored Procedure: GetUserLibrary({$user_id}, '{$sort_option}')</div>
                <div style='margin-bottom: 15px; color: #bbbbbb;'>Retrieving user's library sorted by most recently added</div>
                {$games_html}
                <div style='margin-top: 10px; color: #00ff00; font-weight: bold;'>✓ SUCCESS: Retrieved user's library sorted by recent purchases!</div>
            </div>
        ";
        
    } catch (Exception $e) {
        return "<div style='color: #ff4444;'>Exception in test case 1: " . $e->getMessage() . "</div>";
    }
}

function executeTestCase2($conn) {
    try {
        $user_id = 1;
        $sort_option = "title";
        
        $stmt = $conn->prepare("CALL GetUserLibrary(?, ?)");
        $stmt->bind_param("is", $user_id, $sort_option);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $games = [];
        while ($row = $result->fetch_assoc()) {
            $games[] = $row;
        }
        
        if (empty($games)) {
            return "<div style='color: #ff4444;'>No games found in user {$user_id}'s library</div>";
        }
        
        $games_html = "";
        foreach ($games as $game) {
            $games_html .= "<div style='margin-bottom: 15px; padding: 10px; background-color: #0f3460; border-radius: 8px;'>";
            $games_html .= "<div style='font-weight: bold; color: #ff6b00;'>{$game['gameName']}</div>";
            $games_html .= "<div>Publisher: <span style='color: #4fc3f7;'>{$game['publisherID']}</span></div>";
            $games_html .= "<div>Genre: <span style='color: #a5d6a7;'>{$game['genre']}</span></div>";
            $games_html .= "<div>Added on: <span style='color: #ffcc80;'>{$game['purchaseDate']}</span></div>";
            $games_html .= "</div>";
        }
        
        return "
            <div style='text-align: left;'>
                <div style='color: #00ff00; margin-bottom: 10px; font-weight: bold;'>✓ Test Case 2 - Alphabetical by Title</div>
                <div style='margin-bottom: 5px; color: #bbbbbb;'>Stored Procedure: GetUserLibrary({$user_id}, '{$sort_option}')</div>
                <div style='margin-bottom: 15px; color: #bbbbbb;'>Retrieving user's library sorted alphabetically by title</div>
                {$games_html}
                <div style='margin-top: 10px; color: #00ff00; font-weight: bold;'>✓ SUCCESS: Retrieved user's library sorted by title!</div>
            </div>
        ";
        
    } catch (Exception $e) {
        return "<div style='color: #ff4444;'>Exception in test case 2: " . $e->getMessage() . "</div>";
    }
}

function executeTestCase3($conn) {
    try {
        $user_id = 1;
        $sort_option = "genre";
        
        $stmt = $conn->prepare("CALL GetUserLibrary(?, ?)");
        $stmt->bind_param("is", $user_id, $sort_option);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $games = [];
        while ($row = $result->fetch_assoc()) {
            $games[] = $row;
        }
        
        if (empty($games)) {
            return "<div style='color: #ff4444;'>No games found in user {$user_id}'s library</div>";
        }
        
        $games_html = "";
        foreach ($games as $game) {
            $games_html .= "<div style='margin-bottom: 15px; padding: 10px; background-color: #0f3460; border-radius: 8px;'>";
            $games_html .= "<div style='font-weight: bold; color: #ff6b00;'>{$game['gameName']}</div>";
            $games_html .= "<div>Publisher: <span style='color: #4fc3f7;'>{$game['publisherID']}</span></div>";
            $games_html .= "<div>Genre: <span style='color: #a5d6a7;'>{$game['genre']}</span></div>";
            $games_html .= "<div>Added on: <span style='color: #ffcc80;'>{$game['purchaseDate']}</span></div>";
            $games_html .= "</div>";
        }
        
        return "
            <div style='text-align: left;'>
                <div style='color: #00ff00; margin-bottom: 10px; font-weight: bold;'>✓ Test Case 3 - Sorted by Genre</div>
                <div style='margin-bottom: 5px; color: #bbbbbb;'>Stored Procedure: GetUserLibrary({$user_id}, '{$sort_option}')</div>
                <div style='margin-bottom: 15px; color: #bbbbbb;'>Retrieving user's library sorted by game genre</div>
                {$games_html}
                <div style='margin-top: 10px; color: #00ff00; font-weight: bold;'>✓ SUCCESS: Retrieved user's library sorted by genre!</div>
            </div>
        ";
        
    } catch (Exception $e) {
        return "<div style='color: #ff4444;'>Exception in test case 3: " . $e->getMessage() . "</div>";
    }
}

function executeTestCase4($conn) {
    try {
        $user_id = 999;
        $sort_option = "recent";
        
        $stmt = $conn->prepare("CALL GetUserLibrary(?, ?)");
        $stmt->bind_param("is", $user_id, $sort_option);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $games = [];
        while ($row = $result->fetch_assoc()) {
            $games[] = $row;
        }
        
        if (empty($games)) {
            return "
                <div style='text-align: left;'>
                    <div style='color: #00ff00; margin-bottom: 10px; font-weight: bold;'>✓ Test Case 4 - Non-Existent User</div>
                    <div style='margin-bottom: 5px; color: #bbbbbb;'>Stored Procedure: GetUserLibrary({$user_id}, '{$sort_option}')</div>
                    <div style='margin-bottom: 15px; color: #bbbbbb;'>Testing behavior with a user that doesn't exist</div>
                    <div style='color: #00ff00; font-weight: bold;'>✓ SUCCESS: No games returned for non-existent user as expected!</div>
                </div>
            ";
        } else {
            return "
                <div style='text-align: left;'>
                    <div style='color: #ff4444; margin-bottom: 10px; font-weight: bold;'>✗ Test Case 4 - Non-Existent User</div>
                    <div style='margin-bottom: 5px; color: #bbbbbb;'>Stored Procedure: GetUserLibrary({$user_id}, '{$sort_option}')</div>
                    <div style='margin-bottom: 15px; color: #bbbbbb;'>Expected no results for non-existent user</div>
                    <div style='color: #ff4444; font-weight: bold;'>✗ FAILURE: Got results for non-existent user!</div>
                </div>
            ";
        }
        
    } catch (Exception $e) {
        return "<div style='color: #ff4444;'>Exception in test case 4: " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GameHub - GetUserLibrary Stored Procedure</title>
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
            min-height: 100vh;
        }
        
        .container {
            width: 90%;
            margin: 0 auto;
            padding: 20px;
            max-width: 1000px;
        }
        
        .header {
            text-align: center;
            padding: 30px 0;
            border-bottom: 2px solid #0f3460;
            margin-bottom: 40px;
        }
        
        .procedure-title {
            font-size: 3rem;
            font-weight: bold;
            color: #ff6b00;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
        }
        
        .procedure-subtitle {
            font-size: 1.2rem;
            color: #ccc;
            margin-bottom: 20px;
        }
        
        .responsible {
            background-color: #16213e;
            padding: 10px 20px;
            border-radius: 8px;
            display: inline-block;
            border: 1px solid #0f3460;
        }
        
        .description-section {
            background-color: #16213e;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 40px;
            border: 1px solid #0f3460;
        }
        
        .description-title {
            font-size: 1.5rem;
            color: #ff6b00;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .description-text {
            line-height: 1.6;
            font-size: 1.1rem;
            margin-bottom: 20px;
        }
        
        .procedure-sql {
            background-color: #0f1419;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #2a2a3e;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
        }
        
        .sql-title {
            color: #ff6b00;
            margin-bottom: 10px;
            font-weight: bold;
        }
        
        .sql-code {
            color: #00ff00;
            line-height: 1.4;
            white-space: pre-wrap;
        }
        
        .procedure-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }
        
        .detail-card {
            background-color: #0f3460;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #1a4a73;
        }
        
        .detail-title {
            color: #ff6b00;
            font-weight: bold;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .test-section {
            margin-bottom: 40px;
        }
        
        .test-title {
            font-size: 2rem;
            color: #ff6b00;
            margin-bottom: 30px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }
        
        .test-cases {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .test-case {
            background-color: #16213e;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #0f3460;
            text-align: center;
        }
        
        .case-title {
            font-size: 1.3rem;
            color: #ff6b00;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .case-description {
            margin-bottom: 20px;
            line-height: 1.5;
            color: #ccc;
        }
        
        .test-button {
            background: linear-gradient(135deg, #ff6b00, #ff8c42);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
        }
        
        .test-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,107,0,0.3);
            background: linear-gradient(135deg, #ff8c42, #ff6b00);
        }
        
        .test-button:active {
            transform: translateY(0);
        }
        
        .output-section {
            background-color: #16213e;
            border: 1px solid #0f3460;
            border-radius: 12px;
            padding: 20px;
            margin-top: 30px;
            min-height: 150px;
        }
        
        .output-title {
            color: #ff6b00;
            font-weight: bold;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .output-content {
            background-color: #0f1419;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #2a2a3e;
            font-family: 'Courier New', monospace;
            line-height: 1.5;
            color: #00ff00;
            min-height: 100px;
        }
        
        .no-output {
            color: #666;
            font-style: italic;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100px;
        }
        
        .navigation {
            text-align: center;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid #0f3460;
        }
        
        .nav-button {
            background-color: #16213e;
            color: white;
            border: 1px solid #0f3460;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            margin: 0 10px;
        }
        
        .nav-button:hover {
            background-color: #0f3460;
            border-color: #ff6b00;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,107,0,0.2);
        }
        
        @media (max-width: 768px) {
            .procedure-details {
                grid-template-columns: 1fr;
            }
            
            .test-cases {
                grid-template-columns: 1fr;
            }
            
            .procedure-title {
                font-size: 2rem;
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="procedure-title">
                <i class="fas fa-gamepad"></i>
                GetUserLibrary
            </h1>
            <p class="procedure-subtitle">Stored Procedure - Retrieve User Game Library</p>
            <div class="responsible">
                <i class="fas fa-user"></i> Responsible: [Bulay Ecem Cakalli]
            </div>
        </div>

        <div class="description-section">
            <h2 class="description-title">
                <i class="fas fa-info-circle"></i>
                Procedure Description
            </h2>
            <p class="description-text">
                This stored procedure dynamically retrieves a user's game library with publisher and genre details.
                It supports multiple sorting options to organize the library based on different criteria.
            </p>
            
            <div class="procedure-sql">
                <div class="sql-title">SQL Script:</div>
                <div class="sql-code">DELIMITER //

CREATE PROCEDURE GetUserLibrary(
    IN user_id INT,
    IN sort_option VARCHAR(50)
BEGIN
    /* Retrieves and sorts a user's game library */
    IF sort_option = 'recent' THEN
        SELECT g.gameID, g.gameName, g.genre, p.publisherID, ul.purchaseDate
        FROM UserLibrary ul
        JOIN Game g ON ul.gameID = g.gameID
        JOIN Publisher p ON g.publisherID = p.publisherID
        WHERE ul.userID = user_id
        ORDER BY ul.purchaseDate DESC;
    ELSEIF sort_option = 'title' THEN
        SELECT g.gameID, g.gameName, g.genre, p.publisherID, ul.purchaseDate
        FROM UserLibrary ul
        JOIN Game g ON ul.gameID = g.gameID
        JOIN Publisher p ON g.publisherID = p.publisherID
        WHERE ul.userID = user_id
        ORDER BY g.gameName ASC;
    ELSEIF sort_option = 'genre' THEN
        SELECT g.gameID, g.gameName, g.genre, p.publisherID, ul.purchaseDate
        FROM UserLibrary ul
        JOIN Game g ON ul.gameID = g.gameID
        JOIN Publisher p ON g.publisherID = p.publisherID
        WHERE ul.userID = user_id
        ORDER BY g.genre ASC, g.gameName ASC;
    ELSEIF sort_option = 'publisher' THEN
        SELECT g.gameID, g.gameName, g.genre, p.publisherID, ul.purchaseDate
        FROM UserLibrary ul
        JOIN Game g ON ul.gameID = g.gameID
        JOIN Publisher p ON g.publisherID = p.publisherID
        WHERE ul.userID = user_id
        ORDER BY p.publisherID ASC, g.gameName ASC;
    ELSE
        -- Default sorting (by purchase date)
        SELECT g.gameID, g.gameName, g.genre, p.publisherID, ul.purchaseDate
        FROM UserLibrary ul
        JOIN Game g ON ul.gameID = g.gameID
        JOIN Publisher p ON g.publisherID = p.publisherID
        WHERE ul.userID = user_id
        ORDER BY ul.purchaseDate DESC;
    END IF;
END //

DELIMITER ;</div>
            </div>
            
            <div class="procedure-details">
                <div class="detail-card">
                    <div class="detail-title">
                        <i class="fas fa-database"></i>
                        Parameters
                    </div>
                    <p>user_id (INT), sort_option (VARCHAR)</p>
                </div>
                
                <div class="detail-card">
                    <div class="detail-title">
                        <i class="fas fa-sort"></i>
                        Sort Options
                    </div>
                    <p>'recent', 'title', 'genre', 'publisher'</p>
                </div>
                
                <div class="detail-card">
                    <div class="detail-title">
                        <i class="fas fa-table"></i>
                        Tables Used
                    </div>
                    <p>UserLibrary, Game, Publisher</p>
                </div>
                
                <div class="detail-card">
                    <div class="detail-title">
                        <i class="fas fa-info-circle"></i>
                        Returns
                    </div>
                    <p>Game details with publisher info and purchase date</p>
                </div>
            </div>
        </div>

        <div class="test-section">
            <h2 class="test-title">
                <i class="fas fa-flask"></i>
                Test Cases
            </h2>
            
            <div class="test-cases">
                <div class="test-case">
                    <h3 class="case-title">
                        <i class="fas fa-clock"></i>
                        Case 1: Recent Purchases
                    </h3>
                    <p class="case-description">
                        Test retrieving a user's library sorted by most recent purchases.
                    </p>
                    <form method="POST" style="margin: 0;">
                        <input type="hidden" name="test_case" value="1">
                        <button type="submit" class="test-button">
                            <i class="fas fa-play"></i>
                            Execute Test Case 1
                        </button>
                    </form>
                </div>

                <div class="test-case">
                    <h3 class="case-title">
                        <i class="fas fa-sort-alpha-down"></i>
                        Case 2: Alphabetical
                    </h3>
                    <p class="case-description">
                        Test retrieving a user's library sorted alphabetically by title.
                    </p>
                    <form method="POST" style="margin: 0;">
                        <input type="hidden" name="test_case" value="2">
                        <button type="submit" class="test-button">
                            <i class="fas fa-play"></i>
                            Execute Test Case 2
                        </button>
                    </form>
                </div>

                <div class="test-case">
                    <h3 class="case-title">
                        <i class="fas fa-tags"></i>
                        Case 3: By Genre
                    </h3>
                    <p class="case-description">
                        Test retrieving a user's library sorted by game genre.
                    </p>
                    <form method="POST" style="margin: 0;">
                        <input type="hidden" name="test_case" value="3">
                        <button type="submit" class="test-button">
                            <i class="fas fa-play"></i>
                            Execute Test Case 3
                        </button>
                    </form>
                </div>

                <div class="test-case">
                    <h3 class="case-title">
                        <i class="fas fa-user-slash"></i>
                        Case 4: Non-Existent User
                    </h3>
                    <p class="case-description">
                        Test behavior with a user that doesn't exist.
                    </p>
                    <form method="POST" style="margin: 0;">
                        <input type="hidden" name="test_case" value="4">
                        <button type="submit" class="test-button">
                            <i class="fas fa-play"></i>
                            Execute Test Case 4
                        </button>
                    </form>
                </div>
            </div>

            <div class="output-section">
                <h3 class="output-title">
                    <i class="fas fa-terminal"></i>
                    Output Results
                </h3>
                <div class="output-content">
                    <?php if ($test_executed && !empty($output_message)): ?>
                        <?php echo $output_message; ?>
                    <?php else: ?>
                        <div class="no-output">Click a test case button to see the results...</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="navigation">
            <a href="../index.php" class="nav-button">
                <i class="fas fa-home"></i>
                Go to Homepage
            </a>
            <a href="../support/tickets.php" class="nav-button">
                <i class="fas fa-life-ring"></i>
                Support Tickets
            </a>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>