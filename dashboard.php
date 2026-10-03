<?php

session_start();

require_once "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$book_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM books"
);

$book_data = mysqli_fetch_assoc($book_result);

$total_books = $book_data['total'];

$stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM orders
     WHERE user_id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$order_data = mysqli_fetch_assoc($result);

$total_orders = $order_data['total'];

?>

<!DOCTYPE html>
<html>

<head>

    <title>User Dashboard</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<div class="navbar">

    <a href="dashboard.php">Dashboard</a>

    <a href="books.php">Books</a>

    <a href="orders.php">My Orders</a>

    <a href="logout.php">Logout</a>

</div>

<div class="container">

    <h1>
        Welcome,
        <?php echo htmlspecialchars($_SESSION['user']); ?>
    </h1>

    <div class="dashboard-box">

        <h2><?php echo $total_books; ?></h2>

        <p>Total Books</p>

    </div>

    <div class="dashboard-box">

        <h2><?php echo $total_orders; ?></h2>

        <p>My Orders</p>

    </div>

    <br><br>

    <a class="btn" href="books.php">
        Browse Books
    </a>

</div>

</body>

</html>
