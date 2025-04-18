<?php
require_once 'db_config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $retailer_id = $_POST['retailer_id'] ?? null;
    $amount = $_POST['amount'] ?? null;
    $description = $_POST['description'] ?? '';

    if (!$retailer_id || !$amount) {
        $_SESSION['error'] = "Retailer and amount are required";
        header("Location: money.php");
        exit();
    }

    try {
        // Create the debt record
        $debt_sql = "INSERT INTO debts (retailer_id, amount, payment_status, description) 
                     VALUES (:retailer_id, :amount, 'pending', :description)";
        $debt_stmt = $conn->prepare($debt_sql);
        $debt_stmt->bindParam(':retailer_id', $retailer_id);
        $debt_stmt->bindParam(':amount', $amount);
        $debt_stmt->bindParam(':description', $description);
        
        if ($debt_stmt->execute()) {
            $_SESSION['success'] = "Debt recorded successfully!";
        } else {
            $_SESSION['error'] = "Error: Unable to record debt";
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error: " . htmlspecialchars($e->getMessage());
    }
    
    header("Location: money.php");
    exit();
}

// Get retailers for the dropdown
try {
    $retailers_sql = "SELECT id, retailer_name, phone FROM retailers ORDER BY retailer_name";
    $retailers_stmt = $conn->query($retailers_sql);
    $retailers = $retailers_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $_SESSION['error'] = "Error loading retailers: " . htmlspecialchars($e->getMessage());
    header("Location: money.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Debt</title>
    <link rel="stylesheet" href="styles.css">
    <style>
    .modal-content {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        max-width: 500px;
        margin: 40px auto;
    }
    .form-group {
        margin-bottom: 15px;
    }
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    .form-group select, .form-group input, .form-group textarea {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
    }
    .button-group {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }
    .button-group button, .button-group a {
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        text-decoration: none;
        text-align: center;
    }
    .submit-btn {
        background-color: #27ae60;
        color: white;
    }
    .cancel-btn {
        background-color: #95a5a6;
        color: white;
    }
    .add-retailer-btn {
        display: inline-block;
        padding: 8px 15px;
        background-color: #3498db;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        margin-bottom: 15px;
    }
    </style>
</head>
<body>
    <div class="modal-content">
        <h2>Add New Debt</h2>
        
        <?php if (empty($retailers)): ?>
            <p>No retailers found. Please add a retailer first.</p>
            <a href="retailers.php" class="add-retailer-btn">Add Retailer</a>
        <?php else: ?>
            <form action="add_debt.php" method="post">
                <div class="form-group">
                    <label for="retailer_id">Retailer:</label>
                    <select name="retailer_id" id="retailer_id" required>
                        <option value="">Select a retailer</option>
                        <?php foreach ($retailers as $retailer): ?>
                            <option value="<?php echo $retailer['id']; ?>">
                                <?php echo htmlspecialchars($retailer['retailer_name']) . 
                                    (empty($retailer['phone']) ? '' : ' (' . htmlspecialchars($retailer['phone']) . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="amount">Amount:</label>
                    <input type="number" id="amount" name="amount" step="0.01" min="0.01" required>
                </div>

                <div class="form-group">
                    <label for="description">Description (Optional):</label>
                    <textarea id="description" name="description" rows="3" placeholder="Enter details about the debt"></textarea>
                </div>

                <div class="button-group">
                    <button type="submit" class="submit-btn">Add Debt</button>
                    <a href="money.php" class="cancel-btn">Cancel</a>
                </div>
            </form>
        <?php endif; ?>
        
        <?php if (!empty($retailers)): ?>
            <p style="margin-top: 20px;">
                <a href="retailers.php" class="add-retailer-btn">Manage Retailers</a>
            </p>
        <?php endif; ?>
    </div>
</body>
</html>
