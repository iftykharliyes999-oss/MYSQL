<?php
$db = new mysqli("localhost", "root", "", "crud70_php");

if(isset($_POST['submit'])){

    $name = $_POST['name'];
    $contact = $_POST['contact'];
    $email = $_POST['email'];

    $db->query("INSERT INTO users(name, contact, email) 
                VALUES('$name','$contact','$email')");

    header("Location: view.php");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Insert User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">

<h2>Insert User</h2>

<form method="post">

<input type="text" name="name" class="form-control mb-2" placeholder="Name">
<input type="text" name="contact" class="form-control mb-2" placeholder="Contact">
<input type="text" name="email" class="form-control mb-2" placeholder="Email">

<input type="submit" name="submit" value="Submit" class="btn btn-primary">

</form>

</div>

</body>
</html>