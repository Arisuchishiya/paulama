<?php
require_once 'db_config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = isset($_POST['action']) ? $_POST['action'] : 'add';
    $product_id = isset($_POST['product_id']) ? $_POST['product_id'] : null;

    try {
        if ($action === 'delete' && $product_id) {
            // Check if product is used in any sales before deleting
            $check_sales = "SELECT COUNT(*) FROM sales WHERE product_id = :product_id";
            $check_stmt = $conn->prepare($check_sales);
            $check_stmt->bindParam(':product_id', $product_id);
            $check_stmt->execute();
            
            if ($check_stmt->fetchColumn() > 0) {
                $_SESSION['error'] = "Cannot delete product as it has associated sales records";
                header("Location: products.php");
                exit();
            }

            // Delete product
            $sql = "DELETE FROM products WHERE id = :product_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':product_id', $product_id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Product deleted successfully!";
            } else {
                $_SESSION['error'] = "Error: Unable to delete product";
            }
            header("Location: products.php");
            exit();
        }

        $product_name = $_POST['product_name'];
        $price = $_POST['price'];

        if ($action === 'update' && $product_id) {
            // Check for duplicate name excluding current product
            $check_sql = "SELECT COUNT(*) FROM products WHERE product_name = :product_name AND id != :product_id";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bindParam(':product_name', $product_name);
            $check_stmt->bindParam(':product_id', $product_id);
            $check_stmt->execute();
            
            if ($check_stmt->fetchColumn() > 0) {
                $_SESSION['error'] = "A product with this name already exists!";
                header("Location: products.php");
                exit();
            }

            // Update existing product
            $sql = "UPDATE products SET product_name = :product_name, price = :price WHERE id = :product_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':product_name', $product_name);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':product_id', $product_id);
        } else {
            // Check for duplicate product name
            $check_sql = "SELECT COUNT(*) FROM products WHERE product_name = :product_name";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bindParam(':product_name', $product_name);
            $check_stmt->execute();
            
            if ($check_stmt->fetchColumn() > 0) {
                $_SESSION['error'] = "A product with this name already exists!";
                header("Location: products.php");
                exit();
            }

            // Add new product
            $sql = "INSERT INTO products (product_name, price) VALUES (:product_name, :price)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':product_name', $product_name);
            $stmt->bindParam(':price', $price);
        }
        
        if ($stmt->execute()) {
            $_SESSION['success'] = ($action === 'update') ? "Product updated successfully!" : "Product added successfully!";
        } else {
            $_SESSION['error'] = "Error: Unable to " . ($action === 'update' ? "update" : "add") . " product";
        }
        header("Location: products.php");
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error: " . htmlspecialchars($e->getMessage());
        header("Location: products.php");
        exit();
    }
}
?>