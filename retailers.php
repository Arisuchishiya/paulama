<?php
require_once 'db_config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $retailer_name = $_POST['retailer_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $address = $_POST['address'] ?? '';

    if (empty($retailer_name)) {
        $_SESSION['error'] = "Retailer name is required";
    } else {
        try {
            $sql = "INSERT INTO retailers (retailer_name, phone, email, address) VALUES (:name, :phone, :email, :address)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':name', $retailer_name);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':address', $address);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Retailer added successfully!";
            } else {
                $_SESSION['error'] = "Error adding retailer";
            }
        } catch(PDOException $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
        }
    }
    header("Location: retailers.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retailer Management</title>
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
        <a href="retailers.php" class="current">Retailers</a>
        <a href="transactions.php">Transactions</a>
    </nav>
    <div class="main-content">
        <div class="container">
            <h2>Retailer Management</h2>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="feedback success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="feedback error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <form action="retailers.php" method="post" class="form-style">
                <div class="form-group">
                    <label for="retailer_name">Retailer Name:</label>
                    <input type="text" id="retailer_name" name="retailer_name" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number:</label>
                    <input type="tel" id="phone" name="phone">
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email">
                </div>

                <div class="form-group">
                    <label for="address">Address:</label>
                    <textarea id="address" name="address" rows="3"></textarea>
                </div>

                <input type="submit" value="Add Retailer">
            </form>

            <h3>Retailer List</h3>
            <?php
            try {
                $sql = "SELECT * FROM retailers ORDER BY retailer_name";
                $stmt = $conn->query($sql);
                
                if ($stmt->rowCount() > 0) {
                    echo "<table>
                            <tr>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Address</th>
                            </tr>";
                    
                    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>
                                <td>" . htmlspecialchars($row['retailer_name']) . "</td>
                                <td>" . htmlspecialchars($row['phone'] ?: '-') . "</td>
                                <td>" . htmlspecialchars($row['email'] ?: '-') . "</td>
                                <td>" . htmlspecialchars($row['address'] ?: '-') . "</td>
                            </tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>No retailers found</p>";
                }
            } catch(PDOException $e) {
                echo '<div class="feedback error">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            ?>
        </div>
    </div>
</body>
</html>
