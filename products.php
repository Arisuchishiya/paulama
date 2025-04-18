<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management</title>
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

        // Function to populate form for editing
        window.editProduct = function(id, name, price) {
            document.getElementById('product_id').value = id;
            document.getElementById('product_name').value = name;
            document.getElementById('price').value = price;
            document.getElementById('action').value = 'update';
            document.getElementById('submitBtn').value = 'Update Product';
            document.getElementById('cancelBtn').style.display = 'inline-block';
        }

        // Function to reset form
        window.resetForm = function() {
            document.getElementById('productForm').reset();
            document.getElementById('product_id').value = '';
            document.getElementById('action').value = 'add';
            document.getElementById('submitBtn').value = 'Add Product';
            document.getElementById('cancelBtn').style.display = 'none';
        }

        // Function to delete product
        window.deleteProduct = function(id, name) {
            if (confirm('Are you sure you want to delete product: ' + name + '?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'process_product.php';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete';
                
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'product_id';
                idInput.value = id;
                
                form.appendChild(actionInput);
                form.appendChild(idInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
    });
    </script>
    <style>
    .action-buttons {
        display: flex;
        gap: 5px;
    }
    .action-buttons button {
        padding: 5px 10px;
        cursor: pointer;
    }
    .delete-btn {
        background-color: #ff4444;
        color: white;
        border: none;
        border-radius: 3px;
    }
    .edit-btn {
        background-color: #44b;
        color: white;
        border: none;
        border-radius: 3px;
    }
    </style>
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
        <a href="products.php" class="current">Products</a>
        <a href="retailers.php">Retailers</a>
        <a href="transactions.php">Transactions</a>
    </nav>
    <div class="main-content">
        <div class="container">
            <h2>Product Management</h2>
            <?php
            session_start();
            if (isset($_SESSION['success'])) {
                echo '<div class="feedback success">' . htmlspecialchars($_SESSION['success']) . '</div>';
                unset($_SESSION['success']);
            }
            if (isset($_SESSION['error'])) {
                echo '<div class="feedback error">' . htmlspecialchars($_SESSION['error']) . '</div>';
                unset($_SESSION['error']);
            }
            ?>
            <form id="productForm" action="process_product.php" method="post">
                <input type="hidden" id="product_id" name="product_id" value="">
                <input type="hidden" id="action" name="action" value="add">
                
                <div class="form-group">
                    <label for="product_name">Product Name:</label>
                    <input type="text" id="product_name" name="product_name" required>
                </div>

                <div class="form-group">
                    <label for="price">Unit Price:</label>
                    <input type="number" id="price" name="price" step="0.01" required>
                </div>

                <input type="submit" id="submitBtn" value="Add Product">
                <input type="button" id="cancelBtn" value="Cancel" onclick="resetForm()" style="display: none;">
            </form>

            <h3>Product List</h3>
            <?php
            require_once 'db_config.php';
            
            $sql = "SELECT * FROM products ORDER BY product_name";
            try {
                $stmt = $conn->query($sql);
                
                if ($stmt->rowCount() > 0) {
                    echo "<table>
                            <tr>
                                <th>Product Name</th>
                                <th>Unit Price</th>
                                <th>Actions</th>
                            </tr>";
                    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>
                                <td>" . htmlspecialchars($row["product_name"]) . "</td>
                                <td>Ksh " . number_format(htmlspecialchars($row["price"]), 2) . "</td>
                                <td class='action-buttons'>
                                    <button class='edit-btn' onclick='editProduct(" . 
                                    $row["id"] . ", \"" . 
                                    addslashes($row["product_name"]) . "\", " . 
                                    $row["price"] . ")'>Edit</button>
                                    <button class='delete-btn' onclick='deleteProduct(" . 
                                    $row["id"] . ", \"" . 
                                    addslashes($row["product_name"]) . "\")'>Delete</button>
                                </td>
                            </tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>No products found</p>";
                }
            } catch(PDOException $e) {
                echo '<div class="feedback error">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            ?>
        </div>
    </div>
</body>
</html>