<?php
$conn = mysqli_connect("localhost", "root", "", "students");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$name = $_POST['name'];
$contact = $_POST['contact'];
$email = $_POST['email'];
$address = $_POST['address'];

mysqli_query($conn, "INSERT INTO students_1 (name, contact, email, address)
VALUES ('$name','$contact','$email','$address')");

header("Location: main.php"); // 🔥 important change
?>