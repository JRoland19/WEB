<?php
session_start();
include("config.php");

// Optional: fallback name if not logged in
if (!isset($_SESSION['name'])) {
    $_SESSION['name'] = 'Unknown User';
}

// Handle restore from history
if (isset($_GET['restore'])) {
    $id = intval($_GET['restore']);

    // Fetch the record from history
    $res = $conn->query("SELECT * FROM stock_in_history WHERE id = $id");
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();

        // Insert back into stock_in
        $stmt = $conn->prepare("INSERT INTO stock_in (item_id, quantity, received_by, date_received) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $row['item_id'], $row['quantity'], $row['received_by'], $row['date_received']);
        $stmt->execute();
        $stmt->close();

        // Update items table quantity
        $conn->query("UPDATE items SET quantity = quantity + {$row['quantity']} WHERE id = {$row['item_id']}");

        // Delete from history
        $conn->query("DELETE FROM stock_in_history WHERE id = $id");

        echo "<script>alert('Record restored successfully!'); window.location='stock_in_history.php';</script>";
        exit;
    }
}

// Handle permanent delete from history
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM stock_in_history WHERE id = $id");
    echo "<script>alert('Record permanently deleted from history!'); window.location='stock_in_history.php';</script>";
    exit;
}

// Fetch all history records
$result = $conn->query("
    SELECT stock_in_history.*, items.item_name 
    FROM stock_in_history
    JOIN items ON stock_in_history.item_id = items.id
    ORDER BY stock_in_history.date_received DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Stock In History</title>
<style>
  body { font-family: Arial; margin: 40px; background-color: #f9f9f9; color: #333; }
  h2, h3 { color: #444; }
  table { width: 100%; border-collapse: collapse; background-color: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
  table th, table td { padding: 12px; border: 1px solid #ccc; text-align: left; }
  table th { background-color: #007bff; color: #fff; }
  table tr:hover { background-color: #f1f1f1; }
  a { color: #007bff; text-decoration: none; }
  a:hover { text-decoration: underline; }
  .back-link { display: inline-block; margin-top: 20px; padding: 10px 15px; background-color: #007bff; color: #fff; text-decoration: none; border-radius: 5px; }
  .back-link:hover { background-color: #0056b3; }
</style>
</head>
<body>

<h2>Stock In History</h2>

<table>
    <tr>
        <th>ID</th>
        <th>Item Name</th>
        <th>Quantity</th>
        <th>Date Received</th>
        <th>Received By</th>
        <th>Actions</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['id'] ?></td>
        <td><?= $row['item_name'] ?></td>
        <td><?= $row['quantity'] ?></td>
        <td><?= $row['date_received'] ?></td>
        <td><?= $row['received_by'] ?></td>
        <td>
            <a href="stock_in_history.php?restore=<?= $row['id'] ?>" onclick="return confirm('Restore this record back to Stock In?')">Restore</a> |
            <a href="stock_in_history.php?delete=<?= $row['id'] ?>" onclick="return confirm('Permanently delete this record?')">Delete</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<br>
<a href="stock_in.php" class="back-link">Back to Stock In</a>

</body>
</html>
