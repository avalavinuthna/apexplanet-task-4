<?php

session_start();

require_once "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        o.id,
        o.total_amount,
        o.status,
        o.created_at,
        GROUP_CONCAT(
            CONCAT(b.title, ' x ', oi.quantity)
            SEPARATOR ', '
        ) AS items
     FROM orders o
     JOIN order_items oi
        ON o.id = oi.order_id
     JOIN books b
        ON oi.book_id = b.id
     WHERE o.user_id = ?
     GROUP BY o.id
     ORDER BY o.id DESC"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>
<html>

<head>

    <title>My Orders</title>

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

    <h1>My Orders</h1>

    <?php if (isset($_GET['success'])): ?>

        <div class="success">
            Order placed successfully!
        </div>

    <?php endif; ?>

    <table>

        <tr>
            <th>Order ID</th>
            <th>Items</th>
            <th>Total</th>
            <th>Status</th>
            <th>Date</th>
        </tr>

        <?php while ($order = mysqli_fetch_assoc($result)): ?>

            <tr>

                <td>
                    <?php echo $order['id']; ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($order['items']); ?>
                </td>

                <td>
                    ₹<?php echo number_format(
                        $order['total_amount'],
                        2
                    ); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($order['status']); ?>
                </td>

                <td>
                    <?php echo $order['created_at']; ?>
                </td>

            </tr>

        <?php endwhile; ?>

    </table>

</div>

</body>

</html>
