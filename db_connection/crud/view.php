<?php
$db = new mysqli("localhost", "root", "", "crud70_php");

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

/* DELETE */
if(isset($_GET['deleteid'])){
    $id = $_GET['deleteid'];
    $db->query("DELETE FROM users WHERE id=$id");
    header("Location: view.php");
}

$u = $db->query("SELECT * FROM users");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Users Data</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">

<a href="insert.php" class="btn btn-success mb-3">+ Add New</a>

<h2 class="mb-4">Users List</h2>

<table class="table table-bordered table-striped">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Contact</th>
            <th>Email</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>

    <?php
    while($row = $u->fetch_assoc()) {
    ?>
        <tr>
            <td><?= $row['id']; ?></td>
            <td><?= $row['name']; ?></td>

            <!-- FIX HERE -->
            <td><?= $row['Contact'] ?? '' ?></td>

            <td><?= $row['email']; ?></td>

            <td>
                <a href="edit.php?id=<?= $row['id']; ?>" class="btn btn-warning btn-sm">
    Edit
</a>

                <a href="view.php?deleteid=<?= $row['id']; ?>" 
                   class="btn btn-danger btn-sm"
                   onclick="return confirm('Delete?')">
                   Delete
                </a>
            </td>
        </tr>
    <?php } ?>

    </tbody>
</table>

</div>

</body>
</html>

<?php $db->close(); ?>