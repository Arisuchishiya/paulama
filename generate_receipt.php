<?php
require_once 'db_config.php';
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_GET['sale_id'])) {
    die('Sale ID not provided');
}

$sale_id = $_GET['sale_id'];

try {
    // Get the first sale record to get client and payment info
    $stmt = $conn->prepare("
        SELECT s.*, c.client_name, c.phone, c.email 
        FROM sales s 
        JOIN clients c ON s.client_id = c.id 
        WHERE s.id = ?
    ");
    $stmt->execute([$sale_id]);
    $sale = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sale) {
        die('Sale not found');
    }

    // Get all items from this transaction using exact timestamp and client_id
    $stmt = $conn->prepare("
        SELECT s.*, p.product_name, p.price 
        FROM sales s 
        JOIN products p ON s.product_id = p.id 
        WHERE s.client_id = ? 
        AND s.created_at = ?
        AND s.payment_method = ?
        AND s.product_id IS NOT NULL
        ORDER BY s.id ASC
    ");
    $stmt->execute([$sale['client_id'], $sale['created_at'], $sale['payment_method']]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($items)) {
        die('No items found for this sale');
    }

    // Calculate total amount
    $total_amount = 0;
    foreach ($items as $item) {
        $total_amount += $item['amount'];
    }

    // Get the absolute path for the logo
    $logo_path = __DIR__ . '/logo.jpg';
    $logo_data = base64_encode(file_get_contents($logo_path));
    $logo_src = 'data:image/jpeg;base64,' . $logo_data;

    // Generate receipt HTML
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { 
                font-family: "Courier New", monospace;
                color: #333;
                line-height: 1.4;
            }
            .receipt { 
                width: 80mm;
                padding: 20px;
                margin: 0 auto;
            }
            .header {
                position: relative;
                margin-bottom: 40px;
                border-top: 2px dashed #4CAF50;
                padding-top: 20px;
                text-align: center;
            }
            .receipt-title {
                color: #4CAF50;
                font-size: 24px;
                margin-bottom: 20px;
            }
            .logo {
                width: 100px;
                height: auto;
                margin: 0 auto 20px;
                display: block;
            }
            .info-grid {
                display: grid;
                grid-template-columns: 1fr;
                gap: 15px;
                margin-bottom: 20px;
            }
            .from-section, .bill-to-section {
                margin-bottom: 15px;
            }
            .section-title {
                color: #4CAF50;
                font-weight: bold;
                margin-bottom: 8px;
                text-transform: uppercase;
                font-size: 14px;
            }
            .receipt-details {
                margin: 15px 0;
                text-align: right;
            }
            .receipt-details p {
                margin: 3px 0;
                font-size: 14px;
            }
            .receipt-details .label {
                color: #4CAF50;
                text-transform: uppercase;
            }
            .items {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
                font-size: 14px;
            }
            .items th {
                color: #4CAF50;
                text-transform: uppercase;
                border-bottom: 1px solid #4CAF50;
                text-align: left;
                padding: 8px 4px;
                font-size: 12px;
            }
            .items td {
                padding: 8px 4px;
                border-bottom: 1px solid #ddd;
            }
            .total-section {
                text-align: right;
                margin-top: 20px;
                position: relative;
                padding-bottom: 100px;
            }
            .total-box {
                display: inline-block;
                border: 2px solid #4CAF50;
                padding: 8px 15px;
                margin-top: 10px;
                font-size: 16px;
            }
            .payment-info {
                text-align: right;
                margin-top: 15px;
                font-size: 14px;
                color: #666;
            }
            .payment-box {
                display: inline-block;
                border: 1px solid #4CAF50;
                padding: 6px 12px;
                margin-top: 8px;
                font-size: 14px;
            }
            .payment-label {
                color: #4CAF50;
                text-transform: uppercase;
                font-size: 13px;
            }
            .terms {
                margin-top: 20px;
                color: #666;
                font-size: 12px;
                text-align: center;
                position: relative;
            }
            .terms-conditions {
                margin: 15px 0;
                padding: 10px;
                border-top: 1px dashed #4CAF50;
                border-bottom: 1px dashed #4CAF50;
                color: #666;
                font-size: 11px;
                text-align: center;
                font-style:italic;
            }
            .served-by {
                margin-top: 20px;
                text-align: center;
                font-size: 12px;
                color: #4CAF50;
                border-top: 1px dashed #4CAF50;
                padding-top: 10px;
            }
            .served-by p {
                margin: 2px 0;
            }
            .signature-name {
                font-family: "Lucida Handwriting", "Brush Script MT", "Comic Sans MS", cursive;
                font-size: 22px;
                color: #1a4a1c;
                margin: 8px 0;
                font-weight: normal;
                transform: rotate(-5deg);
                display: inline-block;
                text-decoration: none;
                line-height: 1.2;
            }
            .contact {
                color: #666;
            }
            .paid-stamp-container {
                text-align: center;
                margin-top: 30px;
                position: relative;
                height: 60px;
            }
            .paid-stamp {
                position: absolute;
                left: 50%;
                transform: translateX(-50%) rotate(-12deg);
                border: 3px solid #4CAF50;
                color: #4CAF50;
                padding: 10px 30px;
                font-size: 24px;
                font-weight: bold;
                text-transform: uppercase;
                letter-spacing: 3px;
                border-radius: 10px;
                opacity: 0.85;
                background: rgba(255, 255, 255, 0.9);
                box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
                z-index: 100;
            }
            .paid-stamp::after {
                content: "";
                position: absolute;
                top: -3px;
                left: -3px;
                right: -3px;
                bottom: -3px;
                border: 3px solid #4CAF50;
                border-radius: 10px;
                transform: rotate(3deg);
                z-index: -1;
            }
            .terms-title {
                color: #4CAF50;
                text-transform: uppercase;
                font-weight: bold;
                margin-bottom: 8px;
            }
            p {
                margin: 5px 0;
                font-size: 14px;
            }
        </style>
    </head>
    <body>
        <div class="receipt">
            <div class="header">
                <img src="' . $logo_src . '" alt="Pembe Feeds Logo" class="logo">
                <div class="receipt-title">receipt</div>
                <div class="receipt-details">
                    <p><span class="label">Receipt #:</span> ' . $sale_id . '</p>
                    <p><span class="label">Receipt Date:</span> ' . date('d/m/Y h:i A', strtotime($sale['created_at'])) . '</p>
                </div>
            </div>

            <div class="info-grid">
                <div class="from-section">
                    <div class="section-title">From</div>
                    <p>Pembe Feeds</p>
                </div>

                <div class="bill-to-section">
                    <div class="section-title">Bill To</div>
                    <p>' . htmlspecialchars($sale['client_name']) . '</p>
                <p>Phone: ' . htmlspecialchars($sale['phone']) . '</p>
                <p>Email: ' . htmlspecialchars($sale['email']) . '</p>
                </div>
            </div>

            <table class="items">
                <tr>
                    <th>QTY</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Amount</th>
                </tr>';

    foreach ($items as $item) {
        $html .= '
                <tr>
                    <td>' . $item['quantity'] . '</td>
                    <td>' . htmlspecialchars($item['product_name']) . '</td>
                    <td>Ksh ' . number_format($item['price'], 2) . '</td>
                    <td>Ksh ' . number_format($item['amount'], 2) . '</td>
                </tr>';
    }

    $html .= '
            </table>

            <div class="total-section">
                <div class="total-box">
                    <span class="total-label">Total: </span>
                    <span class="total-amount">Ksh ' . number_format($total_amount, 2) . '</span>
                </div>
                <div class="payment-info">
                    <div class="payment-box">
                        <span class="payment-label">Payment Method: </span>
                        <span>' . htmlspecialchars($sale['payment_method']) . '</span>
                    </div>
                </div>
                <div class="paid-stamp-container">
                    <div class="paid-stamp">PAID</div>
                </div>
            </div>

            <div class="terms">
                <div class="terms-conditions">
                    Terms & Conditions: Goods once sold cannot be returned
                </div>
                <p>Thank you for doing business with us!</p>
                <div class="served-by">
                    <p>Served by:</p>
                    <p class="signature-name">Paul Mwangi</p>
                    <p class="contact">Contact: 0714779995</p>
                </div>
            </div>
        </div>
    </body>
    </html>';

    // Generate PDF
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Output PDF
    $dompdf->stream("receipt_" . $sale_id . ".pdf", array("Attachment" => true));

} catch (Exception $e) {
    die('Error generating receipt: ' . $e->getMessage());
}
?> 