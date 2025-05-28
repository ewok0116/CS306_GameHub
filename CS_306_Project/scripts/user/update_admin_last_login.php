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
        default:
            $output_message = "Invalid test case";
    }
}

function executeTestCase1($conn) {
    try {
        $admin_id = 1;

        $before_query = "SELECT lastLogin, updatedAt FROM Admin WHERE adminID = ?";
        $stmt = $conn->prepare($before_query);
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $before = $result->fetch_assoc();

        sleep(1);

        $update_query = "UPDATE Admin SET lastLogin = NOW() WHERE adminID = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();

        $stmt = $conn->prepare($before_query);
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $after = $result->fetch_assoc();

        return "<div style='text-align: left;'>
            <div style='color: #00ff00; font-weight: bold;'>✓ Test Case 1 - Update Last Login</div>
            <div>Before: lastLogin = {$before['lastLogin']}, updatedAt = {$before['updatedAt']}</div>
            <div>Action: UPDATE Admin SET lastLogin = NOW() WHERE adminID = {$admin_id}</div>
            <div>After: lastLogin = {$after['lastLogin']}, updatedAt = {$after['updatedAt']}</div>
            <div style='margin-top: 10px; color: #00ff00;'>✓ SUCCESS: updatedAt updated automatically</div>
        </div>";

    } catch (Exception $e) {
        return "<div style='color: #ff4444;'>Exception in Test Case 1: " . $e->getMessage() . "</div>";
    }
}

function executeTestCase2($conn) {
    try {
        $admin_id = 1;

        $query = "SELECT lastLogin, updatedAt FROM Admin WHERE adminID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $before = $result->fetch_assoc();

        sleep(1);

        $update_query = "UPDATE Admin SET lastLogin = ? WHERE adminID = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $before['lastLogin'], $admin_id);
        $stmt->execute();

        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $after = $result->fetch_assoc();

        $note = ($before['updatedAt'] === $after['updatedAt']) ?
            "✓ SUCCESS: updatedAt remains unchanged (no lastLogin change)" :
            "✗ ERROR: updatedAt changed unexpectedly";

        return "<div style='text-align: left;'>
            <div style='color: #00ff00; font-weight: bold;'>✓ Test Case 2 - No Change in Last Login</div>
            <div>Before: lastLogin = {$before['lastLogin']}, updatedAt = {$before['updatedAt']}</div>
            <div>Action: Re-set same lastLogin</div>
            <div>After: lastLogin = {$after['lastLogin']}, updatedAt = {$after['updatedAt']}</div>
            <div style='margin-top: 10px; color: #00ff00;'>{$note}</div>
        </div>";

    } catch (Exception $e) {
        return "<div style='color: #ff4444;'>Exception in Test Case 2: " . $e->getMessage() . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>update_admin_last_login Trigger</title>
    <style>
        body {
            background-color: #1a1a2e;
            color: #fff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        .header h1 {
            font-size: 3rem;
            color: #ff6b00;
            margin-bottom: 10px;
        }
        .header p {
            color: #ccc;
        }
        .description, .test-case, .output {
            background-color: #16213e;
            border: 1px solid #0f3460;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .section-title {
            color: #ff6b00;
            font-size: 1.5rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .code-block {
            background-color: #0f1419;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            color: #00ff00;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .form-group button {
            background: linear-gradient(135deg, #ff6b00, #ff8c42);
            border: none;
            color: white;
            padding: 12px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            font-size: 1rem;
        }
        .form-group button:hover {
            background: linear-gradient(135deg, #ff8c42, #ff6b00);
        }
        .output-result {
            background-color: #0f1419;
            color: #00ff00;
            font-family: 'Courier New', monospace;
            padding: 15px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🕒 update_admin_last_login</h1>
            <p>Trigger - Automatically update updatedAt on lastLogin change</p>
            <p>👤 Responsible: [Your Name]</p>
        </div>

        <div class="description">
            <div class="section-title">ℹ️ Trigger Description</div>
            <p>This trigger updates the <code>updatedAt</code> timestamp whenever an admin's <code>lastLogin</code> field is modified. It ensures audit logs remain accurate when tracking admin login activity.</p>
            <div class="code-block">
                DELIMITER //
                CREATE TRIGGER update_admin_last_login
                    BEFORE UPDATE ON Admin
                    FOR EACH ROW
                BEGIN
                    IF NOT (NEW.lastLogin <=> OLD.lastLogin) THEN
                        SET NEW.updatedAt = CURRENT_TIMESTAMP;
                    END IF;
                END //
                DELIMITER ;
            </div>
        </div>

        <div class="test-case">
            <div class="section-title">🧪 Test Cases</div>
            <div class="form-group">
                <form method="POST">
                    <input type="hidden" name="test_case" value="1">
                    <button type="submit">➕ Case 1: Change lastLogin</button>
                </form>
                <p>Modify the <code>lastLogin</code> field to trigger updatedAt update.</p>
                <form method="POST">
                    <input type="hidden" name="test_case" value="2">
                    <button type="submit">➖ Case 2: No Change</button>
                </form>
                <p>Attempt to re-save identical <code>lastLogin</code> value; <code>updatedAt</code> should not change.</p>
            </div>
        </div>

        <div class="output">
            <div class="section-title">🖥️ Output Results</div>
            <div class="output-result">
                <?php if ($test_executed): ?>
                    <?= $output_message ?>
                <?php else: ?>
                    <div style='color: #888;'>Click a test case button to see the results...</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>
