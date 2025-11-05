<?php
session_start();
include("config.php");

// Optional fallback if not logged in
if (!isset($_SESSION['name'])) {
    $_SESSION['name'] = 'Unknown User';
}

// Handle adding stock-in record
if (isset($_POST['add_stock_in'])) {
    $item_id = $_POST['item_id'];
    $quantity = $_POST['quantity'];
    $received_by = $_SESSION['name'];

    // Insert record into stock_in table
    $stmt = $conn->prepare("INSERT INTO stock_in (item_id, quantity, received_by) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $item_id, $quantity, $received_by);
    $stmt->execute();
    $stmt->close();

    // Update item quantity (increase)
    $conn->query("UPDATE items SET quantity = quantity + $quantity WHERE id = $item_id");

    echo "<p class='success-msg'>Stock added successfully!</p>";
}

// Handle delete (move to history)
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Fetch the record to move to history
    $res = $conn->query("SELECT * FROM stock_in WHERE id = $id");
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();

        // Use session name as received_by if original is empty
        $received_by = !empty($row['received_by']) ? $row['received_by'] : $_SESSION['name'];

        // Insert into history table
        $stmt = $conn->prepare("INSERT INTO stock_in_history (item_id, quantity, received_by, date_received) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $row['item_id'], $row['quantity'], $received_by, $row['date_received']);
        $stmt->execute();
        $stmt->close();

        // Delete the record from stock_in
        $conn->query("DELETE FROM stock_in WHERE id = $id");

        // Update item quantity (decrease)
        $conn->query("UPDATE items SET quantity = quantity - {$row['quantity']} WHERE id = {$row['item_id']}");

        echo "<script>alert('Stock In record moved to history and deleted successfully!'); window.location='stock_in.php';</script>";
        exit;
    } else {
        echo "<script>alert('Record not found!'); window.location='stock_in.php';</script>";
        exit;
    }
}

// Fetch all stock-in records
$result = $conn->query("
    SELECT stock_in.*, items.item_name 
    FROM stock_in 
    JOIN items ON stock_in.item_id = items.id
    ORDER BY stock_in.date_received DESC
");

// Fetch items for the add stock-in dropdown
$items = $conn->query("SELECT * FROM items");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Stock In</title>
<style>
  body { font-family: Arial; margin: 40px; background-color: #f9f9f9; color: #333; }
  h2, h3 { color: #444; }
  form { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); margin-bottom: 30px; max-width: 400px; display: flex; flex-direction: column; gap: 12px; }
  form select, form input, form button { padding: 10px; font-size: 14px; border-radius: 4px; border: 1px solid #ccc; }
  form button { background-color: #28a745; color: #fff; border: none; cursor: pointer; max-width: 150px; }
  form button:hover { background-color: #218838; }
  table { width: 100%; border-collapse: collapse; background-color: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
  table th, table td { padding: 12px; border: 1px solid #ccc; text-align: left; }
  table th { background-color: #007bff; color: #fff; }
  table tr:hover { background-color: #f1f1f1; }
  .success-msg { color: green; font-weight: bold; margin-bottom: 15px; }
  a { color: #007bff; text-decoration: none; }
  a:hover { text-decoration: underline; }
  .back-link { display: inline-block; margin-top: 20px; padding: 10px 15px; background-color: #007bff; color: #fff; text-decoration: none; border-radius: 5px; }
  .back-link:hover { background-color: #0056b3; }
</style>
</head>
<body>

<h2>Stock In - Add / Manage Items</h2>

<form method="POST">
  <label>Select Item:</label>
  <select name="item_id" required>
    <option value="">-- Choose Item --</option>
    <?php while ($item = $items->fetch_assoc()): ?>
      <option value="<?= $item['id'] ?>"><?= $item['item_name'] ?> (Qty: <?= $item['quantity'] ?>)</option>
    <?php endwhile; ?>
  </select>

  <label>Quantity Received:</label>
  <input type="number" name="quantity" required>

  <button type="submit" name="add_stock_in">Add Stock</button>
</form>

<hr>

<h3>Stock In Records</h3>
<table>
  <tr>
    <th>ID</th>
    <th>Item Name</th>
    <th>Quantity</th>
    <th>Date Received</th>
    <th>Received By</th>
    <th>Action</th>
  </tr>
  <?php while ($row = $result->fetch_assoc()): ?>
  <tr>
    <td><?= $row['id'] ?></td>
    <td><?= $row['item_name'] ?></td>
    <td><?= $row['quantity'] ?></td>
    <td><?= $row['date_received'] ?></td>
    <td><?= $row['received_by'] ?></td>
    <td>
      <a href="stock_in.php?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this record?')">Delete</a>
    </td>
  </tr>
  <?php endwhile; ?>
</table>

<br>
<a href="dashboard.php" class="back-link">Back to Dashboard</a>
<a href="stock_in_history.php" class="back-link">History</a>

</body>
</html>
