<?php
require_once 'db_config.php';

try {
    // Create sales table
    $sql = "CREATE TABLE IF NOT EXISTS sales (
        id INT PRIMARY KEY AUTO_INCREMENT,
        client_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (client_id) REFERENCES clients(id),
        FOREIGN KEY (product_id) REFERENCES products(id)
    )";
    $conn->exec($sql);
    echo "Sales table created successfully<br>";

    // Create debts table
    $sql = "CREATE TABLE IF NOT EXISTS debts (
        id INT PRIMARY KEY AUTO_INCREMENT,
        client_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        sale_id INT NOT NULL,
        payment_status ENUM('pending', 'paid') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (client_id) REFERENCES clients(id),
        FOREIGN KEY (sale_id) REFERENCES sales(id)
    )";
    $conn->exec($sql);
    echo "Debts table created successfully<br>";

} catch(PDOException $e) {
    echo "Error creating tables: " . $e->getMessage();
}
?> 