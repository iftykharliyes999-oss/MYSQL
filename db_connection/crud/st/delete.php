<?php
$conn = mysqli_connect("localhost", "root", "", "students");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if(isset($_GET['id'])){
    $id = $_GET['id'];
    mysqli_query($conn, "DELETE FROM students_1 WHERE id=$id");
}

header("Location: main.php");
?>