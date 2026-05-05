<?php
$conn = new mysqli("localhost", "root", "", "mid");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Manu_insert (Procedure)
if (isset($_POST['m_submit'])) {
    $name = $_POST['m_name'];
    $address = $_POST['m_address'];
    $contact = $_POST['m_contact'];

    $stmt = $conn->prepare("CALL insert_manufacturer(?, ?, ?)");
    $stmt->bind_param("sss", $name, $address, $contact);
    $stmt->execute();
    $stmt->close();
}

// Product_insert (Procedure)
if (isset($_POST['p_submit'])) {
    $pname = $_POST['p_name'];
    $price = $_POST['price'];
    $mid = $_POST['manufacturer_id'];

    $stmt = $conn->prepare("CALL insert_product(?, ?, ?)");
    $stmt->bind_param("sii", $pname, $price, $mid);
    $stmt->execute();
    $stmt->close();
}

// Delete Manufacturer
if (isset($_POST['delete'])) {
    $mid = $_POST['delete_id'];

    $conn->query("DELETE FROM manufacturer WHERE id = $mid");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>mid</title>
</head>
<body>

<h2>Manufacturer Form</h2>
<form method="POST">
    Name: <input type="text" name="m_name" required><br><br>
    Address: <input type="text" name="m_address" required><br><br>
    Contact: <input type="text" name="m_contact" required><br><br>
    <input type="submit" name="m_submit" value="Insert Manufacturer">
</form>

<hr>

<h2>Product Form</h2>
<form method="POST">
    Name: <input type="text" name="p_name" required><br><br>
    Price: <input type="number" name="price" required><br><br>

    Manufacturer:
    <select name="manufacturer_id">
        <?php
        $res = $conn->query("SELECT * FROM manufacturer");
        while ($row = $res->fetch_assoc()) {
            echo "<option value='".$row['id']."'>".$row['name']."</option>";
        }
        ?>
    </select><br><br>

    <input type="submit" name="p_submit" value="Insert Product">
</form>

<hr>

<h2>Delete Manufacturer</h2>
<form method="POST">
    <select name="delete_id">
        <?php
        $res = $conn->query("SELECT * FROM manufacturer");
        while ($row = $res->fetch_assoc()) {
            echo "<option value='".$row['id']."'>".$row['name']."</option>";
        }
        ?>
    </select>

    <input type="submit" name="delete" value="Delete Manufacturer">
</form>

<hr>

<h2>Products</h2>

<table border="1">
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Price</th>
    <th>Manufacturer ID</th>
</tr>

<?php
$result = $conn->query("SELECT * FROM product_view");

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>".$row['id']."</td>
                <td>".$row['name']."</td>
                <td>".$row['price']."</td>
                <td>".$row['manufacturer_id']."</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='4'>No Data Found</td></tr>";
}
?>

</table>

</body>
</html>