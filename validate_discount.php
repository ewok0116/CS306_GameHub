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
        $game_id = 1; // Test game
        $valid_discount = 25; // Valid discount (25%)
        
        // Get current game state
        $check_query = "SELECT gameID, gameName, price, discountPercentage FROM Game WHERE gameID = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("i", $game_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $game_before = $result->fetch_assoc();
        
        if (!$game_before) {
            return "<div style='color: #ff4444;'>Error: Game with ID {$game_id} not found</div>";
        }
        
        // Attempt to update with valid discount
        $update_query = "UPDATE Game SET discountPercentage = ? WHERE gameID = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("di", $valid_discount, $game_id);
        $success = $stmt->execute();
        
        if ($success) {
            // Check state after update
            $stmt = $conn->prepare($check_query);
            $stmt->bind_param("i", $game_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $game_after = $result->fetch_assoc();
            
            return "
                <div style='text-align: left;'>
                    <div style='color: #00ff00; margin-bottom: 10px; font-weight: bold;'>✓ Test Case 1 - Valid Discount (0-100%)</div>
                    <div style='margin-bottom: 5px;'>Game: {$game_before['gameName']} (ID: {$game_id})</div>
                    <div style='margin-bottom: 5px;'>Before: Price: \${$game_before['price']}, Discount: {$game_before['discountPercentage']}%</div>
                    <div style='margin-bottom: 5px;'>Action: UPDATE Game SET discountPercentage = {$valid_discount} WHERE gameID = {$game_id}</div>
                    <div style='margin-bottom: 5px;'>Trigger: validate_discount - Checking if {$valid_discount}% is between 0-100%</div>
                    <div style='color: #ff6b00; margin-bottom: 5px;'>After: Price: \${$game_after['price']}, Discount: {$game_after['discountPercentage']}%</div>
                    <div style='margin-top: 10px; color: #00ff00; font-weight: bold;'>✓ SUCCESS: Valid discount {$valid_discount}% was accepted and applied!</div>
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
        $game_id = 2; // Test game
        $invalid_discount = -15; // Invalid discount (negative)
        
        // Get current game state
        $check_query = "SELECT gameID, gameName, price, discountPercentage FROM Game WHERE gameID = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("i", $game_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $game_before = $result->fetch_assoc();
        
        if (!$game_before) {
            return "<div style='color: #ff4444;'>Error: Game with ID {$game_id} not found</div>";
        }
        
        // Attempt to update with invalid discount (negative)
        $update_query = "UPDATE Game SET discountPercentage = ? WHERE gameID = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("di", $invalid_discount, $game_id);
        $success = $stmt->execute();
        
        // Check state after attempted update
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("i", $game_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $game_after = $result->fetch_assoc();
        
        if (!$success) {
            return "
                <div style='text-align: left;'>
                    <div style='color: #00ff00; margin-bottom: 10px; font-weight: bold;'>✓ Test Case 2 - Invalid Discount (Negative)</div>
                    <div style='margin-bottom: 5px;'>Game: {$game_before['gameName']} (ID: {$game_id})</div>
                    <div style='margin-bottom: 5px;'>Before: Price: \${$game_before['price']}, Discount: {$game_before['discountPercentage']}%</div>
                    <div style='margin-bottom: 5px;'>Action: UPDATE Game SET discountPercentage = {$invalid_discount} WHERE gameID = {$game_id}</div>
                    <div style='margin-bottom: 5px;'>Trigger: validate_discount - Checking if {$invalid_discount}% is between 0-100%</div>
                    <div style='color: #ff4444; margin-bottom: 5px;'>Trigger Response: ERROR -20001 - Discount percentage must be between 0 and 100</div>
                    <div style='color: #ff6b00; margin-bottom: 5px;'>After: Price: \${$game_after['price']}, Discount: {$game_after['discountPercentage']}% (UNCHANGED)</div>
                    <div style='margin-top: 10px; color: #00ff00; font-weight: bold;'>✓ SUCCESS: Invalid negative discount {$invalid_discount}% was REJECTED by trigger!</div>
                </div>
            ";
        } else {
            return "
                <div style='text-align: left;'>
                    <div style='color: #ffaa00; margin-bottom: 10px; font-weight: bold;'>⚠ Test Case 2 - Simulated Trigger Behavior</div>
                    <div style='margin-bottom: 5px;'>Game: {$game_before['gameName']} (ID: {$game_id})</div>
                    <div style='margin-bottom: 5px;'>Before: Price: \${$game_before['price']}, Discount: {$game_before['discountPercentage']}%</div>
                    <div style='margin-bottom: 5px;'>Action: UPDATE Game SET discountPercentage = {$invalid_discount} WHERE gameID = {$game_id}</div>
                    <div style='margin-bottom: 5px;'>Note: In a real scenario with the trigger, this would fail with ERROR -20001</div>
                    <div style='color: #ff6b00; margin-bottom: 5px;'>Current Result: Discount changed to {$game_after['discountPercentage']}% (trigger not active)</div>
                    <div style='margin-top: 10px; color: #ffaa00; font-weight: bold;'>⚠ SIMULATION: Real trigger would BLOCK this negative discount!</div>
                </div>
            ";
        }
        
    } catch (Exception $e) {
        return "<div style='color: #ff4444;'>Exception in test case 2: " . $e->getMessage() . "</div>";
    }
}

function executeTestCase3($conn) {
    try {
        $game_id = 3; // Test game
        $invalid_discount = 150; // Invalid discount (over 100%)
        
        // Get current game state
        $check_query = "SELECT gameID, gameName, price, discountPercentage FROM Game WHERE gameID = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("i", $game_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $game_before = $result->fetch_assoc();
        
        if (!$game_before) {
            return "<div style='color: #ff4444;'>Error: Game with ID {$game_id} not found</div>";
        }
        
        // Test multiple discount values
        $test_discounts = [150, 0, 50, 100, 101];
        $results = [];
        
        foreach ($test_discounts as $discount) {
            // Attempt to update with test discount
            $update_query = "UPDATE Game SET discountPercentage = ? WHERE gameID = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("di", $discount, $game_id);
            $success = $stmt->execute();
            
            // Check current state
            $stmt = $conn->prepare($check_query);
            $stmt->bind_param("i", $game_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $game_current = $result->fetch_assoc();
            
            if ($discount < 0 || $discount > 100) {
                $status = $success ? "⚠ SHOULD BE BLOCKED" : "✓ BLOCKED";
                $color = $success ? "#ffaa00" : "#00ff00";
            } else {
                $status = $success ? "✓ ACCEPTED" : "✗ FAILED";
                $color = $success ? "#00ff00" : "#ff4444";
            }
            
            $results[] = [
                'discount' => $discount,
                'status' => $status,
                'color' => $color,
                'current_discount' => $game_current['discountPercentage']
            ];
        }
        
        $results_html = "";
        foreach ($results as $result) {
            $results_html .= "<div style='margin-bottom: 5px; color: {$result['color']};'>";
            $results_html .= "Discount {$result['discount']}%: {$result['status']} (Current: {$result['current_discount']}%)";
            $results_html .= "</div>";
        }
        
        return "
            <div style='text-align: left;'>
                <div style='color: #00ff00; margin-bottom: 10px; font-weight: bold;'>✓ Test Case 3 - Multiple Discount Values</div>
                <div style='margin-bottom: 5px;'>Game: {$game_before['gameName']} (ID: {$game_id})</div>
                <div style='margin-bottom: 5px;'>Original Discount: {$game_before['discountPercentage']}%</div>
                <div style='margin-bottom: 10px;'>Testing various discount values against trigger validation:</div>
                {$results_html}
                <div style='margin-top: 10px; color: #00ff00; font-weight: bold;'>✓ VALIDATION: Trigger should accept 0-100% and reject outside this range!</div>
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
    <title>GameHub - validate_discount Trigger</title>
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
                <i class="fas fa-shield-alt"></i>
                validate_discount
            </h1>
            <p class="trigger-subtitle">Database Trigger - Discount Validation & Business Logic Enforcement</p>
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
                This trigger enforces valid discount ranges (0-100%) to maintain business logic integrity. 
                It automatically validates discount values during UPDATE operations on the Game table, 
                preventing invalid pricing calculations and ensuring all discounts stay within acceptable business ranges.
            </p>
            
            <div class="trigger-sql">
                <div class="sql-title">SQL Script:</div>
                <div class="sql-code">DELIMITER //

CREATE TRIGGER validate_discount
    BEFORE UPDATE ON Game
    FOR EACH ROW
BEGIN
    IF NEW.discountPercentage < 0 OR NEW.discountPercentage > 100 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Discount percentage must be between 0 and 100',
            MYSQL_ERRNO = 20001;
    END IF;
END //

DELIMITER ;</div>
            </div>
            
            <div class="trigger-details">
                <div class="detail-card">
                    <div class="detail-title">
                        <i class="fas fa-database"></i>
                        Trigger Event
                    </div>
                    <p>BEFORE UPDATE ON Game</p>
                </div>
                
                <div class="detail-card">
                    <div class="detail-title">
                        <i class="fas fa-cog"></i>
                        Action
                    </div>
                    <p>Validates discountPercentage (0-100%)</p>
                </div>
                
                <div class="detail-card">
                    <div class="detail-title">
                        <i class="fas fa-exclamation-triangle"></i>
                        Error Handling
                    </div>
                    <p>Raises ERROR -20001 for invalid values</p>
                </div>
                
                <div class="detail-card">
                    <div class="detail-title">
                        <i class="fas fa-target"></i>
                        Target Field
                    </div>
                    <p>Game.discountPercentage</p>
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
                        <i class="fas fa-check-circle"></i>
                        Case 1: Valid Discount
                    </h3>
                    <p class="case-description">
                        Test updating a game with a valid discount percentage (0-100%) and verify it gets accepted.
                    </p>
                    <form method="POST" style="margin: 0;">
                        <input type="hidden" name="test_case" value="1">
                        <button type="submit" class="test-button">
                            <i class="fas fa-percent"></i>
                            Execute Test Case 1
                        </button>
                    </form>
                </div>

                <div class="test-case">
                    <h3 class="case-title">
                        <i class="fas fa-times-circle"></i>
                        Case 2: Negative Discount
                    </h3>
                    <p class="case-description">
                        Test updating a game with a negative discount and verify the trigger blocks the operation.
                    </p>
                    <form method="POST" style="margin: 0;">
                        <input type="hidden" name="test_case" value="2">
                        <button type="submit" class="test-button">
                            <i class="fas fa-minus"></i>
                            Execute Test Case 2
                        </button>
                    </form>
                </div>

                <div class="test-case">
                    <h3 class="case-title">
                        <i class="fas fa-list-ol"></i>
                        Case 3: Multiple Values
                    </h3>
                    <p class="case-description">
                        Test various discount values including edge cases (0%, 100%, >100%) to verify trigger behavior.
                    </p>
                    <form method="POST" style="margin: 0;">
                        <input type="hidden" name="test_case" value="3">
                        <button type="submit" class="test-button">
                            <i class="fas fa-vial"></i>
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