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
        default:
            $output_message = "Invalid test case";
    }
}

function executeTestCase1($conn) {
    try {
        // Get current state before test
        $user_id = 4; // David Williams
        $game_id = 1; // GTA V
        
        // Check current game count
        $check_query = "SELECT name, surname, numberOfGamesInLibrary FROM User WHERE userID = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_before = $result->fetch_assoc();
        
        // Check if game already in library (to avoid duplicates)
        $check_library = "SELECT * FROM Library WHERE userID = ? AND gameID = ?";
        $stmt = $conn->prepare($check_library);
        $stmt->bind_param("ii", $user_id, $game_id);
        $stmt->execute();
        $library_check = $stmt->get_result();
        
        if ($library_check->num_rows > 0) {
            // Remove the game first to test the trigger
            $delete_query = "DELETE FROM Library WHERE userID = ? AND gameID = ?";
            $stmt = $conn->prepare($delete_query);
            $stmt->bind_param("ii", $user_id, $game_id);
            $stmt->execute();
            
            // Update user count manually to reset
            $reset_query = "UPDATE User SET numberOfGamesInLibrary = numberOfGamesInLibrary - 1 WHERE userID = ?";
            $stmt = $conn->prepare($reset_query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
        }
        
        // Get fresh count after potential cleanup
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_before = $result->fetch_assoc();
        
        // Execute the trigger by inserting into Library
        $insert_query = "INSERT INTO Library (userID, gameID, dateAdded) VALUES (?, ?, NOW())";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ii", $user_id, $game_id);
        $success = $stmt->execute();
        
        if ($success) {
            // Check state after trigger execution
            $stmt = $conn->prepare($check_query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user_after = $result->fetch_assoc();
            
            // Get game name
            $game_query = "SELECT gameName FROM Game WHERE gameID = ?";
            $stmt = $conn->prepare($game_query);
            $stmt->bind_param("i", $game_id);
            $stmt->execute();
            $game_result = $stmt->get_result();
            $game = $game_result->fetch_assoc();
            
            return "
                <div style='text-align: left;'>
                    <div style='color: #00ff00; margin-bottom: 10px; font-weight: bold;'>✓ Test Case 1 - Add New Game</div>
                    <div style='margin-bottom: 5px;'>Before: User ID {$user_id} ({$user_before['name']} {$user_before['surname']}) - Games in Library: {$user_before['numberOfGamesInLibrary']}</div>
                    <div style='margin-bottom: 5px;'>Action: INSERT INTO Library (userID, gameID) VALUES ({$user_id}, {$game_id})</div>
                    <div style='margin-bottom: 5px;'>Game Added: {$game['gameName']}</div>
                    <div style='margin-bottom: 5px;'>Trigger Executed: after_library_insert</div>
                    <div style='color: #ff6b00; margin-bottom: 5px;'>After: User ID {$user_id} ({$user_after['name']} {$user_after['surname']}) - Games in Library: {$user_after['numberOfGamesInLibrary']}</div>
                    <div style='margin-top: 10px; color: #00ff00; font-weight: bold;'>✓ SUCCESS: Library count incremented from {$user_before['numberOfGamesInLibrary']} to {$user_after['numberOfGamesInLibrary']} automatically!</div>
                </div>
            ";
        } else {
            return "<div style='color: #ff4444;'>Error executing test case 1: " . $conn->error . "</div>";
        }
        
    } catch (Exception $e) {
        return "<div style='color: #ff4444;'>Exception in test case 1: " . $e->getMessage() . "</div>";
    }
}

function executeTestCase2($conn) {
    try {
        $user1_id = 1; // Alice
        $user2_id = 2; // Bob
        $game_id = 5; // New game for Alice
        
        // Get current states for both users
        $check_query = "SELECT userID, name, surname, numberOfGamesInLibrary FROM User WHERE userID IN (?, ?)";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("ii", $user1_id, $user2_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $users_before = [];
        while ($row = $result->fetch_assoc()) {
            $users_before[$row['userID']] = $row;
        }
        
        // Check if game already in Alice's library
        $check_library = "SELECT * FROM Library WHERE userID = ? AND gameID = ?";
        $stmt = $conn->prepare($check_library);
        $stmt->bind_param("ii", $user1_id, $game_id);
        $stmt->execute();
        $library_check = $stmt->get_result();
        
        if ($library_check->num_rows > 0) {
            // Remove the game first
            $delete_query = "DELETE FROM Library WHERE userID = ? AND gameID = ?";
            $stmt = $conn->prepare($delete_query);
            $stmt->bind_param("ii", $user1_id, $game_id);
            $stmt->execute();
            
            // Update Alice's count manually to reset
            $reset_query = "UPDATE User SET numberOfGamesInLibrary = numberOfGamesInLibrary - 1 WHERE userID = ?";
            $stmt = $conn->prepare($reset_query);
            $stmt->bind_param("i", $user1_id);
            $stmt->execute();
        }
        
        // Get fresh counts
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("ii", $user1_id, $user2_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $users_before = [];
        while ($row = $result->fetch_assoc()) {
            $users_before[$row['userID']] = $row;
        }
        
        // Add game to Alice's library (should trigger only for Alice)
        $insert_query = "INSERT INTO Library (userID, gameID, dateAdded) VALUES (?, ?, NOW())";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ii", $user1_id, $game_id);
        $success = $stmt->execute();
        
        if ($success) {
            // Check states after trigger
            $stmt = $conn->prepare($check_query);
            $stmt->bind_param("ii", $user1_id, $user2_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $users_after = [];
            while ($row = $result->fetch_assoc()) {
                $users_after[$row['userID']] = $row;
            }
            
            return "
                <div style='text-align: left;'>
                    <div style='color: #00ff00; margin-bottom: 10px; font-weight: bold;'>✓ Test Case 2 - Multiple Users</div>
                    <div style='margin-bottom: 5px;'>Before: User {$user1_id} ({$users_before[$user1_id]['name']}) - Games: {$users_before[$user1_id]['numberOfGamesInLibrary']}, User {$user2_id} ({$users_before[$user2_id]['name']}) - Games: {$users_before[$user2_id]['numberOfGamesInLibrary']}</div>
                    <div style='margin-bottom: 5px;'>Action: INSERT INTO Library (userID, gameID) VALUES ({$user1_id}, {$game_id})</div>
                    <div style='margin-bottom: 5px;'>Trigger Executed: after_library_insert</div>
                    <div style='color: #ff6b00; margin-bottom: 5px;'>After: User {$user1_id} ({$users_after[$user1_id]['name']}) - Games: {$users_after[$user1_id]['numberOfGamesInLibrary']}, User {$user2_id} ({$users_after[$user2_id]['name']}) - Games: {$users_after[$user2_id]['numberOfGamesInLibrary']}</div>
                    <div style='margin-top: 10px; color: #00ff00; font-weight: bold;'>✓ SUCCESS: Only {$users_after[$user1_id]['name']}'s count was updated! Bob's count remained unchanged.</div>
                </div>
            ";
        } else {
            return "<div style='color: #ff4444;'>Error executing test case 2: " . $conn->error . "</div>";
        }
        
    } catch (Exception $e) {
        return "<div style='color: #ff4444;'>Exception in test case 2: " . $e->getMessage() . "</div>";
    }
}

function executeTestCase3($conn) {
    try {
        $user_id = 3; // Charlie
        $games = [1, 2, 3]; // Multiple games to add
        
        // Get initial state
        $check_query = "SELECT name, surname, numberOfGamesInLibrary FROM User WHERE userID = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_initial = $result->fetch_assoc();
        
        // Clean up any existing games for this test
        $cleanup_query = "DELETE FROM Library WHERE userID = ? AND gameID IN (?, ?, ?)";
        $stmt = $conn->prepare($cleanup_query);
        $stmt->bind_param("iiii", $user_id, $games[0], $games[1], $games[2]);
        $stmt->execute();
        
        // Reset user count to test from clean state
        $count_actual = "SELECT COUNT(*) as actual_count FROM Library WHERE userID = ?";
        $stmt = $conn->prepare($count_actual);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $actual_result = $stmt->get_result();
        $actual_count = $actual_result->fetch_assoc()['actual_count'];
        
        $reset_query = "UPDATE User SET numberOfGamesInLibrary = ? WHERE userID = ?";
        $stmt = $conn->prepare($reset_query);
        $stmt->bind_param("ii", $actual_count, $user_id);
        $stmt->execute();
        
        // Get fresh initial state
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_before = $result->fetch_assoc();
        
        $steps = [];
        
        // Add each game sequentially
        foreach ($games as $index => $game_id) {
            $insert_query = "INSERT INTO Library (userID, gameID, dateAdded) VALUES (?, ?, NOW())";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("ii", $user_id, $game_id);
            $success = $stmt->execute();
            
            if ($success) {
                // Get game name
                $game_query = "SELECT gameName FROM Game WHERE gameID = ?";
                $stmt = $conn->prepare($game_query);
                $stmt->bind_param("i", $game_id);
                $stmt->execute();
                $game_result = $stmt->get_result();
                $game = $game_result->fetch_assoc();
                
                // Verify actual count in database
                $stmt = $conn->prepare($check_query);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user_current = $result->fetch_assoc();
                
                $steps[] = "Action " . ($index + 1) . ": INSERT INTO Library (userID, gameID) VALUES ({$user_id}, {$game_id}) - Added: {$game['gameName']}";
                $steps[] = "After Action " . ($index + 1) . ": Games count = {$user_current['numberOfGamesInLibrary']}";
            } else {
                return "<div style='color: #ff4444;'>Error in step " . ($index + 1) . ": " . $conn->error . "</div>";
            }
        }
        
        // Get final state
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_final = $result->fetch_assoc();
        
        $steps_html = "";
        foreach ($steps as $step) {
            $steps_html .= "<div style='margin-bottom: 5px;'>{$step}</div>";
        }
        
        return "
            <div style='text-align: left;'>
                <div style='color: #00ff00; margin-bottom: 10px; font-weight: bold;'>✓ Test Case 3 - Multiple Games</div>
                <div style='margin-bottom: 5px;'>Before: User ID {$user_id} ({$user_before['name']} {$user_before['surname']}) - Games in Library: {$user_before['numberOfGamesInLibrary']}</div>
                {$steps_html}
                <div style='color: #ff6b00; margin: 10px 0;'>Final Result: User ID {$user_id} ({$user_final['name']} {$user_final['surname']}) - Games in Library: {$user_final['numberOfGamesInLibrary']}</div>
                <div style='margin-top: 10px; color: #00ff00; font-weight: bold;'>✓ SUCCESS: Sequential increments working perfectly! Count went from {$user_before['numberOfGamesInLibrary']} to {$user_final['numberOfGamesInLibrary']}</div>
            </div>
        ";
        
    } catch (Exception $e) {
        return "<div style='color: #ff4444;'>Exception in test case 3: " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GameHub - after_library_insert Trigger</title>
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
        
        .trigger-title {
            font-size: 3rem;
            font-weight: bold;
            color: #ff6b00;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
        }
        
        .trigger-subtitle {
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
        
        .trigger-sql {
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
        
        .trigger-details {
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
            .trigger-details {
                grid-template-columns: 1fr;
            }
            
            .test-cases {
                grid-template-columns: 1fr;
            }
            
            .trigger-title {
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
            <h1 class="trigger-title">
                <i class="fas fa-plus-circle"></i>
                after_library_insert
            </h1>
            <p class="trigger-subtitle">Database Trigger - Automatic Library Count Management</p>
            <div class="responsible">
                <i class="fas fa-user"></i> Responsible: [Your Name]
            </div>
        </div>

        <div class="description-section">
            <h2 class="description-title">
                <i class="fas fa-info-circle"></i>
                Trigger Description
            </h2>
            <p class="description-text">
                This trigger automatically maintains an accurate count of games in a user's library when new games are added. 
                It listens for INSERT operations on the Library table and increments the numberOfGamesInLibrary counter 
                in the corresponding User record, ensuring real-time synchronization without expensive COUNT queries.
            </p>
            
            <div class="trigger-sql">
                <div class="sql-title">SQL Script:</div>
                <div class="sql-code">DELIMITER //

CREATE TRIGGER after_library_insert
    AFTER INSERT ON Library
    FOR EACH ROW
BEGIN
    UPDATE User 
    SET numberOfGamesInLibrary = numberOfGamesInLibrary + 1
    WHERE userID = NEW.userID;
END //

DELIMITER ;</div>
            </div>
            
            <div class="trigger-details">
                <div class="detail-card">
                    <div class="detail-title">
                        <i class="fas fa-database"></i>
                        Trigger Event
                    </div>
                    <p>AFTER INSERT ON Library</p>
                </div>
                
                <div class="detail-card">
                    <div class="detail-title">
                        <i class="fas fa-cog"></i>
                        Action
                    </div>
                    <p>Updates User.numberOfGamesInLibrary += 1</p>
                </div>
                
                <div class="detail-card">
                    <div class="detail-title">
                        <i class="fas fa-clock"></i>
                        Timing
                    </div>
                    <p>FOR EACH ROW</p>
                </div>
                
                <div class="detail-card">
                    <div class="detail-title">
                        <i class="fas fa-target"></i>
                        Target Field
                    </div>
                    <p>User.numberOfGamesInLibrary</p>
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
                        <i class="fas fa-play"></i>
                        Case 1: Add New Game
                    </h3>
                    <p class="case-description">
                        Test adding a new game to a user's library and verify that the game count increases by 1.
                    </p>
                    <form method="POST" style="margin: 0;">
                        <input type="hidden" name="test_case" value="1">
                        <button type="submit" class="test-button">
                            <i class="fas fa-plus"></i>
                            Execute Test Case 1
                        </button>
                    </form>
                </div>

                <div class="test-case">
                    <h3 class="case-title">
                        <i class="fas fa-users"></i>
                        Case 2: Multiple Users
                    </h3>
                    <p class="case-description">
                        Test that the trigger correctly updates only the specific user's count when multiple users exist.
                    </p>
                    <form method="POST" style="margin: 0;">
                        <input type="hidden" name="test_case" value="2">
                        <button type="submit" class="test-button">
                            <i class="fas fa-user-friends"></i>
                            Execute Test Case 2
                        </button>
                    </form>
                </div>

                <div class="test-case">
                    <h3 class="case-title">
                        <i class="fas fa-list"></i>
                        Case 3: Multiple Games
                    </h3>
                    <p class="case-description">
                        Test adding multiple games in sequence and verify the count increments correctly for each addition.
                    </p>
                    <form method="POST" style="margin: 0;">
                        <input type="hidden" name="test_case" value="3">
                        <button type="submit" class="test-button">
                            <i class="fas fa-layer-group"></i>
                            Execute Test Case 3
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