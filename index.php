<?php
// Include the database configuration file
require_once 'db_config.php';

// Initialize variables for feedback
$feedback = "";
$feedbackType = ""; // 'success' or 'error'

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize and validate input data
    $client_name = htmlspecialchars(trim($_POST['name']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $email = htmlspecialchars(trim($_POST['email']));

    // Validate input
    if (empty($client_name) || empty($phone) || empty($email)) {
        $feedback = "All fields are required.";
        $feedbackType = "error";
    } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {
        $feedback = "Invalid phone number format. Must be 10 digits.";
        $feedbackType = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $feedback = "Invalid email format.";
        $feedbackType = "error";
    } else {
        try {
            // Check if phone or email already exists
            $sql = "SELECT COUNT(*) FROM clients WHERE phone = :phone OR email = :email";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->fetchColumn() > 0) {
                $feedback = "Error: Phone number or email already exists.";
                $feedbackType = "error";
            } else {
                // Prepare and execute the SQL query
                $sql = "INSERT INTO clients (client_name, phone, email) VALUES (:client_name, :phone, :email)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':client_name', $client_name);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':email', $email);

                if ($stmt->execute()) {
                    $feedback = "Client registered successfully.";
                    $feedbackType = "success";
                    // Clear form data after successful submission
                    $_POST = array();
                } else {
                    $feedback = "Error registering client.";
                    $feedbackType = "error";
                }
            }
        } catch (PDOException $e) {
            $feedback = "Error: " . $e->getMessage();
            $feedbackType = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Registration Form</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .container {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .feedback {
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 4px;
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
        <a href="index.php" class="current">Register</a>
        <a href="sales.php">Make Sales</a>
        <a href="money.php">Money</a>
        <a href="client.php">Client List</a>
        <a href="products.php">Products</a>
        <a href="retailers.php">Retailers</a>
        <a href="transactions.php">Transactions</a>
    </nav>
    <div class="main-content">
        <div class="container">
            <h2>Client Registration</h2>
            <?php if (!empty($feedback)): ?>
                <div class="feedback <?php echo $feedbackType; ?>">
                    <?php echo $feedback; ?>
                </div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label for="name">Client Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number:</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
                </div>
                <br>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>
                <br><br>
                <input type="submit" value="Register Client">
            </form>
        </div>
    </div>
</body>
</html>
