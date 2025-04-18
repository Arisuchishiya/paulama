<?php
require_once 'db_config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';
    $debt_id = $_POST['debt_id'] ?? null;

    if ($action === 'clear' && $debt_id) {
        try {
            // Get the payment details from the form
            $payment_amount = isset($_POST['payment_amount']) ? floatval($_POST['payment_amount']) : 0;
            $payment_method = $_POST['payment_method'] ?? '';
            
            // Validate input
            if ($payment_amount <= 0) {
                $_SESSION['error'] = "Payment amount must be greater than zero";
                header("Location: money.php");
                exit();
            }
            
            if (empty($payment_method)) {
                $_SESSION['error'] = "Payment method is required";
                header("Location: money.php");
                exit();
            }
            
            // Start transaction
            $conn->beginTransaction();
            
            // Get the debt details
            $debt_query = "SELECT d.*, r.retailer_name 
                          FROM debts d 
                          JOIN retailers r ON d.retailer_id = r.id 
                          WHERE d.id = :debt_id";
            $debt_stmt = $conn->prepare($debt_query);
            $debt_stmt->bindParam(':debt_id', $debt_id);
            $debt_stmt->execute();
            $debt_data = $debt_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$debt_data) {
                throw new Exception("Debt record not found");
            }
            
            $total_debt = floatval($debt_data['amount']);
            
            // Check if payment amount is valid
            if ($payment_amount > $total_debt) {
                throw new Exception("Payment amount cannot exceed the total debt amount");
            }
            
            // Update debt status and amount
            if ($payment_amount == $total_debt) {
                // Full payment - mark as paid
                $update_sql = "UPDATE debts SET 
                             payment_status = 'paid', 
                             paid_amount = :payment_amount, 
                             payment_method = :payment_method, 
                             paid_date = NOW() 
                             WHERE id = :debt_id";
            } else {
                // Partial payment - reduce the debt amount
                $remaining_amount = $total_debt - $payment_amount;
                $update_sql = "UPDATE debts SET 
                             amount = :remaining_amount, 
                             paid_amount = :payment_amount, 
                             payment_method = :payment_method, 
                             paid_date = NOW() 
                             WHERE id = :debt_id";
            }
            
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bindParam(':payment_amount', $payment_amount);
            $update_stmt->bindParam(':payment_method', $payment_method);
            $update_stmt->bindParam(':debt_id', $debt_id);
            
            if (isset($remaining_amount)) {
                $update_stmt->bindParam(':remaining_amount', $remaining_amount);
            }
            
            $update_stmt->execute();
            
            // Record the payment in sales table as a negative amount
            $description = "Debt payment for " . $debt_data['retailer_name'];
            $negative_amount = -1 * $payment_amount;
            
            $sales_sql = "INSERT INTO sales (amount, payment_method, description, created_at) 
                         VALUES (:amount, :payment_method, :description, NOW())";
            $sales_stmt = $conn->prepare($sales_sql);
            $sales_stmt->bindParam(':amount', $negative_amount);
            $sales_stmt->bindParam(':payment_method', $payment_method);
            $sales_stmt->bindParam(':description', $description);
            $sales_stmt->execute();
            
            // Commit transaction
            $conn->commit();
            
            $_SESSION['success'] = "Debt payment of Ksh " . number_format($payment_amount, 2) . " processed successfully!";
        } catch(Exception $e) {
            // Rollback transaction on error
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $_SESSION['error'] = "Error: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "Invalid request";
    }
}

header("Location: money.php");
exit();
?>
