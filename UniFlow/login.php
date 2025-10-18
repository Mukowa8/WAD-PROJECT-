<?php
session_start();
include 'includes/db_connect.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = trim($_POST['email'] ?? '');
  $password = trim($_POST['password'] ?? '');

  if (empty($email) || empty($password)) {
    $message = "⚠️ Both fields are required.";
  } else {
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
      $user = $result->fetch_assoc();
      if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: dashboard.php");
        exit();
      } else {
        $message = "❌ Incorrect password.";
      }
    } else {
      $message = "❌ No account found with that email.";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - UniFlow</title>
  <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>
  <div class="auth-container">
    <h2>Login</h2>
    <?php if (!empty($message)) echo "<p style='color:red; text-align:center;'>$message</p>"; ?>

    <form method="POST" action="">
      <input type="email" name="email" placeholder="Email" required />
      <input type="password" name="password" placeholder="Password" required />
      <button type="submit">Login</button>
    </form>

    <p style="text-align:center; margin-top:10px;">
      Don’t have an account? <a href="register.php">Register</a>
    </p>
  </div>
</body>
</html>