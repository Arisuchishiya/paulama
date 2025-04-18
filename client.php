<?php
require_once 'db_config.php';

// Handle client deletion
if (isset($_POST['delete_client'])) {
    try {
        $client_id = $_POST['client_id'];
        
        // Check if client has any sales or debts
        $sql = "SELECT 
                (SELECT COUNT(*) FROM sales WHERE client_id = :client_id) as sales_count,
                (SELECT COUNT(*) FROM debts WHERE retailer_id = :client_id) as debts_count";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':client_id', $client_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['sales_count'] > 0 || $result['debts_count'] > 0) {
            throw new Exception("Cannot delete client! Client has existing sales or debts records");
        }

        // If no related records exist, delete the client
        $sql = "DELETE FROM clients WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $client_id);
        $stmt->execute();
        
        $feedback = "Client deleted successfully.";
        $feedbackType = "success";
    } catch (PDOException $e) {
        $feedback = "Error deleting client: " . $e->getMessage();
        $feedbackType = "error";
    } catch (Exception $e) {
        $feedback = $e->getMessage();
        $feedbackType = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client List</title>
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

        // Modal functionality
        const modal = document.getElementById('historyModal');
        const span = document.getElementsByClassName('close')[0];
        
        if (span) {
            span.onclick = function() {
                modal.style.display = 'none';
            }
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    });

    function viewHistory(clientId) {
        const modal = document.getElementById('historyModal');
        const historyContent = document.getElementById('historyContent');
        
        fetch('get_client_history.php?client_id=' + clientId)
            .then(response => response.text())
            .then(data => {
                historyContent.innerHTML = data;
                modal.style.display = 'block';
            })
            .catch(error => {
                console.error('Error:', error);
                historyContent.innerHTML = 'Error loading client history';
            });
    }
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
        <a href="client.php" class="current">Client List</a>
        <a href="products.php">Products</a>
        <a href="retailers.php">Retailers</a>
        <a href="transactions.php">Transactions</a>
    </nav>
    <div class="main-content">
        <div class="container">
            <h2>Client List</h2>
            
            <?php if (isset($feedback)): ?>
                <div class="feedback <?php echo $feedbackType; ?>">
                    <?php echo $feedback; ?>
                </div>
            <?php endif; ?>

            <?php
            try {
                $sql = "SELECT * FROM clients ORDER BY client_name";
                $stmt = $conn->query($sql);

                if ($stmt->rowCount() > 0) {
                    echo "<table>
                            <tr>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>";

                    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>
                                <td>" . htmlspecialchars($row["client_name"]) . "</td>
                                <td>" . htmlspecialchars($row["phone"]) . "</td>
                                <td>" . htmlspecialchars($row["email"]) . "</td>
                                <td>
                                    <form action='' method='post' style='display: inline;'>
                                        <input type='hidden' name='client_id' value='" . $row["id"] . "'>
                                        <button type='submit' name='delete_client' class='delete-btn' 
                                                onclick='return confirm(\"Are you sure you want to delete this client?\");'
                                                style='background-color: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;'>
                                            Delete
                                        </button>
                                    </form>
                                    <button onclick='viewHistory(" . $row["id"] . ")' 
                                            style='background-color: #007bff; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; margin-left: 5px;'>
                                        View History
                                    </button>
                                </td>
                            </tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>No clients found</p>";
                }
            } catch(PDOException $e) {
                echo '<div class="feedback error">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            ?>

            <!-- Client History Modal -->
            <div id="historyModal" class="modal" style="display: none; position: fixed; z-index: 1; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);">
                <div class="modal-content" style="background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 600px; border-radius: 5px;">
                    <span class="close" style="color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
                    <h3>Client History</h3>
                    <div id="historyContent"></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>