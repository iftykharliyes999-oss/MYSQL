<?php $conn = mysqli_connect("localhost", "root", "", "students");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
} ?>

<!DOCTYPE html>
<html>
<head>
    <title>Student List</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

<h2>Student List</h2>

<a href="view.php"> Add New Student</a>

<table>
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Contact</th>
    <th>Email</th>
    <th>Address</th>
    <th>Action</th>
</tr>

<?php
$result = mysqli_query($conn, "SELECT * FROM students_1");

while($row = mysqli_fetch_assoc($result)) {
?>

<tr>
    <td><?= $row['id']; ?></td>
    <td><?= $row['name']; ?></td>
    <td><?= $row['contact']; ?></td>
    <td><?= $row['email']; ?></td>
    <td><?= $row['address']; ?></td>
    <td>
        <a class="edit" href="update.php?id=<?= $row['id']; ?>">Edit</a>
        <a class="delete" href="delete.php?id=<?= $row['id']; ?>" onclick="return confirm('Delete?')">Delete</a>
    </td>
</tr>

<?php } ?>

</table>

</div>

</body>
</html>