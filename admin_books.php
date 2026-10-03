<?php

session_start();

require_once "db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: login.php");
    exit;
}

$result = mysqli_query(
    $conn,
    "SELECT * FROM books ORDER BY id DESC"
);

?>

<!DOCTYPE html>
<html>

<head>

    <title>Manage Books</title>

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

    <h1>Manage Books</h1>

    <a class="btn" href="add_book.php">
        Add New Book
    </a>

    <br><br>

    <table>

        <tr>

            <th>ID</th>
            <th>Image</th>
            <th>Title</th>
            <th>Author</th>
            <th>Category</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Actions</th>

        </tr>

        <?php while ($book = mysqli_fetch_assoc($result)): ?>

            <tr>

                <td><?php echo $book['id']; ?></td>

                <td>

                    <?php if (!empty($book['image'])): ?>

                        <img
                            src="uploads/<?php echo htmlspecialchars($book['image']); ?>"
                            width="80"
                        >

                    <?php endif; ?>

                </td>

                <td>
                    <?php echo htmlspecialchars($book['title']); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($book['author']); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($book['category']); ?>
                </td>

                <td>
                    ₹<?php echo number_format($book['price'], 2); ?>
                </td>

                <td>
                    <?php echo $book['stock']; ?>
                </td>

                <td>

                    <a
                        class="btn"
                        href="edit_book.php?id=<?php echo $book['id']; ?>"
                    >
                        Edit
                    </a>

                    <a
                        class="btn"
                        href="delete_book.php?id=<?php echo $book['id']; ?>"
                        onclick="return confirm('Delete this book?');"
                    >
                        Delete
                    </a>

                </td>

            </tr>

        <?php endwhile; ?>

    </table>

</div>

</body>

</html>
