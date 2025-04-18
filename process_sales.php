<?php
require_once 'db_config.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $client_name = $_POST['name'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';
    $total_amount = $_POST['total_amount'] ?? 0;
    $cart_items = json_decode($_POST['cart_items'] ?? '[]', true);

    if (empty($client_name) || empty($payment_method) || empty($cart_items)) {
        throw new Exception('Missing required fields');
    }

    // Get client_id from client_name
    $stmt = $conn->prepare("SELECT id FROM clients WHERE client_name = ?");
    $stmt->execute([$client_name]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$client) {
        throw new Exception('Client not found');
    }

    $client_id = $client['id'];
    $last_sale_id = null;

    $conn->beginTransaction();

    // For each item in cart, create a separate sale record
    foreach ($cart_items as $item) {
        // Get product_id from product_name
        $stmt = $conn->prepare("SELECT id, price FROM products WHERE product_name = ?");
        $stmt->execute([$item['product']]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            throw new Exception('Product not found: ' . $item['product']);
        }

        $product_id = $product['id'];
        $quantity = $item['quantity'];
        $amount = $item['total'];

        // Insert into sales table
        $stmt = $conn->prepare("INSERT INTO sales (client_id, product_id, quantity, amount, payment_method, created_at) 
                               VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$client_id, $product_id, $quantity, $amount, $payment_method]);
        $last_sale_id = $conn->lastInsertId();
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'sale_id' => $last_sale_id,
        'message' => 'Sales recorded successfully'
    ]);

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>