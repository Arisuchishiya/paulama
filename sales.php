<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Form</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .receipt-actions {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .receipt-actions .button {
            transition: all 0.3s ease;
        }
        .receipt-actions .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .receipt-actions .button i {
            margin-right: 8px;
        }
        .cart-container {
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        .cart-item:last-child {
            border-bottom: none;
        }
        .cart-total {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #dee2e6;
            text-align: right;
            font-weight: bold;
        }
        .remove-item {
            color: #dc3545;
            cursor: pointer;
            padding: 5px;
        }
        .remove-item:hover {
            color: #c82333;
        }
    </style>
    <script src="notifications.js"></script>
    <script>
    let cart = [];
    let totalAmount = 0;

    function calculateTotal() {
        const quantity = document.getElementById('quantity').value;
        const productSelect = document.getElementById('product');
        const price = productSelect.options[productSelect.selectedIndex].getAttribute('data-price');
        const total = quantity * price;
        document.getElementById('amount').value = total.toFixed(2);
    }

    function addToCart() {
        const productSelect = document.getElementById('product');
        const quantity = document.getElementById('quantity').value;
        const price = productSelect.options[productSelect.selectedIndex].getAttribute('data-price');
        const productName = productSelect.options[productSelect.selectedIndex].value;
        
        if (quantity <= 0) {
            showNotification('Please enter a valid quantity', 'error');
            return;
        }

        const item = {
            product: productName,
            quantity: parseInt(quantity),
            price: parseFloat(price),
            total: parseFloat(quantity) * parseFloat(price)
        };

        cart.push(item);
        updateCartDisplay();
        document.getElementById('quantity').value = '';
        document.getElementById('amount').value = '';
        showNotification('Item added to cart', 'success');
    }

    function removeFromCart(index) {
        cart.splice(index, 1);
        updateCartDisplay();
        showNotification('Item removed from cart', 'success');
    }

    function updateCartDisplay() {
        const cartContainer = document.getElementById('cart-items');
        const cartTotal = document.getElementById('cart-total');
        cartContainer.innerHTML = '';
        totalAmount = 0;

        cart.forEach((item, index) => {
            const cartItem = document.createElement('div');
            cartItem.className = 'cart-item';
            cartItem.innerHTML = `
                <span>${item.product} x ${item.quantity}</span>
                <span>Ksh ${item.total.toFixed(2)}</span>
                <span class="remove-item" onclick="removeFromCart(${index})">
                    <i class="fas fa-trash"></i>
                </span>
            `;
            cartContainer.appendChild(cartItem);
            totalAmount += item.total;
        });

        cartTotal.textContent = `Total: Ksh ${totalAmount.toFixed(2)}`;
        document.getElementById('total_amount').value = totalAmount.toFixed(2);
    }

    function submitSale() {
        if (cart.length === 0) {
            showNotification('Please add items to cart first', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('name', document.getElementById('name').value);
        formData.append('payment_method', document.getElementById('payment_method').value);
        formData.append('total_amount', totalAmount.toFixed(2));
        formData.append('cart_items', JSON.stringify(cart));

        fetch('process_sales.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Sale recorded successfully!', 'success');
                cart = [];
                updateCartDisplay();
                if (data.sale_id) {
                    window.location.href = `sales.php?sale_id=${data.sale_id}`;
                }
            } else {
                showNotification(data.error || 'Error recording sale', 'error');
            }
        })
        .catch(error => {
            showNotification('Error processing sale', 'error');
        });
    }

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
    <button class="menu-toggle" aria-label="Toggle menu">
        <span></span>
        <span></span>
        <span></span>
    </button>
    <nav>
    <br><br><br>
        <a href="index.php">Register</a>
        <a href="sales.php" class="current">Make Sales</a>
        <a href="money.php">Money</a>
        <a href="client.php">Client List</a>
        <a href="products.php">Products</a>
        <a href="retailers.php">Retailers</a>
        <a href="transactions.php">Transactions</a>
    </nav>
    <div class="main-content">
        <div class="container">
            <h2>Sales Form</h2>
            <?php
            if (isset($_GET['success'])) {
                echo '<div class="feedback success">Sale recorded successfully!</div>';
            }
            if (isset($_GET['error'])) {
                echo '<div class="feedback error">Error: ' . htmlspecialchars($_GET['error']) . '</div>';
            }
            if (isset($_GET['sale_id'])) {
                echo '<div class="receipt-actions" style="margin: 20px 0;">
                        <a href="generate_receipt.php?sale_id=' . htmlspecialchars($_GET['sale_id']) . '" 
                           class="button" style="background-color: #27ae60; color: white; padding: 10px 20px; 
                           text-decoration: none; border-radius: 5px; display: inline-block;">
                            <i class="fas fa-download"></i> Download Receipt
                        </a>
                      </div>';
            }
            ?>
            <form id="salesForm" onsubmit="event.preventDefault(); submitSale();">
                <div class="form-group">
                    <label for="name">Client Name:</label>
                    <select id="name" name="name" required>
                        <?php
                        require_once 'db_config.php';
                        $sql = "SELECT client_name FROM clients";
                        try {
                            $stmt = $conn->query($sql);
                            if ($stmt->rowCount() > 0) {
                                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='" . htmlspecialchars($row['client_name']) . "'>" 
                                         . htmlspecialchars($row['client_name']) . "</option>";
                                }
                            } else {
                                echo "<option value=''>No clients found</option>";
                            }
                        } catch(PDOException $e) {
                            echo "<option value=''>Error: " . htmlspecialchars($e->getMessage()) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="payment_method">Method of Payment:</label>
                    <select id="payment_method" name="payment_method" required>
                        <option value="cash">Cash</option>
                        <option value="m_pesa">M-Pesa</option>
                        <option value="bank">Bank</option>
                    </select>
                </div>

                <div class="cart-container">
                    <h3>Shopping Cart</h3>
                    <div id="cart-items"></div>
                    <div id="cart-total" class="cart-total"></div>
                </div>

                <input type="hidden" id="total_amount" name="total_amount">
                <input type="submit" value="Submit Sale" class="button" style="background-color: #007bff;">
            </form>

            <form id="addToCartForm" onsubmit="event.preventDefault(); addToCart();">
                <div class="form-group">
                    <label for="product">Product:</label>
                    <select id="product" name="product" required onchange="calculateTotal()">
                        <?php
                        $sql = "SELECT product_name, price FROM products";
                        try {
                            $stmt = $conn->query($sql);
                            if ($stmt->rowCount() > 0) {
                                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='" . htmlspecialchars($row['product_name']) . "' data-price='" 
                                         . htmlspecialchars($row['price']) . "'>" 
                                         . htmlspecialchars($row['product_name']) . " (Ksh " 
                                         . htmlspecialchars($row['price']) . ")</option>";
                                }
                            } else {
                                echo "<option value=''>No products found</option>";
                            }
                        } catch(PDOException $e) {
                            echo "<option value=''>Error: " . htmlspecialchars($e->getMessage()) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="quantity">Quantity:</label>
                    <input type="number" id="quantity" name="quantity" required onchange="calculateTotal()" onkeyup="calculateTotal()">
                </div>

                <div class="form-group">
                    <label for="amount">Amount:</label>
                    <input type="number" id="amount" name="amount" step="0.01" required readonly>
                </div>

                <div class="form-group">
                    <button type="submit" class="button" style="background-color: #28a745;">
                        <i class="fas fa-plus"></i> Add to Cart
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>