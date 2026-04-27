<?php
$db = new mysqli("localhost", "root", "", "crud70_php");

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

/* ID check */
$id = $_GET['id'];

/* old data fetch */
$result = $db->query("SELECT * FROM users WHERE id=$id");
$data = $result->fetch_assoc();

/* update process */
if(isset($_POST['update'])){

    $name = $_POST['name'];
    $Contact = $_POST['Contact'];
    $email = $_POST['email'];

    $db->query("UPDATE users SET 
        name='$name',
        Contact='$Contact',
        email='$email'
        WHERE id=$id
    ");

    header("Location: view.php");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">

<h2>Edit User</h2>

<form method="post">

<label>Name</label>
<input type="text" name="name" class="form-control mb-2"
value="<?= $data['name'] ?? '' ?>">

<label>Contact</label>
<input type="text" name="Contact" class="form-control mb-2"
value="<?= $data['Contact'] ?? '' ?>">

<label>Email</label>
<input type="text" name="email" class="form-control mb-2"
value="<?= $data['email'] ?? '' ?>">

<input type="submit" name="update" value="Save Changes" class="btn btn-primary">

</form>

</div>

</body>
</html>