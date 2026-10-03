<?php

session_start();

require_once "db.php";

$message = "";

if (isset($_GET['registered'])) {
    $message = "Registration successful. Please login.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, fullname, password, role_id
         FROM users
         WHERE email = ?"
    );

    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {

        if (password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user'] = $user['fullname'];
            $_SESSION['role_id'] = $user['role_id'];

            $update = mysqli_prepare(
                $conn,
                "UPDATE users SET last_login = NOW() WHERE id = ?"
            );

            mysqli_stmt_bind_param(
                $update,
                "i",
                $user['id']
            );

            mysqli_stmt_execute($update);
            mysqli_stmt_close($update);

            if ($user['role_id'] == 1) {
                header("Location: admin.php");
            } else {
                header("Location: dashboard.php");
            }

            exit;

        } else {
            $message = "Invalid email or password.";
        }

    } else {
        $message = "Invalid email or password.";
    }

    mysqli_stmt_close($stmt);
}

?>

<!DOCTYPE html>
<html>
<head>

    <title>Login - Task 4</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<div class="container">

    <div class="card">

        <h2>Login</h2>

        <?php if ($message != ""): ?>

            <div class="success">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php endif; ?>

        <form method="POST">

            <label>Email</label>

            <input
                type="email"
                name="email"
                required
            >

            <label>Password</label>

            <input
                type="password"
                name="password"
                required
            >

            <button type="submit">
                Login
            </button>

        </form>

        <p>
            Don't have an account?
            <a href="register.php">Register</a>
        </p>

    </div>

</div>

</body>
</html>
