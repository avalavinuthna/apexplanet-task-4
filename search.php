<?php

$q = isset($_GET['q'])
    ? trim($_GET['q'])
    : "";

header(
    "Location: books.php?search=" . urlencode($q)
);

exit;

?>
