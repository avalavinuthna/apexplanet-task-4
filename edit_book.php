<?php

session_start();

require_once "db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: admin_books.php");
    exit;
}

$id = intval($_GET['id']);

$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM books WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$book = mysqli_fetch_assoc($result);

if (!$book) {
    die("Book not found.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $category = trim($_POST['category']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $description = trim($_POST['description']);

    $image = $book['image'];

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

            $new_image = uniqid("book_") . "." . $extension;

            if (!empty($image) && file_exists("uploads/" . $image)) {
                unlink("uploads/" . $image);
            }

            move_uploaded_file(
                $_FILES['image']['tmp_name'],
                "uploads/" . $new_image
            );

            $image = $new_image;

        } else {

            $message = "Only JPG/PNG images up to 2MB are allowed.";

        }
    }

    if ($message == "") {

        $update = mysqli_prepare(
            $conn,
            "UPDATE books
             SET title = ?,
                 author = ?,
                 category = ?,
                 price = ?,
                 stock = ?,
                 image = ?,
                 description = ?
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $update,
            "sssdissi",
            $title,
            $author,
            $category,
            $price,
            $stock,
            $image,
            $description,
            $id
        );

        if (mysqli_stmt_execute($update)) {

            header("Location: admin_books.php");
            exit;

        } else {

            $message = "Failed to update book.";

        }

        mysqli_stmt_close($update);
    }
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Edit Book</title>

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

        <h1>Edit Book</h1>

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

            <input
                type="text"
                name="title"
                value="<?php echo htmlspecialchars($book['title']); ?>"
                required
            >

            <label>Author</label>

            <input
                type="text"
                name="author"
                value="<?php echo htmlspecialchars($book['author']); ?>"
                required
            >

            <label>Category</label>

            <input
                type="text"
                name="category"
                value="<?php echo htmlspecialchars($book['category']); ?>"
                required
            >

            <label>Price</label>

            <input
                type="number"
                name="price"
                step="0.01"
                value="<?php echo $book['price']; ?>"
                required
            >

            <label>Stock</label>

            <input
                type="number"
                name="stock"
                value="<?php echo $book['stock']; ?>"
                required
            >

            <label>Current Image</label>

            <?php if (!empty($book['image'])): ?>

                <br>

                <img
                    src="uploads/<?php echo htmlspecialchars($book['image']); ?>"
                    width="150"
                >

                <br><br>

            <?php endif; ?>

            <label>New Image</label>

            <input
                type="file"
                name="image"
                accept=".jpg,.jpeg,.png"
            >

            <label>Description</label>

            <textarea
                name="description"
                rows="5"
            ><?php echo htmlspecialchars($book['description']); ?></textarea>

            <button type="submit">
                Update Book
            </button>

        </form>

    </div>

</div>

</body>

</html>
