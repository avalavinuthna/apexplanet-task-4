<?php

session_start();

require_once "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: books.php");
    exit;
}

$book_id = intval($_GET['id']);

$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM books WHERE id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $book_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$book = mysqli_fetch_assoc($result);

if (!$book) {
    die("Book not found.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $quantity = intval($_POST['quantity']);

    if ($quantity <= 0) {

        $message = "Invalid quantity.";

    } elseif ($quantity > $book['stock']) {

        $message = "Not enough stock available.";

    } else {

        $user_id = $_SESSION['user_id'];

        $total = $book['price'] * $quantity;

        mysqli_begin_transaction($conn);

        try {

            $order_stmt = mysqli_prepare(
                $conn,
                "INSERT INTO orders
                (user_id, total_amount, status)
                VALUES (?, ?, 'Pending')"
            );

            mysqli_stmt_bind_param(
                $order_stmt,
                "id",
                $user_id,
                $total
            );

            mysqli_stmt_execute($order_stmt);

            $order_id = mysqli_insert_id($conn);

            $item_stmt = mysqli_prepare(
                $conn,
                "INSERT INTO order_items
                (order_id, book_id, quantity, price)
                VALUES (?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $item_stmt,
                "iiid",
                $order_id,
                $book_id,
                $quantity,
                $book['price']
            );

            mysqli_stmt_execute($item_stmt);

            $stock_stmt = mysqli_prepare(
                $conn,
                "UPDATE books
                 SET stock = stock - ?
                 WHERE id = ?"
            );

            mysqli_stmt_bind_param(
                $stock_stmt,
                "ii",
                $quantity,
                $book_id
            );

            mysqli_stmt_execute($stock_stmt);

            mysqli_commit($conn);

            header("Location: orders.php?success=1");
            exit;

        } catch (Exception $e) {

            mysqli_rollback($conn);

            $message = "Order failed.";

        }

    }
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Book Details</title>

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

    <div class="card">

        <h1>
            <?php echo htmlspecialchars($book['title']); ?>
        </h1>

        <?php if ($message != ""): ?>

            <div class="error">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php endif; ?>

        <?php if (!empty($book['image'])): ?>

            <img
                src="uploads/<?php echo htmlspecialchars($book['image']); ?>"
                alt="Book"
            >

        <?php endif; ?>

        <p>
            <strong>Author:</strong>
            <?php echo htmlspecialchars($book['author']); ?>
        </p>

        <p>
            <strong>Category:</strong>
            <?php echo htmlspecialchars($book['category']); ?>
        </p>

        <p>
            <strong>Price:</strong>
            ₹<?php echo number_format($book['price'], 2); ?>
        </p>

        <p>
            <strong>Available Stock:</strong>
            <?php echo $book['stock']; ?>
        </p>

        <p>
            <?php echo nl2br(htmlspecialchars($book['description'])); ?>
        </p>

        <?php if ($book['stock'] > 0): ?>

            <form method="POST">

                <label>Quantity</label>

                <input
                    type="number"
                    name="quantity"
                    min="1"
                    max="<?php echo $book['stock']; ?>"
                    value="1"
                    required
                >

                <button type="submit">
                    Place Order
                </button>

            </form>

        <?php else: ?>

            <div class="warning">
                Out of stock.
            </div>

        <?php endif; ?>

    </div>

</div>

</body>

</html>
