<!DOCTYPE html>
<html>
<head>
    <title>Add Student</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
<h2>Add Student</h2>

<form action="insert.php" method="POST" class="form">
    <input type="text" name="name" placeholder="Name" required>
    <input type="text" name="contact" placeholder="Contact" required>
    <input type="text" name="email" placeholder="Email" required>
    <input type="text" name="address" placeholder="Address" required>

    <button type="submit">Add Student</button>
</form>

</div>

</body>
</html>