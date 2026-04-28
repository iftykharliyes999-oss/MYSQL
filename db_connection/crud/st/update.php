<?php
$conn = mysqli_connect("localhost", "root", "", "students");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// ID check
if(!isset($_GET['id'])){
    die("ID missing");
}

$id = $_GET['id'];

// form submit হলে update করবে
if(isset($_POST['update'])){

    $name = $_POST['name'];
    $contact = $_POST['contact'];
    $email = $_POST['email'];
    $address = $_POST['address'];

    $query = "UPDATE students_1 SET 
        name='$name',
        contact='$contact',
        email='$email',
        address='$address'
        WHERE id=$id";

    if(mysqli_query($conn, $query)){
        header("Location: main.php");
        exit();
    } else {
        echo "Update Failed";
    }
}

$result = mysqli_query($conn, "SELECT * FROM students_1 WHERE id=$id");
$row = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="container">

<h2>Update Student</h2>

<form method="POST" class="form">
    <input type="text" name="name" value="<?= $row['name']; ?>" required>
    <input type="text" name="contact" value="<?= $row['contact']; ?>" required>
    <input type="text" name="email" value="<?= $row['email']; ?>" required>
    <input type="text" name="address" value="<?= $row['address']; ?>" required>

    <button type="submit" name="update">Update</button>
</form>

</div>

</body>
</html>