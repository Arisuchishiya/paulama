<?php
require_once 'db_config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions History</title>
    <link rel="stylesheet" href="styles.css">
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
        <a href="money.php">Money</a>
        <a href="client.php">Client List</a>
        <a href="products.php">Products</a>
        <a href="retailers.php">Retailers</a>
        <a href="transactions.php" class="current">Transactions</a>
    </nav>
    <div class="main-content">
        <div class="container">
            <h2>Transaction History</h2>
            <a href="money.php" class="button" style="display: inline-block; margin-bottom: 20px;">Back to Money Management</a>

            <?php
            try {
                $sql = "SELECT s.*, c.client_name, p.product_name 
                        FROM sales s 
                        JOIN clients c ON s.client_id = c.id 
                        JOIN products p ON s.product_id = p.id 
                        ORDER BY s.created_at DESC";
                $stmt = $conn->query($sql);
                
                if ($stmt->rowCount() > 0) {
                    echo "<table>
                            <tr>
                                <th>Date</th>
                                <th>Client</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                            </tr>";
                    
                    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>
                                <td>" . date('Y-m-d H:i', strtotime($row['created_at'])) . "</td>
                                <td>" . htmlspecialchars($row['client_name']) . "</td>
                                <td>" . htmlspecialchars($row['product_name']) . "</td>
                                <td>" . htmlspecialchars($row['quantity']) . "</td>
                                <td>Ksh " . number_format($row['amount'], 2) . "</td>
                                <td>" . ucfirst(str_replace('_', '-', htmlspecialchars($row['payment_method']))) . "</td>
                            </tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>No transactions found</p>";
                }
            } catch(PDOException $e) {
                echo '<div class="feedback error">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            ?>
        </div>
    </div>
</body>
</html>
