<?php
include 'config.php';

$id = $_GET['id'];

$conn->query("DELETE FROM items WHERE id = $id");

echo "<script>alert('Item deleted successfully!'); window.location='items.php';</script>";
?>
