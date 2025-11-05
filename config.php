<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "warehouse_db";

// connect to MySQL
$conn = new mysqli($servername, $username, $password, $database);

// check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// handle form submission (AFTER connection is made)
if (isset($_POST['add_item'])) {
    $name = $_POST['item_name'];
    $desc = $_POST['description'];
    $qty = $_POST['quantity'];
    $supplier = $_POST['supplier'];

    $stmt = $conn->prepare("INSERT INTO items (item_name, description, quantity, supplier) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssis", $name, $desc, $qty, $supplier);
    $stmt->execute();
    $stmt->close();

    echo "<p style='color:green;'>Item added successfully!</p>";
}
?>
