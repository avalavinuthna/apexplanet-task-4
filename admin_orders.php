<?php

session_start();

require_once "db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $order_id = intval($_POST['order_id']);
    $status = $_POST['status'];

    $allowed_status = [
        "Pending",
        "Processing",
        "Completed",
        "Cancelled"
    ];

    if (in_array($status, $allowed_status)) {

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE orders
             SET status = ?
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "si",
            $status,
            $order_id
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);
    }
}

$result = mysqli_query(
    $conn,
    "SELECT
        o.id,
        o.total_amount,
        o.status,
        o.created_at,
        u.fullname,
        u.email
     FROM orders o
     JOIN users u
        ON o.user_id = u.id
     ORDER BY o.id DESC"
);

?>

<!DOCTYPE html>
<html>

<head>

    <title>Manage Orders</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<div class="navbar">

    <a href="admin.php">Dashboard</a>
    <a href="admin_users.php">Users</a>
    <a href="admin_books.php">Books</a>
    <a href="admin_orders.php">Orders</a>
    <a href="logout.php">Logout</a>

</div>

<div class="container">

    <h1>Manage Orders</h1>

    <table>

        <tr>

            <th>ID</th>
            <th>User</th>
            <th>Email</th>
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
                    <?php echo htmlspecialchars($order['fullname']); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($order['email']); ?>
                </td>

                <td>
                    ₹<?php echo number_format(
                        $order['total_amount'],
                        2
                    ); ?>
                </td>

                <td>

                    <form method="POST">

                        <input
                            type="hidden"
                            name="order_id"
                            value="<?php echo $order['id']; ?>"
                        >

                        <select name="status">

                            <?php

                            $statuses = [
                                "Pending",
                                "Processing",
                                "Completed",
                                "Cancelled"
                            ];

                            foreach ($statuses as $status):

                            ?>

                                <option
                                    value="<?php echo $status; ?>"
                                    <?php
                                    if ($order['status'] == $status) {
                                        echo "selected";
                                    }
                                    ?>
                                >
                                    <?php echo $status; ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                        <button type="submit">
                            Update
                        </button>

                    </form>

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
