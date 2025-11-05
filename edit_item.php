<?php
include 'config.php';

$id = $_GET['id'];
$result = $conn->query("SELECT * FROM items WHERE id = $id");
$item = $result->fetch_assoc();

if (isset($_POST['update_item'])) {
    $name = $_POST['item_name'];
    $desc = $_POST['description'];
    $qty = $_POST['quantity'];
    $supplier = $_POST['supplier'];

    $stmt = $conn->prepare("UPDATE items SET item_name=?, description=?, quantity=?, supplier=? WHERE id=?");
    $stmt->bind_param("ssisi", $name, $desc, $qty, $supplier, $id);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('Item updated successfully!'); window.location='items.php';</script>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Item</title>
</head>
<body>
    <h2>Edit Item</h2>
    <form method="post">
        <input type="text" name="item_name" value="<?= $item['item_name'] ?>" required><br><br>
        <input type="text" name="description" value="<?= $item['description'] ?>"><br><br>
        <input type="number" name="quantity" value="<?= $item['quantity'] ?>" required><br><br>
        <input type="text" name="supplier" value="<?= $item['supplier'] ?>"><br><br>
        <button type="submit" name="update_item">Update Item</button>
    </form>
</body>
</html>
