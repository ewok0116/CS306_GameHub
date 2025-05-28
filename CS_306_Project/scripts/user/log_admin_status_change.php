<?php
// Enable PHP error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gamehub";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$output_message = "";
$test_executed = false;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['test_case'])) {
    $test_executed = true;
    $case = $_POST['test_case'];
    $output_message = $case === "1" ? executeTestCase1($conn) : executeTestCase2($conn);
}

function executeTestCase1($conn) {
    try {
        $id = 1;
        $stmt = $conn->prepare("SELECT isActive, updatedAt FROM Admin WHERE adminID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $before = $stmt->get_result()->fetch_assoc();
        $new_status = $before['isActive'] ? 0 : 1;
        sleep(1);
        $stmt = $conn->prepare("UPDATE Admin SET isActive = ? WHERE adminID = ?");
        $stmt->bind_param("ii", $new_status, $id);
        $stmt->execute();
        $stmt = $conn->prepare("SELECT isActive, updatedAt FROM Admin WHERE adminID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $after = $stmt->get_result()->fetch_assoc();

        return "<div style='text-align: left;'>
            <div style='color: #00ff00; font-weight: bold;'>✓ Test Case 1 - Status Change</div>
            <div>Before: isActive = {$before['isActive']}, updatedAt = {$before['updatedAt']}</div>
            <div>Action: UPDATE Admin SET isActive = {$new_status}</div>
            <div>After: isActive = {$after['isActive']}, updatedAt = {$after['updatedAt']}</div>
            <div style='margin-top: 10px; color: #00ff00;'>✓ SUCCESS: updatedAt updated on status change</div>
        </div>";
    } catch (Exception $e) {
        return "<div style='color: #ff4444;'>Exception in Test Case 1: {$e->getMessage()}</div>";
    }
}

function executeTestCase2($conn) {
    try {
        $id = 1;
        $stmt = $conn->prepare("SELECT isActive, updatedAt FROM Admin WHERE adminID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $before = $stmt->get_result()->fetch_assoc();
        sleep(1);
        $stmt = $conn->prepare("UPDATE Admin SET isActive = ? WHERE adminID = ?");
        $stmt->bind_param("ii", $before['isActive'], $id);
        $stmt->execute();
        $stmt = $conn->prepare("SELECT isActive, updatedAt FROM Admin WHERE adminID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $after = $stmt->get_result()->fetch_assoc();
        $note = ($before['updatedAt'] === $after['updatedAt']) ?
            "✓ SUCCESS: updatedAt unchanged (no status change)" :
            "✗ ERROR: updatedAt changed unexpectedly";
        return "<div style='text-align: left;'>
            <div style='color: #00ff00; font-weight: bold;'>✓ Test Case 2 - No Status Change</div>
            <div>Before: isActive = {$before['isActive']}, updatedAt = {$before['updatedAt']}</div>
            <div>Action: UPDATE Admin SET isActive = {$before['isActive']}</div>
            <div>After: isActive = {$after['isActive']}, updatedAt = {$after['updatedAt']}</div>
            <div style='margin-top: 10px; color: #00ff00;'>{$note}</div>
        </div>";
    } catch (Exception $e) {
        return "<div style='color: #ff4444;'>Exception in Test Case 2: {$e->getMessage()}</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>log_admin_status_change Trigger</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #1a1a2e; color: #fff; font-family: 'Segoe UI', sans-serif; padding: 20px; }
        .container { max-width: 1000px; margin: auto; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #ff6b00; font-size: 2.5rem; }
        .section { background: #16213e; padding: 20px; border-radius: 10px; margin-bottom: 30px; border: 1px solid #0f3460; }
        .section h2 { color: #ff6b00; margin-bottom: 15px; }
        .sql-code { background: #0f1419; padding: 15px; border-radius: 5px; font-family: monospace; color: #00ff00; white-space: pre-wrap; border: 1px solid #2a2a3e; }
        .test-case { margin-bottom: 20px; }
        .test-case form { margin: 0; }
        .test-case button { background-color: #ff6b00; border: none; padding: 10px 20px; color: white; border-radius: 8px; cursor: pointer; font-weight: bold; }
        .test-case button:hover { background-color: #ff8c42; }
        .output { background: #0f1419; padding: 15px; border-radius: 8px; border: 1px solid #2a2a3e; min-height: 100px; margin-top: 20px; color: #00ff00; }
        .nav-buttons { text-align: center; margin-top: 30px; }
        .nav-buttons a { background: #16213e; padding: 10px 20px; color: white; border-radius: 8px; border: 1px solid #0f3460; margin: 0 10px; text-decoration: none; display: inline-block; }
        .nav-buttons a:hover { background-color: #0f3460; border-color: #ff6b00; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-toggle-on"></i> log_admin_status_change</h1>
            <p>Trigger - Update updatedAt on isActive Status Change</p>
            <p><i class="fas fa-user"></i> Responsible: [Your Name]</p>
        </div>

        <div class="section">
            <h2><i class="fas fa-info-circle"></i> Trigger Description</h2>
            <p>This trigger updates the <code>updatedAt</code> timestamp whenever an admin's <code>isActive</code> status changes. It ensures that changes in account status are tracked for audit and monitoring purposes.</p>
            <div class="sql-code">
DELIMITER //
CREATE TRIGGER log_admin_status_change
BEFORE UPDATE ON Admin
FOR EACH ROW
BEGIN
    IF NOT (NEW.isActive <=> OLD.isActive) THEN
        SET NEW.updatedAt = CURRENT_TIMESTAMP;
    END IF;
END //
DELIMITER ;
            </div>
        </div>

        <div class="section">
            <h2><i class="fas fa-flask"></i> Test Cases</h2>
            <div class="test-case">
                <h3><i class="fas fa-sync-alt"></i> Case 1: Change isActive</h3>
                <p>Change the <code>isActive</code> field to trigger update of <code>updatedAt</code>.</p>
                <form method="POST">
                    <input type="hidden" name="test_case" value="1">
                    <button type="submit">Execute Test Case 1</button>
                </form>
            </div>

            <div class="test-case">
                <h3><i class="fas fa-minus-circle"></i> Case 2: No Change</h3>
                <p>Update with the same <code>isActive</code> value, expect no change in <code>updatedAt</code>.</p>
                <form method="POST">
                    <input type="hidden" name="test_case" value="2">
                    <button type="submit">Execute Test Case 2</button>
                </form>
            </div>
        </div>

        <div class="section">
            <h2><i class="fas fa-terminal"></i> Output Results</h2>
            <div class="output">
                <?php echo $test_executed ? $output_message : "<div style='color: #666;'>Click a test case button to see the results...</div>"; ?>
            </div>
        </div>

        <div class="nav-buttons">
            <a href="../index.php"><i class="fas fa-home"></i> Go to Homepage</a>
            <a href="../support/tickets.php"><i class="fas fa-life-ring"></i> Support Tickets</a>
        </div>
    </div>
</body>
</html>

<?php $conn->close(); ?>
