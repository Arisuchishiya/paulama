<?php
require_once 'db_config.php';

if (isset($_GET['client_id'])) {
    try {
        $client_id = $_GET['client_id'];
        
        // Get client details
        $sql = "SELECT client_name FROM clients WHERE id = :client_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':client_id', $client_id);
        $stmt->execute();
        $client = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get sales history
        $sql = "SELECT s.*, p.product_name 
                FROM sales s 
                JOIN products p ON s.product_id = p.id 
                WHERE s.client_id = :client_id 
                ORDER BY s.created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':client_id', $client_id);
        $stmt->execute();
        $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($client) {
            echo "<h4>Client: " . htmlspecialchars($client['client_name']) . "</h4>";
            
            if ($stmt->rowCount() > 0) {
                echo "<table class='client-table'>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                            </tr>
                        </thead>
                        <tbody>";
                
                foreach ($sales as $sale) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($sale['created_at']) . "</td>";
                    echo "<td>" . htmlspecialchars($sale['product_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($sale['quantity']) . "</td>";
                    echo "<td>Ksh " . number_format($sale['amount'], 2) . "</td>";
                    echo "<td>" . 
                        ($sale['payment_method'] == 'cash' ? 'Cash' : 
                        ($sale['payment_method'] == 'bank' ? 'Bank' : 
                        ($sale['payment_method'] == 'm_pesa' ? 'M-Pesa' : 
                        ucfirst($sale['payment_method'])))) . 
                    "</td>";
                    echo "</tr>";
                }
                
                echo "</tbody></table>";
            } else {
                echo "<p>No transaction history found for this client.</p>";
            }
        } else {
            echo "<p>Client not found.</p>";
        }
    } catch (PDOException $e) {
        echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p>No client ID provided.</p>";
}
?> 