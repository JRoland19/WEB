<?php
session_start();
include("config.php");

if (!isset($_SESSION['name'])) {
    $_SESSION['name'] = 'Unknown User';
}

// Restore archived record
if (isset($_GET['restore'])) {
    $history_id = $_GET['restore'];
    $record = $conn->query("SELECT * FROM stock_out_history WHERE id=$history_id")->fetch_assoc();

    if ($record) {
        // Insert back into stock_out table
        $stmt = $conn->prepare("
            INSERT INTO stock_out (id, item_id, quantity, released_by, date_released)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "iiiss",
            $record['stock_out_id'],
            $record['item_id'],
            $record['quantity'],
            $record['released_by'],
            $record['date_released']
        );
        $stmt->execute();
        $stmt->close();

        // Subtract quantity from items
        $conn->query("UPDATE items SET quantity = quantity - {$record['quantity']} WHERE id={$record['item_id']}");

        // Remove from history
        $conn->query("DELETE FROM stock_out_history WHERE id=$history_id");
        echo "<p class='success-msg'>Record restored successfully!</p>";
    }
}

// Permanently delete archived record
if (isset($_GET['delete_history'])) {
    $history_id = $_GET['delete_history'];
    $conn->query("DELETE FROM stock_out_history WHERE id=$history_id");
    echo "<p class='delete-msg'>Archived record permanently deleted!</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Stock Out Archive</title>
<style>
body { font-family: Arial, sans-serif; margin: 40px; background-color: #f9f9f9; color: #333; }
h2, h3 { color: #444; }
table { width: 100%; border-collapse: collapse; background-color: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0px 0px 10px rgba(0,0,0,0.1); }
table th, table td { padding: 12px; border: 1px solid #ccc; text-align: left; }
table th { background-color: #007bff; color: #fff; }
table tr:hover { background-color: #f1f1f1; }
.success-msg { color: green; font-weight: bold; margin-bottom: 15px; }
.delete-msg { color: red; font-weight: bold; margin-bottom: 15px; }
a { color: #007bff; text-decoration: none; }
a:hover { text-decoration: underline; }
.back-link { display: inline-block; margin-top: 20px; padding: 10px 15px; background-color: #007bff; color: #fff; text-decoration: none; border-radius: 5px; }
.back-link:hover { background-color: #0056b3; }
</style>
</head>
<body>

<h2>Stock Out Archive</h2>

<table>
<tr>
    <th>ID</th>
    <th>Item Name</th>
    <th>Quantity</th>
    <th>Date Released</th>
    <th>Released By</th>
    <th>Deleted At</th>
    <th>Deleted By</th>
    <th>Action</th>
</tr>

<?php
$result = $conn->query("
    SELECT history.*, items.item_name 
    FROM stock_out_history AS history
    JOIN items ON history.item_id = items.id
    ORDER BY history.deleted_at DESC
");

while ($row = $result->fetch_assoc()) {
    echo "<tr>
        <td>{$row['id']}</td>
        <td>{$row['item_name']}</td>
        <td>{$row['quantity']}</td>
        <td>{$row['date_released']}</td>
        <td>{$row['released_by']}</td>
        <td>{$row['deleted_at']}</td>
        <td>{$row['deleted_by']}</td>
        <td>
            <a href='?restore={$row['id']}' onclick='return confirm(\"Restore this record?\")'>Restore</a> | 
            <a href='?delete_history={$row['id']}' onclick='return confirm(\"Permanently delete this record?\")'>Delete</a>
        </td>
    </tr>";
}
?>
</table>

<br>
<a href='stock_out.php' class="back-link">Back to Stock Out</a>

</body>
</html>
