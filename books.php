<?php

session_start();

require_once "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$search = isset($_GET['search'])
    ? trim($_GET['search'])
    : "";

$category = isset($_GET['category'])
    ? trim($_GET['category'])
    : "";

$page = isset($_GET['page'])
    ? max(1, intval($_GET['page']))
    : 1;

$limit = 6;

$offset = ($page - 1) * $limit;

$where = "WHERE 1=1";

if ($search != "") {
    $search_safe = mysqli_real_escape_string(
        $conn,
        $search
    );

    $where .= " AND (title LIKE '%$search_safe%'
                OR author LIKE '%$search_safe%')";
}

if ($category != "") {

    $category_safe = mysqli_real_escape_string(
        $conn,
        $category
    );

    $where .= " AND category = '$category_safe'";
}

$count_query = "
    SELECT COUNT(*) AS total
    FROM books
    $where
";

$count_result = mysqli_query(
    $conn,
    $count_query
);

$count_data = mysqli_fetch_assoc($count_result);

$total_books = $count_data['total'];

$total_pages = ceil($total_books / $limit);

$query = "
    SELECT *
    FROM books
    $where
    ORDER BY id DESC
    LIMIT $limit OFFSET $offset
";

$result = mysqli_query($conn, $query);

$categories = mysqli_query(
    $conn,
    "SELECT DISTINCT category
     FROM books
     ORDER BY category"
);

?>

<!DOCTYPE html>
<html>

<head>

    <title>Books</title>

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

    <h1>Books</h1>

    <form method="GET">

        <input
            type="text"
            name="search"
            placeholder="Search title or author"
            value="<?php echo htmlspecialchars($search); ?>"
        >

        <select name="category">

            <option value="">All Categories</option>

            <?php while ($cat = mysqli_fetch_assoc($categories)): ?>

                <option
                    value="<?php echo htmlspecialchars($cat['category']); ?>"
                    <?php
                    if ($category == $cat['category']) {
                        echo "selected";
                    }
                    ?>
                >
                    <?php echo htmlspecialchars($cat['category']); ?>
                </option>

            <?php endwhile; ?>

        </select>

        <button type="submit">
            Search
        </button>

    </form>

    <br>

    <div class="book-grid">

        <?php while ($book = mysqli_fetch_assoc($result)): ?>

            <div class="book-card">

                <?php if (!empty($book['image'])): ?>

                    <img
                        src="uploads/<?php echo htmlspecialchars($book['image']); ?>"
                        alt="Book"
                    >

                <?php endif; ?>

                <h3>
                    <?php echo htmlspecialchars($book['title']); ?>
                </h3>

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
                    <strong>Stock:</strong>
                    <?php echo $book['stock']; ?>
                </p>

                <a
                    class="btn"
                    href="book_details.php?id=<?php echo $book['id']; ?>"
                >
                    View Details
                </a>

            </div>

        <?php endwhile; ?>

    </div>

    <div class="pagination">

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>

            <a href="books.php?search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&page=<?php echo $i; ?>">

                <?php echo $i; ?>

            </a>

        <?php endfor; ?>

    </div>

</div>

</body>

</html>
