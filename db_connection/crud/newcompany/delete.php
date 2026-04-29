<?php $conn = mysqli_connect("localhost", "root", "", "shop_db");

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

$id=$_GET['id'];

mysqli_query($conn,"DELETE FROM products WHERE id=$id");

header("Location: product_list.php");
?>