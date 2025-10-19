<?php
session_start();
include 'includes/db_connect.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = trim($_POST['username'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = trim($_POST['password'] ?? '');

  if (empty($username) || empty($email) || empty($password)) {
    $message = "⚠️ All fields are required.";
  } else {
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
      $message = "❌ Email already registered!";
    } else {
      $hashed = password_hash($password, PASSWORD_DEFAULT);
      $insert = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
      $insert->bind_param("sss", $username, $email, $hashed);

      if ($insert->execute()) {
        $message = "✅ Registration successful! You can now log in.";
      } else {
        $message = "❌ Error during registration.";
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register - UniFlow</title>
  <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>
  <div class="auth-container">
    <h2>Create an Account</h2>
    <?php if (!empty($message)) echo "<p style='color:red; text-align:center;'>$message</p>"; ?>

    <form method="POST" action="">
      <input type="text" name="username" placeholder="Username" required />
      <input type="email" name="email" placeholder="Email" required />
      <input type="password" name="password" placeholder="Password" required />
      <button type="submit">Register</button>
    </form>

    <p style="text-align:center; margin-top:10px;">
      Already have an account? <a href="login.php">Login</a>
    </p>
  </div>
</body>
</html>