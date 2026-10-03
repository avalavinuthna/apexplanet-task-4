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
    "SELECT image FROM books WHERE id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$book = mysqli_fetch_assoc($result);

if ($book) {

    $delete = mysqli_prepare(
        $conn,
        "DELETE FROM books WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $delete,
        "i",
        $id
    );

    if (mysqli_stmt_execute($delete)) {

        if (
            !empty($book['image']) &&
            file_exists("uploads/" . $book['image'])
        ) {
            unlink("uploads/" . $book['image']);
        }
    }

    mysqli_stmt_close($delete);
}

mysqli_stmt_close($stmt);

header("Location: admin_books.php");

exit;

?>
