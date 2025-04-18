<?php
require_once 'db_config.php';
session_start();

// Initialize variables
$total_sales = 0;
$total_cash = 0;
$total_bank = 0;
$total_mpesa = 0;
$total_debts = 0;

try {
    // Get total sales and payment method totals
    $sql = "SELECT 
            SUM(amount) as total_sales,
            SUM(CASE WHEN payment_method = 'cash' THEN amount ELSE 0 END) as cash_total,
            SUM(CASE WHEN payment_method = 'mpesa' THEN amount ELSE 0 END) as mpesa_total,
            SUM(CASE WHEN payment_method = 'bank' THEN amount ELSE 0 END) as bank_total
            FROM sales";
    $stmt = $conn->query($sql);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $total_sales = $result['total_sales'] ?? 0;
    $total_cash = $result['cash_total'] ?? 0;
    $total_mpesa = $result['mpesa_total'] ?? 0;
    $total_bank = $result['bank_total'] ?? 0;

    // Get debt totals - only include records from the debts table that are not related to sales
    // and have a payment_status of 'pending' (unpaid debts)
    $debt_sql = "SELECT 
                 SUM(amount) as total_debts
                 FROM debts 
                 WHERE payment_status = 'pending'";
    $debt_stmt = $conn->query($debt_sql);
    $debt_result = $debt_stmt->fetch(PDO::FETCH_ASSOC);
    
    $total_debts = $debt_result['total_debts'] ?? 0;
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Money Management</title>
    <link rel="stylesheet" href="styles.css">
    <style>
    .action-button {
        display: inline-block;
        padding: 10px 20px;
        background-color: #3498db;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        margin: 10px 10px 10px 0;
    }
    .add-debt-button {
        background-color: #e67e22;
    }
    .add-retailer-button {
        background-color: #27ae60;
    }
    .clear-debt {
        background-color: #27ae60;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 3px;
        cursor: pointer;
    }
    .clear-debt:hover {
        background-color: #219a52;
    }
    .button-group {
        margin: 20px 0;
    }
    .description-cell {
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    </style>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.querySelector('.menu-toggle');
        const nav = document.querySelector('nav');
        const mainContent = document.querySelector('.main-content');
        
        menuToggle.addEventListener('click', function() {
            menuToggle.classList.toggle('active');
            nav.classList.toggle('active');
            mainContent.classList.toggle('shifted');
        });
    });

    function confirmClearDebt(debtId, retailerName, amount) {
        if (confirm('Are you sure you want to mark the debt of $' + amount + ' for ' + retailerName + ' as paid?')) {
            document.getElementById('clear-debt-form-' + debtId).submit();
        }
    }
    </script>
</head>
<body>
    <button class="menu-toggle">
        <span></span>
        <span></span>
        <span></span>
    </button>
    <nav>
    <br><br><br>
        <a href="index.php">Register</a>
        <a href="sales.php">Make Sales</a>
        <a href="money.php" class="current">Money</a>
        <a href="client.php">Client List</a>
        <a href="products.php">Products</a>
        <a href="retailers.php">Retailers</a>
        <a href="transactions.php">Transactions</a>
    </nav>
    <div class="main-content">
        <div class="container">
            <h2>Money Management</h2>

            <?php 
            if (isset($_SESSION['success'])) {
                echo '<div class="feedback success">' . htmlspecialchars($_SESSION['success']) . '</div>';
                unset($_SESSION['success']);
            }
            if (isset($_SESSION['error'])) {
                echo '<div class="feedback error">' . htmlspecialchars($_SESSION['error']) . '</div>';
                unset($_SESSION['error']);
            }
            if (isset($error)): ?>
                <div class="feedback error"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="summary-cards" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
                <div class="card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <h3 style="margin: 0 0 10px 0; color: #333;">Total Sales</h3>
                    <p style="font-size: 24px; margin: 0; color: #2c3e50;">Ksh <?php echo number_format($total_sales, 2); ?></p>
                </div>
                <div class="card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <h3 style="margin: 0 0 10px 0; color: #333;">Cash</h3>
                    <p style="font-size: 24px; margin: 0; color: #27ae60;">Ksh <?php echo number_format($total_cash, 2); ?></p>
                </div>
                <div class="card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <h3 style="margin: 0 0 10px 0; color: #333;">M-Pesa</h3>
                    <p style="font-size: 24px; margin: 0; color: #8e44ad;">Ksh <?php echo number_format($total_mpesa, 2); ?></p>
                </div>
                <div class="card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <h3 style="margin: 0 0 10px 0; color: #333;">Bank</h3>
                    <p style="font-size: 24px; margin: 0; color: #2980b9;">Ksh <?php echo number_format($total_bank, 2); ?></p>
                </div>
                <div class="card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <h3 style="margin: 0 0 10px 0; color: #333;">Total Debts</h3>
                    <p style="font-size: 24px; margin: 0; color: #e74c3c;">Ksh <?php echo number_format($total_debts, 2); ?></p>
                </div>
            </div>

            <div class="button-group">
                <a href="transactions.php" class="action-button">View All Transactions</a>
                <a href="add_debt.php" class="action-button add-debt-button">Add New Debt</a>
                <a href="retailers.php" class="action-button add-retailer-button">Manage Retailers</a>
            </div>

            <h3>Debts to Retailers</h3>
            <?php
            try {
                $sql = "SELECT d.*, r.retailer_name, r.phone 
                        FROM debts d 
                        JOIN retailers r ON d.retailer_id = r.id
                        ORDER BY d.created_at DESC";
                $stmt = $conn->query($sql);
                
                if ($stmt->rowCount() > 0) {
                    echo "<table>
                            <tr>
                                <th>Date</th>
                                <th>Retailer</th>
                                <th>Contact</th>
                                <th>Amount</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>";
                    
                    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>
                                <td>" . date('Y-m-d H:i', strtotime($row['created_at'])) . "</td>
                                <td>" . htmlspecialchars($row['retailer_name']) . "</td>
                                <td>" . htmlspecialchars($row['phone'] ?: '-') . "</td>
                                <td>Ksh " . number_format($row['amount'], 2) . "</td>
                                <td class='description-cell'>" . 
                                    (empty($row['description']) ? '-' : htmlspecialchars($row['description'])) . 
                                "</td>
                                <td>" . ucfirst($row['payment_status']) . "</td>
                                <td>";
                        if ($row['payment_status'] === 'pending') {
                            echo "<button type='button' class='clear-debt' 
                                onclick='openDebtForm(" . $row['id'] . ", \"" . 
                                addslashes($row['retailer_name']) . "\", " . 
                                $row['amount'] . ")'>Clear Debt</button>";
                        }
                        echo "</td></tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>No debts found</p>";
                }
            } catch(PDOException $e) {
                echo '<div class="feedback error">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            ?>
        </div>
    </div>
</body>

<!-- Modal for clearing debt -->
<div id="debtFormModal" class="modal" style="display: none; position: fixed; z-index: 1; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);">
    <div class="modal-content" style="background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 50%; border-radius: 5px;">
        <span class="close" onclick="closeDebtForm()" style="color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
        <h3>Clear Debt</h3>
        <form id="clear-debt-form" action="process_debt.php" method="post">
            <input type="hidden" id="debt_id" name="debt_id" value="">
            <input type="hidden" name="action" value="clear">
            
            <div style="margin-bottom: 15px;">
                <label for="retailer_name" style="display: block; margin-bottom: 5px;">Retailer:</label>
                <input type="text" id="retailer_name" readonly style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            
            <div style="margin-bottom: 15px;">
                <label for="total_amount" style="display: block; margin-bottom: 5px;">Total Debt Amount:</label>
                <input type="text" id="total_amount" readonly style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            
            <div style="margin-bottom: 15px;">
                <label for="payment_amount" style="display: block; margin-bottom: 5px;">Payment Amount:</label>
                <input type="number" id="payment_amount" name="payment_amount" required step="0.01" min="0.01" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                <small style="color: #666; margin-top: 5px; display: block;">Enter the full amount to clear the debt completely, or a partial amount to reduce it.</small>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label for="payment_method" style="display: block; margin-bottom: 5px;">Payment Method:</label>
                <select id="payment_method" name="payment_method" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="">Select payment method</option>
                    <option value="cash">Cash</option>
                    <option value="mpesa">M-Pesa</option>
                    <option value="bank">Bank</option>
                </select>
            </div>
            
            <button type="submit" style="background-color: #27ae60; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer;">Submit Payment</button>
        </form>
    </div>
</div>

<script>
    function openDebtForm(debtId, retailerName, amount) {
        document.getElementById('debt_id').value = debtId;
        document.getElementById('retailer_name').value = retailerName;
        document.getElementById('total_amount').value = 'Ksh ' + amount.toFixed(2);
        document.getElementById('payment_amount').value = amount.toFixed(2);
        document.getElementById('payment_amount').max = amount;
        document.getElementById('debtFormModal').style.display = 'block';
    }
    
    function closeDebtForm() {
        document.getElementById('debtFormModal').style.display = 'none';
    }
    
    // Close the modal if clicked outside of it
    window.onclick = function(event) {
        var modal = document.getElementById('debtFormModal');
        if (event.target == modal) {
            closeDebtForm();
        }
    }
</script>
</html>