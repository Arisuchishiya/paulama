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
            <div class="search-container">
                <input type="text" id="search" placeholder="Search by client name, product name, or quantity" class="search-input">
            </div>
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
                    echo "<table id='transaction-table'>
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

    <style>
        .search-container {
            margin-bottom: 20px;
            width: 100%;
        }
        .search-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }
        .search-input:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.2);
        }
        .highlight {
            background-color: #ffeb3b;
            padding: 2px;
            border-radius: 3px;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function highlightText(text, searchTerm) {
                if (!searchTerm) return text;
                const regex = new RegExp(`(${searchTerm})`, 'gi');
                return text.replace(regex, '<span class="highlight">$1</span>');
            }

            function search() {
                var input = document.getElementById("search");
                var filter = input.value.toUpperCase();
                var table = document.getElementById("transaction-table");
                if (!table) return;
                
                var tr = table.getElementsByTagName("tr");
                var anyResults = false;
                
                // Start from 1 to skip header row
                for (var i = 1; i < tr.length; i++) {
                    var td = tr[i].getElementsByTagName("td");
                    var found = false;
                    
                    // Check all columns
                    for (var j = 0; j < td.length; j++) {
                        if (td[j]) {
                            var originalText = td[j].textContent || td[j].innerText;
                            var txtValue = originalText.toUpperCase();
                            
                            if (txtValue.indexOf(filter) > -1) {
                                found = true;
                                anyResults = true;
                                // Highlight the matching text
                                td[j].innerHTML = highlightText(originalText, input.value);
                            } else {
                                // Reset to original text if no match
                                td[j].textContent = originalText;
                            }
                        }
                    }
                    
                    tr[i].style.display = found ? "" : "none";
                }

                // Show/hide "No results" message
                var noResultsMsg = document.getElementById("no-results");
                if (!noResultsMsg) {
                    noResultsMsg = document.createElement("p");
                    noResultsMsg.id = "no-results";
                    noResultsMsg.style.textAlign = "center";
                    noResultsMsg.style.padding = "10px";
                    noResultsMsg.style.color = "#666";
                    table.parentNode.insertBefore(noResultsMsg, table.nextSibling);
                }
                noResultsMsg.textContent = !anyResults && filter.length > 0 ? "No matching transactions found" : "";
            }

            var searchInput = document.getElementById("search");
            if (searchInput) {
                // Handle both typing and Enter key
                searchInput.addEventListener("input", search);
                searchInput.addEventListener("keypress", function(e) {
                    if (e.key === "Enter") {
                        e.preventDefault();
                        search();
                    }
                });
            }
        });
    </script>
</body>
</html>
