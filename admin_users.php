<?php

session_start();

require_once "db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = intval($_POST['user_id']);
    $role_id = intval($_POST['role_id']);

    if ($user_id == $_SESSION['user_id']) {

        $message = "You cannot change your own role.";

    } elseif ($role_id == 1 || $role_id == 2) {

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE users SET role_id = ? WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ii",
            $role_id,
            $user_id
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);

        $message = "User role updated.";
    }
}

if (isset($_GET['delete'])) {

    $user_id = intval($_GET['delete']);

    if ($user_id != $_SESSION['user_id']) {

        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM users WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $user_id
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);
    }
}

$result = mysqli_query(
    $conn,
    "SELECT
        users.id,
        users.fullname,
        users.email,
        users.role_id,
        roles.role_name,
        users.created_at
     FROM users
     JOIN roles
        ON users.role_id = roles.id
     ORDER BY users.id DESC"
);

?>

<!DOCTYPE html>
<html>

<head>

    <title>Manage Users</title>

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

    <h1>Manage Users</h1>

    <?php if ($message != ""): ?>

        <div class="success">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php endif; ?>

    <table>

        <tr>

            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Created</th>
            <th>Action</th>

        </tr>

        <?php while ($user = mysqli_fetch_assoc($result)): ?>

            <tr>

                <td><?php echo $user['id']; ?></td>

                <td>
                    <?php echo htmlspecialchars($user['fullname']); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($user['email']); ?>
                </td>

                <td>

                    <form method="POST">

                        <input
                            type="hidden"
                            name="user_id"
                            value="<?php echo $user['id']; ?>"
                        >

                        <select name="role_id">

                            <option
                                value="1"
                                <?php
                                if ($user['role_id'] == 1) {
                                    echo "selected";
                                }
                                ?>
                            >
                                Admin
                            </option>

                            <option
                                value="2"
                                <?php
                                if ($user['role_id'] == 2) {
                                    echo "selected";
                                }
                                ?>
                            >
                                User
                            </option>

                        </select>

                        <button type="submit">
                            Update
                        </button>

                    </form>

                </td>

                <td>
                    <?php echo $user['created_at']; ?>
                </td>

                <td>

                    <?php if ($user['id'] != $_SESSION['user_id']): ?>

                        <a
                            class="btn"
                            href="admin_users.php?delete=<?php echo $user['id']; ?>"
                            onclick="return confirm('Delete this user?');"
                        >
                            Delete
                        </a>

                    <?php endif; ?>

                </td>

            </tr>

        <?php endwhile; ?>

    </table>

</div>

</body>

</html>
