<?php
session_start();
if (!isset($_SESSION["name"])) {
    header("Location: index.php");
    exit;
}

include("config.php"); // ✅ connect to database

// Get total items
$item_result = $conn->query("SELECT COUNT(*) AS total_items FROM items");
$item_data = $item_result->fetch_assoc();
$total_items = $item_data['total_items'];

// Get total suppliers
$supplier_result = $conn->query("SELECT COUNT(*) AS total_suppliers FROM suppliers");
$supplier_data = $supplier_result->fetch_assoc();
$total_suppliers = $supplier_data['total_suppliers'];

// Get total quantity of all stock
$stock_result = $conn->query("SELECT SUM(quantity) AS total_stock FROM items");
$stock_data = $stock_result->fetch_assoc();
$total_stock = $stock_data['total_stock'];

// Get total stock in
$stock_in_result = $conn->query("SELECT SUM(quantity) AS total_in FROM stock_in");
$stock_in_data = $stock_in_result->fetch_assoc();
$total_in = $stock_in_data['total_in'] ?? 0;

// Get total stock out
$stock_out_result = $conn->query("SELECT SUM(quantity) AS total_out FROM stock_out");
$stock_out_data = $stock_out_result->fetch_assoc();
$total_out = $stock_out_data['total_out'] ?? 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f0f2f5;
        }
        h2, h3 {
            color: #333;
        }
        a {
            text-decoration: none;
            color: inherit;
        }
        .dashboard {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }
        .summary-box {
            flex: 1 1 180px;
            background-color: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .summary-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
            background-color: #e0f7fa;
        }
        .summary-box h4 {
            margin-bottom: 10px;
            font-size: 18px;
            color: #00796b;
        }
        .summary-box p {
            font-size: 24px;
            font-weight: bold;
            color: #004d40;
        }
    </style>
</head>
<body>
    <h2>Welcome, <?php echo $_SESSION["name"]; ?>!</h2>
    <p>Your role is: <?php echo $_SESSION["role"]; ?></p>
    <a href="logout.php">Logout</a>

    <h3>📊 Warehouse Summary</h3>
    <div class="dashboard">
        <a href="items.php">
            <div class="summary-box">
                <h4>Total Items</h4>
                <p><?php echo $total_items; ?></p>
            </div>
        </a>

        <a href="suppliers.php">
            <div class="summary-box">
                <h4>Total Suppliers</h4>
                <p><?php echo $total_suppliers; ?></p>
            </div>
        </a>

        <a href="stock.php">
            <div class="summary-box">
                <h4>Total Stock Quantity</h4>
                <p><?php echo $total_stock; ?></p>
            </div>
        </a>

        <a href="stock_in.php">
            <div class="summary-box">
                <h4>Total Stock In</h4>
                <p><?php echo $total_in; ?></p>
            </div>
        </a>

        <a href="stock_out.php">
            <div class="summary-box">
                <h4>Total Stock Out</h4>
                <p><?php echo $total_out; ?></p>
            </div>
        </a>
    </div>
</body>
</html>
