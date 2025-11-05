<?php
session_start();
if (!isset($_SESSION["name"])) {
    header("Location: index.php");
    exit;
}

include("config.php");

// Fetch all items with current stock
$sql = "
    SELECT i.id, i.item_name, i.quantity AS current_stock,
           IFNULL(SUM(si.quantity),0) AS total_in,
           IFNULL(SUM(so.quantity),0) AS total_out
    FROM items i
    LEFT JOIN stock_in si ON i.id = si.item_id
    LEFT JOIN stock_out so ON i.id = so.item_id
    GROUP BY i.id
    ORDER BY i.item_name ASC
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Stock Overview</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f0f2f5;
        }
        h2 {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px 15px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #00796b;
            color: #fff;
        }
        tr:hover {
            background-color: #e0f7fa;
        }
    </style>
</head>
<body>
    <h2>📦 Stock Overview</h2>
    <a href="dashboard.php">← Back to Dashboard</a>

    <table>
        <thead>
            <tr>
                <th>Item Name</th>
                <th>Current Stock</th>
                <th>Total Stock In</th>
                <th>Total Stock Out</th>
            </tr>
        </thead>
        <tbody>
            <?php if($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                        <td><?php echo $row['current_stock']; ?></td>
                        <td><?php echo $row['total_in']; ?></td>
                        <td><?php echo $row['total_out']; ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">No items found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
