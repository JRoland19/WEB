<?php
include 'config.php';
session_start();

// Add item
if (isset($_POST['add_item'])) {
    $name = $_POST['item_name'];
    $desc = $_POST['description'];
    $qty = $_POST['quantity'];
    $supplier = $_POST['supplier'];

    $stmt = $conn->prepare("INSERT INTO items (item_name, description, quantity, supplier) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssis", $name, $desc, $qty, $supplier);
    $stmt->execute();
    $stmt->close();

    // Store a session flag to show success message
    $_SESSION['item_added'] = true;

    // Redirect to prevent duplicate submission
    header("Location: items.php");
    exit;
}

// Fetch all items
$result = $conn->query("SELECT * FROM items");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Inventory System</title>
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 40px;
        background-color: #f9f9f9;
        color: #333;
    }
    h3 {
        color: #444;
    }
    form {
        margin-bottom: 20px;
    }
    form input, form button {
        padding: 8px;
        margin-right: 5px;
        margin-bottom: 5px;
        font-size: 14px;
    }
    form button {
        background-color: #28a745;
        color: #fff;
        border: none;
        cursor: pointer;
    }
    form button:hover {
        background-color: #218838;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    table th, table td {
        padding: 8px;
        border: 1px solid #ccc;
    }
    table th {
        background-color: #007bff;
        color: #fff;
    }
    table tr:hover {
        background-color: #f1f1f1;
    }
    .success-msg {
        color: green;
        font-weight: bold;
        margin-bottom: 15px;
    }
    a {
        color: #007bff;
        text-decoration: none;
    }
    a:hover {
        text-decoration: underline;
    }
</style>
</head>
<body>

<h3>Item List</h3>
<h3>Add New Item</h3>

<?php
// Show success message once
if (isset($_SESSION['item_added'])) {
    echo "<p class='success-msg'>Item added successfully!</p>";
    unset($_SESSION['item_added']);
}
?>

<form method="post">
    <input type="text" name="item_name" placeholder="Item name" required>
    <input type="text" name="description" placeholder="Description">
    <input type="number" name="quantity" placeholder="Quantity" required>
    <input type="text" name="supplier" placeholder="Supplier">
    <button type="submit" name="add_item">Add Item</button>
</form>

<table>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Description</th>
        <th>Quantity</th>
        <th>Supplier</th>
        <th>Date Added</th>
        <th>Actions</th>
    </tr>
    <?php while ($item = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $item['id'] ?></td>
        <td><?= $item['item_name'] ?></td>
        <td><?= $item['description'] ?></td>
        <td><?= $item['quantity'] ?></td>
        <td><?= $item['supplier'] ?></td>
        <td><?= $item['date_added'] ?></td>
        <td>
            <a href="edit_item.php?id=<?= $item['id'] ?>">Edit</a> |
            <a href="delete_item.php?id=<?= $item['id'] ?>" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<br>
<a href="dashboard.php">Back to Dashboard</a>

</body>
</html>
