<?php
include "config.php"; // connects to the database
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];
    $role = $_POST["role"]; // get role from the form

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE email=? AND password=?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION["name"] = $row["name"];
        $_SESSION["role"] = $role; // store the role user picked in session
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid email or password.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Login Page</title>
</head>
<body>
  <h2>Login</h2>
  <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
  <form method="post">
    Email:<br>
    <input type="text" name="email" required><br><br>
    
    Password:<br>
    <input type="password" name="password" required><br><br>
    
    Role:<br>
    <select name="role" required>
      <option value="">--Select Role--</option>
      <option value="Admin">Admin</option>
      <option value="Staff">Staff</option>
    </select><br><br>
    
    <button type="submit">Login</button>
  </form>
</body>
</html>
