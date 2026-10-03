<?php

session_start();

require_once "db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $category = trim($_POST['category']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $description = trim($_POST['description']);

    $image = "";

    if (
        isset($_FILES['image']) &&
        $_FILES['image']['error'] == 0
    ) {

        $allowed = [
            "image/jpeg",
            "image/png"
        ];

        if (
            in_array(
                $_FILES['image']['type'],
                $allowed
            ) &&
            $_FILES['image']['size'] <= 2 * 1024 * 1024
        ) {

            $extension = pathinfo(
                $_FILES['image']['name'],
                PATHINFO_EXTENSION
            );

            $image = uniqid("book_") . "." . $extension;

            move_uploaded_file(
                $_FILES['image']['tmp_name'],
                "uploads/" . $image
            );

        } else {

            $message = "Only JPG/PNG images up to 2MB are allowed.";

        }
    }

    if ($message == "") {

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO books
            (title, author, category, price, stock, image, description)
            VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sssdiss",
            $title,
            $author,
            $category,
            $price,
            $stock,
            $image,
            $description
        );

        if (mysqli_stmt_execute($stmt)) {

            header("Location: admin_books.php");
            exit;

        } else {

            $message = "Failed to add book.";

        }

        mysqli_stmt_close($stmt);
    }
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Add Book</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<div class="navbar">

    <a href="admin.php">Dashboard</a>
    <a href="admin_books.php">Books</a>
    <a href="admin_orders.php">Orders</a>
    <a href="logout.php">Logout</a>

</div>

<div class="container">

    <div class="card">

        <h1>Add Book</h1>

        <?php if ($message != ""): ?>

            <div class="error">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php endif; ?>

        <form
            method="POST"
            enctype="multipart/form-data"
        >

            <label>Title</label>
            <input type="text" name="title" required>

            <label>Author</label>
            <input type="text" name="author" required>

            <label>Category</label>
            <input type="text" name="category" required>

            <label>Price</label>
            <input
                type="number"
                name="price"
                step="0.01"
                min="0"
                required
            >

            <label>Stock</label>
            <input
                type="number"
                name="stock"
                min="0"
                required
            >

            <label>Book Image</label>
            <input
                type="file"
                name="image"
                accept=".jpg,.jpeg,.png"
            >

            <label>Description</label>

            <textarea
                name="description"
                rows="5"
            ></textarea>

            <button type="submit">
                Add Book
            </button>

        </form>

    </div>

</div>

</body>

</html>
