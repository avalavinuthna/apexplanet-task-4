<?php

session_start();

require_once "db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: login.php");
    exit;
}

$users = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM users"
    )
)['total'];

$books = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM books"
    )
)['total'];

$orders = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM orders"
    )
)['total'];

$revenue = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COALESCE(SUM(total_amount),0) AS total
         FROM orders
         WHERE status != 'Cancelled'"
    )
)['total'];

$active = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM users
         WHERE last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
    )
)['total'];

$daily = mysqli_query(
    $conn,
    "SELECT
        DATE(created_at) AS order_date,
        COUNT(*) AS total_orders
     FROM orders
     GROUP BY DATE(created_at)
     ORDER BY order_date DESC
     LIMIT 10"
);

?>

<!DOCTYPE html>
<html>

<head>

    <title>Admin Dashboard</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<div class="navbar">

    <a href="admin.php">Admin Dashboard</a>
    <a href="admin_users.php">Users</a>
    <a href="admin_books.php">Books</a>
    <a href="admin_orders.php">Orders</a>
    <a href="logout.php">Logout</a>

</div>

<div class="container">

    <h1>Admin Dashboard</h1>

    <div class="dashboard-box">
        <h2><?php echo $users; ?></h2>
        <p>Total Users</p>
    </div>

    <div class="dashboard-box">
        <h2><?php echo $books; ?></h2>
        <p>Total Books</p>
    </div>

    <div class="dashboard-box">
        <h2><?php echo $orders; ?></h2>
        <p>Total Orders</p>
    </div>

    <div class="dashboard-box">
        <h2>₹<?php echo number_format($revenue, 2); ?></h2>
        <p>Revenue</p>
    </div>

    <div class="dashboard-box">
        <h2><?php echo $active; ?></h2>
        <p>Active Users</p>
    </div>

    <h2>Orders Per Day</h2>

    <table>

        <tr>
            <th>Date</th>
            <th>Total Orders</th>
        </tr>

        <?php while ($row = mysqli_fetch_assoc($daily)): ?>

            <tr>
                <td>
                    <?php echo $row['order_date']; ?>
                </td>

                <td>
                    <?php echo $row['total_orders']; ?>
                </td>
            </tr>

        <?php endwhile; ?>

    </table>

</div>

</body>

</html>
