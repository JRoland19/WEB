<?php
session_start();
include("config.php");

// Optional fallback user
if (!isset($_SESSION['name'])) {
    $_SESSION['name'] = 'Unknown User';
}

// Create history table if it doesn't exist
$conn->query("
CREATE TABLE IF NOT EXISTS stock_out_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    stock_out_id INT,
    item_id INT,
    quantity INT,
    released_by VARCHAR(255),
    date_released DATETIME,
    deleted_at DATETIME,
    deleted_by VARCHAR(255)
)");

// Add stock-out record
if (isset($_POST['add_stock_out'])) {
    $item_id = $_POST['item_id'];
    $quantity = $_POST['quantity'];
    $released_by = $_SESSION['name'];

    $check = $conn->query("SELECT quantity FROM items WHERE id=$item_id")->fetch_assoc();

    if ($check['quantity'] >= $quantity) {
        $stmt = $conn->prepare("INSERT INTO stock_out (item_id, quantity, released_by) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $item_id, $quantity, $released_by);
        $stmt->execute();
        $stmt->close();

        $conn->query("UPDATE items SET quantity = quantity - $quantity WHERE id=$item_id");
        echo "<p class='success-msg'>Stock released successfully!</p>";
    } else {
        echo "<p class='error-msg'>Not enough stock available!</p>";
    }
}

// Delete stock-out record (archive)
if (isset($_GET['delete'])) {
    $stock_out_id = $_GET['delete'];
    $current_user = $_SESSION['name'];

    $record = $conn->query("SELECT * FROM stock_out WHERE id=$stock_out_id")->fetch_assoc();
    if ($record) {
        // Archive record
        $stmt = $conn->prepare("
            INSERT INTO stock_out_history 
            (stock_out_id, item_id, quantity, released_by, date_released, deleted_at, deleted_by) 
            VALUES (?, ?, ?, ?, ?, NOW(), ?)
        ");
        $stmt->bind_param(
            "iiisss",
            $stock_out_id,
            $record['item_id'],
            $record['quantity'],
            $record['released_by'],
            $record['date_released'],
            $current_user
        );
        $stmt->execute();
        $stmt->close();

        // Return stock to inventory
        $conn->query("UPDATE items SET quantity = quantity + {$record['quantity']} WHERE id={$record['item_id']}");
        $conn->query("DELETE FROM stock_out WHERE id=$stock_out_id");

        echo "<p class='delete-msg'>Stock-out record archived successfully!</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Stock Out</title>
<style>
  body { font-family: Arial, sans-serif; margin: 40px; background-color: #f9f9f9; color: #333; }
  h2, h3 { color: #444; }
  form { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0,0,0,0.1); margin-bottom: 30px; display: flex; flex-direction: column; gap: 12px; max-width: 400px; }
  form select, form input, form button { padding: 10px; font-size: 14px; border-radius: 4px; border: 1px solid #ccc; }
  form button { background-color: #dc3545; color: #fff; border: none; cursor: pointer; max-width: 150px; }
  form button:hover { background-color: #c82333; }
  table { width: 100%; border-collapse: collapse; background-color: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0px 0px 10px rgba(0,0,0,0.1); }
  table th, table td { padding: 12px; border: 1px solid #ccc; text-align: left; }
  table th { background-color: #007bff; color: #fff; }
  table tr:hover { background-color: #f1f1f1; }
  .success-msg { color: green; font-weight: bold; margin-bottom: 15px; }
  .error-msg { color: red; font-weight: bold; margin-bottom: 15px; }
  .delete-msg { color: red; font-weight: bold; margin-bottom: 15px; }
  a { color: #007bff; text-decoration: none; }
  a:hover { text-decoration: underline; }
  .back-link { display: inline-block; margin-top: 20px; padding: 10px 15px; background-color: #007bff; color: #fff; text-decoration: none; border-radius: 5px; }
  .back-link:hover { background-color: #0056b3; }
</style>
</head>
<body>

<h2>Stock Out - Record Released Items</h2>

<form method="POST">
  <label>Select Item:</label>
  <select name="item_id" required>
    <option value="">-- Choose Item --</option>
    <?php
    $items = $conn->query("SELECT * FROM items");
    while ($row = $items->fetch_assoc()) {
        echo "<option value='{$row['id']}'>{$row['item_name']} (Qty: {$row['quantity']})</option>";
    }
    ?>
  </select>

  <label>Quantity Released:</label>
  <input type="number" name="quantity" required>

  <button type="submit" name="add_stock_out">Release Stock</button>
</form>

<hr>

<h3>Stock Out Records</h3>
<table>
  <tr>
    <th>ID</th>
    <th>Item Name</th>
    <th>Quantity</th>
    <th>Date Released</th>
    <th>Released By</th>
    <th>Action</th>
  </tr>
  <?php
  $result = $conn->query("
    SELECT stock_out.*, items.item_name 
    FROM stock_out 
    JOIN items ON stock_out.item_id = items.id
    ORDER BY stock_out.date_released DESC
  ");
  while ($row = $result->fetch_assoc()) {
      echo "<tr>
        <td>{$row['id']}</td>
        <td>{$row['item_name']}</td>
        <td>{$row['quantity']}</td>
        <td>{$row['date_released']}</td>
        <td>{$row['released_by']}</td>
        <td>
          <a href='?delete={$row['id']}' onclick='return confirm(\"Are you sure you want to delete this record?\")'>Archive</a>
        </td>
      </tr>";
  }
  ?>
</table>

<br>
<a href='dashboard.php' class="back-link">Back to Dashboard</a>
<a href='stock_out_history.php' class="back-link">Archive</a>

</body>
</html>
