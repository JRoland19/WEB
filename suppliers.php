<?php
session_start();
include("config.php");

// Add supplier
if (isset($_POST['add_supplier'])) {
  $name = $_POST['name'];
  $contact_person = $_POST['contact_person'];
  $phone = $_POST['phone'];
  $address = $_POST['address'];

  $stmt = $conn->prepare("INSERT INTO suppliers (name, contact_person, phone, address) VALUES (?, ?, ?, ?)");
  $stmt->bind_param("ssss", $name, $contact_person, $phone, $address);
  $stmt->execute();
  $stmt->close();

  echo "<p class='success-msg'>Supplier added successfully!</p>";
}

// Delete supplier
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Delete the supplier
    $conn->query("DELETE FROM suppliers WHERE id=$id");

    // Resequence IDs
    $conn->query("SET @count = 0");
    $conn->query("UPDATE suppliers SET id = @count:= @count + 1 ORDER BY id");

    // Reset AUTO_INCREMENT to next number
    $result = $conn->query("SELECT COUNT(*) AS total FROM suppliers");
    $total = $result->fetch_assoc()['total'];
    $conn->query("ALTER TABLE suppliers AUTO_INCREMENT = " . ($total + 1));

    echo "<p class='delete-msg'>Supplier deleted successfully!</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Supplier Management</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 40px;
      background-color: #f9f9f9;
      color: #333;
    }
    h2, h3 {
      color: #444;
    }
    form {
      background-color: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
      margin-bottom: 30px;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }
    form input, form textarea, form button {
      padding: 10px;
      font-size: 14px;
      border-radius: 4px;
      border: 1px solid #ccc;
    }
    form textarea {
      resize: vertical;
    }
    form button {
      background-color: #28a745;
      color: #fff;
      border: none;
      cursor: pointer;
      max-width: 150px;
    }
    form button:hover {
      background-color: #218838;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background-color: #fff;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
    }
    table th, table td {
      padding: 12px;
      border: 1px solid #ccc;
      text-align: left;
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
    }
    .delete-msg {
      color: red;
      font-weight: bold;
    }
    a {
      color: #007bff;
      text-decoration: none;
    }
    a:hover {
      text-decoration: underline;
    }
    .back-link {
      display: inline-block;
      margin-top: 20px;
      padding: 10px 15px;
      background-color: #007bff;
      color: #fff;
      text-decoration: none;
      border-radius: 5px;
    }
    .back-link:hover {
      background-color: #0056b3;
    }
  </style>
</head>
<body>

<h2>Supplier Management</h2>

<form method="POST">
  <label>Supplier Name:</label>
  <input type="text" name="name" required>

  <label>Contact Person:</label>
  <input type="text" name="contact_person">

  <label>Phone:</label>
  <input type="text" name="phone">

  <label>Address:</label>
  <textarea name="address"></textarea>

  <button type="submit" name="add_supplier">Add Supplier</button>
</form>

<hr>

<h3>Supplier List</h3>
<table>
  <tr>
    <th>ID</th>
    <th>Name</th>
    <th>Contact Person</th>
    <th>Phone</th>
    <th>Address</th>
    <th>Action</th>
  </tr>
  <?php
  $result = $conn->query("SELECT * FROM suppliers");
  while ($row = $result->fetch_assoc()) {
    echo "<tr>
      <td>{$row['id']}</td>
      <td>{$row['name']}</td>
      <td>{$row['contact_person']}</td>
      <td>{$row['phone']}</td>
      <td>{$row['address']}</td>
      <td><a href='?delete={$row['id']}' onclick='return confirm(\"Are you sure you want to delete this supplier?\")'>Delete</a></td>
    </tr>";
  }
  ?>
</table>

<br>
<a href='dashboard.php' class="back-link">Back to Dashboard</a>

</body>
</html>
